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

use Tygh\Tygh;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'details') {
    $order_info = Tygh::$app['view']->getTemplateVars('order_info');
    $carriers = Tygh::$app['view']->getTemplateVars('carriers');

    if (isset($order_info['shipping']) && is_array($order_info['shipping'])) {
        $shipping = reset($order_info['shipping']);
        if (isset($shipping['module']) && $shipping['module'] !== 'rus_boxberry') {
            unset($carriers['boxberry']);
        }
    }

    Tygh::$app['view']->assign('carriers', $carriers);
}

if ($mode == 'update_details'){
    if (!empty($_REQUEST['point_id'])) {

        $new_point_id = $_REQUEST['point_id'];
        $order_id = $_REQUEST['order_id'];
        $order_info = fn_get_order_info($order_id);

        foreach ($order_info['product_groups'] as $group_key => &$product_group) {
            $product_group['chosen_shippings'][$group_key]['point_id'] = $new_point_id;
        }

        fn_update_order_data($order_id, $order_info);
    }
}
