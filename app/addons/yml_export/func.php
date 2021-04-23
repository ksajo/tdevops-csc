<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

if (!defined('BOOTSTRAP')) { die('Access denied'); }

use Tygh\Addons\ProductVariations\ServiceProvider as ProductVariationsServiceProvider;
use Tygh\Registry;
use Tygh\Settings;
use Tygh\Languages\Languages;

/**
 * Hooks
 */

function fn_yml_export_tools_change_status(&$params, &$result)
{
    if ($result && !empty($params['table']) && in_array($params['table'], array('categories'))) {
        fn_yml_update_categories_price_lists();
    }
}

function fn_yml_export_get_product_data_post(&$product_data, $auth, $preview, $lang_code)
{
    if (!empty($product_data['yml2_delivery_options'])) {
        $product_data['yml2_delivery_options'] = unserialize($product_data['yml2_delivery_options']);
    } else {
        $product_data['yml2_delivery_options'] = array();
    }

    if (empty($product_data['yml2_market_category'])) {
        $product_data['yml2_market_category'] = fn_yml_get_parent_categories_field('yml2_market_category', $product_data['main_category'], true);
    }
}

function fn_yml_export_update_product_pre(&$product_data, $product_id, $lang_code, $can_update)
{
    if (!empty($product_data['yml2_delivery_options'])) {
        foreach($product_data['yml2_delivery_options'] as $index => $delivery_option) {
            if (fn_is_empty($delivery_option)) {
                unset($product_data['yml2_delivery_options'][$index]);
            }
        }

        $product_data['yml2_delivery_options'] = array_slice($product_data['yml2_delivery_options'], 0, 5);
        $product_data['yml2_delivery_options'] = serialize($product_data['yml2_delivery_options']);
    }

    if (isset($product_data['yml2_exclude_price_ids'])) {
        $exclude_price_ids = !empty($product_data['yml2_exclude_price_ids']) ? $product_data['yml2_exclude_price_ids'] : array();
        $old_price_ids = db_get_fields(
            'SELECT price_id FROM ?:yml_exclude_objects WHERE object_id = ?i AND object_type = ?s',
            $product_id, 'product'
        );

        foreach($exclude_price_ids as $price_id) {
            $data = array(
                'price_id' => $price_id,
                'object_id' => $product_id,
                'object_type' => 'product'
            );

            db_replace_into('yml_exclude_objects', $data);
        }

        $delete_prices_ids = array_diff($old_price_ids, $exclude_price_ids);
        if (!empty($delete_prices_ids)) {
            db_query(
                'DELETE FROM ?:yml_exclude_objects WHERE price_id IN (?a) AND object_type = ?s AND object_id = ?i',
                $delete_prices_ids, 'product', $product_id
            );
        }
    }

    fn_set_hook('yml_export_update_product_pre_post', $product_data, $product_id);
}

function fn_yml_export_update_category_post(&$category_data, &$category_id, &$lang_code)
{
    fn_yml_update_categories_price_lists();
}

function fn_yml_export_update_product_feature_pre(&$feature_data)
{

    if (isset($feature_data['yml2_exclude_prices']) && is_array($feature_data['yml2_exclude_prices'])) {
        $yml2_exclude_prices = array_keys($feature_data['yml2_exclude_prices']);
        $feature_data['yml2_exclude_prices'] = implode(',', $yml2_exclude_prices);
    }
}

function fn_yml_export_get_product_feature_data_before_select(&$fields)
{
    $fields[] = "?:product_features.yml2_exclude_prices";
    $fields[] = "?:product_features_descriptions.yml2_variants_unit";
}

function fn_yml_export_get_product_features(&$fields, $join, $condition, $params)
{
    $fields[] = "pf.yml2_exclude_prices";
    $fields[] = "?:product_features_descriptions.yml2_variants_unit";
}

function fn_yml_export_get_product_features_post(&$data, $params, $has_ungroupped)
{
    $product_update = (Registry::get('runtime.controller') == 'products' && Registry::get('runtime.mode') == 'update');

    foreach($data as &$feature) {
        $feature['yml2_exclude_prices'] = !empty($feature['yml2_exclude_prices']) ? $feature['yml2_exclude_prices'] : '';
        $feature['yml2_exclude_prices'] = explode(",", $feature['yml2_exclude_prices']);

        if ($product_update && !empty($feature['variants'])) {
            foreach ($feature['variants'] as &$_variant) {
                if (!empty($_variant['yml2_unit'])) {
                    $_variant['variant'] .= $_variant['yml2_unit'];
                }
            }
        }

        if (!empty($feature['variants']) && !empty($feature['variant_id']) && !empty($feature['variants'][$feature['variant_id']])) {
            $variant = $feature['variants'][$feature['variant_id']];
            if (!empty($variant['yml2_unit']) && !$product_update) {
                $feature['variants'][$feature['variant_id']]['variant'] .= $variant['yml2_unit'];
            } else {
                $feature['suffix'] = $feature['yml2_variants_unit'] . $feature['suffix'];
            }
        } elseif (!empty($feature['yml2_variants_unit'])) {
            $feature['suffix'] = $feature['yml2_variants_unit'] . $feature['suffix'];
        }

        if (isset($feature['subfeatures'])) {
            fn_yml_export_get_product_features_post($feature['subfeatures'], $params, $has_ungroupped);
        }
    }
}

