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

use Tygh\Addons\YandexCheckout\Commands\ChangeOrderStatusWithYandexCheckout;
use Tygh\Addons\YandexCheckout\Commands\ChangeOrderStatusWithYandexCheckoutForMarketplaces;
use Tygh\Addons\YandexCheckout\Enum\ProcessorScript;

class OrdersHookHandler
{
    /**
     * The "change_order_status_post" hook handler.
     *
     * Actions performed:
     *     - Creates full payment receipt for YooKassa
     *     - Creates full pre-payment and full payment receipts for YooKassa for Marketplaces
     *     - Creates withdrawals for YooKassa for Marketplaces
     *
     * @see \fn_change_order_status()
     */
    public function onChangeOrderStatusPost(
        $order_id,
        $status_to,
        $status_from,
        $force_notification,
        $place_order,
        $order_info,
        $edp_data
    ) {
        $processor_id = null;
        if (isset($order_info['payment_method']['processor_id'])) {
            $processor_id = (int) $order_info['payment_method']['processor_id'];
        }

        $is_yandex_checkout_payment = (bool) db_get_field(
            'SELECT 1'
            . ' FROM ?:payment_processors'
            . ' WHERE processor_script = ?s'
            . ' AND addon = ?s'
            . ' AND processor_id = ?i',
            ProcessorScript::YANDEX_CHECKOUT,
            'yandex_checkout',
            $processor_id
        );
        if ($is_yandex_checkout_payment) {
            $command = new ChangeOrderStatusWithYandexCheckout($order_id, $status_to, $order_info);
            $order_status_change_result = $command->run();
            if (!$order_status_change_result->isSuccess()) {
                $order_status_change_result->showNotifications(false, 'S');
            }

            return;
        }

        $is_yandex_checkout_for_marketplaces_payment = (bool) db_get_field(
            'SELECT 1'
            . ' FROM ?:payment_processors'
            . ' WHERE processor_script = ?s'
            . ' AND addon = ?s'
            . ' AND processor_id = ?i',
            ProcessorScript::YANDEX_CHECKOUT_FOR_MARKETPLACES,
            'yandex_checkout',
            $processor_id
        );
        if ($is_yandex_checkout_for_marketplaces_payment) {
            $command = new ChangeOrderStatusWithYandexCheckoutForMarketplaces($order_id, $status_to, $order_info);
            $order_status_change_result = $command->run();
            if (!$order_status_change_result->isSuccess()) {
                $order_status_change_result->showNotifications(false, 'S');
            }

            return;
        }
    }
}