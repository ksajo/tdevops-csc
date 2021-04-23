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


namespace Tygh\Addons\RusOnlineCashRegister\CashRegister\Atol;

use Tygh\Addons\RusOnlineCashRegister\CashRegister\SendResponse as BaseSendResponse;
use Tygh\Addons\RusOnlineCashRegister\Receipt\Receipt;
use \Tygh\Addons\RusOnlineCashRegister\CashRegister\Response;

/**
 * The response class represents request response on creating receipt.
 *
 * @package Tygh\Addons\RusOnlineCashRegister\CashRegister\Atol
 */
class SendResponse extends BaseSendResponse
{
    /**
     * SendResponse constructor.
     *
     * @param string $response Raw response string.
     */
    public function __construct($response)
    {
        self::parseJson($response, $this);
    }

    /**
     * Parse json.
     *
     * @param string    $raw_response   Raw response string
     * @param Response  $response       Instance of the Response
     *
     * @return array|null
     */
    public static function parseJson($raw_response, Response $response)
    {
        $data = @json_decode($raw_response, true);

        if (json_last_error()) {
            $response->setError(json_last_error(), json_last_error_msg());
        } elseif (!is_array($data)) {
            $response->setError('internal', 'Response json is invalid');
        } elseif (isset($data['status'])) {
            if ($data['status'] == 'fail') {
                $response->setStatus(Receipt::STATUS_FAIL);

                if (isset($data['error']['text'])) {
                    $response->setStatusMessage($data['error']['text']);
                }
            } elseif ($data['status'] == 'done') {
                $response->setStatus(Receipt::STATUS_DONE);
            } elseif ($data['status'] == 'wait') {
                $response->setStatus(Receipt::STATUS_WAIT);
            }
        }

        if (isset($data['error'])) {
            $response->setError($data['error']['code'], $data['error']['text']);

            $response->setStatus(Receipt::STATUS_FAIL);
            $response->setStatusMessage($data['error']['text']);
        }

        if (isset($data['uuid'])) {
            $response->setUUID($data['uuid']);
        }

        return $data;
    }
}