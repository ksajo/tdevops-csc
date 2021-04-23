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

use Tygh\Addons\RusTaxes\TaxType;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * Handler on install add-on.
 */
function fn_rus_taxes_install()
{
    $taxes_exists = db_get_field('SELECT COUNT(*) FROM ?:taxes WHERE status = ?s', 'A');

    if ($taxes_exists) {
        return;
    }

    $destinations = fn_get_destinations();

    $taxes = array(
        array(
            'tax' => __('rus_taxes.tax.vat20'),
            'regnumber' => '',
            'priority' => 0,
            'address_type' => 'S',
            'status' => 'A',
            'price_includes_tax' => 'Y',
            'tax_type' => TaxType::VAT_20,
            'rate_value' => 20,
            'rate_type' => 'P'
        ),
        array(
            'tax' => __('rus_taxes.tax.vat10'),
            'regnumber' => '',
            'priority' => 0,
            'address_type' => 'S',
            'status' => 'D',
            'price_includes_tax' => 'Y',
            'tax_type' => TaxType::VAT_10,
            'rate_value' => 10,
            'rate_type' => 'P'
        ),
        array(
            'tax' => __('rus_taxes.tax.vat0'),
            'regnumber' => '',
            'priority' => 0,
            'address_type' => 'S',
            'status' => 'D',
            'price_includes_tax' => 'Y',
            'tax_type' => TaxType::VAT_0,
            'rate_value' => 0,
            'rate_type' => 'P'
        ),
    );

    foreach ($taxes as $tax) {
        $tax['rates'] = array();

        foreach ($destinations as $destination) {
            $tax['rates'][$destination['destination_id']] = array(
                'rate_value' => $tax['rate_value'],
                'rate_type' => $tax['rate_type']
            );
        }

        fn_update_tax($tax, 0);
    }
}
