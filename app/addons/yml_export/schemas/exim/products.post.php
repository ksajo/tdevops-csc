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

$schema['export_fields']['YML Brand'] = array (
    'db_field' => 'yml2_brand'
);
$schema['export_fields']['YML Country of origin'] = array (
    'db_field' => 'yml2_origin_country'
);
$schema['export_fields']['YML Allow retail store purchase'] = array (
    'db_field' => 'yml2_store'
);
$schema['export_fields']['YML Allow booking and self delivery'] = array (
    'db_field' => 'yml2_pickup'
);
$schema['export_fields']['YML Adult'] = array (
    'db_field' => 'yml2_adult'
);
$schema['export_fields']['YML Allow delivery'] = array (
    'db_field' => 'yml2_delivery'
);
$schema['export_fields']['YML Allow delivery options'] = array (
    'db_field' => 'yml2_delivery_options',
    'convert_put' => array('fn_yml_import_delivery_options', '#this'),
    'process_get' => array('fn_yml_export_delivery_options', '#this')
);
$schema['export_fields']['YML Exclude from prices'] = array (
    'process_get' => array('fn_yml_export_exclude_prices', '#key'),
    'process_put' => array('fn_yml_import_exclude_prices', '#key', '#this'),
    'linked' => false,
);
$schema['export_fields']['YML Basic bid'] = array (
    'db_field' => 'yml2_bid'
);
$schema['export_fields']['YML Card bid'] = array (
    'db_field' => 'yml2_cbid'
);
$schema['export_fields']['YML Fee'] = array (
    'db_field' => 'yml2_fee'
);
$schema['export_fields']['YML Model'] = array (
    'db_field' => 'yml2_model'
);
$schema['export_fields']['YML Sales notes'] = array (
    'db_field' => 'yml2_sales_notes',
);
$schema['export_fields']['YML typePrefix'] = array (
    'db_field' => 'yml2_type_prefix',
);
$schema['export_fields']['YML Offer type'] = array (
    'db_field' => 'yml2_offer_type',
);
$schema['export_fields']['YML Market category'] = array (
    'db_field' => 'yml2_market_category',
);
$schema['export_fields']['YML Manufacturer warranty'] = array (
    'db_field' => 'yml2_manufacturer_warranty',
);
$schema['export_fields']['YML Lifetime'] = array (
    'db_field' => 'yml2_expiry',
);
$schema['export_fields']['YML Market Ordering'] = array (
    'db_field' => 'yml2_cpa',
);
$schema['export_fields']['YML Description'] = array (
    'db_field' => 'yml2_description',
);

return $schema;
