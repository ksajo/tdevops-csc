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

use Tygh\Addons\RusTaxes\Receipt\Receipt;
use Tygh\Addons\YandexCheckout\Enum\ProcessorScript;
use Tygh\Addons\YandexCheckout\Payments\YandexCheckout;
use Tygh\Addons\YandexCheckout\ServiceProvider;
use Tygh\Enum\Addons\Rma\ReturnOperationStatuses;
use Tygh\Enum\NotificationSeverity;
use Tygh\Enum\YesNo;
use Tygh\Tygh;
use YooKassa\Common\Exceptions\BadApiRequestException;

class RmaHookHandler
{
    /**
     * The "rma_update_details_post" hook handler.
     *
     * Actions performed:
     *     - Creates refund at YooKassa in right conditions.
     *
     * @param array<string, string|array<string, int|string>> $data                   Information about returning products.
     * @param bool                                            $show_confirmation_page Status of next step in return process.
     * @param bool                                            $show_confirmation      True - if confirmation page should be shown, false - if should not.
     * @param string                                          $is_refund              Y - its a refund, N - its a replace.
     * @param array<string, string>                           $_data                  Modified information about returning products.
     * @param string                                          $confirmed              Y - operation confirmed by user, N - operation not confirmed by user.
     *
     * @throws \YooKassa\Common\Exceptions\ApiException                YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ForbiddenException          YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\InternalServerError         YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\NotFoundException           YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ResponseProcessingException YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\TooManyRequestsException    YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\UnauthorizedException       YooKassa API Exception.
     * @throws \Exception                                              YooKassa API Exception.
     *
     * @see \fn_rma_update_details()
     */
    public function onRmaUpdateDetailsPost(array $data, $show_confirmation_page, $show_confirmation, $is_refund, array $_data, $confirmed)
    {
        //Return if this is not refund or operation was not confirmed.
        if ($confirmed === YesNo::NO || !YesNo::toBool($is_refund)) {
            return;
        }

        /** @var array<string, int|string> $change_return_status */
        $change_return_status = $data['change_return_status'];
        if (
            $change_return_status['status_to'] === $change_return_status['status_from']
            || empty($change_return_status['yandex_checkout_perform_refund'])
        ) {
            return;
        }

        $return_statuses = fn_get_statuses(STATUSES_RETURN);
        if ($return_statuses[$change_return_status['status_to']]['params']['inventory'] === ReturnOperationStatuses::DECLINED) {
            return;
        }

        $return_data = fn_get_return_info($change_return_status['return_id']);
        $extra = empty($return_data['extra']) ? [] : unserialize($return_data['extra']);
        $order_info = fn_get_order_info((int) $change_return_status['order_id']);
        if (!$order_info) {
            return;
        }
        if (!empty($order_info['payment_info']['yandex_checkout.full_refund_id'])) {
            return;
        }

        $is_yandex_checkout_payment = (bool) db_get_field(
            'SELECT 1'
            . ' FROM ?:payment_processors'
            . ' WHERE processor_script = ?s'
            . ' AND addon = ?s'
            . ' AND processor_id = ?i',
            ProcessorScript::YANDEX_CHECKOUT,
            'yandex_checkout',
            $order_info['payment_method']['processor_id']
        );
        if (!$is_yandex_checkout_payment) {
            return;
        }

        //Flag that this is second call of this function.
        if ($show_confirmation && YesNo::toBool($confirmed)) {
            if (!isset($extra['yandex_checkout_refund_params'])) {
                return;
            }
            $params = unserialize($extra['yandex_checkout_refund_params']);
            $this->makeRefundRequest($params, $order_info, (int) $change_return_status['return_id'], $extra);
            return;
        }


        $return_amount = 0.0;
        if (!empty($order_info['products'])) {
            $total = $order_info['subtotal'];
            $total_discount = $order_info['subtotal_discount'];
            foreach ($order_info['products'] as $cart_id => $product) {
                $item_total = $product['price'];
                $discount = Receipt::roundPrice($item_total / $total * $total_discount);

                $total_discount -= $discount;
                $total -= $item_total;
                if (!isset($product['extra']['returns'], $return_data['items']['A'][$cart_id])) {
                    continue;
                }
                foreach ($product['extra']['returns'] as $product_return_data) {
                    $return_amount += ($return_data['items']['A'][$cart_id]['price'] - $discount) * $product_return_data['amount'];
                }
            }
        }
        if ($return_amount === 0.0) {
            return;
        }

        $is_refund_first = $this->isThisAFirstRefund($order_info['order_id']);
        $is_refund_partial = !$is_refund_first
            || (int) $order_info['parent_order_id']
            || $return_amount !== (float) $order_info['subtotal']
            || ($return_amount === (float) $order_info['subtotal'] && (float) $order_info['shipping_cost'] !== 0.0);
        $processor_params = $order_info['payment_method']['processor_params'];
        $payment = new YandexCheckout(
            $processor_params['shop_id'],
            $processor_params['scid'],
            ServiceProvider::getReceiptService()
        );
        $params = $payment->createRefundParams(
            $order_info,
            $return_data,
            $return_amount,
            $processor_params,
            $order_info['payment_info']['payment_id'],
            $is_refund_partial
        );
        $params['partial_refund'] = $is_refund_partial;
        $extra['yandex_checkout_refund_params'] = serialize($params);
        Tygh::$app['db']->query(
            'UPDATE ?:rma_returns SET extra = ?s WHERE return_id = ?i',
            serialize($extra),
            $change_return_status['return_id']
        );
        //If this is not last call of this function - exit.
        if ($show_confirmation) {
            return;
        }
        $this->makeRefundRequest($params, $order_info, (int) $change_return_status['return_id'], $extra);
    }

