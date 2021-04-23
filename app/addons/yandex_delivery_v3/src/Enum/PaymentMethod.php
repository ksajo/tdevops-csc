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

namespace Tygh\Addons\YandexDelivery\Enum;

/**
 * Class PaymentMethod contains all possible values of payment method for shipping service.
 *
 * @package Tygh\Addons\YandexDelivery\Enum
 */
class PaymentMethod
{
    const PREPAID = 'PREPAID';
    const CASH = 'CASH';
    const CREDIT = 'CREDIT';
}
