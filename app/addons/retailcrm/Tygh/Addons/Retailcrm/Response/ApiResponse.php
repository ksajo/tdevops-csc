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

namespace Tygh\Addons\Retailcrm\Response;

use RetailCrm\Response\ApiResponse as BaseResponse;

/**
 * The class wrapper for base RetailCrm ApiResponse.
 * Adds the ability to resend the checking a status.
 *
 * @package Tygh\Addons\Retailcrm
 */
class ApiResponse extends BaseResponse
{
    /**
     * @inheritDoc
     */
    public function isSuccessful()
    {
        return (isset($this->response['success']) ? $this->response['success'] : true) && parent::isSuccessful();
    }

    public static function fromOriginalResponse(BaseResponse $original_response)
    {
        $self = new self($original_response->statusCode);
        $self->response = $original_response->response;

        return $self;
    }
}