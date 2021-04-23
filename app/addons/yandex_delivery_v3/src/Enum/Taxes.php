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

use Tygh\Addons\RusTaxes\TaxType;

/**
 * Class Taxes contains all possible values of product taxes for shipping service.
 *
 * @package Tygh\Addons\YandexDelivery\Enum
 */
class Taxes
{
    const VAT_20 = 'VAT_20';
    const VAT_10 = 'VAT_10';
    const VAT_0 = 'VAT_0';
    const NO_VAT = 'NO_VAT';

    /**
     * Gets tax identifier by CS system and return appropriate identifier for Yandex.Delivery.
     *
     * @param string $id Tax identifier.
     *
     * @return string
     */
    public static function getTax($id)
    {
        switch ($id) {
            case TaxType::VAT_0:
                return self::VAT_0;
            case TaxType::VAT_10:
                return self::VAT_10;
            case TaxType::VAT_20:
                return self::VAT_20;
            case TaxType::NONE:
            default:
                return self::NO_VAT;
        }
    }
}
