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

namespace Tygh\Addons\YandexCheckout\Enum;

/**
 * Class PaymentStatus represents all available payment statuses in YooKassa API
 *
 * @package Tygh\Addons\YandexCheckout\Enum
 */
class PaymentStatus
{
    const PENDING = 'pending';
    const WAITING_FOR_CAPTURE = 'waiting_for_capture';
    const SUCCEEDED = 'succeeded';
    const CANCELED = 'canceled';
}