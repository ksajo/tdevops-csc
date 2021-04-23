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

use Tygh\Addons\SchemesManager;
use Tygh\Languages\Languages;
use Tygh\Registry;
use Tygh\Settings;
use Tygh\Tools\Url;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_rus_exim_1c_install()
{
    $category = db_get_row('SELECT * FROM ?:categories');

    db_query('UPDATE ?:settings_objects SET value = ?s WHERE name = ?s', $category['category_id'], 'exim_1c_default_category');
}

function fn_settings_variants_addons_rus_exim_1c_exim_1c_order_statuses()
{
    $order_statuses = fn_get_simple_statuses('O', false, false, CART_LANGUAGE);

    return $order_statuses;
}

function fn_update_default_category_settings($setting)
{
    if (isset($setting)) {
        Settings::instance()->updateValue($setting['setting_name'], $setting['setting_value']);
    }
}

function fn_get_default_category_settings($lang_code = DESCR_SL)
{
    $default_category = "";

    $settings = Settings::instance()->getValues('rus_exim_1c', 'ADDON');
    if (!empty($settings['general']['exim_1c_default_category'])) {
        $default_category = $settings['general']['exim_1c_default_category'];
    }

    return $default_category;
}

function fn_settings_variants_addons_rus_exim_1c_exim_1c_lang()
{
    $languages = Languages::getAll();

    foreach ($languages as $language) {
        $langs[$language['lang_code']] = $language['name'];
    }

    return $langs;
}

function fn_rus_exim_1c_get_information()
{
    $storefront_url = Registry::get('config.http_location');

    $exim_1c_info = '';
    if (!empty($storefront_url)) {
        $exim_1c_info = __('addons.rus_exim_1c.information', array(
            '[http_location]' => $storefront_url . '/' . 'exim_1c',
        )) . __('addons.rus_exim_1c.info_store');
    }

    return $exim_1c_info;
}

function fn_rus_exim_1c_get_information_shipping_features()
{
    $exim_1c_info_features = __('exim_1c_information_shipping_features');

    return $exim_1c_info_features;
}

function fn_rus_exim_1c_get_orders($params, $fields, $sortings, &$condition, $join, $group)
{
    if (isset($params['place']) && $params['place'] == 'exim_1c') {
        $order_id = trim(Settings::instance()->getValue('exim_1c_from_order_id', 'rus_exim_1c', $params['company_id']));
        if (!empty($order_id)) {
            $condition .= db_quote(' AND ?:orders.order_id >= ?i', $order_id);
        }
    }
}

/**
 * Sets company_id depending on the current user.
 *
 * @param  array  $user_data  The array with information about the user.
 *
 * @return void
 */
function fn_commerceml_change_company_store($user_data)
{
    if (PRODUCT_EDITION == 'ULTIMATE') {
        if (Registry::get('runtime.simple_ultimate')) {
            $company_id = Registry::get('runtime.forced_company_id');
        } else {
            if ($user_data['company_id'] != 0) {
                $company_id = $user_data['company_id'];
                Registry::set('runtime.company_id', $company_id);
            }
        }
    } elseif ($user_data['user_type'] == 'V') {
        if ($user_data['company_id'] != 0) {
            $company_id = $user_data['company_id'];
            Registry::set('runtime.company_id', $company_id);
        }
    } else {
        Registry::set('runtime.company_id', 0);
    }
}

function fn_rus_exim_1c_before_dispatch()
{
    if (!empty($_REQUEST['dispatch']) && $_REQUEST['dispatch'] == 'exim_1c') {
        if (!empty($_REQUEST[Tygh::$app['session']->getName()])) {
            unset($_REQUEST[Tygh::$app['session']->getName()]);
        }
    }
}

/**
 * The "store_locator_delete_store_location_post" hook handler.
 *
 * Actions performed:
 *  - Removes releated external ID
 *
 * @see fn_delete_store_location
 */
function fn_rus_exim_1c_store_locator_delete_store_location_post($store_location_id, $affected_rows, $deleted)
{
    db_query('DELETE FROM ?:rus_exim_1c_warehouses WHERE warehouse_id = ?i', $store_location_id);
}

/**
 * The "store_locator_get_store_location_before_select" hook handler.
 *
 * Actions performed:
 *  - Joins warehouse external ID field
 *
 * @see fn_get_store_location
 */
function fn_rus_exim_1c_store_locator_get_store_location_before_select($store_locator_id, $lang_code, &$fields, &$join, $condition)
{
    $fields[] = 'GROUP_CONCAT(?:rus_exim_1c_warehouses.external_id) AS external_1c_ids';
    $join .= 'LEFT JOIN ?:rus_exim_1c_warehouses ON ?:store_locations.store_location_id = ?:rus_exim_1c_warehouses.warehouse_id ';
}

