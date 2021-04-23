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

use Tygh\Registry;
use Tygh\Ym\Yml2;
use Tygh\Enum\ProductFeatures;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'update_offers') {
        if (!empty($_REQUEST['data']['ym_features'])) {
            fn_yml_update_offers_features($_REQUEST['data']['ym_features']);
        }

        $suffix = '.offers_params';

    } elseif ($mode == 'update') {

        $price_id = $_REQUEST['price_id'];
        if (!empty($_REQUEST['pricelist_data'])) {
            $price_id = fn_yml_update_price_list($price_id, $_REQUEST['pricelist_data']);

            if ($price_id === false) {
                return array(CONTROLLER_STATUS_REDIRECT, 'yml.manage');
            }
        }

        $suffix = ".update?price_id=" . $price_id;

    } elseif ($mode == 'delete_price_list') {
        fn_yml_delete_price_lists(array($_REQUEST['price_id']));
        $suffix = '.manage';

    } elseif ($mode == 'm_delete_price_lists') {

        if (!empty($_REQUEST['price_ids'])) {
            fn_yml_delete_price_lists($_REQUEST['price_ids']);
        }

        $suffix = '.manage';

    } elseif  ($mode == 'stop_generate') {

        $price_id = $_REQUEST['price_id'];
        fn_yml_stop_generate($price_id);

        $suffix = ".manage";

    }

    return array(CONTROLLER_STATUS_OK, 'yml' . $suffix);
}

