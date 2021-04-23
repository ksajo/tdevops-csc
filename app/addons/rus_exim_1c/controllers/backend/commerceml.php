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
use Tygh\Settings;
use Tygh\BlockManager\SchemesManager;
use Tygh\Commerceml\RusEximCommerceml;
use \Tygh\Database\Connection;
use Tygh\Commerceml\Logs;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$path_file = 'exim/1C_' . date('dmY') . '/';
$path = fn_get_files_dir_path() . $path_file;
$path_commerceml = fn_get_files_dir_path();

$log = new Logs($path_file, $path);
$exim_commerceml = new RusEximCommerceml(Tygh::$app['db'], $log, $path_commerceml);

list($cml, $s_commerceml) = $exim_commerceml->getParamsCommerceml();
$s_commerceml = $exim_commerceml->getCompanySettings();

$params = $_REQUEST;
$company_id = fn_get_runtime_company_id();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $suffix = '';

    if ($mode == 'save_offers_data') {
        if ($s_commerceml['exim_1c_create_prices'] == 'Y') {
            $prices = $_REQUEST['prices_1c'];
            if (!empty($_REQUEST['list_price_1c'])) {
                $_list_prices = fn_explode(',', $_REQUEST['list_price_1c']);
                $list_prices = array();
                foreach($_list_prices as $_list_price) {
                    $list_prices[] = array(
                        'price_1c' => trim($_list_price),
                        'usergroup_id' => 0,
                        'type' => 'list',
                        'company_id' => $company_id
                    );
                }        
                $prices = fn_array_merge($list_prices, $prices, false);
            }

            if (!empty($_REQUEST['base_price_1c'])) {
                $_base_prices = fn_explode(',', $_REQUEST['base_price_1c']);
                $base_prices = array();
                foreach($_base_prices as $_base_price) {
                    $base_prices[] = array(
                        'price_1c' => trim($_base_price),
                        'usergroup_id' => 0,
                        'type' => 'base',
                        'company_id' => $company_id
                    );
                }

                foreach($prices as $k_price => $price) {
                    $prices[$k_price]['company_id'] = $company_id;
                }

                $prices = fn_array_merge($base_prices, $prices, false);
                db_query("DELETE FROM ?:rus_exim_1c_prices WHERE company_id = ?i", $company_id);
                foreach ($prices as $price) {
                    if (!empty($price['price_1c'])) {
                        db_query("INSERT INTO ?:rus_exim_1c_prices ?e", $price);
                    }
                }
            } else {
                fn_set_notification('W', __('warning'), __('base_price_empty'));
            }
        }

        $suffix = '.offers';
    }

    if ($mode == 'save_currencies_data') {
        if (!empty($_REQUEST['data_currencies']['commerceml_currency'])) {
            $data_currency = $_REQUEST['data_currencies'];

            if (!empty($data_currency['currency_key'])) {
                db_query("DELETE FROM ?:rus_commerceml_currencies WHERE id = ?i", $data_currency['currency_key']);
                unset($data_currency['currency_key']);
            }

            if (!empty($data_currency['commerceml_currency'])) {
                db_query("INSERT INTO ?:rus_commerceml_currencies ?e", $data_currency);
            }
        }
        
        $suffix = '.currencies';
    }

    if ($mode == 'save_taxes_data') {
        $taxes_commerceml = $_REQUEST['taxes_1c'];
        db_query("DELETE FROM ?:rus_exim_1c_taxes WHERE company_id = ?i", $company_id);
        foreach ($taxes_commerceml as $tax_commerceml) {
            if (!empty($tax_commerceml['tax_1c'])) {
                $tax_commerceml['company_id'] = $company_id;

                db_query("INSERT INTO ?:rus_exim_1c_taxes ?e", $tax_commerceml);
            }
        }

        $suffix = '.taxes';
    }

    if ($mode == 'currency_delete') {
        if (!empty($_REQUEST['id'])) {
            $id = $_REQUEST['id'];

            db_query("DELETE FROM ?:rus_commerceml_currencies WHERE id = ?i", $id);
        }

        $suffix = '.currencies';
    }

    return array(CONTROLLER_STATUS_OK, "commerceml$suffix");
}

