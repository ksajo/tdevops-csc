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

namespace Tygh\Addons\Retailcrm\Client\Http;


use RetailCrm\Http\Client as BaseClient;
use Tygh\Addons\Retailcrm\Response\ApiResponse;

/**
 * The class wrapper for base RetailCrm http Client.
 * Adds the ability to resend a request when errors are associated with rate limit.
 *
 * @package Tygh\Addons\Retailcrm
 */
class Client extends BaseClient
{
    const DELAY = 300000;

    const ATTEMPT_COUNT = 3;

    const ERROR_RATE_LIMIT_CODE = 503;

    /**
     * @inheritDoc
     */
    public function makeRequest($path, $method, array $parameters = array())
    {
        $attempt = 0;
        $result = null;

        while ($attempt < self::ATTEMPT_COUNT) {
            /** @var \RetailCrm\Response\ApiResponse $result */
            $result = parent::makeRequest($path, $method, $parameters);

            if ($result->getStatusCode() === self::ERROR_RATE_LIMIT_CODE) {
                usleep(self::DELAY);
            } else {
                break;
            }

            $attempt++;
        }

        return ApiResponse::fromOriginalResponse($result);
    }
}