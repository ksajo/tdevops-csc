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

fn_register_hooks(
    'get_product_data_post',
    'tools_change_status',
    'update_product_pre',
    'update_category_post',
    'save_log',
    'update_product_feature_pre',
    'get_product_feature_data_before_select',
    'get_product_feature_data_post',
    'get_product_features_list_before_select',
    'get_product_features_list_post',
    'get_product_option_data_pre',
    'get_selected_product_options_before_select',
    'get_product_features',
    'get_product_features_post',
    'update_product_feature_variant_before_select',
    'get_product_feature_variant_name_post',
    'get_filters_products_count_before_select_filters',
    'get_filters_products_count_post',
    'get_categories',
    'chown_company',
    ['yml_export_update_product_pre_post', '', 'product_variations'],
    ['yml_export_generate_offers_before_gather_additional_products_data', '', 'product_variations'],
    ['yml_export_generate_offers_before_gather_additional_products_data', '', 'warehouses']
);
