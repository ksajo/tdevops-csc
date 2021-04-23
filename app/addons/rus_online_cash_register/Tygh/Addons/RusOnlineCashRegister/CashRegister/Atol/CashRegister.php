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

use Tygh\Addons\RusOnlineCashRegister\CashRegister\ICashRegister;
use Tygh\Addons\RusOnlineCashRegister\Receipt\Receipt;
use Tygh\Addons\RusOnlineCashRegister\RequestLogger;
use Tygh\Addons\RusOnlineCashRegister\CashRegister\Atol\ReceiptRequest as ReceiptRequestV3;
use Tygh\Addons\RusOnlineCashRegister\CashRegister\Atol\v4\ReceiptRequest as ReceiptRequestV4;
use Tygh\Http;
use Exception;

/**
 * Provides methods to access ATOL Online.
 *
 * @package Tygh\Addons\RusOnlineCashRegister\CashRegister\Atol
 */
class CashRegister implements ICashRegister
{
    const MAX_ATTEMPTS = 5;
    const DELAY = 1000000;

    const API_URL = 'https://online.atol.ru/possystem';
    const TEST_API_URL = 'https://testonline.atol.ru/possystem';

    /** @var string */
    protected $callback_url;

    /** @var string  */
    protected $inn;

    /** @var string */
    protected $group_code;

    /** @var string */
    protected $payment_address;

    /** @var string */
    protected $login;

    /** @var string */
    protected $password;

    /** @var Http */
    protected $http_client;

    /** @var RequestLogger */
    protected $request_logger;

    /** @var string */
    protected $token;

    /** @var string */
    protected $mode;

    /** @var string */
    protected $api_version;

    /** @var string */
    protected $company_email;

    /**
     * CashRegister constructor
     *
     * @param string            $inn                Company INN
     * @param string            $group_code         Group identifier
     * @param string            $payment_address    Payment address
     * @param string            $login              Login
     * @param string            $password           Password
     * @param string            $callback_url       URL address for notification of the processed receipt
     * @param Http              $http_client        Instance of the http client
     * @param RequestLogger     $request_logger     Instance of the request logger
     * @param string            $mode               Run mode
     * @param string            $api_version        API version
     * @param string            $company_email      Company email
     */
    public function __construct(
        $inn, $group_code, $payment_address, $login, $password,
        $callback_url, Http $http_client, RequestLogger $request_logger, $mode = 'live', $api_version = '4', $company_email = 'admin@example.com'
    ) {
        $this->inn = (string) $inn;
        $this->group_code = (string) $group_code;
        $this->payment_address = (string) $payment_address;
        $this->callback_url = (string) $callback_url;
        $this->login = (string) $login;
        $this->password = (string) $password;
        $this->http_client = $http_client;
        $this->request_logger = $request_logger;
        $this->mode = (string) $mode;
        $this->api_version = $api_version;
        $this->company_email = $company_email;
    }

    /**
     * Gets auth token.
     *
     * @return null|string
     */
    public function getToken()
    {
        //TODO Add caching
        if ($this->token === null) {
            $response = $this->auth();
            $this->token = $response->getToken();
        }

        return $this->token;
    }
    /**
     * Get API URL
     *
     * @return string
     */
    protected function getApiUrl()
    {
        if ($this->mode == 'test') {
            $url = sprintf('%s/v%s', self::TEST_API_URL, $this->api_version);
        } else {
            $url = sprintf('%s/v%s', self::API_URL, $this->api_version);
        }
        return $url;
    }

    /**
     * Authorize user.
     *
     * @return TokenResponse
     */
    public function auth()
    {
        $url = $this->getApiUrl() . '/getToken';
        $data = array(
            'login' => $this->login,
            'pass' => $this->password,
        );
        $response = $this->makeRequest($url, json_encode($data), 'post', self::MAX_ATTEMPTS, false);
        return new TokenResponse($response);
    }

    /**
     * Sends receipt.
     *
     * @param \Tygh\Addons\RusOnlineCashRegister\Receipt\Receipt $receipt
     *
     * @return \Tygh\Addons\RusOnlineCashRegister\CashRegister\Atol\SendResponse|\Tygh\Addons\RusOnlineCashRegister\CashRegister\SendResponse
     * @throws \Exception
     */
    public function send(Receipt $receipt)
    {
        if ($this->api_version == '4') {
            $request = new ReceiptRequestV4($receipt, $this->inn, $this->payment_address, $this->callback_url, $this->company_email);
            $json = $request->json();
        } else {
            $request = new ReceiptRequestV3($receipt, $this->inn, $this->payment_address, $this->callback_url);
            $json = $request->json();
        }

        if ($receipt->getType() === Receipt::TYPE_SELL) {
            $url = $this->getUrl('sell');
        } elseif ($receipt->getType() === Receipt::TYPE_SELL_REFUND) {
            $url = $this->getUrl('sell_refund');
        } elseif ($receipt->getType() === Receipt::TYPE_BUY) {
            $url = $this->getUrl('buy');
        } else {
            throw new Exception('Receipt type is undefined');
        }

        $response = $this->makeRequest($url, $json, 'post', self::MAX_ATTEMPTS);

        return new SendResponse($response);
    }

    /**
     * Gets receipt info.
     *
     * @param string $uuid Receipt identifier
     *
     * @return InfoResponse
     */
    public function info($uuid)
    {
        $response = $this->makeRequest($this->getUrl('report', array('uuid' => $uuid)), $uuid, 'get', 1);

        return new InfoResponse($response);
    }

    /**
     * Gets resource URL.
     *
     * @param string $operation
     * @param array  $parts
     * @param array  $params
     *
     * @return string
     */
    protected function getUrl($operation, array $parts = array(), array $params = array())
    {
        $params['tokenid'] = $this->getToken();

        return sprintf('%s/%s/%s/%s?%s',
            $this->getApiUrl(),
            $this->group_code,
            $operation,
            implode('/', $parts),
            http_build_query($params)
        );
    }

    /**
     * Make request.
     *
     * @param string $url           Resource URL
     * @param string $data          Data
     * @param string $method        Request method
     * @param int    $max_attempts  Number of the max attempts
     * @param bool   $log_data      Whether to logged data of request or response
     *
     * @return string
     */
    protected function makeRequest($url, $data, $method, $max_attempts, $log_data = true)
    {
        $status = $response_raw = null;
        $attempt = 0;
        $headers = [];
        $log_id = $this->request_logger->startRequest(str_replace($this->token, '******', $url), $log_data ? $data : '-');

        $logging = Http::$logging;
        Http::$logging = false;

        if ($this->api_version == '4') {
            $headers[] = 'Content-type: application/json';

            if ($this->token !== null) {
                $headers[] = "Token: {$this->token}";
            }
        }

        while ($attempt < $max_attempts) {
            if ($method === 'post') {
                $response_raw = $this->http_client->post($url, $data, ['headers' => $headers]);
            } else {
                $response_raw = $this->http_client->get($url, [], ['headers' => $headers]);
            }

            $status = $this->http_client->getStatus();

            if ($status == Http::STATUS_OK) {
                break;
            }

            usleep(self::DELAY);
            $attempt++;
        }

        $error = $this->http_client->getError();
        Http::$logging = $logging;

        if (empty($error) && $status != Http::STATUS_OK) {
            $error = 'Response http status code: ' . $status;
        }

        if ($error) {
            $this->request_logger->failRequest($log_id, $response_raw, $error);
        } else {
            $this->request_logger->successRequest($log_id, $log_data ? $response_raw : '-');
        }

        return $response_raw;
    }
}
