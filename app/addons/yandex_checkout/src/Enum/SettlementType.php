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
 * Class SettlementType contains possible values for the `settlements[].type` API request field.
 *
 * @package Tygh\Addons\YandexCheckout\Enum
 */
class SettlementType
{
    const CASHLESS = 'cashless';
    const PREPAYMENT = 'prepayment';
    const POSTPAYMENT = 'postpayment';
    const CONSIDERATION = 'consideration';
}