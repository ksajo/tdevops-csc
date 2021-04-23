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

namespace Tygh\Addons\YandexCheckout\HookHandlers;

use Tygh\Tygh;

class InitHookHandler
{
    /**
     * The "user_init" hook handler.
     *
     * Actions performed:
     *     - Clears the cart in the session if notification for placed orders was received.
     *
     * @see \fn_user_init()
     */
    public function onUserInit(&$auth, &$user_info, &$first_init)
    {
        $orders_list = [];
        if (!empty(Tygh::$app['session']['cart']['processed_order_id'])) {
            $orders_list = array_merge($orders_list, (array)Tygh::$app['session']['cart']['processed_order_id']);
        }
        if (!empty(Tygh::$app['session']['cart']['failed_order_id'])) {
            $orders_list = array_merge($orders_list, (array)Tygh::$app['session']['cart']['failed_order_id']);
        }
        foreach ($orders_list as $order_id) {
            $order_info = fn_get_order_info($order_id);
            if (!empty($order_info['payment_info']['yandex_checkout.notification_received'])) {
                fn_clear_cart(Tygh::$app['session']['cart'], false, false);
                break;
            }
        }
    }

}