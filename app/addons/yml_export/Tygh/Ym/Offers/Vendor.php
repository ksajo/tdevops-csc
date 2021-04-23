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

namespace Tygh\Ym\Offers;

use Tygh\Ym\Logs;

class Vendor extends Base
{
    protected $offer_type = 'vendor';

    protected $schema = array(
        'url',
        'price',
        'oldprice',
        'currencyId',
        'categoryId',
        'picture',
        'store',
        'pickup',
        'delivery',
        'delivery-options',
        'typePrefix',
        'vendor',
        'vendorCode',
        'model',
        'description',
        'sales_notes',
        'manufacturer_warranty',
        'country_of_origin',
        'adult',
        'age',
        'barcode',
        'cpa',
        'rec',
        'expiry',
        'weight',
        'dimensions',
        'downloadable',
        'purchase_price',
        'param',
    );

    public function postBuild($xml, $product, $offer_data)
    {
        if (empty($offer_data['items']['vendor'])) {
            $this->log->write(Logs::SKIP_PRODUCT, $product, __('yml2_log_brand_is_empty'));
            return false;
        }

        return true;
    }

    public function gatherAdditionalExt($product)
    {
        $this->offer['attr']['type'] = "vendor.model";

        return true;
    }
}