function fn_yml_export_get_filters_products_count_before_select_filters(&$sf_fields, $sf_join, $condition, $sf_sorting, $params)
{
    $sf_fields .= ",?:product_features_descriptions.yml2_variants_unit";
}

function fn_yml_export_get_filters_products_count_post($params, $lang_code, &$filters)
{
    $variant_ids = array();
    foreach ($filters as $feature) {
        if (!empty($feature['variants'])) {
            $variant_ids = array_merge($variant_ids, array_keys($feature['variants']));
        }

        if (!empty($feature['selected_variants'])) {
            $variant_ids = array_merge($variant_ids, array_keys($feature['selected_variants']));
        }
    }

    if ($variant_ids) {
        $yml_units = db_get_hash_array('SELECT variant_id, yml2_unit FROM ?:product_feature_variant_descriptions WHERE variant_id IN (?a) AND lang_code = ?s', 'variant_id', $variant_ids, $lang_code);
    } else {
        $yml_units = [];
    }

    foreach ($filters as &$feature) {
        if (!empty($feature['variants'])) {
            foreach ($feature['variants'] as $variant_id => &$variant_data) {
                if (!empty($yml_units[$variant_id]['yml2_unit'])) {
                    $variant_data['variant'] .= $yml_units[$variant_id]['yml2_unit'];

                } elseif (!empty($feature['yml2_variants_unit'])) {
                    $variant_data['variant'] .= $feature['yml2_variants_unit'];
                }
            }
        }

        if (!empty($feature['selected_variants'])) {
            foreach ($feature['selected_variants'] as $variant_id => &$variant_data) {
                if (!empty($yml_units[$variant_id]['yml2_unit'])) {
                    $variant_data['variant'] .= $yml_units[$variant_id]['yml2_unit'];

                } elseif (!empty($feature['yml2_variants_unit'])) {
                    $variant_data['variant'] .= $feature['yml2_variants_unit'];
                }
            }
        }
    }
}

function fn_yml_export_get_product_feature_data_post(&$feature_data)
{
    if (!empty($feature_data)) {
        $feature_data['yml2_exclude_prices'] = explode(",", $feature_data['yml2_exclude_prices']);
    }
}

function fn_yml_export_get_product_features_list_before_select(&$fields)
{
    $fields .= ', f.yml2_exclude_prices, fd.yml2_variants_unit, vd.yml2_unit';
}

function fn_yml_export_get_product_features_list_post(&$features_list, $product, $display_on, $lang_code)
{
    foreach($features_list as $feature_id => &$feature) {
        if (isset($feature['yml2_exclude_prices'])) {
            $features_list[$feature_id]['yml2_exclude_prices'] = explode(',', $feature['yml2_exclude_prices']);
        }

        if (!empty($feature['yml2_unit'])) {
            $yml_variant_unit = $feature['yml2_unit'];
            $feature['variants'][$feature['variant_id']]['yml2_unit'] = $feature['yml2_unit'];
        } else {
            $yml_variant_unit = '';
        }

        if (!empty($yml_variant_unit)) {
            $feature['suffix'] = $yml_variant_unit . $feature['suffix'];
        } elseif (isset($feature['yml2_variants_unit'])) {
            $feature['suffix'] = $feature['yml2_variants_unit'] . $feature['suffix'];
        }

        if (isset($feature['subfeatures'])) {
            fn_yml_export_get_product_features_list_post($feature, $product, $display_on, $lang_code);
        }
    }
    unset($feature);
}

function fn_yml_export_get_product_option_data_pre($option_id, $product_id, $fields, $condition, $join, &$extra_variant_fields)
{
    $extra_variant_fields .= "a.yml2_variant, ";
}

function fn_yml_export_get_selected_product_options_before_select($fields, $condition, $join, &$extra_variant_fields)
{
    $extra_variant_fields .= "a.yml2_variant, ";
}

