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

$cart = & Tygh::$app['session']['cart'];

if (!empty($cart['pickpoint_office'])) {
    Tygh::$app['view']->assign('pickpoint_office', $cart['pickpoint_office']);
}

if (!empty($cart['user_data'])) {
    $fromcity = '';
    $city = '';

    if (!empty($cart['user_data']['s_state_descr'])) {
        $fromcity = $cart['user_data']['s_state_descr'];

    } elseif (!empty($cart['user_data']['b_state_descr'])) {
        $fromcity = $cart['user_data']['b_state_descr'];
    }

    if (!empty($cart['user_data']['s_city'])) {
        $city = $cart['user_data']['s_city'];
    } elseif (!empty($cart['user_data']['b_city'])) {
        $city = $cart['user_data']['b_city'];
    }
    Tygh::$app['view']->assign('fromcity', $fromcity);
    Tygh::$app['view']->assign('pickpoint_city', $city);
}
