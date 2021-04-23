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

namespace Tygh\Addons\Retailcrm\Client;

use RetailCrm\ApiClient as BaseApiClient;
use Tygh\Addons\Retailcrm\Client\Http\Client;
use Tygh\Addons\Retailcrm\Response\ApiResponse;

/**
 * The class wrapper for base RetailCrm Client.
 * Replaces base http client.
 *
 * @package Tygh\Addons\Retailcrm\Client
 */
class ApiClient extends BaseApiClient
{
    /**
     * @inheritdoc
     */
    public function __construct($url, $api_key, $site = null)
    {
        if ('/' !== $url[strlen($url) - 1]) {
            $url .= '/';
        }

        $url = $url . 'api/' . self::VERSION;

        $this->client = new Client($url, array('apiKey' => $api_key));
        $this->siteCode = $site;
    }
}