/**
 * The "update_product_feature_variant_before_select" hook handler.
 *
 * Actions performed:
 *  - Add to conditions to search variant by name, check unique od variant also by yml2_unit.
 *
 * @param int                       $feature_id   Feature identifier
 * @param string                    $feature_type Feature type
 * @param array<string, int|string> $variant      Feature variant data
 * @param string                    $lang_code    Two letters language code
 * @param int                       $variant_id   Variant identifier
 * @param array<string, string>     $fields       Fields which will be got from database
 * @param array<string, string>     $joins        Prepared query for joined tables
 * @param array<string, string>     $conditions   Prepared condition which will be add to query divided by AND
 *
 * @see fn_update_product_feature_variant
 */
function fn_yml_export_update_product_feature_variant_before_select($feature_id, $feature_type, $variant, $lang_code, $variant_id, array $fields, array $joins, array &$conditions)
{
    $yml2_unit = !empty($variant['yml2_unit']) ? $variant['yml2_unit'] : '';
    $conditions['yml2_unit'] = db_quote('yml2_unit = ?s', $yml2_unit);
}

/**
 * The "get_product_feature_variant_name_post" hook handler.
 *
 * Actions performed:
 *  - Adds yml unit to feature variant name.
 *
 * @param int    $variant_id   Product identifier
 * @param string $lang_code    Two letters language code
 * @param string $variant_name Feature variant name
 *
 * @see fn_get_product_feature_variant_name
 */
function fn_yml_export_get_product_feature_variant_name_post($variant_id, $lang_code, &$variant_name)
{
    $yml2_unit = db_get_field('SELECT yml2_unit FROM ?:product_feature_variant_descriptions WHERE variant_id = ?i AND lang_code = ?s', $variant_id, $lang_code);

    if (empty($yml2_unit)) {
        return;
    }
    $variant_name = sprintf('%s %s', $variant_name, $yml2_unit);
}

/**
 * \Hooks
 */

/**
 * Functions
 */

function fn_yml_addon_install()
{
    fn_yml_add_logs();

    $company_ids = fn_get_available_company_ids();
    foreach($company_ids as $company_id) {

        $offers_params = array(
            'vendorCode' => array(
                'type' => 'product',
                'value' => 'product_code'
            ),
            'description' => array(
                'type' => 'product',
                'value' => 'yml2_description'
            ),
            'model' => array(
                'type' => 'product',
                'value' => 'yml2_model'
            ),
            'typePrefix' => array(
                'type' => 'product',
                'value' => 'yml2_type_prefix'
            ),
            'vendor' => array(
                'type' => 'product',
                'value' => 'yml2_brand'
            ),
        );

        $brand_id = db_get_field("SELECT feature_id FROM ?:product_features WHERE status = 'A' AND feature_type = 'E' AND company_id = ?i", $company_id);

        if (!empty($brand_id)) {
            $offers_params['vendor'] = array(
                'type' => 'feature',
                'value' => $brand_id
            );
        }

        $data = array(
            'param_type' => 'offer',
            'param_key' => 'common',
            'param_data' => serialize($offers_params),
            'company_id' => $company_id
        );

        db_query("INSERT INTO ?:yml_param ?e ON DUPLICATE KEY UPDATE ?u", $data, $data);
    }
}

function fn_yml_export_get_market_categories()
{
    return fn_get_schema('yml', 'categories');
}

function fn_yml_array_to_yml($data, $level = 0)
{
    if (!is_array($data)) {
        return $data;
    }

    $return = '';
    foreach ($data as $key => $value) {
        $attr = '';
        if (is_array($value) && is_numeric(key($value))) {
            foreach ($value as $k => $v) {
                $arr = array(
                    $key => $v
                );
                $return .= fn_array_to_xml($arr);
                unset($value[$k]);
            }
            unset($data[$key]);
            continue;
        }

        if (strpos($key, '@') !== false) {
            $data = explode('@', $key);
            $key = $data[0];
            unset($data[0]);

            if (count($data) > 0) {
                foreach ($data as $prop) {
                    if (strpos($prop, '=') !== false) {
                        $prop = explode('=', $prop);
                        $attr .= ' ' . $prop[0] . '="' . $prop[1] . '"';
                    } else {
                        $attr .= ' ' . $prop . '=""';
                    }
                }
            }
        }

        if (strpos($key, '+') !== false) {
            list($key) = explode('+', $key, 2);
        }

        $tab = str_repeat('    ', $level);

        if ($value === false || $value == '') {
            $return .= $tab . "<" . $key . $attr . "/>\n";

        } elseif (is_array($value)) {
            $return .= $tab . "<" . $key . $attr . ">\n" . fn_yml_array_to_yml($value, $level + 1) . '</' . $key . ">\n";

        } else {
            $return .= $tab . "<" . $key . $attr . '>' . fn_yml_array_to_yml($value, $level + 1) . '</' . $key . ">\n";
        }

    }

    return $return;
}

