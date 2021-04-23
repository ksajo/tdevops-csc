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


use Tygh\Addons\RusOnlineCashRegister\CashRegister\Response;

/**
 * The response class represents request response on authorizing user.
 *
 * @package Tygh\Addons\RusOnlineCashRegister\CashRegister\Atol
 */
class TokenResponse extends Response
{
    /** @var string|null */
    protected $token;

    /**
     * TokenResponse constructor.
     *
     * @param string $response Raw response string.
     */
    public function __construct($response)
    {
        $data = @json_decode($response, true);

        if (json_last_error()) {
            $this->setError(json_last_error(), json_last_error_msg());
        } elseif (!is_array($data)) {
            $this->setError('internal', 'Response json is invalid');
        } else {
            if (isset($data['error'])) {
                $this->setError($data['error']['code'], $data['error']['text']);
            } elseif (isset($data['code']) && $data['code'] >= 2) {
                // Backward compatible for API v3
                // If the error code is 0, then the auth token issued.
                // If the error code is 1, then the old auth token issued.
                // If the error code is greater than or equal to 2, the authorization failed.

                $this->setError($data['code'], $data['text']);
            } else {
                $this->token = $data['token'];
            }
        }
    }

    /**
     * @return null|string
     */
    public function getToken()
    {
        return $this->token;
    }
}
