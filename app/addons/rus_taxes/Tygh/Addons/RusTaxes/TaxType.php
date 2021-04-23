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


namespace Tygh\Addons\RusTaxes;

/**
 * Provides tax types
 *
 * @package Tygh\Addons\RusTaxes
 */
class TaxType
{
    const NONE = 'none';

    const VAT_0 = 'vat0';

    const VAT_10 = 'vat10';

    /** @deprecated since 4.9.2.SP2 */
    const VAT_18 = 'vat18';

    const VAT_20 = 'vat20';

    const VAT_110 = 'vat110';

    /** @deprecated since 4.9.2.SP2 */
    const VAT_118 = 'vat118';

    const VAT_120 = 'vat120';

    /** @var array|null Internal cache for tax types */
    protected static $tax_types;

    /** @var array|null Internal cache for tax types map */
    protected static $tax_types_map;

    /**
     * Gets tax types list.
     *
     * @param bool $use_legacy Whether to return obsolete VAT rate types
     *
     * @return array
     */
    public static function getList($use_legacy = false)
    {
        if (self::$tax_types === null) {
            self::$tax_types = fn_get_schema('tax_types', 'schema');
        }

        $list = [];
        foreach (self::$tax_types as $type => $tax) {
            if (empty($tax['is_legacy']) || $use_legacy) {
                $list[$type] = $tax;
            }
        }

        return $list;
    }

    /**
     * Gets tax types map (tax_id => tax_type).
     *
     * @return array
     */
    public static function getMap()
    {
        if (self::$tax_types_map === null) {
            self::$tax_types_map = db_get_hash_single_array(
                'SELECT tax_id, tax_type FROM ?:taxes',
                array('tax_id', 'tax_type')
            );
        }

        return self::$tax_types_map;
    }
}
