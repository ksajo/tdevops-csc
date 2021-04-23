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

$schema = [
    TaxType::NONE    => 1105,
    TaxType::VAT_0   => 1104,
    TaxType::VAT_10  => 1103,
    TaxType::VAT_18  => 1102,
    TaxType::VAT_20  => 1102,
    TaxType::VAT_110 => 1107,
    TaxType::VAT_118 => 1106,
    TaxType::VAT_120 => 1106,
];

return $schema;
