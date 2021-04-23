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

namespace Tygh\Enum;

use Tygh\Addons\RusTaxes\TaxType;

/**
 * Class YandexCheckpointVatTypes contains IDs of product VAT types for Yandex.Checkpoint.
 *
 * @package Tygh\Enum
 */
class YandexCheckpointVatTypes
{
    /**
     * No VAT
     *
     * @var int
     */
    const VAT_NONE = 1;

    /**
     * VAT 0%
     *
     * @var int
     */
    const VAT_0 = 2;

    /**
     * VAT 10%
     *
     * @var int
     */
    const VAT_10 = 3;

    /**
     * VAT 18%
     *
     * @var int
     *
     * @deprecated since 4.9.2.SP2
     */
    const VAT_18 = 4;

    /**
     * VAT 20%
     *
     * @var int
     */
    const VAT_20 = 4;

    /**
     * VAT 10/100
     *
     * @var int
     */
    const VAT_10_110 = 5;

    /**
     * VAT 18/118
     *
     * @var int
     *
     * @deprecated since 4.9.2.SP2
     */
    const VAT_18_118 = 6;

    /**
     * VAT 20/120
     *
     * @var int
     */
    const VAT_20_120 = 6;

    /**
     * Provides IDs of possible VAT types.
     *
     * @return array Array of VAT types IDs
     */
    public static function getAll()
    {
        return [
            self::VAT_NONE   => self::VAT_NONE,
            self::VAT_0      => self::VAT_0,
            self::VAT_10     => self::VAT_10,
            self::VAT_18     => self::VAT_18,
            self::VAT_20     => self::VAT_20,
            self::VAT_10_110 => self::VAT_10_110,
            self::VAT_18_118 => self::VAT_18_118,
            self::VAT_20_120 => self::VAT_20_120,
        ];
    }

    /**
     * Gets yandex checkpoint tax type.
     *
     * @param string $base_type Base tax type (TaxType::VAT_0, TaxType::VAT_10, TaxType::VAT_18, etc)
     *
     * @return int
     */
    public static function getTaxTypeByBaseType($base_type)
    {
        $map = [
            TaxType::NONE    => self::VAT_NONE,
            TaxType::VAT_0   => self::VAT_0,
            TaxType::VAT_10  => self::VAT_10,
            TaxType::VAT_18  => self::VAT_18,
            TaxType::VAT_20  => self::VAT_20,
            TaxType::VAT_110 => self::VAT_10_110,
            TaxType::VAT_118 => self::VAT_18_118,
            TaxType::VAT_120 => self::VAT_20_120,
        ];

        return isset($map[$base_type]) ? $map[$base_type] : self::VAT_NONE;
    }
}