/**
 * The "store_locator_get_store_location_post" hook handler.
 *
 * Actions performed:
 *  - Conversions external ids data to array
 *
 * @see fn_get_store_location
 */
function fn_rus_exim_1c_store_locator_get_store_location_post($store_location_id, $lang_code, &$store_location)
{
    if (empty($store_location_id) || empty($store_location['external_1c_ids'])) {
        return;
    }
    $store_location['external_1c_ids'] = explode(',', $store_location['external_1c_ids']);
}

/**
 * The "store_locator_update_store_location_post" hook handler.
 *
 * Actions performed:
 *  - Saves external ID
 *
 * @see fn_update_store_location
 */
function fn_rus_exim_1c_store_locator_update_store_location_post($store_location_data, $store_location_id, $lang_code)
{
    if (!isset($store_location_data['external_1c_ids'])) {
        return;
    }

    $store_location_data['external_1c_ids'] = array_unique(array_filter($store_location_data['external_1c_ids']));

    db_query('DELETE FROM ?:rus_exim_1c_warehouses WHERE warehouse_id = ?i', $store_location_id);

    if (empty($store_location_data['external_1c_ids'])) {
        return;
    }

    $params = [
        'get_external_1c_ids' => true,
        'external_1c_ids'     => $store_location_data['external_1c_ids'],
    ];

    list($warehouses_with_existing_ids, ) = fn_get_store_locations($params);

    if (!empty($warehouses_with_existing_ids)) {
        $notification_data = [];

        foreach ($warehouses_with_existing_ids as $warehouse) {
            foreach ($warehouse['external_1c_ids'] as $external_id) {
                $notification_data[] = __('rus_exim_1c.store_locator_already_used_external_id_link', [
                    '[external_id]' => $external_id,
                    '[warehouse_link]' => fn_url(
                        Url::buildUrn('store_locator.update', [
                            'store_location_id' => $warehouse['store_location_id']
                        ])
                    ),
                    '[warehouse_name]' => $warehouse['name']
                ]);
            }
        }

        fn_set_notification('W', __('warning'), __('rus_exim_1c.store_locator_external_id_already_used', [
            '[warehouses_list]' => implode(' ', $notification_data)
        ]));
    }

    $warehouses_data =[];

    foreach ($store_location_data['external_1c_ids'] as $external_id) {
        $warehouses_data[] = [
            'warehouse_id' => $store_location_id,
            'external_id'  => $external_id
        ];
    }
    db_query('INSERT INTO ?:rus_exim_1c_warehouses ?m', $warehouses_data);

    if (fn_allowed_for('ULTIMATE')) {
        fn_ult_update_share_object($store_location_id, 'store_locations', $store_location_data['company_id']);
    }
}

/**
 * The "get_product_feature_data_before_select" hook handler.
 *
 * Actions performed:
 *  - Joins product feature external ID field
 *
 * @see fn_get_product_feature_data
 */
function fn_rus_exim_1c_get_product_feature_data_before_select(&$fields, $join, $condition, $feature_id, $get_variants, $get_variant_images, $lang_code)
{
    $fields[] = '?:product_features.external_id';
}

/**
 * The "get_product_feature_variants" hook handler.
 *
 * Actions performed:
 *  - Joins product feature variant external ID field
 *
 * @see fn_get_product_feature_variants
 */
function fn_rus_exim_1c_get_product_feature_variants(&$fields, $join, $condition, $feature_id, $get_variants, $get_variant_images, $lang_code)
{
    $fields[] = '?:product_feature_variants.external_id';
}

/**
 * The "get_store_locations_before_select" hook handler.
 *
 * Actions performed:
 *  - Adds additional parameter external_id to searching conditions
 *
 * @see fn_get_store_locations
 */
function fn_rus_exim_1c_get_store_locations_before_select($params, &$fields, &$joins, &$conditions)
{
    if (empty($params['get_external_1c_ids']) && empty($params['external_1c_ids'])) {
        return;
    }

    $joins['rus_exim_1c_warehouses'] = 'LEFT JOIN ?:rus_exim_1c_warehouses AS rus_exim_1c_warehouses'
        . ' ON ?:store_locations.store_location_id = rus_exim_1c_warehouses.warehouse_id';

    if (!empty($params['get_external_1c_ids'])) {
        $fields['external_1c_ids'] = 'GROUP_CONCAT(rus_exim_1c_warehouses.external_id) AS external_1c_ids';
    }

    if (!empty($params['external_1c_ids'])) {
        $conditions['rus_exim_1c_warehouses'] = db_quote('rus_exim_1c_warehouses.external_id IN (?a)', $params['external_1c_ids']);
    }
}

