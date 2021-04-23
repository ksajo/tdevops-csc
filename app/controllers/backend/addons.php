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

use Tygh\Addons\SchemesManager;
use Tygh\Enum\NotificationSeverity;
use Tygh\Providers\StorefrontProvider;
use Tygh\Registry;
use Tygh\Settings;
use Tygh\Snapshot;
use Tygh\Tools\Url;
use Tygh\Tygh;

defined('BOOTSTRAP') or die('Access denied');

/** @var \Tygh\SmartyEngine\Core $view */
$view = Tygh::$app['view'];

$storefront_id = empty($_REQUEST['storefront_id'])
    ? 0
    : (int) $_REQUEST['storefront_id'];

if (fn_allowed_for('ULTIMATE')) {
    $storefront_id = 0;
    if (fn_get_runtime_company_id()) {
        $storefront_id = StorefrontProvider::getStorefront()->storefront_id;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    /** @var string $dispatch_extra */
    $dispatch_extra = isset($dispatch_extra)
        ? $dispatch_extra
        : '';

    fn_trusted_vars(
        'addon_data'
    );

    $redirect_url = !empty($_REQUEST['return_url'])
        ? $_REQUEST['return_url']
        : 'addons.manage';

    if ($mode === 'update') {
        $addon_scheme = SchemesManager::getScheme($_REQUEST['addon']);

        if ($addon_scheme === false || $addon_scheme->isPromo()) {
            return [CONTROLLER_STATUS_NO_PAGE];
        }

        if (isset($_REQUEST['addon_data'])) {
            fn_update_addon($_REQUEST['addon_data'], $storefront_id);
        }

        $redirect_url_params = [
            'addon' => $_REQUEST['addon'],
        ];

        if (fn_allowed_for('MULTIVENDOR')) {
            $redirect_url_params['storefront_id'] = $storefront_id;
        }

        return [
            CONTROLLER_STATUS_OK,
            Url::buildUrn(['addons', 'update'], $redirect_url_params),
        ];
    } elseif ($mode === 'recheck') {
        $addon_name = $_REQUEST['addon_name'];
        $addon_extract_path = $_REQUEST['addon_extract_path'];
        $source = Registry::get('config.dir.root') . '/' . $addon_extract_path;
        $destination = Registry::get('config.dir.root');

        if (!file_exists($source) || !fn_validate_addon_structure($addon_name, $source)) {
            fn_set_notification(
                NotificationSeverity::ERROR,
                __('error'),
                __('broken_addon_pack')
            );

            if (defined('AJAX_REQUEST')) {
                Tygh::$app['ajax']->assign('non_ajax_notifications', true);
                Tygh::$app['ajax']->assign('force_redirection', fn_url($redirect_url));
                exit();
            } else {
                return [CONTROLLER_STATUS_REDIRECT, $redirect_url];
            }
        }

        if ($action === 'ftp_upload') {
            $ftp_access = [
                'hostname'  => $_REQUEST['ftp_access']['ftp_hostname'],
                'username'  => $_REQUEST['ftp_access']['ftp_username'],
                'password'  => $_REQUEST['ftp_access']['ftp_password'],
                'directory' => $_REQUEST['ftp_access']['ftp_directory'],
            ];

            if ($dispatch_extra === 'uninstall') {
                fn_uninstall_addon($addon_name, false, true);
            }

            $ftp_install_result = __('cant_remove_addon_files');
            if (fn_remove_addon_files($addon_name, $ftp_access)) {
                $ftp_install_result = fn_copy_by_ftp($source, $destination, $ftp_access);
            }

            if ($ftp_install_result === true && fn_check_addon_exists($addon_name)) {
                if (fn_reinstall_addon_files($addon_name)) {
                    fn_set_notification('N', __('notice'), __('addon_files_was_copied', [
                        '[addon]' => $addon_name
                    ]));
                }
            } elseif ($ftp_install_result === true) {
                fn_install_addon($addon_name);
            } else {
                fn_set_notification(
                    NotificationSeverity::ERROR,
                    __('error'),
                    $ftp_install_result
                );
            }

            if (defined('AJAX_REQUEST')) {
                Tygh::$app['ajax']->assign('non_ajax_notifications', true);
                Tygh::$app['ajax']->assign('force_redirection', fn_url($redirect_url));
                exit();
            } else {
                return [CONTROLLER_STATUS_OK, $redirect_url];
            }
        }

        $non_writable_folders = fn_check_copy_ability($source, $destination);

        if (!empty($non_writable_folders)) {
            if (!empty($_REQUEST['ftp_access'])) {
                $view->assign('ftp_access', $_REQUEST['ftp_access']);
            }

            $view->assign([
                'non_writable' => $non_writable_folders,
                'return_url'   => $redirect_url,
            ]);

            if (defined('AJAX_REQUEST')) {
                $view->assign([
                    'addon_name'         => $addon_name,
                    'addon_extract_path' => $addon_extract_path,
                    'dispatch_extra'     => $dispatch_extra,
                ]);
                $view->display('views/addons/components/correct_permissions.tpl');
                exit();
            }
        } else {
            if ($dispatch_extra === 'uninstall') {
                fn_uninstall_addon($addon_name, false, true);
            }

            fn_addons_move_and_install($source, Registry::get('config.dir.root'));

            if (defined('AJAX_REQUEST')) {
                Tygh::$app['ajax']->assign('force_redirection', fn_url($redirect_url));
                exit();
            }
        }
    } elseif ($mode == 'upload') {
        if (defined('RESTRICTED_ADMIN') || Registry::get('runtime.company_id')) {
            fn_set_notification(
                NotificationSeverity::ERROR,
                __('error'),
                __('access_denied')
            );

            return [CONTROLLER_STATUS_REDIRECT, $redirect_url];
        }

        $addon_pack = fn_filter_uploaded_data('addon_pack', Registry::get('config.allowed_pack_exts'));
        $addon_pack = reset($addon_pack);

        if (!$addon_pack) {
            fn_set_notification(
                NotificationSeverity::ERROR,
                __('error'),
                __('text_allowed_to_upload_file_extension', [
                    '[ext]' => implode(',', Registry::get('config.allowed_pack_exts'))
                ])
            );
        } else {
            $tmp_path = fn_get_cache_path(false) . 'tmp/';
            $addon_file = $tmp_path . $addon_pack['name'];

            fn_mkdir($tmp_path);
            fn_copy($addon_pack['path'], $addon_file);

            $addon_pack_result = fn_extract_addon_package($addon_file);

            fn_rm($addon_file);

            if ($addon_pack_result) {
                list($addon_name, $extract_path) = $addon_pack_result;

                if (fn_validate_addon_structure($addon_name, $extract_path)) {
                    $view->assign([
                        'addon_extract_path' => fn_get_rel_dir($extract_path),
                        'addon_name'         => $addon_name,
                        'return_url'         => $redirect_url,
                    ]);

                    if (Registry::get("addons.{$addon_name}.status") && defined('AJAX_REQUEST')) {
                        $view->display('views/addons/components/reinstall.tpl');
                        exit();
                    }

                    $non_writable_folders = fn_check_copy_ability($extract_path, Registry::get('config.dir.root'));
                    if (!empty($non_writable_folders)) {
                        $view->assign('non_writable', $non_writable_folders);

                        if (defined('AJAX_REQUEST')) {
                            $view->display('views/addons/components/correct_permissions.tpl');
                            exit();
                        }
                    } else {
                        fn_addons_move_and_install($extract_path, Registry::get('config.dir.root'));

                        if (defined('AJAX_REQUEST')) {
                            Tygh::$app['ajax']->assign('force_redirection', fn_url($redirect_url));
                            exit();
                        }
                    }
                }
            }

            fn_set_notification(
                NotificationSeverity::ERROR,
                __('error'),
                __('broken_addon_pack')
            );

            if (defined('AJAX_REQUEST')) {
                Tygh::$app['ajax']->assign('non_ajax_notifications', true);
                Tygh::$app['ajax']->assign('force_redirection', fn_url($redirect_url));
                exit();
            } else {
                return [CONTROLLER_STATUS_REDIRECT, $redirect_url];
            }
        }

        if (defined('AJAX_REQUEST')) {
            $view->display('views/addons/components/upload_addon.tpl');

            exit();
        }
    } elseif ($mode == 'licensing') {  // Used for saving add-on license key to the DB
        if (!isset($_REQUEST['addon'], $_REQUEST['redirect_url'], $_REQUEST['marketplace_license_key'])) {
            return [CONTROLLER_STATUS_NO_PAGE];
        }

        $addon_id = $_REQUEST['addon'];
        $redirect_url = $_REQUEST['redirect_url'];
        $license_key = $_REQUEST['marketplace_license_key'];

        $addon_data = db_get_row(
            'SELECT * FROM ?:addons AS a'
            . ' WHERE a.addon = ?s'
            . ' AND a.unmanaged <> 1'
            . ' AND a.marketplace_id IS NOT NULL',
            $addon_id
        );

        if (empty($addon_data)) {
            return [CONTROLLER_STATUS_NO_PAGE];
        }

        fn_update_addon_license_key($addon_id, $license_key);

        fn_set_notification(
            NotificationSeverity::NOTICE,
            __('notice'),
            __('text_changes_saved')
        );

        // Redirect browser back
        if (defined('AJAX_REQUEST')) {
            Tygh::$app['ajax']->assign('non_ajax_notifications', true);
            Tygh::$app['ajax']->assign('force_redirection', $redirect_url);
        } else {
            return [CONTROLLER_STATUS_REDIRECT, $redirect_url];
        }

        exit;
    }

    if ($mode == 'update_status') {
        $is_snapshot_correct = fn_check_addon_snapshot($_REQUEST['id']);

        if (!$is_snapshot_correct) {
            $status = false;
        } else {
            $status = fn_update_addon_status($_REQUEST['id'], $_REQUEST['status']);
        }

        if ($status !== true) {
            Tygh::$app['ajax']->assign('return_status', $status);
        }
        Registry::clearCachedKeyValues();

        return [CONTROLLER_STATUS_REDIRECT, $redirect_url];
    }

    if ($mode == 'install') {
        fn_install_addon($_REQUEST['addon']);
        Registry::clearCachedKeyValues();

        return [CONTROLLER_STATUS_REDIRECT, $redirect_url];
    }

    if ($mode == 'uninstall') {
        fn_uninstall_addon($_REQUEST['addon']);

        return [CONTROLLER_STATUS_REDIRECT, $redirect_url];
    }

    if ($mode == 'tools') {
        if (Snapshot::exist()) {
            $init_addons = !empty($_REQUEST['init_addons']) ? $_REQUEST['init_addons'] : '';

            if ($init_addons != 'none' && $init_addons != 'core') {
                $init_addons = '';
            }

            Settings::instance()->updateValue('init_addons', $init_addons);
            fn_clear_cache();
        } else {
            fn_set_notification('E', __('error'), __('tools_snapshot_not_found'));
        }

        return [CONTROLLER_STATUS_REDIRECT, $redirect_url];
    }

    if ($mode == 'refresh') {
        if (!empty($_REQUEST['addon'])) {
            $addon_id = $_REQUEST['addon'];
            $addon_scheme = SchemesManager::getScheme($addon_id);

            fn_update_addon_language_variables($addon_scheme);

            $setting_values = [];
            $settings_values = fn_get_addon_settings_values($addon_id);
            $settings_vendor_values = fn_get_addon_settings_vendor_values($addon_id);

            $update_addon_settings_result = fn_update_addon_settings(
                $addon_scheme,
                true,
                $settings_values,
                $settings_vendor_values
            );

            fn_clear_cache();
            Registry::clearCachedKeyValues();

            if ($update_addon_settings_result) {
                fn_set_notification(
                    NotificationSeverity::NOTICE,
                    __('notice'),
                    __('text_addon_refreshed', [
                        '[addon]' => $addon_id,
                    ])
                );
            }

            return [CONTROLLER_STATUS_REDIRECT, $redirect_url];
        }
    }

    return [CONTROLLER_STATUS_OK, $redirect_url];
}

if ($mode === 'update') {
    $addon_scheme = SchemesManager::getScheme($_REQUEST['addon']);

    if ($addon_scheme === false || $addon_scheme->isPromo()) {
        return [CONTROLLER_STATUS_NO_PAGE];
    }

    $settings_manager = Settings::instance(['storefront_id' => $storefront_id]);

    $view->assign('selected_storefront_id', $storefront_id);

    $section = $settings_manager->getSectionByName($_REQUEST['addon'], Settings::ADDON_SECTION);

    if (empty($section)) {
        return [CONTROLLER_STATUS_NO_PAGE];
    }

    $subsections = $settings_manager->getSectionTabs($section['section_id'], CART_LANGUAGE);
    $options = $settings_manager->getList($section['section_id']);

    fn_update_lang_objects('sections', $subsections);
    fn_update_lang_objects('options', $options);

    $addon = db_get_row(
        'SELECT a.addon, a.status, b.name as name, b.description as description, a.separate, a.install_datetime'
        . ' FROM ?:addons as a'
        . ' LEFT JOIN ?:addon_descriptions as b ON b.addon = a.addon AND b.lang_code = ?s WHERE a.addon = ?s'
        . ' ORDER BY b.name ASC',
        CART_LANGUAGE,
        $_REQUEST['addon']
    );

    $view->assign([
        'options'                => $options,
        'subsections'            => $subsections,
        'addon_version'          => $addon_scheme->getVersion(),
        'addon_supplier'         => $addon_scheme->getSupplier(),
        'addon_supplier_link'    => $addon_scheme->getSupplierLink(),
        'addon_install_datetime' => $addon['install_datetime'],
    ]);

    if ($addon['separate'] || !defined('AJAX_REQUEST')) {
        $view->assign([
            'separate'   => true,
            'addon_name' => $addon['name'],
        ]);
    }
} elseif ($mode === 'manage') {
    list($addons, $search, $addons_counter) = fn_get_addons(
        [],
        0,
        CART_LANGUAGE,
        $storefront_id,
        fn_get_runtime_company_id()
    );
    $developers = array_filter(array_unique(array_column($addons, 'supplier')));
    $all_suppliers = fn_get_addon_suppliers($addons);
    $view->assign(['all_suppliers' => $all_suppliers]);
    $suppliers = array_slice($all_suppliers, 0, 4, true);

    if (!empty($_REQUEST)) {
        $params = $_REQUEST;
        $params['for_company'] = (bool) Registry::get('runtime.company_id');

        list($addons, $search, $addons_counter) = fn_get_addons(
            $params,
            0,
            CART_LANGUAGE,
            $storefront_id,
            fn_get_runtime_company_id()
        );
    }

    $view->assign([
        'search'                 => $search,
        'addons_list'            => $addons,
        'addons_counter'         => $addons_counter,
        'snapshot_exist'         => Snapshot::exist(),
        'developers'             => $developers,
        'selected_storefront_id' => $storefront_id,
        'suppliers'              => $suppliers,
    ]);
} elseif ($mode == 'licensing') {
    if (empty($_REQUEST['addon'])) {
        return [CONTROLLER_STATUS_NO_PAGE];
    }

    $addon_id = $_REQUEST['addon'];
    $redirect_url = isset($_REQUEST['return_url']) ? $_REQUEST['return_url'] : null;

    $addon_data = db_get_row(
        'SELECT * FROM ?:addons AS a'
        . ' WHERE a.addon = ?s'
        . ' AND a.unmanaged <> 1'
        . ' AND a.marketplace_id IS NOT NULL',
        $addon_id
    );

    if (empty($addon_data)) {
        return [CONTROLLER_STATUS_NO_PAGE];
    }

    $view
        ->assign('addon_data', $addon_data)
        ->assign('redirect_url', $redirect_url);
}
