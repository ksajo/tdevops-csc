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

include_once(Registry::get('config.dir.addons') . 'yml_export/schemas/yml/import_products.functions.php');

$schema = array (
    'yml2_brand' => array(
        'type' => 'field',
        'name' => 'yml_brand'
    ),
    'yml2_origin_country' => array(
        'type' => 'field',
        'name' => 'yml_origin_country'
    ),
    'yml2_store' => array(
        'type' => 'field',
        'name' => 'yml_store'
    ),
    'yml2_pickup' => array(
        'type' => 'field',
        'name' => 'yml_pickup'
    ),
    'yml2_delivery' => array(
        'type' => 'field',
        'name' => 'yml_delivery'
    ),
    'yml2_adult' => array(
        'type' => 'field',
        'name' => 'yml_adult'
    ),
    'yml2_delivery_options' => array(
        'type' => 'function',
        'name' => 'fn_yml_export_import_delivery_options'
    ),
    'yml2_bid'=> array(
        'type' => 'field',
        'name' => 'yml_bid'
    ),
    'yml2_cbid'=> array(
        'type' => 'field',
        'name' => 'yml_cbid'
    ),
    'yml2_model'=> array(
        'type' => 'field',
        'name' => 'yml_model'
    ),
    'yml2_sales_notes'=> array(
        'type' => 'field',
        'name' => 'yml_sales_notes'
    ),
    'yml2_type_prefix'=> array(
        'type' => 'field',
        'name' => 'yml_type_prefix'
    ),
    'yml2_market_category'=> array(
        'type' => 'field',
        'name' => 'yml_market_category'
    ),
    'yml2_manufacturer_warranty'=> array(
        'type' => 'field',
        'name' => 'yml_manufacturer_warranty'
    ),
);

return $schema;