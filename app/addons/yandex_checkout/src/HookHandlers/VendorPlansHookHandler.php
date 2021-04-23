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

use Tygh\Addons\YandexCheckout\Enum\CommissionType;
use Tygh\Addons\YandexCheckout\Enum\ProcessorScript;

class VendorPlansHookHandler
{
    /**
     * The "vendor_plans_calculate_commission_for_payout_before" hook handler.
     *
     * Actions performed:
     *     - Change commission value to specified by lease with Yandex number.
     *
     * @param array $order_info              Order information
     * @param array $company_data            Company to which order belongs to
     * @param array $payout_data             Payout data to be written to database
     * @param float $total                   Order total amount
     * @param float $shipping_cost           Order shipping cost amount
     * @param float $surcharge_from_total    Order payment surcharge to be subtracted from total
     * @param float $surcharge_to_commission Order payment surcharge to be added to commission amount
     * @param float $commission              The transaction percent value
     *
     * @see \fn_calculate_commission_for_payout()
     */
    public function onBeforePayouts(
        $order_info,
        $company_data,
        $payout_data,
        $total,
        $shipping_cost,
        $surcharge_from_total,
        $surcharge_to_commission,
        &$commission
    ) {
        if (isset($order_info['payment_method']['processor_id'])) {
            $processor_id = (int) $order_info['payment_method']['processor_id'];
        }
        if (empty($processor_id)) {
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

        if (!$is_yandex_checkout_for_marketplaces_payment) {
            return;
        }
        if ($company_data['yandex_checkout_commission_type'] === CommissionType::FLEXIBLE) {
            return;
        }
        $commission = isset($company_data['yandex_checkout_marketplace_fee']) ? $company_data['yandex_checkout_marketplace_fee'] : 0.0;
    }
}