if ($mode == 'offers_params') {

    $offers = fn_get_schema('yml', 'offer_types');
    $offers_features_values = fn_yml_get_offers_features();

    $offer_class = "\\Tygh\\Ym\\Offers\\" . fn_camelize('base');

    if (class_exists($offer_class)) {
        $offer = new $offer_class();
    } else {
        throw new \Exception("The wrong offer");
    }

    $offer_common_features_list = $offer->getCommonFeatures();

    $offers_features = array();
    foreach($offer_common_features_list as $offer_feature_key => $offer_feature_name) {

        if (is_array($offer_feature_name)) {
            $offers_features['common'][$offer_feature_key] = array();
            if (!empty($offers_features_values['common'][$offer_feature_key])) {
                $offers_features['common'][$offer_feature_key] = $offers_features_values['common'][$offer_feature_key];
            }

            $offers_features['common'][$offer_feature_key] = array_merge($offers_features['common'][$offer_feature_key], $offer_feature_name);

            if (isset($offers_features['common'][$offer_feature_key]['type']) && $offers_features['common'][$offer_feature_key]['type'] == 'feature') {
                $offers_features['common'][$offer_feature_key]['feature_name'] = fn_get_feature_name($offers_features['common'][$offer_feature_key]['value']);
            }

        } elseif (!empty($offers_features_values['common'][$offer_feature_name])) {
            $offers_features['common'][$offer_feature_name] = $offers_features_values['common'][$offer_feature_name];

            if (isset($offers_features['common'][$offer_feature_name]['type']) && $offers_features['common'][$offer_feature_name]['type'] == 'feature') {
                $offers_features['common'][$offer_feature_name]['feature_name'] = fn_get_feature_name($offers_features['common'][$offer_feature_name]['value']);
            }

        } else {
            $offers_features['common'][$offer_feature_name] = array();
        }


    }

    foreach($offers as $offer_code => $offer_name) {
        $offer_class = "\\Tygh\\Ym\\Offers\\" . fn_camelize($offer_code);

        if (class_exists($offer_class)) {
            $offer = new $offer_class();
        }

        $offer_features_list = $offer->getFeatures();
        foreach($offer_features_list as $offer_feature_name) {
            if (!empty($offers_features_values[$offer_code][$offer_feature_name])) {
                $offers_features[$offer_code][$offer_feature_name] = $offers_features_values[$offer_code][$offer_feature_name];

                if (isset($offers_features[$offer_code][$offer_feature_name]['type']) && $offers_features[$offer_code][$offer_feature_name]['type'] == 'feature') {
                    $offers_features[$offer_code][$offer_feature_name]['feature_name'] = fn_get_feature_name($offers_features[$offer_code][$offer_feature_name]['value']);
                }
            } else {
                $offers_features[$offer_code][$offer_feature_name] = array();
            }
        }
    }

    Tygh::$app['view']->assign('yml_offer_types', $offers);
    Tygh::$app['view']->assign('yml_offer_features', $offers_features);

} elseif ($mode == 'manage') {

    $price_lists = fn_yml_get_price_lists();
    $generation_statuses = array();

    foreach($price_lists as $price_id => $price) {
        $price_lists[$price_id]['offset'] = 0;
        $price_lists[$price_id]['offset'] = fn_get_storage_data('yml2_export_offset_' . $price_id);
        $price_lists[$price_id]['count'] = fn_get_storage_data('yml2_export_count_' . $price_id);

        $time = fn_get_storage_data('yml2_export_time_' . $price_id);
        $price_lists[$price_id]['time'] = "";
        if (!empty($time)) {
            $price_lists[$price_id]['time'] = fn_date_format($time, Registry::get('settings.Appearance.date_format') . " " . Registry::get('settings.Appearance.time_format'));
        }

        $runtime = fn_get_storage_data('yml2_export_start_time_' . $price_id);

        $price_lists[$price_id]['runtime'] = 0;
        if (!empty($runtime)) {
            $time = mktime(null, null, time() - $runtime);
            //$mktime = mktime(null, null, time() - $runtime);
            $price_lists[$price_id]['runtime'] = date("H:i:s", $time);
        }

        if ($price_lists[$price_id]['offset'] > $price_lists[$price_id]['count']) {
            $price_lists[$price_id]['offset'] = $price_lists[$price_id]['count'];
        }

        $price_lists[$price_id]['generate_link'] = fn_yml_get_generate_link($price);
        $price_lists[$price_id]['get_link'] = fn_yml_get_link($price);

        $generation_statuses[$price_id] = fn_get_storage_data('yml2_status_generate_' . $price_id);

        if ($generation_statuses[$price_id] == 'redirect') {
            $price_lists[$price_id]['count'] = 0;
            $generation_statuses[$price_id] = 'abort';
        }
    }

    Tygh::$app['view']->assign('price_lists', $price_lists);
    Tygh::$app['view']->assign('generation_statuses', $generation_statuses);

    if (defined('AJAX_REQUEST')) {
        Tygh::$app['view']->display('addons/yml_export/views/yml/manage.tpl');
        exit();
    }

} elseif ($mode == "update") {

    $price_list = array();
    $schema_price_list = fn_get_schema('yml', 'price_list');
    $schema_price_list = $schema_price_list['default'];
    $schema_price_list['general']['shop_name']['default'] = Registry::get('runtime.company_data.company');

    $simple = Registry::get('runtime.simple_ultimate');
    $schema_price_list['export_data']['export_shared_products']['disabled'] = !empty($simple) ? true : false;

    if (!empty($_REQUEST['price_id'])) {
        $price_list = fn_yml_get_price_list($_REQUEST['price_id']);

        if (empty($price_list)) {
            return array(CONTROLLER_STATUS_OK, 'yml.manage');
        }

        $price_list['company_id'] = empty($price_list['company_id']) ? Registry::get('runtime.company_id') : $price_list['company_id'];

        $repository = Tygh::$app['storefront.repository'];
        $storefront = $repository->findByCompanyId($price_list['company_id']) ?: $repository->findDefault();

        $price_list['storefront_id'] = $storefront->storefront_id;

        Tygh::$app['view']->assign('price', $price_list);
    }

    $tabs = array();
    $tabs_codes = array_keys($schema_price_list);
    foreach($tabs_codes as $tab_code) {
        $tabs[$tab_code] = array (
            'title' => __('yml_export.tab_' . $tab_code),
            'js' => true
        );
    }

    Registry::set('navigation.tabs', $tabs);
    Tygh::$app['view']->assign('price_lists', $schema_price_list);

    if (!empty($price_list['param_id'])) {

        $yml2_console_cmd = fn_yml_get_console_cmd($price_list);
        $yml2_information = __('yml_export.text_available_in_customer', array(
            '[yml2_generate_url]' => fn_yml_get_generate_link($price_list),
            '[yml2_get_url]' => fn_yml_get_link($price_list),
        ));

        if ($yml2_console_cmd) {
            $yml2_cron_command_information = __('yml_export.text_cron_command', array(
                '[yml2_console_generate]' => $yml2_console_cmd
            ));

            Tygh::$app['view']->assign('yml2_cron_command_information', $yml2_cron_command_information);
        }

        Tygh::$app['view']->assign('yml2_categories_information', __('yml_export.categories_information'));

        Tygh::$app['view']->assign('yml2_information', $yml2_information);
        Tygh::$app['view']->assign('access_key', $price_list['param_key']);
    } else {
        Tygh::$app['view']->assign('access_key', fn_yml_get_key($price_list));
    }

} elseif ($mode == "get_variants_list") {

    $offer = $_REQUEST['offer'];
    $offer_key = $_REQUEST['offer_key'];

    $page_number = isset($_REQUEST['page']) ? (int) $_REQUEST['page'] : 1;
    $page_size = isset($_REQUEST['page_size']) ? (int) $_REQUEST['page_size'] : 3;
    $search_query = isset($_REQUEST['q']) ? $_REQUEST['q'] : null;
    $lang_code = isset($_REQUEST['lang_code']) ? $_REQUEST['lang_code'] : CART_LANGUAGE;

    if ($offer == 'common') {

        $offer_class = "\\Tygh\\Ym\\Offers\\" . fn_camelize('base');

        if (class_exists($offer_class)) {
            $offer = new $offer_class();
        } else {
            throw new \Exception("The wrong offer");
        }

        $offer_common_features_list = $offer->getCommonFeatures();

        $offers_features = array();
        foreach($offer_common_features_list as $offer_feature_key => $offer_feature_name) {

            if (is_array($offer_feature_name)) {
                $offers_features[$offer_feature_key] = array();
                if (!empty($offers_features_values[$offer_feature_key])) {
                    $offers_features[$offer_feature_key] = $offers_features_values[$offer_feature_key];
                }

                $offers_features[$offer_feature_key] = array_merge($offers_features[$offer_feature_key], $offer_feature_name);

            } elseif (!empty($offers_features_values[$offer_feature_name])) {
                $offers_features[$offer_feature_name] = $offers_features_values[$offer_feature_name];

            } else {
                $offers_features[$offer_feature_name] = array();
            }
        }

    } else {
        $offer_class = "\\Tygh\\Ym\\Offers\\" . fn_camelize($offer);

        if (class_exists($offer_class)) {
            $offer = new $offer_class();
        }

        $offer_features_list = $offer->getFeatures();
        foreach($offer_features_list as $offer_feature_name) {
            if (!empty($offers_features_values[$offer_feature_name])) {
                $offers_features[$offer_feature_name] = $offers_features_values[$offer_feature_name];
            } else {
                $offers_features[$offer_feature_name] = array();
            }
        }
    }

    $objects = array();
    $objects[] = array(
        'id' => 'empty',
        'text' => __('empty'),
    );
    if ($page_number == 1) {
        if (!empty($offers_features[$offer_key]['product_fields'])) {

            $product_objects = array();
            foreach($offers_features[$offer_key]['product_fields'] as $product_field) {
                $product_objects[] = array(
                    'id' => 'product.' . $product_field,
                    'text' => __('yml2_product_field_' . $product_field),
                );
            }

            $objects[] = array(
                'id' => 'product_fields',
                'text' => __('yml_export.product_fields'),
                'children' => $product_objects
            );
        }
    }

    $statuses = array('A');
    $fields = db_quote("?:product_features.feature_id, ?:product_features_descriptions.description");
    $join = db_quote("LEFT JOIN ?:product_features_descriptions ON ?:product_features_descriptions.feature_id = ?:product_features.feature_id AND ?:product_features_descriptions.lang_code = ?s", $lang_code);
    $condition = db_quote("AND ?:product_features.status IN (?a) AND ?:product_features.feature_type != ?s", $statuses, ProductFeatures::GROUP);

    if (!empty($search_query)) {
        $condition .= db_quote(' AND ?:product_features_descriptions.description LIKE ?l',
            '%' . trim($search_query) . '%'
        );
    }

    $total_items = 1;
    if (!empty($page_size)) {
        $total_items = db_get_field("SELECT COUNT(*) FROM ?:product_features $join WHERE 1 $condition");

        $limit = db_paginate($page_number, $page_size, $total_items);
    }

    $features = db_get_hash_array("SELECT $fields FROM ?:product_features $join WHERE 1 $condition ORDER BY ?:product_features.position $limit", 'feature_id');

    $feature_objects = array();
    foreach($features as $feature) {
        $feature_objects[] = array(
            'id' => 'feature.' . $feature['feature_id'],
            'text' => $feature['description']
        );
    }

    $objects[] = array(
        'id' => 'product_features',
        'text' => __('yml_export.product_features'),
        'children' => $feature_objects
    );

    Tygh::$app['ajax']->assign('objects', $objects);
    Tygh::$app['ajax']->assign('total_objects', $total_items);

    exit;
}
