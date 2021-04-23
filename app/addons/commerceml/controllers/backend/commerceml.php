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

use Tygh\Addons\CommerceML\ServiceProvider;
use Tygh\Registry;
use Tygh\Tools\Url;

defined('BOOTSTRAP') or die('Access denied');

/**
 * @var string $mode
 * @var string $action
 */

if ($mode === 'map') {
    $mappable_schema = ServiceProvider::getManualMappableEntitiesSchema();
    $company_id = fn_get_runtime_company_id();
    $settings_schema = ServiceProvider::getImportSettingsSchema($company_id);
    $settings = ServiceProvider::getImportSettings($company_id);

    $type = isset($_REQUEST['type']) ? (string) $_REQUEST['type'] : null;
    $page = isset($_REQUEST['page']) ? (int) $_REQUEST['page'] : 0;
    $items_per_page = isset($_REQUEST['items_per_page'])
        ? (int) $_REQUEST['items_per_page']
        : (int) Registry::get('settings.Appearance.admin_elements_per_page');

    if (!$type || !isset($mappable_schema[$type])) {
        $type = key($mappable_schema);
        return [CONTROLLER_STATUS_REDIRECT, Url::buildUrn('commerceml.map', ['type' => $type])];
    }

    if (!empty($mappable_schema[$type]['parent'])) {
        return [CONTROLLER_STATUS_REDIRECT, Url::buildUrn('commerceml.map', ['type' => $mappable_schema[$type]['parent']])];
    }

    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];
    $map_repository = ServiceProvider::getImportEntityMapRepository();
    $schema = $mappable_schema[$type];
    $sub_types = [];

    if (isset($schema['items_provider'])) {
        $items = (array) call_user_func($schema['items_provider']);
    } else {
        $items = [];
    }

    list($records, $search) = $map_repository->findAll([
        'page'           => $page,
        'items_per_page' => $items_per_page,
        'company_id'     => $company_id,
        'entity_type'    => $type,
        'order_by'       => 'timestamp'
    ]);

    foreach ($mappable_schema as $key => $item) {
        if (!isset($item['parent']) || $item['parent'] !== $type) {
            continue;
        }

        $sub_types[] = $key;
    }

    if ($sub_types) {
        foreach ($records as &$record) {
            $record['sub_records'] = [];

            list($sub_records) = $map_repository->findAll([
                'company_id'     => $company_id,
                'entity_types'   => $sub_types,
                'entity_id_like' => $record['entity_id'] . '#',
                'order_by'       => 'timestamp'
            ]);

            foreach ($sub_records as $sub_record) {
                $record['sub_records'][$sub_record['entity_type']][] = $sub_record;
            }
        }
        unset($record);
    }

    $view->assign([
        'types'           => array_keys($mappable_schema),
        'type'            => $type,
        'records'         => $records,
        'items'           => $items,
        'search'          => $search,
        'import_settings' => $settings,
        'settings_schema' => $settings_schema
    ]);
}

if ($mode === 'get_log') {
    $file_path = Tygh::$app['addons.commerceml.log_file_path'];

    if ($file_path) {
        fn_get_file($file_path);
    }
}