function fn_yml_c_encode($s)
{
    $rep = array(
        ' ' => '%20',
        'а' => '%D0%B0', 'А' => '%D0%90',
        'б' => '%D0%B1', 'Б' => '%D0%91',
        'в' => '%D0%B2', 'В' => '%D0%92',
        'г' => '%D0%B3', 'Г' => '%D0%93',
        'д' => '%D0%B4', 'Д' => '%D0%94',
        'е' => '%D0%B5', 'Е' => '%D0%95',
        'ё' => '%D1%91', 'Ё' => '%D0%81',
        'ж' => '%D0%B6', 'Ж' => '%D0%96',
        'з' => '%D0%B7', 'З' => '%D0%97',
        'и' => '%D0%B8', 'И' => '%D0%98',
        'й' => '%D0%B9', 'Й' => '%D0%99',
        'к' => '%D0%BA', 'К' => '%D0%9A',
        'л' => '%D0%BB', 'Л' => '%D0%9B',
        'м' => '%D0%BC', 'М' => '%D0%9C',
        'н' => '%D0%BD', 'Н' => '%D0%9D',
        'о' => '%D0%BE', 'О' => '%D0%9E',
        'п' => '%D0%BF', 'П' => '%D0%9F',
        'р' => '%D1%80', 'Р' => '%D0%A0',
        'с' => '%D1%81', 'С' => '%D0%A1',
        'т' => '%D1%82', 'Т' => '%D0%A2',
        'у' => '%D1%83', 'У' => '%D0%A3',
        'ф' => '%D1%84', 'Ф' => '%D0%A4',
        'х' => '%D1%85', 'Х' => '%D0%A5',
        'ц' => '%D1%86', 'Ц' => '%D0%A6',
        'ч' => '%D1%87', 'Ч' => '%D0%A7',
        'ш' => '%D1%88', 'Ш' => '%D0%A8',
        'щ' => '%D1%89', 'Щ' => '%D0%A9',
        'ъ' => '%D1%8A', 'Ъ' => '%D0%AA',
        'ы' => '%D1%8B', 'Ы' => '%D0%AB',
        'ь' => '%D1%8C', 'Ь' => '%D0%AC',
        'э' => '%D1%8D', 'Э' => '%D0%AD',
        'ю' => '%D1%8E', 'Ю' => '%D0%AE',
        'я' => '%D1%8F', 'Я' => '%D0%AF'
    );

    $s = strtr($s, $rep);

    return $s;
}

function fn_yml_check_country($country)
{
    $countries = fn_get_schema('yml', 'countries');

    return isset($countries[$country]);
}

function fn_yml_get_offers_features()
{
    $condition = fn_get_company_condition('?:yml_param.company_id');
    $offers_feature = db_get_array("SELECT param_key, param_data FROM ?:yml_param WHERE param_type = 'offer' $condition");

    $feature_values = array();
    foreach($offers_feature as $feature_data) {
        $feature_values[$feature_data['param_key']] = unserialize($feature_data['param_data']);
    }

    return $feature_values;
}

function fn_yml_update_offers_features($features)
{
    $company_id = fn_get_runtime_company_id();

    foreach($features as $feature_code => $f_data) {

        $feature_data = array();
        foreach($f_data as $feature_key => $feature_value) {
            if (strpos($feature_value, '.') !== false) {
                list($type, $value) = explode('.', $feature_value);
                $feature_data[$feature_key] = array(
                    'type' => $type,
                    'value' => $value
                );
            } else {
                $feature_data[$feature_key] = array(
                    'type' => 'feature',
                    'value' => ''
                );
            }
        }

        $data = array(
            'param_type' => 'offer',
            'param_key' => $feature_code,
            'param_data' => serialize($feature_data),
            'company_id' => $company_id
        );

        db_replace_into('yml_param', $data);
    }
}

function fn_yml_get_price_id($access_key)
{
    $price_id = db_get_field("SELECT param_id FROM ?:yml_param WHERE param_key = ?s", $access_key);

    return $price_id;
}

/**
 * Gets options for specific price list as merging default options from scheme and overridden options for current price list
 *
 * @param int $price_id  Price list identifier
 *
 * @return array
 */
function fn_yml_get_options($price_id)
{
    $price_list = db_get_row("SELECT param_id, param_key, param_data, company_id FROM ?:yml_param WHERE param_id = ?s", $price_id);

    if (empty($price_list)) {
        return false;
    }

    $schema_price_list = fn_get_schema('yml', 'price_list');
    $schema_price_list = $schema_price_list['default'];

    $options = array(
        'price_id'           => $price_id,
        'use_yml_categories' => true
    );
    foreach ($schema_price_list as $tab_code => $params) {
        foreach ($params as $param_code => $param_data) {
            if (isset($param_data['default'])) {
                $options[$param_code] = $param_data['default'];
            }
        }
    }

    $options = array_merge($options, unserialize($price_list['param_data']));

    /**
     * Executed after getting specific price list options. Allows to modify getting options.
     *
     * @param int   $price_id Price list identifier
     * @param array $options  Option data
     */
    fn_set_hook('yml_export_get_options_post', $price_id, $options);

    return $options;
}

