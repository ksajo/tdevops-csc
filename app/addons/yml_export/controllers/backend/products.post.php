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

if ($mode == 'manage') {

    $selected_fields = Tygh::$app['view']->getTemplateVars('selected_fields');

    $selected_fields[] = array('name' => '[data][yml2_brand]', 'text' => 'YML ' . __('yml2_brand'));
    $selected_fields[] = array('name' => '[data][yml2_offer_type]', 'text' => 'YML ' . __('yml2_offer_type'));
    $selected_fields[] = array('name' => '[data][yml2_origin_country]', 'text' => 'YML ' . __('yml2_country'));
    $selected_fields[] = array('name' => '[data][yml2_store]', 'text' => 'YML ' . __('yml2_store'));
    $selected_fields[] = array('name' => '[data][yml2_pickup]', 'text' => 'YML ' . __('yml2_pickup'));
    $selected_fields[] = array('name' => '[data][yml2_delivery]', 'text' => 'YML ' . __('yml2_delivery'));
    $selected_fields[] = array('name' => '[data][yml2_adult]', 'text' => 'YML ' . __('yml2_adult'));
    $selected_fields[] = array('name' => '[data][yml2_exclude_price_ids]', 'text' => 'YML ' . __('yml_export.yml2_exclude_export'));
    $selected_fields[] = array('name' => '[data][yml2_bid]', 'text' => 'YML ' . __('yml2_bid'));
    $selected_fields[] = array('name' => '[data][yml2_cbid]', 'text' => 'YML ' . __('yml2_cbid'));
    $selected_fields[] = array('name' => '[data][yml2_fee]', 'text' => 'YML ' . __('yml_export.fee'));
    $selected_fields[] = array('name' => '[data][yml2_model]', 'text' => 'YML ' . __('yml2_model'));
    $selected_fields[] = array('name' => '[data][yml2_market_category]', 'text' => 'YML ' . __('yml2_market_category'));
    $selected_fields[] = array('name' => '[data][yml2_sales_notes]', 'text' => 'YML ' . __('yml2_sales_notes'));
    $selected_fields[] = array('name' => '[data][yml2_type_prefix]', 'text' => 'YML ' . __('yml2_type_prefix'));
    $selected_fields[] = array('name' => '[data][yml2_manufacturer_warranty]', 'text' => 'YML ' . __('yml2_manufacturer_warranty'));
    $selected_fields[] = array('name' => '[data][yml2_cpa]', 'text' => 'YML ' . __('yml_export.yml2_cpa'));
    $selected_fields[] = array('name' => '[data][yml2_description]', 'text' => 'YML ' . __('yml_export.yml2_description'));

    Tygh::$app['view']->assign('selected_fields', $selected_fields);

} elseif ($mode == 'm_update') {

    $selected_fields = $_SESSION['selected_fields'];

    $field_groups = Tygh::$app['view']->getTemplateVars('field_groups');
    $filled_groups = Tygh::$app['view']->getTemplateVars('filled_groups');
    $field_names = Tygh::$app['view']->getTemplateVars('field_names');

    $offer_types = fn_get_schema('yml', 'offer_types');
    unset($offer_types['common']);

    if (!empty($selected_fields['data']['yml2_brand'])) {
        $field_groups['A']['yml2_brand'] = 'products_data';
        $filled_groups['A']['yml2_brand'] = 'YML ' . __('yml2_brand');
        unset($field_names['yml2_brand']);
    }

    if (!empty($selected_fields['data']['yml2_offer_type'])) {
        $field_groups['S']['yml2_offer_type']['name'] = 'products_data';
        $field_groups['S']['yml2_offer_type']['variants'] = $offer_types;

        $filled_groups['S']['yml2_offer_type'] = 'YML ' . __('yml2_offer_type');
        unset($field_names['yml2_offer_type']);
    }

    if (!empty($selected_fields['data']['yml2_cpa'])) {
        $field_groups['S']['yml2_cpa']['name'] = 'products_data';
        $field_groups['S']['yml2_cpa']['variants'] = array(
            'N' => 'no',
            'Y' => 'yes',
        );
        $filled_groups['S']['yml2_cpa'] = __('yml_export.yml2_cpa');
        unset($field_names['yml2_cpa']);
    }

    if (!empty($selected_fields['data']['yml2_sales_notes'])) {
        $field_groups['A']['yml2_sales_notes'] = 'products_data';
        $filled_groups['A']['yml2_sales_notes'] = 'YML ' . __('yml2_sales_notes');
        unset($field_names['yml2_sales_notes']);
    }

    if (!empty($selected_fields['data']['yml2_market_category'])) {
        $field_groups['A']['yml2_market_category'] = 'products_data';
        $filled_groups['A']['yml2_market_category'] = 'YML ' . __('yml2_export_market_category');
        unset($field_names['yml2_market_category']);
    }

    if (!empty($selected_fields['data']['yml2_type_prefix'])) {
        $field_groups['A']['yml2_type_prefix'] = 'products_data';
        $filled_groups['A']['yml2_type_prefix'] = 'YML ' . __('yml2_type_prefix');
        unset($field_names['yml2_type_prefix']);
    }

    if (!empty($selected_fields['data']['yml2_origin_country'])) {
        $field_groups['A']['yml2_origin_country'] = 'products_data';
        $filled_groups['A']['yml2_origin_country'] = 'YML ' . __('yml2_country');
        unset($field_names['yml2_origin_country']);
    }

    if (!empty($selected_fields['data']['yml2_store'])) {
        $field_groups['S']['yml2_store']['name'] = 'products_data';
        $field_groups['S']['yml2_store']['variants'] = array(
            'Y' => 'yes',
            'N' => 'no',
        );
        $filled_groups['S']['yml2_store'] = 'YML ' . __('yml2_store');
        unset($field_names['yml2_store']);
    }

    if (!empty($selected_fields['data']['yml2_pickup'])) {
        $field_groups['S']['yml2_pickup']['name'] = 'products_data';
        $field_groups['S']['yml2_pickup']['variants'] = array(
            'Y' => 'yes',
            'N' => 'no',
        );
        $filled_groups['S']['yml2_pickup'] = 'YML ' . __('yml2_pickup');
        unset($field_names['yml2_pickup']);
    }

    if (!empty($selected_fields['data']['yml2_delivery'])) {
        $field_groups['S']['yml2_delivery']['name'] = 'products_data';
        $field_groups['S']['yml2_delivery']['variants'] = array(
            'Y' => 'yes',
            'N' => 'no',
        );
        $filled_groups['S']['yml2_delivery'] = 'YML ' . __('yml2_delivery');
        unset($field_names['yml2_delivery']);
    }

    if (!empty($selected_fields['data']['yml2_adult'])) {
        $field_groups['S']['yml2_adult']['name'] = 'products_data';
        $field_groups['S']['yml2_adult']['variants'] = array(
            'N' => 'no',
            'Y' => 'yes',
        );
        $filled_groups['S']['yml2_adult'] = 'YML ' . __('yml2_adult');
        unset($field_names['yml2_adult']);
    }


    if (!empty($selected_fields['data']['yml2_cost'])) {
        $field_groups['A']['yml2_cost'] = 'products_data';
        $filled_groups['A']['yml2_cost'] = 'YML ' . __('yml2_cost');
        unset($field_names['yml2_cost']);
    }

    if (!empty($selected_fields['data']['yml2_exclude_price_ids'])) {
        $field_names['yml2_exclude_price_ids'];
        Tygh::$app['view']->assign('yml2_exclude_prices', fn_yml_get_price_lists());
    }

    if (!empty($selected_fields['data']['yml2_bid'])) {
        $field_groups['A']['yml2_bid'] = 'products_data';
        $filled_groups['A']['yml2_bid'] = 'YML ' . __('yml2_bid');
        unset($field_names['yml2_bid']);
    }

    if (!empty($selected_fields['data']['yml2_cbid'])) {
        $field_groups['A']['yml2_cbid'] = 'products_data';
        $filled_groups['A']['yml2_cbid'] = 'YML ' . __('yml2_cbid');
        unset($field_names['yml2_cbid']);
    }

    if (!empty($selected_fields['data']['yml2_fee'])) {
        $field_groups['A']['yml2_fee'] = 'products_data';
        $filled_groups['A']['yml2_fee'] = 'YML ' . __('yml_export.fee');
        unset($field_names['yml2_fee']);
    }

    if (!empty($selected_fields['data']['yml2_model'])) {
        $field_groups['A']['yml2_model'] = 'products_data';
        $filled_groups['A']['yml2_model'] = 'YML ' . __('yml2_model');
        unset($field_names['yml2_model']);
    }
    
    if (!empty($selected_fields['data']['yml2_manufacturer_warranty'])) {
        $field_groups['S']['yml2_manufacturer_warranty']['name'] = 'products_data';
        $field_groups['S']['yml2_manufacturer_warranty']['variants'] = array(
            '' => 'yml2_none',
            'N' => 'yml2_false',
            'Y' => 'yml2_true',
        );
        $filled_groups['S']['yml2_manufacturer_warranty'] = 'YML ' . __('yml2_manufacturer_warranty');
        unset($field_names['yml2_manufacturer_warranty']);
    }

    if (!empty($selected_fields['data']['yml2_description'])) {
        $field_groups['D']['yml2_description'] = 'products_data';
        $filled_groups['D']['yml2_description'] = 'YML ' . __('yml_export.yml2_description');
        unset($field_names['yml2_description']);
    }

    Tygh::$app['view']->assign('field_groups', $field_groups);
    Tygh::$app['view']->assign('filled_groups', $filled_groups);
    Tygh::$app['view']->assign('field_names', $field_names);


    $products_data = Tygh::$app['view']->getTemplateVars('products_data');

    foreach($products_data as &$product) {

        $yml2_exclude = fm_yml_get_exclude_products($product['product_id']);

        $yml2_exclude_prices = array();
        foreach($yml2_exclude as $exclude) {
            $yml2_exclude_prices[] = $exclude['price_id'];
        }

        $product['yml2_exclude_prices'] = $yml2_exclude_prices;
    }

    Tygh::$app['view']->assign('products_data', $products_data);

} elseif ($mode == 'update' || $mode == 'add') {
    $offer_types = fn_get_schema('yml', 'offer_types');
    unset($offer_types['common']);

    if (!empty($_REQUEST['product_id'])) {
        $product = Tygh::$app['view']->getTemplateVars('product_data');
        $parent_offer_type = fn_yml_get_parent_categories_field('yml2_offer_type', $product['main_category'], true);

        $yml2_model_category = fn_yml_get_parent_categories_field('yml2_model', $product['main_category'], true);
        $yml2_type_prefix_category = fn_yml_get_parent_categories_field('yml2_type_prefix', $product['main_category'], true);
        $yml2_market_category = fn_yml_get_parent_categories_field('yml2_market_category', $product['main_category'], true);

        $yml2_parent_offer_type_name = '';
        if (!empty($parent_offer_type)) {
            $yml2_parent_offer_type_name = $offer_types[$parent_offer_type];
        }

        Tygh::$app['view']->assign('yml2_parent_offer_type_name', $yml2_parent_offer_type_name);
        Tygh::$app['view']->assign('offer_type_parent_category', $parent_offer_type);
        Tygh::$app['view']->assign('yml2_model_category', $yml2_model_category);
        Tygh::$app['view']->assign('yml2_type_prefix_category', $yml2_type_prefix_category);
        Tygh::$app['view']->assign('yml2_market_category', $yml2_market_category);

        $yml2_exclude = fm_yml_get_exclude_products($_REQUEST['product_id']);

        $yml2_exclude_prices = array();

        foreach($yml2_exclude as $exclude) {
            $yml2_exclude_prices[] = $exclude['price_id'];
        }

        Tygh::$app['view']->assign('yml2_exclude_prices', $yml2_exclude_prices);

        $yml2_prices = fn_yml_get_price_lists();

        Tygh::$app['view']->assign('yml2_prices', $yml2_prices);
    }

    Tygh::$app['view']->assign('yml2_offer_types', $offer_types);

    Registry::set('navigation.tabs.yml', array (
        'title' => __('yml_export'),
        'js' => true
    ));
}