/**
 * The "store_locator_get_store_locations_post" hook handler.
 *
 * Actions performed:
 *  - Conversions external ids of each store location to array
 *
 * @see fn_get_store_locations
 */
function fn_rus_exim_1c_store_locator_get_store_locations_post($params, $items_per_page, $lang_code, &$data)
{
    if (empty($data)) {
        return;
    }

    foreach ($data as &$store_location) {
        if (empty($store_location['external_1c_ids'])) {
            continue;
        }
        $store_location['external_1c_ids'] = explode(',', $store_location['external_1c_ids']);
    }
    unset($store_location);
}

function fn_rus_exim_1c_get_local_to_global_options_1c_map()
{
    static $exim_1c_options_map;

    if ($exim_1c_options_map !== null) {
        return $exim_1c_options_map;
    }

    $exim_1c_global_option_map = [];

    $options_from_1c = db_get_hash_array(
        'SELECT opt.option_id, opt.product_id'
        . ' FROM ?:product_options AS opt'
        . ' INNER JOIN ?:product_option_variants AS opt_var ON opt.option_id = opt_var.option_id'
        . ' AND opt_var.external_id <> ?s'
        . ' WHERE opt.product_id > 0'
        . ' GROUP BY opt.option_id, opt.product_id',
        'option_id',
        ''
    );

    if (empty($options_from_1c)) {
        return;
    }

    $exim_1c_global_options = db_get_hash_array(
        'SELECT opt.option_id, opt_desc.internal_option_name as option_name'
        . ' FROM ?:product_options AS opt'
        . ' LEFT JOIN ?:product_options_descriptions AS opt_desc ON opt.option_id = opt_desc.option_id'
        . ' AND opt_desc.lang_code = ?s'
        . ' WHERE opt.external_id <> ?s AND opt.product_id = 0',
        'option_id',
        CART_LANGUAGE,
        ''
    );

    if (empty($exim_1c_global_options)) {
        return;
    }

    $exim_1c_global_options_variants = db_get_hash_multi_array(
        'SELECT var.option_id, var.variant_id, var_desc.variant_name'
        . ' FROM ?:product_option_variants AS var'
        . ' LEFT JOIN ?:product_option_variants_descriptions AS var_desc ON var.variant_id = var_desc.variant_id'
        . ' AND var_desc.lang_code = ?s'
        . ' WHERE var.option_id IN (?a)'
        . 'GROUP BY var.variant_id',
        ['option_id', 'variant_id'],
        CART_LANGUAGE,
        array_keys($exim_1c_global_options)
    );

    foreach ($exim_1c_global_options_variants as $option_id => $option_variants) {
        $variants = [];

        foreach ($option_variants as $variant_id => $variant) {
            $variants[$variant_id] = $variant['variant_name'];
        }

        $exim_1c_global_option_map[$option_id] = [
            'option_name' => $exim_1c_global_options[$option_id]['option_name'],
            'variants'    => $variants
        ];
    }

    foreach ($options_from_1c as $local_option_id => $local_option) {
        $local_option_data = fn_get_product_option_data($local_option['option_id'], $local_option['product_id']);
        if (empty($local_option_data['variants'])) {
            continue;
        }

        foreach ($local_option_data['variants'] as $local_variant) {
            $variants_by_global = fn_rus_exim_1c_convert_variant_name_into_array($local_variant['variant_name']);
            if (empty($variants_by_global) || !is_array($variants_by_global)) {
                continue;
            }

            $global_variants_by_ids = [];

            foreach ($exim_1c_global_option_map as $global_option_id => $global_option) {
                if (!in_array($global_option['option_name'], array_keys($variants_by_global))) {
                    continue;
                }
                foreach ($global_option['variants'] as $variant_id => $variant) {
                    if (!empty($variants_by_global[$global_option['option_name']]['option_value']) && $variant == $variants_by_global[$global_option['option_name']]['option_value']) {
                        $global_variants_by_ids[$global_option_id] = $variant_id;
                        break;
                    }
                }
            }

            if (!empty($global_variants_by_ids)) {
                $exim_1c_options_map[$local_option['product_id']][$local_option_id][$local_variant['variant_id']] = $global_variants_by_ids;
            }
        }
    }

    return $exim_1c_options_map;
}

