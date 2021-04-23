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

use Tygh\Ym\Offers;

class Book extends Base
{
    protected $offer_type = 'book';

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
        'author',
        'name',
        'publisher',
        'series',
        'year',
        'ISBN',
        'volume',
        'part',
        'language',
        'binding',
        'page_extent',
        'table_of_contents',
        'description',
        'sales_notes',
        'manufacturer_warranty',
        'barcode',
        'age',
        'adult',
        'cpa',
        'expiry',
        'weight',
        'dimensions',
        'downloadable',
        'purchase_price',
        'country_of_origin',
        'param'
    );

    protected $features = array(
        'author',
        'publisher',
        'series',
        'year',
        'ISBN',
        'volume',
        'part',
        'language',
        'binding',
        'page_extent',
        'table_of_contents'
    );

    public function gatherAdditional($product)
    {
        $this->offer['attr']['type'] = "book";
        $this->offer['items']['name'] = $this->getOfferName($product);

        return true;
    }
}
