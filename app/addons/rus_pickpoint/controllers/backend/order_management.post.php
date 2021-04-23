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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$_cart = & Tygh::$app['session']['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'select_customer') {
        $_cart['pickpoint_office'] = array();
    }
}

if ($mode == 'customer_info') {
    $_cart['pickpoint_office'] = array();
}

if ($mode == 'add') {
    if (!empty($_cart['product_groups'])) {
        if (!empty($_cart['pickpoint_office'])) {
            Tygh::$app['view']->assign('pickpoint_postamat', $_cart['pickpoint_office']);
        }
    }
}