function fn_rus_exim_1c_product_variations_convert_find_usage_options_post($by_variations, $by_combinations, $filter_product_ids, &$options)
{
    if (!$by_combinations || empty($options)) {
        return;
    }

    $option_external_ids = db_get_hash_array(
        'SELECT option_id, external_id FROM ?:product_options'
        . ' WHERE option_id IN (?a) AND external_id <> ?s',
        'option_id',
        array_keys($options),
        ''
    );

    if (!empty($option_external_ids)) {
        foreach ($option_external_ids as $option_id => $option) {
            if (empty($options[$option_id])) {
                continue;
            }
            $options[$option_id]['external_id'] = $option['external_id'];
        }
    }

    $variant_external_ids = db_get_hash_multi_array(
        'SELECT option_id, variant_id, external_id FROM ?:product_option_variants'
        . ' WHERE option_id IN (?a) AND external_id <> ?s',
        ['option_id', 'variant_id'],
        array_keys($options),
        ''
    );

    foreach ($variant_external_ids as $option_id => $variants) {
        foreach ($variants as $variant_id => $variant) {
            if (empty($options[$option_id]['variants'][$variant_id])) {
                continue;
            }
            $options[$option_id]['variants'][$variant_id]['external_id'] = $variant['external_id'];
        }
    }
}

function fn_rus_exim_1c_product_variations_convert_process_product_with_combinations_pre(
    $product_id,
    &$combinations
){
    $local_to_global_options_1c_map = fn_rus_exim_1c_get_local_to_global_options_1c_map();
    $global_to_local_options_1c_map = [];

    if (empty($combinations)
        || empty($local_to_global_options_1c_map)
        || empty($local_to_global_options_1c_map[$product_id])
    ) {
        return;
    }

    foreach ($local_to_global_options_1c_map[$product_id] as $local_option_id => $local_value) {
        foreach ($local_value as $local_variant_id => $global_options) {
            $key = fn_generate_cart_id($product_id, ['product_options' => $global_options]);
            $global_to_local_options_1c_map[$key] = [
                'combination_hash'  => fn_generate_cart_id($product_id, ['product_options' => [$local_option_id => $local_variant_id]]),
                'variation_options' => [$local_option_id => $local_variant_id]
            ];
        }
    }

    foreach ($combinations as &$combination) {
        if (empty($global_to_local_options_1c_map[$combination['combination_hash']])) {
            continue;
        }
        $combination_hash = $combination['combination_hash'];
        $combination['exim_1c_options'] = [
            'combination_hash'  => $combination['combination_hash'],
            'variation_options' => $combination['variation_options']
        ];
        $combination['combination_hash'] = $global_to_local_options_1c_map[$combination_hash]['combination_hash'];
        $combination['variation_options'] = $global_to_local_options_1c_map[$combination_hash]['variation_options'];
    }
    unset($combination);
}

function fn_rus_exim_1c_product_variations_convert_process_product_with_combinations_after_prepare_data(
    $product_id,
    &$combinations,
    $product_exceptions,
    $product_row,
    $prices,
    $ult_prices,
    &$combinations_images,
    $combination_ids,
    &$combination_id_map
){
    foreach($combinations as &$combination) {

        $combination['external_id'] = db_get_field(
            'SELECT CONCAT(p.external_id, "#", poi.external_id) AS external_id'
            . ' FROM ?:products AS p'
            . ' LEFT JOIN ?:product_options_inventory AS poi ON p.product_id = poi.product_id'
            . ' WHERE p.product_id = ?i AND poi.combination_hash = ?s',
            $product_id,
            $combination['combination_hash']
        );

        if (empty($combination['exim_1c_options'])) {
            continue;
        }

        foreach ($combination['variation_options'] as $local_option_variant) {
            if ($combination_id_map['option_' . $local_option_variant]) {
                $key = 'option_' . implode('_', $combination['exim_1c_options']['variation_options']);
                $combination_id_map[$key] = $combination_id_map['option_' . $local_option_variant];
                unset($combination_id_map['option_' . $local_option_variant]);
            }
        }
        if (!empty($combinations_images[$combination['combination_hash']])) {
            $combinations_images[$combination['exim_1c_options']['combination_hash']] = $combinations_images[$combination['combination_hash']];
            unset($combinations_images[$combination['combination_hash']]);
        }
        $combination['combination_hash'] = $combination['exim_1c_options']['combination_hash'];
        $combination['variation_options'] = $combination['exim_1c_options']['variation_options'];
        unset($combination['exim_1c_options']);
    }
    unset($combination);
}

