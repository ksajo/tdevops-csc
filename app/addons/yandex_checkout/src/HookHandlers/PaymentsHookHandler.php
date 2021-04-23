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

use Tygh\Addons\YandexCheckout\Enum\ProcessorScript;
use Tygh\Enum\SiteArea;
use Tygh\Tygh;

class PaymentsHookHandler
{
    /**
     * The "get_payment_processors_post" hook handler.
     *
     * Actions performed:
     *     - Adds specific attributes to some payment processor for categorization.
     *
     * @see \fn_get_payment_processors()
     */
    function onGetPaymentProcessorsPost($lang_code, &$processors)
    {
        foreach ($processors as &$processor) {
            if ($processor['addon'] === 'yandex_checkout') {
                $processor['russian'] = true;
            }
        }
        unset($processor);
    }

    /**
     * The "prepare_checkout_payment_methods_before_get_payments" hook handler.
     *
     * Actions performed:
     *  - Adds company_id into get payments params on repay
     *
     * @see \fn_prepare_checkout_payment_methods()
     */
    public function onBeforeGetPayments(
        $cart,
        $auth,
        $lang_code,
        $get_payment_groups,
        $payment_methods,
        &$get_payments_params
    ) {
        if (!empty($cart['order_id']) && !empty($cart['company_id'])) {
            $get_payments_params['company_id'] = $cart['company_id'];
        }
    }

    /**
     * The "get_payments" hook handler.
     *
     * Actions performed:
     *     - Excludes Yandex Checkout for Marketplaces from payments selection when products' vendor
     *       has no Yandex Checkout shopID
     */
    public function onGetPayments($params, $fields, $join, $order, &$condition, $having)
    {
        if ($params['area'] !== SiteArea::STOREFRONT
            && !defined('ORDER_MANAGEMENT')
            || empty(Tygh::$app['session']['cart']['product_groups'])
        ) {
            return;
        }

        foreach (Tygh::$app['session']['cart']['product_groups'] as $product_group) {
            if (!$product_group['company_id']) {
                continue;
            }

            $company_data = fn_get_company_data($product_group['company_id']);
            if ($company_data['yandex_checkout_shopid']) {
                continue;
            }

            $condition[] = db_quote(
                '(?:payment_processors.processor_script IS NULL'
                . ' OR ?:payment_processors.processor_script <> ?s)',
                ProcessorScript::YANDEX_CHECKOUT_FOR_MARKETPLACES
            );

            return;
        }
    }

    /**
     * The "prepare_checkout_payment_methods_after_get_payments" hook handler.
     *
     * Actions performed:
     *  - Excludes Yandex Checkout for Marketplaces from payments selection if vendor has no Yandex Checkout shopID
     *
     * @see \fn_prepare_checkout_payment_methods()
     */
    public function onAfterGetPayments(
        $cart,
        $auth,
        $lang_code,
        $get_payment_groups,
        &$payment_methods,
        $get_payments_params,
        $cache_key
    ) {
        if (empty($payment_methods[$cache_key])
            || empty($get_payments_params['company_id'])
        ) {
            return;
        }

        $company_data = fn_get_company_data($get_payments_params['company_id']);
        if ($company_data['yandex_checkout_shopid']) {
            return;
        }

        foreach ($payment_methods[$cache_key] as $payment_id => $payment_method) {
            if ($payment_method['processor_script'] === ProcessorScript::YANDEX_CHECKOUT_FOR_MARKETPLACES) {
                unset($payment_methods[$cache_key][$payment_id]);
            }
        }
    }
}