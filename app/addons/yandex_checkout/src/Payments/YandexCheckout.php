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

namespace Tygh\Addons\YandexCheckout\Payments;

use Tygh\Addons\YandexCheckout\Services\ReceiptService;
use Tygh\Enum\YesNo;
use Tygh\Tygh;
use YooKassa\Client;
use YooKassa\Request\Payments\CreatePaymentResponse;

/**
 * Class YandexCheckout
 *
 * @package Tygh\Addons\YandexCheckout
 */
class YandexCheckout
{
    /** @var string  */
    protected $shop_id;

    /** @var string  */
    protected $secret_key;

    /** @var \YooKassa\Client */
    protected $client;

    /** @var \Tygh\Addons\YandexCheckout\Services\ReceiptService */
    protected $receipt_service;

    public function __construct($shop_id, $secret_key, ReceiptService $receipt_service)
    {
        $this->client = new Client();
        $this->shop_id = $shop_id;
        $this->secret_key = $secret_key;
        $this->receipt_service = $receipt_service;
        $this->authorize();
    }

    /**
     *
     */
    protected function authorize()
    {
        $this->client->setAuth($this->shop_id, $this->secret_key);
    }

    /**
     * Creates attribute 'amount' for Payment.
     *
     * @param float  $order_total Order total sum.
     * @param string $currency    Payment processor parameters.
     *
     * @return array{currency: string, value: float}
     */
    protected function createPaymentAmount($order_total, $currency)
    {
        return [
            'value' => ($currency !== CART_PRIMARY_CURRENCY)
                ? fn_format_price_by_currency($order_total, CART_PRIMARY_CURRENCY, $currency)
                : $order_total,
            'currency' => $currency,
        ];
    }

    /**
     * Creates payment at YooKassa server side.
     *
     * @param array<string, string|float|int> $order_info       Order information.
     * @param array<string, string>           $processor_params Payment processor parameters.
     *
     * @return \YooKassa\Request\Payments\CreatePaymentResponse
     *
     * @throws \YooKassa\Common\Exceptions\ApiException                YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\BadApiRequestException      YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ForbiddenException          YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\InternalServerError         YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\NotFoundException           YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ResponseProcessingException YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\TooManyRequestsException    YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\UnauthorizedException       YooKassa API Exception.
     * @throws \Exception                                              YooKassa API Exception.
     */
    public function createPayment(array $order_info, array $processor_params)
    {
        /** @var \Tygh\Storefront\Repository $repository */
        $repository = Tygh::$app['storefront.repository'];
        $storefront = $repository->findById((int) $order_info['storefront_id']);
        if (!$storefront) {
            return new CreatePaymentResponse([]);
        }
        $protocol = fn_get_storefront_protocol() . '://';
        $storefront_url = $protocol . $storefront->url;
        $params = [
            'amount' => $this->createPaymentAmount((float) $order_info['total'], $processor_params['currency']),
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => $storefront_url . '/yoomoney/return_to_store/' . $order_info['order_id'],
            ],
            'capture' => !YesNo::toBool($processor_params['are_held_payments_enabled']),
            'metadata' => [
                'order_id' => $order_info['order_id'],
            ],
        ];

        if (!empty($processor_params['selected_payment_method'])) {
            $params['payment_method_data'] = [
                'type' => $processor_params['selected_payment_method'],
            ];
        }

        if (YesNo::toBool($processor_params['send_receipt'])) {
            $receipt = $this->receipt_service->getReceiptFromOrder($order_info, 'full_prepayment');
            $params['receipt'] = $receipt;
        }