function fn_rus_exim_1c_product_variations_convert_get_features_post($options, &$result)
{
    foreach ($options as $option) {
        if (!empty($option['external_id']) && !empty($result[$option['feature_key']])) {
            $result[$option['feature_key']]['external_id'] = $option['external_id'];
        }
        foreach ($option['variants'] as $variant) {
            if (empty($variant['external_id']) || empty($result[$option['feature_key']]['variants'][$variant['feature_variant_key']])) {
                continue;
            }

            $result[$option['feature_key']]['variants'][$variant['feature_variant_key']]['external_id'] = $variant['external_id'];
        }
    }
}

function fn_rus_exim_1c_product_variations_convert_process_feature_post($feature)
{
    if (!empty($feature['external_id']) && !empty($feature['feature_id'])) {
        db_query(
            'UPDATE ?:product_features SET ?u WHERE feature_id = ?i',
            ['external_id' => $feature['external_id']],
            $feature['feature_id']
        );
    }

    foreach ($feature['variants'] as $variant) {
        if (!empty($variant['external_id']) && !empty($variant['variant_id'])) {
            db_query(
                'UPDATE ?:product_feature_variants SET ?u WHERE variant_id = ?i',
                ['external_id' => $variant['external_id']],
                $variant['feature_variant_id']
            );
        }
    }
}

function fn_rus_exim_1c_product_variations_convert_get_products_using_combinations($filter_product_ids, $limit, &$products)
{
    $local_to_global_options_1c_map = fn_rus_exim_1c_get_local_to_global_options_1c_map();

    if (empty($products) || empty($local_to_global_options_1c_map)) {
        return;
    }

    foreach ($products as &$product) {
        $variation_options = fn_get_product_options_by_combination($product['combination']);
        $combination = '';
        foreach ($variation_options as $local_option_id => $local_variant_id) {
            if (empty($local_to_global_options_1c_map[$product['product_id']][$local_option_id][$local_variant_id])) {
                continue;
            }
            unset($variation_options[$local_option_id]);
            $combination = fn_rus_exim_1c_get_combination_from_array($local_to_global_options_1c_map[$product['product_id']][$local_option_id][$local_variant_id]);
            $variation_options = $local_to_global_options_1c_map[$product['product_id']][$local_option_id][$local_variant_id];
        }
        if (!empty($combination)) {
            $product['combination'] = $combination;
            $product['combination_hash'] = fn_generate_cart_id($product['product_id'], ['product_options' => $variation_options]);
        }

    }
    unset($product);
}

function fn_rus_exim_1c_product_variations_convert_process_product_with_combinations_post(
    $product_id,
    $combinations,
    $group_products,
    $product_repository,
    $combination_id_map,
    $result
){
    foreach ($group_products as $group_product) {
        $feature_values = [];

        foreach ($group_product->getFeatureValues() as $feature_value) {
            $feature_values[$feature_value->getFeatureId()] = $feature_value->getVariantId();
        }

        $combination_id = $product_repository->generateCombinationId($feature_values);

        if (isset($combination_id_map[$combination_id], $combinations[$combination_id_map[$combination_id]])) {
            $combination_key = $combination_id_map[$combination_id];
            $combination = $combinations[$combination_key];

            db_query(
                'UPDATE ?:products SET ?u WHERE product_id = ?i',
                ['external_id' => $combination['external_id']],
                $group_product->getProductId()
            );
        }
    }
}

function fn_rus_exim_1c_variations_convert_process_post($by_variations, $by_combinations, $product_ids, $counter, $errors)
{
    $import_mode = Registry::get('addons.rus_exim_1c.exim_1c_import_mode_offers');

    if ($by_combinations && $counter['products_with_combinations'] && $import_mode !== 'variations') {
        Settings::instance()->updateValue('exim_1c_import_mode_offers', 'variations', 'rus_exim_1c');
    }
}

function fn_rus_exim_1c_convert_variant_name_into_array($variant_name)
{
    if (strpos($variant_name, ', ') === false && strpos($variant_name, ': ') === false){
        return $variant_name;
    }

    $result = [];
    $options = explode(', ', $variant_name);
    foreach ($options as $option) {
        list($option_name) = explode(': ', $option);
        $option_value = str_replace($option_name . ': ', '', $option);
        $result[$option_name] =  [
            'option_name'  => $option_name,
            'option_value' => $option_value
        ];
    }

    return $result;
}

function fn_rus_exim_1c_get_combination_from_array($options_variants)
{
    $combination = '';
    foreach ($options_variants as $option_id => $option_variant){
        $variant = sprintf('%s_%s', $option_id, $option_variant);
        $combination = (empty($combination)) ? $variant : sprintf('%s_%s', $combination, $variant);
    }

    return $combination;
}