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

defined('BOOTSTRAP') or die('Access denied');

if ($mode === 'details') {
    $order_id = $_REQUEST['order_id'];
    if ($order_id) {
        $order_info = fn_get_order_info($order_id);
        $shipping = reset($order_info['shipping']);
        Tygh::$app['view']->assign('is_order_delivered_by_russian_post', $shipping['module'] === 'russian_post');
    }
}
