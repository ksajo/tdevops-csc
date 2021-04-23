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
 * Class ItemPaymentStatus contains possible values for the `payment_mode` API request field.
 *
 * @package Tygh\Addons\YandexCheckout\Enum
 */
class PaymentMode
{
    const FULL_PREPAYMENT = 'full_prepayment';
    const PARTIAL_PREPAYMENT = 'partial_prepayment';
    const ADVANCE = 'advance';
    const FULL_PAYMENT = 'full_payment';
    const PARTIAL_PAYMENT = 'partial_payment';
    const CREDIT = 'credit';
    const CREDIT_PAYMENT = 'credit_payment';
}