if ($mode == 'currency_update') {
    $data_currencies = Registry::get('currencies');

    if (!empty($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
        $commerceml_currency = db_get_row("SELECT * FROM ?:rus_commerceml_currencies WHERE id = ?i", $id);

        $commerceml_currency['currency'] = db_get_field("SELECT currency_code FROM ?:currencies WHERE currency_id = ?i", $commerceml_currency['currency_id']);

        Tygh::$app['view']->assign('commerceml_currency', $commerceml_currency);
    }

    Tygh::$app['view']->assign('data_currencies', $data_currencies);
}

if ($mode == 'currencies') {
    $data_currencies = Registry::get('currencies');
    $commerceml_currencies = db_get_array(
        "SELECT a.*, b.currency_code as currency"
        . " FROM ?:rus_commerceml_currencies as a LEFT JOIN ?:currencies as b ON a.currency_id = b.currency_id"
    );

    foreach ($commerceml_currencies as $commerceml_key => $commerceml_currency) {
        if (!empty($data_currencies[$commerceml_currency['currency']])) {
            $commerceml_currencies[$commerceml_key]['currency_description'] = $data_currencies[$commerceml_currency['currency']]['description'];
        }
    }

    Tygh::$app['view']->assign('commerceml_currencies', $commerceml_currencies);
    Tygh::$app['view']->assign('data_currencies', $data_currencies);
}

if ($mode == 'taxes') {
    $taxes = fn_get_taxes();
    $taxes_data = db_get_array("SELECT * FROM ?:rus_exim_1c_taxes WHERE company_id = ?i", $company_id);
    
    Tygh::$app['view']->assign('taxes_data', $taxes_data);
    Tygh::$app['view']->assign('taxes', $taxes);
}

if ($mode == 'offers') {
    if ($s_commerceml['exim_1c_create_prices'] == 'Y') {
        $prices_data = db_get_array("SELECT * FROM ?:rus_exim_1c_prices WHERE company_id = ?i", $company_id);
        $prices = array();
        $list_price_1c = $base_price_1c = '';
        foreach ($prices_data as $price) {
            if ($price['type'] == 'base') {
                $base_price_1c .= $price['price_1c'] . ',';

            } elseif ($price['type'] == 'list') {
                $list_price_1c .= $price['price_1c'] . ',';

            } else {
                $prices[] = $price;
            }
        }

        Tygh::$app['view']->assign('list_price_1c', trim($list_price_1c, ','));
        Tygh::$app['view']->assign('base_price_1c', trim($base_price_1c, ','));
        Tygh::$app['view']->assign('prices_data', $prices);

        if ($s_commerceml['exim_1c_check_prices'] == 'Y') {
            list($path_commerceml, $url_commerceml, $url_images) = $exim_commerceml->getDirCommerceML();
            $result = array();
            $file_offers = glob($path_commerceml . "offers*");

            if (!empty($file_offers)) {
                foreach ($file_offers as $file_offer) {
                    $filename = fn_basename($file_offer);
                    list($xml, $d_status, $text_message) = $exim_commerceml->getFileCommerceml($filename);

                    if (isset($xml->{$cml['packages']}->{$cml['offers']}->{$cml['offer']})) {
                        $check_prices = $exim_commerceml->checkPricesOffers($xml->{$cml['packages']}, $company_id);
                    }

                    foreach ($check_prices as $k_price => $data_price) {
                        if (isset($result[$k_price])) {
                            if (isset($data_price['valid']) && !isset($result[$k_price]['valid'])) {
                                $result[$k_price]['valid'] = $data_price['valid'];
                            }
                        } else {
                            $result[$k_price] = $data_price;
                        }
                    }
                }
            } else {
                fn_set_notification('W', __('warning'), __('offers_not_found'));
            } 

            Tygh::$app['view']->assign('resul_test', $result);
        }
    }

    Tygh::$app['view']->assign('show_prices', $s_commerceml['exim_1c_create_prices']);
}
