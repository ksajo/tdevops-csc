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

use Tygh\Addons\YandexCheckout\Enum\CommissionType;
use Tygh\Addons\YandexCheckout\Services\PayoutsManagerService;
use Tygh\Addons\YandexCheckout\Services\ReceiptService;
use Tygh\Enum\YesNo;
use Tygh\Addons\YandexCheckout\Api\Client;
use Tygh\Tygh;
use YooKassa\Request\Payments\CreatePaymentResponse;

class YandexCheckoutForMarketplaces
{
    /** @var string */
    protected $shop_id;

    /** @var string */
    protected $secret_key;

    /** @var \YooKassa\Client */
    protected $client;

    /** @var \Tygh\Addons\YandexCheckout\Services\ReceiptService */
    protected $receipt_service;

    /** @var \Tygh\Addons\YandexCheckout\Services\PayoutsManagerService */
    protected $payouts_manager_service;

    public function __construct(
        $shop_id,
        $secret_key,
        ReceiptService $receipt_service,
        PayoutsManagerService $payouts_manager_service
    ) {
        $this->client = new Client();
        $this->shop_id = $shop_id;
        $this->secret_key = $secret_key;
        $this->receipt_service = $receipt_service;
        $this->payouts_manager_service = $payouts_manager_service;
        $this->authorize();
    }

    protected function authorize()
    {
        $this->client->setAuth($this->shop_id, $this->secret_key);
    }

    /**
     * @param array $order_info
     * @param array $processor_data
     *
     * @return \YooKassa\Request\Payments\CreatePaymentResponse
     *
     * @throws \Exception YooKassa API Exception.
     */
    public function createPayment(array $order_info, array $processor_data)
    {
        /** @var \Tygh\Storefront\Repository $repository */
        $repository = Tygh::$app['storefront.repository'];
        $storefront = $repository->findById($order_info['storefront_id']);
        if (!$storefront) {
            return new CreatePaymentResponse([]);
        }
        $protocol = fn_get_storefront_protocol() . '://';
        $storefront_url = $protocol . $storefront->url;
        $params = [
            'amount'       => [
                'value'    => $processor_data['processor_params']['currency'] != CART_PRIMARY_CURRENCY
                    ? fn_format_price_by_currency($order_info['total'], CART_PRIMARY_CURRENCY, $processor_data['processor_params']['currency'])
                    : $order_info['total'],
                'currency' => $processor_data['processor_params']['currency'],
            ],
            'confirmation' => [
                'type'       => 'redirect',
                'return_url' => $storefront_url . '/yoomoney/platform/return_to_store/' . $order_info['order_id'],
            ],
            'capture'      => true,
            'metadata'     => [
                'order_id' => $order_info['order_id'],
            ],
        ];

        $transfers = $this->getTransfers($order_info);
        foreach ($transfers as $transfer) {
            $param_transfer = [
                'account_id' => $transfer['shop_id'],
                'amount'     => [
                    'value'    => $processor_data['processor_params']['currency'] != CART_PRIMARY_CURRENCY
                        ? fn_format_price_by_currency($transfer['total'], CART_PRIMARY_CURRENCY, $processor_data['processor_params']['currency'])
                        : $transfer['total'],
                    'currency' => $processor_data['processor_params']['currency'],
                ],
            ];
            if (isset($transfer['fee'])) {
                $param_transfer['platform_fee_amount'] = [
                    'value' => $transfer['fee'],
                    'currency' => $processor_data['processor_params']['currency'],
                ];
            }
            $params['transfers'][] = $param_transfer;
            if ($transfer['company_id']) {
                $params['metadata']["transfer_{$transfer['company_id']}"] = $transfer['withdrawal_amount'];
            }
        }

        $payment = $this->client->createPayment($params);

        return $payment;
    }

    /**
     * @param array  $order_info
     * @param int    $shop_id
     * @param string $payment_mode
     * @param string $settlement_type
     *
     * @return \YooKassa\Request\Receipts\AbstractReceiptResponse|null
     *
     * @throws \YooKassa\Common\Exceptions\ApiConnectionException      YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ApiException                YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\AuthorizeException          YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\BadApiRequestException      YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ForbiddenException          YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\InternalServerError         YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\NotFoundException           YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\ResponseProcessingException YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\TooManyRequestsException    YooKassa API Exception.
     * @throws \YooKassa\Common\Exceptions\UnauthorizedException       YooKassa API Exception.
     */
    public function createReceipt(array $order_info, $shop_id, $payment_mode, $settlement_type)
    {
        if (!$shop_id) {
            $shop_id = $this->shop_id;
        }

        $receipt = $this->receipt_service->getPaymentReceiptFromOrder(
            $order_info,
            $shop_id,
            $payment_mode,
            $settlement_type
        );

        return $this->client->createReceipt($receipt);
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
     * @return array
     */
    public function getTransfers(array $order_info)
    {
        $transfers = [];
        if (YesNo::toBool($order_info['is_parent_order'])) {
            $orders = fn_get_suborders_info($order_info['order_id']);
        } else {
            $orders = [$order_info];
        }

        foreach ($orders as $order) {
            $order_id = (int) $order['order_id'];
            $company_id = (int) $order['company_id'];
            $total = (float) $order['total'];

            $fee = $this->payouts_manager_service->getManager($company_id)->getOrderFee($order_id);
            if ($fee) {
                $fee = min($fee, $total);
                $total -= $fee;
            }

            if (!$total) {
                continue;
            }

            $company_data = fn_get_company_data($company_id);
            $shop_id = $company_data['yandex_checkout_shopid']
                ?: null;

            $transfer = [
                'order_id'          => $order_id,
                'company_id'        => $company_id,
                'total'             => $order['total'],
                'shop_id'           => $shop_id,
                'withdrawal_amount' => (string) $total,
            ];
            if ($company_data['yandex_checkout_commission_type'] === CommissionType::FLEXIBLE) {
                $transfer['fee'] = $fee;
            }
            $transfers[] = $transfer;
        }

        return $transfers;
    }
}