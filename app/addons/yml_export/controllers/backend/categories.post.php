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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return array(CONTROLLER_STATUS_OK);
}

if ($mode == 'update') {

    Registry::set('navigation.tabs.yml', array (
        'title' => __('yml_export'),
        'js' => true
    ));

    $offer_class = "\\Tygh\\Ym\\Offers\\" . fn_camelize('base');

    if (class_exists($offer_class)) {
        $offer = new $offer_class();
    } else {
        throw new \Exception("The wrong offer class");
    }

    $category_data = Tygh::$app['view']->getTemplateVars('category_data');
    $offer_common_features_list = $offer->getCommonFeatures();

    $offers_features = array();
    foreach($offer_common_features_list as $offer_feature_key => $offer_feature_name) {

        if (!in_array($offer_feature_key, array('model', 'typePrefix'))) {
            continue;
        }

        if (is_array($offer_feature_name)) {
            $offers_features['common'][$offer_feature_key] = array();

            if (!empty($category_data['yml2_' . fn_uncamelize($offer_feature_key) . '_select'])) {
                $offer_data = explode('.', $category_data['yml2_' . fn_uncamelize($offer_feature_key) . '_select']);
                $offers_features['common'][$offer_feature_key] = array(
                    'type' => isset($offer_data[0]) ? $offer_data[0] : '',
                    'value' => isset($offer_data[1]) ? $offer_data[1] : ''
                );
            }

            $offers_features['common'][$offer_feature_key] = array_merge($offers_features['common'][$offer_feature_key], $offer_feature_name);
        }
    }

    Tygh::$app['view']->assign('yml2_offer_features', $offers_features);
    Tygh::$app['view']->assign('yml2_model_select', $offers_features['common']['model']);
    Tygh::$app['view']->assign('yml2_type_prefix_select', $offers_features['common']['typePrefix']);

    $params = array(
        'category_ids' => $category_data['category_id'],
        'plain' => true
    );

    list($features) = fn_get_product_features($params);
    Tygh::$app['view']->assign('features', $features);

    $parent = false;
    if (!empty($_REQUEST['category_id'])) {
        $category_id = $_REQUEST['category_id'];

    } elseif (!empty($_REQUEST['parent_id'])) {
        $category_id = $_REQUEST['parent_id'];
        $parent = true;
    }

    $offer_type_parent_category = '';
    if (!empty($category_id)) {
        $offer_type_parent_category = fn_yml_get_parent_categories_field('yml2_offer_type', $category_id, $parent);

        $yml2_model_category = fn_yml_get_parent_categories_field('yml2_model', $category_id, $parent);
        $yml2_type_prefix_category = fn_yml_get_parent_categories_field('yml2_type_prefix', $category_id, $parent);
        $yml2_market_category = fn_yml_get_parent_categories_field('yml2_market_category', $category_id, $parent);

        $yml2_parent_type_prefix_select = fn_yml_get_parent_categories_field('yml2_type_prefix_select', $category_id, $parent);
        $yml2_parent_type_prefix_select = explode('.', $yml2_parent_type_prefix_select);

        if (fn_is_empty($yml2_parent_type_prefix_select)) {
            $yml2_parent_type_prefix_select = array();
        }

        $yml2_parent_model_select = fn_yml_get_parent_categories_field('yml2_model_select', $category_id, $parent);
        $yml2_parent_model_select = explode('.', $yml2_parent_model_select);

        if (fn_is_empty($yml2_parent_model_select)) {
            $yml2_parent_model_select = array();
        }

        Tygh::$app['view']->assign('yml2_model_category', $yml2_model_category);
        Tygh::$app['view']->assign('yml2_type_prefix_category', $yml2_type_prefix_category);
        Tygh::$app['view']->assign('yml2_market_category', $yml2_market_category);
        Tygh::$app['view']->assign('yml2_parent_type_prefix_select', $yml2_parent_type_prefix_select);
        Tygh::$app['view']->assign('yml2_parent_model_select', $yml2_parent_model_select);
    }

    $offer_types = fn_get_schema('yml', 'offer_types');
    unset($offer_types['common']);

    Tygh::$app['view']->assign('yml2_offer_types', $offer_types);
    Tygh::$app['view']->assign('offer_type_parent_category', $offer_type_parent_category);

    if (!empty($offer_type_parent_category)) {
        Tygh::$app['view']->assign('offer_type_parent_name', $offer_types[$offer_type_parent_category]);
    }
}
