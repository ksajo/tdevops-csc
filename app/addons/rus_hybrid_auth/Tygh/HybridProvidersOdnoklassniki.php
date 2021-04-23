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

namespace Tygh;

class HybridProvidersOdnoklassniki extends \Hybrid_Providers_Odnoklassniki
{
    public $useSafeUrls = true;

    /**
     * Finishes login process for provider.
     */
    function loginFinish()
    {
        $error = (array_key_exists('error', $_REQUEST)) ? $_REQUEST['error'] : '';

        // Check for errors
        if ($error) {
            throw new Exception('Authentication failed! ' . $this->providerId . ' returned an error: ' . $error, 5);
        }

        // Try to authenticate user
        $code = (array_key_exists('code', $_REQUEST)) ? $_REQUEST['code'] : '';

        try {
            $this->authodnoklass($code);
        } catch (Exception $e) {
            throw new Exception('User profile request failed! ' . $this->providerId . ' returned an error: ' . $e->getMessage(), 6);
        }

        // Check if authenticated
        if (!$this->api->access_token) {
            throw new Exception('Authentication failed! ' . $this->providerId . ' returned an invalid access token.', 5);
        }

        // Store tokens
        $this->token('access_token' , $this->api->access_token);
        $this->token('refresh_token', $this->api->refresh_token);
        $this->token('expires_in'   , $this->api->access_token_expires_in);
        $this->token('expires_at'   , $this->api->access_token_expires_at);

        // Set user connected locally
        $this->setUserConnected();
    }
}

