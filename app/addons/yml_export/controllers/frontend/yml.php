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
use Tygh\Ym\Yml2;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'generate' || $mode == 'get') {

    $access_key = !empty($_REQUEST['access_key']) ? $_REQUEST['access_key'] : '';
    $price_id = !empty($_REQUEST['price_id']) ? $_REQUEST['price_id'] : 0;

    if (empty($price_id) && !empty($access_key)) {
        $price_id = fn_yml_get_price_id($access_key);
    }

    $options = fn_yml_get_options($price_id);

    if (!empty($options) && $options['enable_authorization'] == 'Y' && empty($access_key)) {
        $options = array();
    }

    if (!empty($options)) {
        $company_id = Registry::get('runtime.company_id');

        if (fn_allowed_for('MULTIVENDOR')) {
            $company_id = 0;
        }

        $offset = !empty($_REQUEST['offset']) ? $_REQUEST['offset'] : 0;

        $lang_code = DESCR_SL;
        if (Registry::isExist('languages.ru')) {
            $lang_code = 'ru';
        }

        $yml = new Yml2($company_id, $price_id, $lang_code, $offset, isset($_REQUEST['debug']));

        if ($mode == 'get') {
            $yml->get();

        } else {
            $yml->generate();
        }
    } else {
        fn_echo(__("error"));
    }

    exit;
}