function fn_yml_get_price_list($price_id)
{
    $price_lists = fn_yml_get_price_lists(array($price_id));
    return reset($price_lists);
}

function fn_yml_get_price_lists($price_ids = array())
{
    $condition = '';
    if (!empty($price_ids)) {
        $condition .= db_quote(" AND param_id IN (?a)", $price_ids);
    }

    $condition .= fn_get_company_condition('?:yml_param.company_id');

    $price_lists = db_get_hash_array("SELECT param_id, param_key, param_data, status, company_id FROM ?:yml_param WHERE param_type = 'price_list' $condition", 'param_id');

    foreach($price_lists as $price_id => $price_data) {
        $price_lists[$price_id]['param_data'] = unserialize($price_data['param_data']);
    }

    return $price_lists;
}

function fn_yml_update_price_list($price_id, $price_list)
{
    $company_id = fn_get_runtime_company_id();

    if (!empty($price_id)) {
        $price = fn_yml_get_price_list($price_id);

        if ($price['company_id'] != $company_id) {
            fn_set_notification('E', __('error'), __('access_denied'), '', 'company_access_denied');

            return false;
        }

        $price_list['price_id'] = $price_id;
        $price_list['company_id'] = $company_id;
    }

    fn_yml_validate_price_list($price_list);

    if (!empty($price_list['delivery_options'])) {
        foreach($price_list['delivery_options'] as $index => $delivery_option) {
            if (fn_is_empty($delivery_option)) {
                unset($price_list['delivery_options'][$index]);
            }
        }

        $price_list['delivery_options'] = array_slice($price_list['delivery_options'], 0, 5);
    }

    fn_yml_get_categories_data($price_list);

    $data = array(
        'param_type' => 'price_list',
        'param_key' => $price_list['access_key'],
        'param_data' => serialize($price_list),
        'company_id' => $company_id
    );

    if (!empty($price_id)) {
        $data['param_id'] = $price_id;
    }

    $new_price_id = db_replace_into('yml_param', $data);

    return !empty($new_price_id) ? $new_price_id : $price_id;
}

function fn_yml_validate_price_list(&$price_list)
{
    $schema_price_list = fn_get_schema('yml', 'price_list');
    $schema_price_list = $schema_price_list['default'];

    foreach ($schema_price_list as $tab => $tab_settings) {
        foreach ($tab_settings as $setting_name => $setting_params) {

            if (array_key_exists($setting_name, $price_list)) {
                if (isset($setting_params['min']) && $setting_params['min'] > $price_list[$setting_name]) {
                    $price_list[$setting_name] = $setting_params['min'];
                    fn_set_notification('W', __('warning'), __('yml_export.min_value', array('[setting]' => __('yml_export.param_' . $setting_name), '[min_value]' => $setting_params['min'])));
                }

                if (isset($setting_params['max']) && $setting_params['max'] < $price_list[$setting_name]) {
                    $price_list[$setting_name] = $setting_params['max'];
                    fn_set_notification('W', __('warning'), __('yml_export.max_value', array('[setting]' => __('yml_export.param_' . $setting_name), '[max_value]' => $setting_params['max'])));
                }
            }
        }
    }
}