        return $this->client->createPayment($params);
    }

    /**
     * @param $payment_id
     *
     * @return \YooKassa\Model\PaymentInterface
     *
     * @throws \YooKassa\Common\Exceptions\ApiException                YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\BadApiRequestException      YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ExtensionNotFoundException  YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ForbiddenException          YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\InternalServerError         YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\NotFoundException           YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ResponseProcessingException YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\TooManyRequestsException    YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\UnauthorizedException       YooKassa API Exception.
     */
    public function getPaymentInfo($payment_id)
    {
        return $this->client->getPaymentInfo($payment_id);
    }

    /**
     * @param array $order_info
     *
     * @return \YooKassa\Request\Receipts\AbstractReceiptResponse|null
     *
     * @throws \YooKassa\Common\Exceptions\BadApiRequestException      YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ApiConnectionException      YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ApiException                YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\AuthorizeException          YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ForbiddenException          YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\InternalServerError         YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\NotFoundException           YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ResponseProcessingException YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\TooManyRequestsException    YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\UnauthorizedException       YooKassa API Exception.
     */
    public function createReceipt(array $order_info)
    {
        $receipt = $this->receipt_service->getPaymentReceiptFromOrder($order_info);
        return $this->client->createReceipt($receipt);
    }

    /**
     * Gets payment methods connected to YooKassa account.
     *
     * @return array<string>
     *
     * @throws \YooKassa\Common\Exceptions\BadApiRequestException      YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ApiException                YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\AuthorizeException          YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ExtensionNotFoundException  YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ForbiddenException          YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\InternalServerError         YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\NotFoundException           YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ResponseProcessingException YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\TooManyRequestsException    YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\UnauthorizedException       YooKassa API Exception.
     */
    public function getPaymentMethods()
    {
        $account = $this->client->me();

        return isset($account['payment_methods']) ? $account['payment_methods'] : [];
    }

    /**
     * @param array<string, array<string, string>> $order_info       Order information.
     * @param array<string, string>                $processor_params Payment processor parameters.
     *
     * @return \YooKassa\Request\Payments\Payment\CreateCaptureResponse
     *
     * @throws \YooKassa\Common\Exceptions\BadApiRequestException      YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ApiException                YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ForbiddenException          YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\InternalServerError         YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\NotFoundException           YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ResponseProcessingException YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\TooManyRequestsException    YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\UnauthorizedException       YooKassa API Exception.
     * @throws \Exception                                              YooKassa API Exception.
     */
    public function capturePayment(array $order_info, array $processor_params)
    {
        return $this->client->capturePayment(
            [
                'amount' => $this->createPaymentAmount((float) $order_info['total'], $processor_params['currency']),
            ],
            $order_info['payment_info']['payment_id']
        );
    }

    /**
     * Creates refund request in YooKassa.
     *
     * @param array{amount: array{currency: string, value: float}, payment_id: string, receipt?: array{customer: array{email: string, phone: string}, items: list<array{amount: array{currency: string, value: float}, description: string, quantity: float, vat_code: string}>}|null} $params Required parameters for request.
     *
     * @return \YooKassa\Request\Refunds\CreateRefundResponse
     *
     * @throws \YooKassa\Common\Exceptions\ApiException                YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\BadApiRequestException      YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ForbiddenException          YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\InternalServerError         YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\NotFoundException           YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ResponseProcessingException YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\TooManyRequestsException    YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\UnauthorizedException       YooKassa API Exception.
     * @throws \Exception                                              YooKassa API Exception.
     */
    public function createRefund(array $params)
    {
        return $this->client->createRefund($params);
    }

    /**
     * @param array<string, string>                                               $order_info        Information about order.
     * @param array<string, array<string,  array<string, array<string, string>>>> $return_data       Information about requested return.
     * @param float                                                               $amount            Requested to refund amount of money.
     * @param array<string, string>                                               $processor_params  Payment processor params.
     * @param string                                                              $payment_id        Currently refunded payment id.
     * @param bool                                                                $is_refund_partial Flag that indicates type of this refund.
     *
     * @return array{
     *          amount: array{
     *              currency: string,
     *              value: float
     *          },
     *          payment_id: string,
     *          receipt?: array{
     *              customer: array{email: string, phone: string},
     *              items: list<array{amount: array{currency: string, value: float},
     *                                description: string,
     *                                quantity: float,
     *                                vat_code: string
     *                              }>
     *          }
     *        |null}
     */
    public function createRefundParams(array $order_info, array $return_data, $amount, array $processor_params, $payment_id, $is_refund_partial)
    {
        $params = [
            'payment_id' => $payment_id,
            'amount'     => $this->createPaymentAmount($amount, $processor_params['currency']),
        ];
        if ($is_refund_partial && YesNo::toBool($processor_params['send_receipt'])) {
            $params['receipt'] = $this->receipt_service->getReceiptFromRefund($order_info, $return_data['items']['A'], $processor_params['currency']);
        }
        return $params;
    }
}
