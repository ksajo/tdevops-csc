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

include_once(Registry::get('config.dir.addons') . 'yml_export/schemas/exim/product_yml_options.functions.php');
include_once(Registry::get('config.dir.schemas') . 'exim/products.functions.php');

$schema = array(
    'section' => 'products',
    'name' => __('product_yml2_options'),
    'pattern_id' => 'product_yml_options',
    'key' => array('option_id'),
    'order' => 30,
    'table' => 'product_options',
    'permissions' => array(
        'import' => 'manage_catalog',
        'export' => 'view_catalog',
    ),
    'export_notice' => __('yml_export.exim_notice'),
    'condition' => array(
        'use_company_condition' => true,
    ),
    'order_by' => 'option_name, option_id',
    'references' => array(
        'products' => array(
            'reference_fields' => array('product_id' => '#product_options.product_id'),
            'join_type' => 'INNER'
        ),
        'product_options_descriptions' => array(
            'reference_fields' => array('option_id' => '#key', 'lang_code' => DESCR_SL),
            'join_type' => 'LEFT'
        ),
        'product_descriptions' => array(
            'reference_fields' => array('product_id' => '#product_options.product_id', 'lang_code' => DESCR_SL),
            'join_type' => 'LEFT'
        )
    ),
    'export_fields' => array(
        'Option ID' => array(
            'required' => true,
            'alt_key' => true,
            'db_field' => 'option_id',
        ),
        'Option name' => array(
            'table' => 'product_options_descriptions',
            'db_field' => 'option_name',
            'export_only' => true,
        ),
        'Product ID' => array(
            'db_field' => 'product_id',
            'export_only' => true,
        ),
        'Product name' => array(
            'table' => 'product_descriptions',
            'db_field' => 'product',
            'export_only' => true,
        ),
        'Product code' => array(
            'table' => 'products',
            'db_field' => 'product_code',
            'export_only' => true,
        ),
        'YML option' => array(
            'db_field' => 'yml2_type_options',
            'process_get' => array('fn_export_yml2_option', '#this'),
            'convert_put' => array('fn_import_yml2_option', '#this')
        ),
        'YML option param' => array(
            'db_field' => 'yml2_option_param',
        ),
        'Product status' => array(
            'table' => 'products',
            'db_field' => 'status',
            'export_only' => true,
        ),
    ),
);

return $schema;