function fn_yml_get_categories_data(&$price_list)
{
    $exclude_categories_ext = array();
    if (!empty($price_list['exclude_categories'])) {
        $exclude_categories = explode(',', $price_list['exclude_categories']);

        $params = array(
            'visible' => false,
            'simple' => true,
            'plain' => true,
            'get_images' => false
        );

        foreach ($exclude_categories as $category_id) {
            $params['category_id'] = $category_id;
            $exclude_categories_ext[$category_id] = $category_id;

            list($subcategories,) = fn_get_categories($params);

            if (!empty($subcategories)) {
                foreach ($subcategories as $subcategory) {
                    $exclude_categories_ext[$subcategory['category_id']] = $subcategory['category_id'];
                }
            }
        }

        sort($exclude_categories_ext);
        $price_list['exclude_categories_ext'] = implode(',', $exclude_categories_ext);
    }

    list($export_categories) = fn_yml_get_categories_ids(0, array('A'));

    if (!empty($exclude_categories_ext)) {
        $export_categories = array_diff($export_categories, $exclude_categories_ext);
    }

    $price_list['export_categories'] = implode(',', $export_categories);

    $price_list['hidden_categories_ext'] = '';
    $price_list['hidden_categories_data'] = array();
    if ($price_list['export_hidden_categories'] == 'Y') {
        $hidden_categories_data = array();
        list($hidden_categories, $data) = fn_yml_get_categories_ids(0, array('H'));
        $hidden_categories_data = array_merge($hidden_categories_data, $data);

        $price_list['hidden_categories'] = implode(',', $hidden_categories);

        if (!empty($hidden_categories)) {
            $hidden_categories_ext = array();

            foreach($hidden_categories as $category_id) {
                $hidden_categories_ext[] = $category_id;
                list($ext, $data) = fn_yml_get_categories_ids($category_id, array('A', 'H'));
                $hidden_categories_ext = array_merge($hidden_categories_ext, $ext);
                $hidden_categories_data = array_merge($hidden_categories_data, $data);
            }
            $price_list['hidden_categories_ext'] = implode(',', $hidden_categories_ext);
            $price_list['hidden_categories_data'] = $hidden_categories_data;
        }
    }
}

function fn_yml_get_categories_ids($category_id = 0, $status = array('A'))
{
    $categories_ids = array();
    $categories_data = array();

    $params['plain'] = true;
    $params['status'] = $status;
    $params['category_id'] = $category_id;
    $params['current_category_id'] = $category_id;

    list($categories, ) = fn_get_categories($params, DESCR_SL);

    if (!empty($categories)) {
        foreach ($categories as $category) {
            if (isset($category['category_id'])) {
                $categories_ids[] = $category['category_id'];
                $categories_data[$category['category_id']] = $category;
            }
        }
    }

    return array($categories_ids, $categories_data);
}

function fn_yml_update_categories_price_lists()
{
    $price_lists = fn_yml_get_price_lists();

    foreach ($price_lists as $param_id => $param) {
        fn_yml_update_price_list($param_id, $param['param_data']);
    }
}

function fn_yml_delete_price_lists($price_ids)
{
    db_query("DELETE FROM ?:yml_param WHERE param_type = 'price_list' AND param_id in (?a)", $price_ids);

    return true;
}

function fn_yml_export_save_log($type, $action, $data, $user_id, &$content)
{
    if ($type == 'yml') {
        $content['message'] = $data['message'];
    }
}

function fn_yml_add_logs()
{
    $setting = Settings::instance()->getSettingDataByName('log_type_yml_export');

    if (!$setting) {
        $setting = array(
            'name' => 'log_type_yml_export',
            'section_id' => 12, // Logging
            'section_tab_id' => 0,
            'type' => 'N',
            'position' => 10,
            'is_global' => 'N',
            'edition_type' => 'ROOT,ULT:VENDOR',
            'value' => '#M#export'
        );

        $descriptions = array();
        foreach (Languages::getAll() as $lang_code => $_lang) {
            $descriptions[] = array(
                'object_type' => Settings::SETTING_DESCRIPTION,
                'lang_code' => $lang_code,
                'value' => __('yml2_log')
            );
        }

        $setting_id = Settings::instance()->update($setting, null, $descriptions, true);
        $variant_id = Settings::instance()->updateVariant(array(
            'object_id'  => $setting_id,
            'name'       => 'export',
            'position'   => 5,
        ));
        foreach (Languages::getAll() as $lang_code => $_lang) {
            $description = array(
                'object_id' => $variant_id,
                'object_type' => Settings::VARIANT_DESCRIPTION,
                'lang_code' => $lang_code,
                'value' => __('yml2_log_export')
            );
            Settings::instance()->updateDescription($description);
        }
    }

    return true;
}

function fn_yml_rand_code($length = 12)
{
    $code = '';

    $symbols = '0123456789abcdefghijklmnopqrstuvwxyz';
    for( $i = 0; $i < (int)$length; $i++ ) {
        $num = rand(1, strlen($symbols));
        $code .= substr( $symbols, $num, 1 );
    }

    return $code;
}

function fn_yml_import_delivery_options($delivery_options)
{
    $do = array();
    $delivery_options = explode(";", $delivery_options);

    if (!empty($delivery_options)) {
        foreach($delivery_options as $option) {

            $option = explode(",", $option);

            if (!empty($option)) {
                $option_data = array();

                if (isset($option[0])) {
                    $option_data['cost'] = (int) trim($option[0]);
                }

                if (isset($option[1])) {
                    $option_data['days'] = trim($option[1]);
                }

                if (isset($option[2])) {
                    $option_data['order_before'] = (int) trim($option[2]);
                }

                if (!isset($option_data['cost']) || !isset($option_data['days'])) {
                    continue;
                }

                $do[] = $option_data;
            }
        }
    }

    return serialize($do);
}

