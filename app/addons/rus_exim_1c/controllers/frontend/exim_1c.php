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
use Tygh\Commerceml\RusEximCommerceml;
use \Tygh\Database\Connection;
use Tygh\Commerceml\Logs;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if (!empty($_SERVER['PHP_AUTH_USER'])) {
    $data['user_login'] = $_SERVER['PHP_AUTH_USER'];
    list($status, $user_data, $user_login, $password, $salt) = fn_auth_routines($data, array());
    fn_commerceml_change_company_store($user_data);
}

$params = $_REQUEST;
$type = $mode = $service_exchange = '';
if (isset($params['type'])) {
    $type = $params['type'];
}

if (isset($params['mode'])) {
    $mode = $params['mode'];
}

if (isset($params['service_exchange'])) {
    $service_exchange = $params['service_exchange'];
}

$manual = !empty($params['manual']);

$path_file = 'exim/1C_' . date('dmY') . '/';
$path = fn_get_files_dir_path() . $path_file;
$path_commerceml = fn_get_files_dir_path();

$log = new Logs($path_file, $path);
$exim_commerceml = new RusEximCommerceml(Tygh::$app['db'], $log, $path_commerceml);

$exim_commerceml->import_params['service_exchange'] = $service_exchange;
$exim_commerceml->import_params['manual'] = $manual;

list($cml, $s_commerceml) = $exim_commerceml->getParamsCommerceml();

if ($exim_commerceml->checkParameterFileUpload()) {
    exit;
}

$s_commerceml = $exim_commerceml->getCompanySettings();

$filename = (!empty($params['filename'])) ? fn_basename($params['filename']) : '';
$lang_code = (!empty($s_commerceml['exim_1c_lang'])) ? $s_commerceml['exim_1c_lang'] : CART_LANGUAGE;

$exim_commerceml->getDirCommerceML();
$exim_commerceml->import_params['lang_code'] = $lang_code;

if ($type == 'catalog') {
    if ($mode == 'checkauth') {
        $exim_commerceml->exportDataCheckauth($service_exchange);

    } elseif ($mode == 'init') {
        $exim_commerceml->exportDataInit();

    } elseif ($mode == 'file') {
        if ($exim_commerceml->createImportFile($filename) === false) {
            fn_echo("failure");
            exit;
        }

        fn_echo("success\n");

    } elseif ($mode == 'import') {
        $fileinfo = pathinfo($filename);

        list($xml, $d_status, $text_message) = $exim_commerceml->getFileCommerceml($filename);

        $exim_commerceml->addMessageLog($text_message);
        if ($d_status === false) {
            fn_echo("failure");
            exit;
        }

        if (strpos($fileinfo['filename'], 'import') !== false) {
            if ($s_commerceml['exim_1c_import_products'] != 'not_import') {
                $exim_commerceml->importDataProductFile($xml);
            } else {
                fn_echo("success\n");
            }
        }

        if (strpos($fileinfo['filename'], 'offers') !== false) {
            if ($s_commerceml['exim_1c_only_import_offers'] == 'Y') {
                $exim_commerceml->importDataOffersFile($xml, $service_exchange, $lang_code, $manual);
            } else {
                fn_echo("success\n");
            }
        }
    }

} elseif (($type == 'sale') && ($exim_commerceml->import_params['user_data']['user_type'] != 'V') && ($s_commerceml['exim_1c_check_prices'] != 'Y')) {
    if ($mode == 'checkauth') {
        $exim_commerceml->exportDataCheckauth($service_exchange);

    } elseif ($mode == 'init') {
        $exim_commerceml->exportDataInit();

    } elseif ($mode == 'file') {
        if ($exim_commerceml->createImportFile($filename) === false) {
            fn_echo("failure");
            exit;
        }

        if (($s_commerceml['exim_1c_import_statuses'] == 'Y') && !empty($filename)) {
            list($xml, $d_status, $text_message) = $exim_commerceml->getFileCommerceml($filename);
            $exim_commerceml->addMessageLog($text_message);
            if ($d_status === false) {
                fn_echo("failure");
                exit;
            }

            $exim_commerceml->importFileOrders($xml, $lang_code);
        }

        fn_echo("success\n");

    } elseif ($mode == 'query') {
        if ($s_commerceml['exim_1c_all_product_order'] == 'Y') {
            $exim_commerceml->exportAllProductsToOrders($lang_code);
        } else {
            $exim_commerceml->exportDataOrders($lang_code);
        }

    } elseif ($mode == 'success') {
        fn_echo("success");
    }
}

exit;
