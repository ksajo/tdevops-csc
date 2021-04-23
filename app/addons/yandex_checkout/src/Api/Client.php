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

namespace Tygh\Addons\YandexCheckout\Api;

use YooKassa\Client as BaseClient;
use YooKassa\Common\HttpVerb;
use YooKassa\Helpers\UUID;
use YooKassa\Request\Payments\CreatePaymentRequest;
use YooKassa\Request\Payments\CreatePaymentRequestSerializer;
use YooKassa\Request\Payments\CreatePaymentResponse;

class Client extends BaseClient
{
    /**
     * @inheritDoc
     *
     * @psalm-suppress InvalidNullableReturnType
     */
    public function createPayment($payment, $idempotenceKey = null)
    {
        $path = self::PAYMENTS_PATH;
        if (is_array($payment) && isset($payment['transfers'])) {
            $original_transfers = $payment['transfers'];
        }
        $headers = [];

        if ($idempotenceKey) {
            $headers[self::IDEMPOTENCY_KEY_HEADER] = $idempotenceKey;
        } else {
            $headers[self::IDEMPOTENCY_KEY_HEADER] = UUID::v4();
        }
        if (is_array($payment)) {
            $payment = CreatePaymentRequest::builder()->build($payment);
        }

        $serializer = new CreatePaymentRequestSerializer();
        $serializedData = $serializer->serialize($payment);

        if (isset($serializedData['transfers'])) {
            $size = count($serializedData['transfers']);
            for ($i = 0; $i < $size; $i++) {
                if (!isset($original_transfers[$i]['platform_fee_amount'])) {
                    continue;
                }
                $serializedData['transfers'][$i]['platform_fee_amount'] = $original_transfers[$i]['platform_fee_amount'];
            }
        }
        $httpBody = $this->encodeData($serializedData);
        /** @psalm-suppress InvalidArgument */
        $response = $this->execute($path, HttpVerb::POST, null, $httpBody, $headers);

        $paymentResponse = null;
        if ((int) $response->getCode() === 200) {
            $resultArray = $this->decodeData($response);
            $paymentResponse = new CreatePaymentResponse($resultArray);
        } else {
            $this->handleError($response);
        }
        /** @psalm-suppress NullableReturnStatement */
        return $paymentResponse;
    }
}
