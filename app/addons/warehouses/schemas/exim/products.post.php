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

use Tygh\Addons\AdvancedImport\Readers\Xml;
use Tygh\Enum\Addons\AdvancedImport\ImportStrategies;
use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

require_once(__DIR__ . '/products.functions.php');

foreach (fn_warehouses_exim_get_list() as $warehouse_name => $warehouse) {
    $field = sprintf("%s (Warehouse)", $warehouse['name']);

    $schema['export_fields'][$field] = [
        'process_get' => ['fn_warehouses_exim_get_amount', '#key', $warehouse['store_location_id']],
        'export_only' => true,
        'linked'      => false,
        'warehouse_id'  => $warehouse['store_location_id']
    ];
}

$schema['pre_processing']['reset_warehouses_inventory'] = [
    'function'    => 'fn_warehouses_exim_reset_inventory',
    'args'        => ['$import_data', '@reset_inventory'],
    'import_only' => true,
];

$schema['options']['reset_inventory']['description'] = 'warehouses.exim_reset_inventory_tooltip';

return $schema;