function fn_yml_export_delivery_options($delivery_options)
{
    $delivery_options = unserialize($delivery_options);

    $options_data = array();
    if (!empty($delivery_options)) {
        foreach($delivery_options as $option) {
            if (isset($option['order_before']) && $option['order_before'] == "") {
                unset($option['order_before']);
            }

            $options_data[] = implode(", ", $option);
        }
    }

    return implode("; ", $options_data);
}

function fn_yml_get_generate_link($price_list)
{
    return fn_yml_get_storefront_url($price_list) . '/yml_generate/' . fn_yml_get_key($price_list);
}

function fn_yml_get_link($price_list)
{
    return fn_yml_get_storefront_url($price_list) . '/yml_get/' . fn_yml_get_key($price_list);
}

function fn_yml_get_console_cmd($price_list)
{
    $console_cmd = sprintf('php %s/index.php --dispatch=yml.generate', DIR_ROOT);
    $console_cmd .= ' --switch_storefront_id=' . $price_list['storefront_id'];

    if (isset($price_list['param_data']) && $price_list['param_data']['enable_authorization'] == 'N') {
        $console_cmd .= " --price_id=" . $price_list['param_id'];
    } else {
        $console_cmd .= " --access_key=" . fn_yml_get_key($price_list);
    }

    /**
     * Executed after string of the console command was builded; Allows to modify string of the console command.
     *
     * @param array  $price_list    Data of price list
     * @param string $console_cmd   String of the console command
     */
    fn_set_hook('yml_get_console_cmd', $price_list, $console_cmd);

    return $console_cmd;
}

function fn_yml_get_storefront_url($price_list)
{
    static $storefront_url = null;

    if (!isset($storefront_url)) {
        $storefront_url = Registry::get('config.current_location');

        if (fn_allowed_for('ULTIMATE')) {
            if (!empty($price_list['company_id'])) {
                $company_data = fn_get_company_data($price_list['company_id']);
                $storefront_url = fn_get_storefront_protocol($company_data['company_id']) . '://' . $company_data['storefront'];

            } elseif (Registry::get('runtime.company_id') || Registry::get('runtime.simple_ultimate')) {
                $company = Registry::get('runtime.company_data');
                $storefront_url = fn_get_storefront_protocol($company['company_id']) . '://' . $company['storefront'];

            } else {
                $storefront_url = '';
            }
        }
    }

    return $storefront_url;
}

function fn_yml_get_key($price_list)
{
    static $key = array();

    $price_list['param_id'] = isset($price_list['param_id']) ? $price_list['param_id'] : '';

    if (!isset($key[$price_list['param_id']])) {
        if (!empty($price_list['param_key'])) {
            $access_key = $price_list['param_key'];
        } else {
            $access_key = fn_yml_rand_code();
        }

        if (isset($price_list['param_data']) && $price_list['param_data']['enable_authorization'] == 'N') {
            $key[$price_list['param_id']] = $price_list['param_id'];

        } else {
            $key[$price_list['param_id']] = $access_key;
        }
    }

    return $key[$price_list['param_id']];
}

function fn_yml_get_parent_categories_field($field_name, $category_id, $parent = false)
{
    $parent_field = '';
    static $paths = array();

    if (!isset($paths[$category_id])) {
        $paths[$category_id] = fn_get_category_ids_with_parent($category_id);
    }
    $path = $paths[$category_id];

    if (!$parent) {
        $path = array_diff($path, array($category_id));
    }

    if (!empty($path)) {
        $condition = db_quote(' AND ?p != ?s', $field_name, '');
        $categories_fields = db_get_hash_array(
            'SELECT category_id, ?p FROM ?:categories WHERE category_id IN (?a) ?p',
            'category_id', $field_name, $path, $condition
        );

        foreach($path as $c_id) {
            if (isset($categories_fields[$c_id])) {
                $parent_field = $categories_fields[$c_id][$field_name];
            }
        }
    }

    return $parent_field;
}

function fn_yml_stop_generate($price_id)
{
    fn_set_storage_data('yml2_status_generate_' . $price_id, 'stop');
    fn_set_storage_data('yml2_product_export_' . $price_id);
    fn_set_storage_data('yml2_product_skip_' . $price_id);

    fn_set_storage_data('yml2_export_count_' . $price_id);
    fn_set_storage_data('yml2_export_offset_' . $price_id);
    fn_set_storage_data('yml2_export_time_' . $price_id, time());
}

