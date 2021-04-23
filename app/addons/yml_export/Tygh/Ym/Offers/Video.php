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

class Video extends Base
{
    protected $offer_type = 'video';

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
        'artist',
        'title',
        'year',
        'media',
        'starring',
        'director',
        'originalName',
        'country',
        'description',
        'sales_notes',
        'manufacturer_warranty',
        'country_of_origin',
        'adult',
        'age',
        'barcode',
        'cpa',
        'expiry',
        'weight',
        'dimensions',
        'downloadable',
        'purchase_price',
        'param'
    );

    protected $features = array(
        'year',
        'media',
        'starring',
        'director',
        'originalName',
        'country',
    );

    public function gatherAdditional($product)
    {
        $this->offer['attr']['type'] = "artist.title";
        $this->offer['items']['title'] = $this->getOfferName($product);

        return true;
    }
}
