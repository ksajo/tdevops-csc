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
use Tygh\Enum\NotificationSeverity;
use Tygh\Enum\ObjectStatuses;

defined('BOOTSTRAP') or die('Access denied');

/**
 * Converts data from rus_exim_1c to CommerceML addon.
 *
 * @param string $status Addon status
 *
 * @return void
 */
function fn_settings_actions_addons_post_commerceml($status)
{

    if (
        $status !== ObjectStatuses::ACTIVE
        || Registry::ifGet('addons.rus_exim_1c.status', null) === null
        || fn_allowed_for('MULTIVENDOR')
    ) {
        return;
    }

    /** @var \Tygh\Database\Connection $db */
    $db = Tygh::$app['db'];

    $is_old_external_ids_exists = (bool) db_get_field(
        'SELECT ?:products.product_id FROM ?:products'
        . ' LEFT JOIN ?:commerceml_import_entity_map ON ?:products.external_id = ?:commerceml_import_entity_map.entity_id'
            . ' AND ?:commerceml_import_entity_map.entity_type = ?s'
        . ' WHERE ?:products.external_id <> ?s AND ?:products.product_id <> ?:commerceml_import_entity_map.local_id'
        . ' LIMIT 1',
        'product',
        ''
    );


    if ($is_old_external_ids_exists) {
        $objects_to_disabled = [];
        $objects_to_disabled['products'] = [
            'primary_key'    => 'product_id',
            'updated_fields' => [
                'timestamp' => time(),
                'status'    => ObjectStatuses::DISABLED
            ],
            'objects'        => db_get_hash_single_array(
                'SELECT DISTINCT ?:commerceml_import_entity_map.local_id, ?:products.status'
                . ' FROM ?:commerceml_import_entity_map'
                . ' LEFT JOIN ?:products ON ?:products.product_id = ?:commerceml_import_entity_map.local_id'
                . ' WHERE ?:commerceml_import_entity_map.entity_type = ?s AND ?:products.external_id <> ?:commerceml_import_entity_map.entity_id',
                ['local_id', 'status'],
                'product'
            )
        ];

        $objects_to_disabled['categories'] = [
            'primary_key'    => 'category_id',
            'updated_fields' => [
                'timestamp' => time(),
                'status'    => ObjectStatuses::DISABLED
            ],
            'objects'        => db_get_hash_single_array(
                'SELECT DISTINCT ?:commerceml_import_entity_map.local_id, ?:categories.status'
                . ' FROM ?:commerceml_import_entity_map'
                . ' LEFT JOIN ?:categories ON ?:categories.category_id = ?:commerceml_import_entity_map.local_id'
                . ' WHERE ?:commerceml_import_entity_map.entity_type = ?s AND ?:categories.external_id <> ?:commerceml_import_entity_map.entity_id',
                ['local_id', 'status'],
                'category'
            )
        ];

        $objects_to_disabled['product_features'] = [
            'primary_key'    => 'feature_id',
            'updated_fields' => [
                'updated_timestamp' => time(),
                'status'            => ObjectStatuses::DISABLED
            ],
            'objects'        => db_get_hash_single_array(
                'SELECT DISTINCT ?:commerceml_import_entity_map.local_id, ?:product_features.status'
                . ' FROM ?:commerceml_import_entity_map'
                . ' LEFT JOIN ?:product_features ON ?:product_features.feature_id = ?:commerceml_import_entity_map.local_id'
                . ' WHERE ?:commerceml_import_entity_map.entity_type = ?s AND ?:product_features.external_id <> ?:commerceml_import_entity_map.entity_id',
                ['local_id', 'status'],
                'product_feature'
            )
        ];

        if (!empty($objects_to_disabled)) {
            foreach ($objects_to_disabled as $table => $object_to_disabled) {
                $changed_objects = [];
                foreach ($object_to_disabled['objects'] as $id => $status) {
                    if ($status === ObjectStatuses::DISABLED) {
                        continue;
                    }
                    $changed_objects[] = $id;
                }
                if (empty($changed_objects) || empty($object_to_disabled['updated_fields'])) {
                    continue;
                }
                db_query(
                    'UPDATE ?:?p SET ?u WHERE ?p IN (?n)',
                    $table,
                    $object_to_disabled['updated_fields'],
                    $object_to_disabled['primary_key'],
                    $changed_objects
                );
                if ($table !== 'products') {
                    continue;
                }
                fn_set_notification(
                    NotificationSeverity::WARNING,
                    __('notice'),
                    __('commerceml.products_were_disabled_after_addon_was_activated', [
                        '[href]' => fn_url('products.manage?updated_in_hours=1&status=D')
                    ])
                );
            }
        }
        if (Registry::ifGet('addons.warehouses.status', null) !== null) {
            $exim_1c_warehouses = db_get_hash_single_array('SELECT * FROM ?:rus_exim_1c_warehouses', ['warehouse_id', 'external_id']);
            $store_locations_map =  db_get_hash_array(
                'SELECT DISTINCT ?:commerceml_import_entity_map.local_id, ?:commerceml_import_entity_map.entity_id, ?:store_locations.status'
                . ' FROM ?:commerceml_import_entity_map'
                . ' LEFT JOIN ?:store_locations ON ?:commerceml_import_entity_map.local_id = ?:store_locations.store_location_id'
                . ' WHERE ?:commerceml_import_entity_map.entity_type = ?s',
                'local_id',
                'warehouse'
            );
            $warehouse_to_disabled = [];
            foreach ($store_locations_map as $local_id => $store_location) {
                if (
                    (!empty($store_location['status']) && $store_location['status'] === ObjectStatuses::DISABLED)
                     || (in_array($local_id, array_keys($exim_1c_warehouses)) && $store_location['external_id'] === $exim_1c_warehouses[$local_id])
                ) {
                    continue;
                }
                $warehouse_to_disabled[] = $local_id;
            }
            db_query(
                'UPDATE ?:store_locations SET status = ?s WHERE store_location_id IN (?n)',
                ObjectStatuses::DISABLED,
                $warehouse_to_disabled
            );
        }
    }

    $selections = [];
    $selections['products'] = $db->quote(
        'SELECT DISTINCT'
            . ' products.company_id,'
            . ' products.external_id AS entity_id,'
            . ' ?s AS entity_type,'
            . ' product_descriptions.product AS entity_name,'
            . ' products.product_id AS local_id,'
            . ' ?i AS timestamp'
        . ' FROM ?:products AS products'
        . ' LEFT JOIN ?:product_descriptions AS product_descriptions'
            . ' ON products.product_id = product_descriptions.product_id AND product_descriptions.lang_code = ?s'
        . ' WHERE external_id <> ?s',
        'product',
        time(),
        Registry::get('settings.Appearance.backend_default_language'),
        ''
    );

    $selections['variations'] = $db->quote(
        'SELECT'
            . ' entity_map.company_id,'
            . ' SUBSTR(entity_map.entity_id, 1, INSTR(entity_map.entity_id, ?s) - 1) AS entity_id,'
            . ' entity_map.entity_type,'
            . ' entity_map.entity_name,'
            . ' entity_map.local_id,'
            . ' ?i AS timestamp'
        . ' FROM ?:commerceml_import_entity_map as entity_map'
        . ' LEFT JOIN ?:products ON entity_map.local_id = ?:products.product_id'
        . ' WHERE entity_map.entity_id LIKE ?l AND entity_map.entity_type = ?s AND entity_map.entity_name <> ?s AND ?:products.product_type = ?s',
        '#',
        time(),
        '%#%',
        'product',
        '',
        'P'
    );

    $selections['categories'] = $db->quote(
        'SELECT DISTINCT'
            . ' categories.company_id,'
            . ' categories.external_id AS entity_id,'
            . ' ?s AS entity_type,'
            . ' category_descriptions.category AS entity_name,'
            . ' categories.category_id AS local_id,'
            . ' ?i AS timestamp'
        . ' FROM ?:categories AS categories'
        . ' LEFT JOIN ?:category_descriptions AS category_descriptions'
            . ' ON categories.category_id = category_descriptions.category_id AND category_descriptions.lang_code = ?s'
        . ' WHERE external_id <> ?s',
        'category',
        time(),
        Registry::get('settings.Appearance.backend_default_language'),
        ''
    );

    $selections['features'] = $db->quote(
        'SELECT DISTINCT'
            . ' features.company_id,'
            . ' features.external_id AS entity_id,'
            . ' ?s AS entity_type,'
            . ' features_descriptions.description AS entity_name,'
            . ' features.feature_id AS local_id,'
            . ' ?i AS timestamp'
        . ' FROM ?:product_features AS features'
        . ' LEFT JOIN ?:product_features_descriptions AS features_descriptions'
            . ' ON features.feature_id = features_descriptions.feature_id AND features_descriptions.lang_code = ?s'
        . ' WHERE external_id <> ?s',
        'product_feature',
        time(),
        Registry::get('settings.Appearance.backend_default_language'),
        ''
    );

    $selections['feature_variants'] = $db->quote(
        'SELECT DISTINCT'
            . ' features.company_id,'
            . ' CONCAT(features.external_id, ?s, feature_variants.external_id) AS entity_id,'
            . ' ?s AS entity_type,'
            . ' feature_variant_descriptions.variant AS entity_name,'
            . ' feature_variants.variant_id AS local_id,'
            . ' ?i AS timestamp'
        . ' FROM ?:product_feature_variants AS feature_variants'
        . ' LEFT JOIN ?:product_feature_variant_descriptions AS feature_variant_descriptions'
            . ' ON feature_variants.variant_id = feature_variant_descriptions.variant_id AND feature_variant_descriptions.lang_code = ?s'
        . ' LEFT JOIN ?:product_features AS features ON feature_variants.feature_id = features.feature_id'
        . ' WHERE feature_variants.external_id <> ?s',
        '#',
        'product_feature_variant',
        time(),
        Registry::get('settings.Appearance.backend_default_language'),
        ''
    );

    $selections['currencies'] = $db->quote(
        'SELECT DISTINCT'
            . ' companies.company_id,'
            . ' commerceml_currencies.commerceml_currency AS entity_id,'
            . ' ?s AS entity_type,'
            . ' commerceml_currencies.commerceml_currency AS entity_name,'
            . ' currencies.currency_code AS local_id,'
            . ' ?i AS timestamp'
        . ' FROM ?:rus_commerceml_currencies AS commerceml_currencies'
        . ' LEFT JOIN ?:currencies AS currencies ON currencies.currency_id = commerceml_currencies.currency_id'
        . ' LEFT JOIN ?:companies AS companies ON 1=1',
        'currency',
        time(),
        Registry::get('settings.Appearance.backend_default_language'),
        ''
    );

    if (Registry::ifGet('addons.warehouses.status', null) !== null) {
        $selections['warehouses'] = $db->quote(
            'SELECT DISTINCT'
                . ' store_locations.company_id,'
                . ' exim_1c_warehouses.external_id AS entity_id,'
                . ' ?s AS entity_type,'
                . ' store_location_descriptions.name AS entity_name,'
                . ' exim_1c_warehouses.warehouse_id AS local_id,'
                . ' ?i AS timestamp'
            . ' FROM ?:rus_exim_1c_warehouses AS exim_1c_warehouses'
            . ' LEFT JOIN ?:store_locations AS store_locations ON store_locations.store_location_id = exim_1c_warehouses.warehouse_id'
            . ' LEFT JOIN ?:store_location_descriptions AS store_location_descriptions'
                . ' ON store_locations.store_location_id = store_location_descriptions.store_location_id'
                . ' AND store_location_descriptions.lang_code = ?s',
            'warehouse',
            time(),
            Registry::get('settings.Appearance.backend_default_language'),
            ''
        );
    }

    foreach ($selections as $selection) {
        $db->replaceSelectionInto(
            'commerceml_import_entity_map',
            ['company_id', 'entity_id', 'entity_type', 'entity_name', 'local_id', 'timestamp'],
            $selection,
            ['local_id']
        );
    }
}