function fn_yml_get_setting_value($name, $company_id = 0, $reverse = false)
{
    static $section_id = null;
    if (!isset($section_id)) {
        $section_id = db_get_field(
            'SELECT section_id FROM ?:settings_sections WHERE name = ?s AND type = ?s',
            'yandex_market', 'ADDON'
        );
    }

    $setting = db_get_row(
        'SELECT object_id, value, type FROM ?:settings_objects WHERE name = ?s AND section_id = ?i',
        $name, $section_id
    );

    if (!empty($company_id)) {
        $vendor_setting = db_get_row(
            'SELECT object_id, value FROM ?:settings_vendor_values WHERE object_id = ?i AND company_id = ?i',
            $setting['object_id'], $company_id
        );
        if (isset($vendor_setting['value'])) {
            $setting['value'] = $vendor_setting['value'];
        }
    }

    if ($reverse && $setting['type'] == 'C') {
        $setting['value'] = $setting['value'] == 'N' ? 'Y' : 'N';
    }

    return !empty($setting['value']) ? $setting['value'] : false;
}

function fm_yml_get_exclude_products($product_id = 0)
{
    $condition = "object_type = 'product'";
    if (!empty($product_id)) {
        $condition .= ' AND ' . db_quote("object_id = ?i", $product_id);
    }

    return db_get_array("SELECT * FROM ?:yml_exclude_objects WHERE $condition");
}

/**
 * \Functions
 */

function fn_yml_export_exclude_prices($key)
{
    $exclude_data = fm_yml_get_exclude_products($key);

    $prices = array();
    foreach($exclude_data as $exclude_price) {
        $prices[] = $exclude_price['price_id'];
    }

    return implode(',', $prices);
}

function fn_yml_import_exclude_prices($key, $data)
{
    static $price_lists;

    if (!isset($price_lists)) {
        $price_lists = fn_yml_get_price_lists();
        $price_lists = array_keys($price_lists);
    }

    $exclude_data = explode(',', $data);
    $old_exclude = fm_yml_get_exclude_products($key);

    $old_exclude_data = array();
    if (!empty($old_exclude)) {
        foreach ($old_exclude as $old_data) {
            $old_exclude_data[] = $old_data['price_id'];
        }
    }

    $exclude_data = array_intersect($exclude_data, $price_lists);
    $delete_old_exclude = array_diff($old_exclude_data, $exclude_data);
    $add_new_exclude = array_diff($exclude_data, $old_exclude_data);

    if (!empty($delete_old_exclude)) {
        db_query("DELETE FROM ?:yml_exclude_objects WHERE object_type = 'product' AND object_id = ?i AND price_id IN (?a)", $key, $delete_old_exclude);
    }

    if (!empty($add_new_exclude)) {
        foreach ($add_new_exclude as $price_id) {
            db_query("INSERT INTO ?:yml_exclude_objects (price_id, object_id, object_type) VALUES(?i, ?i, 'product') ", $price_id, $key);
        }
    }

    return true;
}

/**
 * Adds additional parameters and table for the categories select.
 */
function fn_yml_export_get_categories($params, &$join, $condition, &$fields, &$group_by, $sortings, $lang_code)
{
    if (isset($params['market_category'])) {
        $fields[] = '?:categories.yml2_market_category';
        $fields[] = 'GROUP_CONCAT(pc.product_id) as product_ids';
        $join .= ' LEFT JOIN ?:products_categories as pc ON ?:categories.category_id = pc.category_id';
        $group_by .= ' GROUP BY ?:categories.category_id';
    }
}


/**
 * Hook handler: delete vendor-specified settings for offers
 */
function fn_yml_export_chown_company($from_company_id, $to_company_id, $excluded_tables, $tables)
{
    db_query('DELETE FROM ?:yml_param WHERE param_type = ?s AND company_id = ?i', 'offer', $from_company_id);
}

/**
 * Hook handler: removes or adds variation to the exclude yml objects together with their parent.
 */
function fn_product_variations_yml_export_update_product_pre_post($product_data, $product_id)
{
    if (empty($product_id)) {
        return;
    }
    $sync_service = ProductVariationsServiceProvider::getSyncService();
    $sync_service->onTableChanged('yml_exclude_objects', $product_id);
}


function fn_product_variations_yml_export_generate_offers_before_gather_additional_products_data($yml2, $products, &$params)
{
    $params['get_variation_info'] = true;
}

function fn_warehouses_yml_export_generate_offers_before_gather_additional_products_data($yml2, $products, &$params)
{
    $params['get_warehouse_total_amount'] = true;
}

/**
 * Removes add-on logging settings.
 *
 * @return void
 */
function fn_yml_addon_uninstall()
{
    $setting = Settings::instance()->getSettingDataByName('log_type_yml_export');
    if (!$setting) {
        return;
    }

    /** @psalm-var array{object_id: int} $setting */
    Settings::instance()->removeById($setting['object_id']);
}
