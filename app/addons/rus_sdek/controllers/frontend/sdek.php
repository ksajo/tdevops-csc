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

if ($mode == 'sdek_offices') {
    $group_key = $_REQUEST['group_key'];
    $shipping_id = $_REQUEST['shipping_id'];
    $select_office = $_REQUEST['old_office_id'];

    $sdek_offices = Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key][$shipping_id]['offices'];

    Tygh::$app['view']->assign('group_key', $group_key);
    Tygh::$app['view']->assign('shipping_id', $shipping_id);
    Tygh::$app['view']->assign('old_office_id', $select_office);
    Tygh::$app['view']->assign('sdek_offices', $sdek_offices);
    Tygh::$app['view']->display('addons/rus_sdek/views/sdek/sdek_offices.tpl');

    exit;
}