    /**
     * @codingStandardsIgnoreStart
     * @param array{
     *            amount: array{
     *                currency: string,
     *                value: float
     *            },
     *            partial_refund: bool,
     *            payment_id: string,
     *            receipt?: array{
     *                          customer: array{email: string, phone: string},
     *                          items: list<array{amount: array{currency: string, value: float}, description: string, quantity: float, vat_code: string}>
     *                      }|null
     *         } $params Prepared parameters for refund request.
     * @codingStandardsIgnoreFinish
     * @param array<string, array<string, array<string, string>>> $order_info Information about order.
     * @param int                                                 $return_id  Return identifier.
     * @param array<string, string>                               $extra      Additional return information.
     *
     * @throws \YooKassa\Common\Exceptions\ApiException                YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ForbiddenException          YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\InternalServerError         YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\NotFoundException           YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ResponseProcessingException YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\TooManyRequestsException    YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\UnauthorizedException       YooKassa API Exception.
     * @throws \Exception                                              YooKassa API Exception.
     */
    protected function makeRefundRequest(array $params, array $order_info, $return_id, array $extra)
    {
        $is_refund_partial = $params['partial_refund'];
        unset($params['partial_refund']);
        $processor_params = $order_info['payment_method']['processor_params'];
        $payment = new YandexCheckout(
            $processor_params['shop_id'],
            $processor_params['scid'],
            ServiceProvider::getReceiptService()
        );
        try {
            $response = $payment->createRefund($params);
            if ($is_refund_partial) {
                $extra['yandex_checkout_refund_transaction_id'] = $response->getId();
                Tygh::$app['db']->query(
                    'UPDATE ?:rma_returns SET extra = ?s WHERE return_id = ?i',
                    serialize($extra),
                    $return_id
                );
                if ($order_info['status'] !== $processor_params['partial_refund_order_status']) {
                    fn_change_order_status((int) $order_info['order_id'], $processor_params['partial_refund_order_status']);
                }
            } else {
                fn_update_order_payment_info((int) $order_info['order_id'], ['yandex_checkout.full_refund_id' => $response->getId()]);
                if ($order_info['status'] !== $processor_params['full_refund_order_status']) {
                    fn_change_order_status((int) $order_info['order_id'], $processor_params['full_refund_order_status']);
                }
            }
        } catch (BadApiRequestException $exception) {
            fn_set_notification(NotificationSeverity::ERROR, __('error'), $exception->getMessage());
        }
    }

    /**
     * @param int $order_id Order identifier.
     *
     * @return bool
     */
    protected function isThisAFirstRefund($order_id)
    {
        $rma_returns_data = db_get_array('SELECT extra, status FROM ?:rma_returns WHERE order_id = ?i', $order_id);
        $is_refund_first = true;
        if (count($rma_returns_data) > 1) {
            foreach ($rma_returns_data as $rma_return_data) {
                $extra = unserialize($rma_return_data['extra']);
                $is_refund_first = $is_refund_first
                    && !isset($extra['yandex_checkout_refund_transaction_id'])
                    && !($rma_return_data['status'] === ReturnOperationStatuses::APPROVED)
                    && !($rma_return_data['status'] === ReturnOperationStatuses::COMPLETED);
            }
        }
        return $is_refund_first;
    }
}
