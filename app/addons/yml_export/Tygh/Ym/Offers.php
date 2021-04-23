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

namespace Tygh\Ym;

use Tygh\Addons\ProductVariations\Product\Manager as ProductManager;

class Offers
{
    protected $offers = array();
    protected $options = array();
    protected $log = array();

    public function __construct($options = array(), $log)
    {
        $this->options = $options;
        $this->log = $log;
    }

    public function build($product)
    {
        $offer = $this->getOffer($product);

        return $offer->xml($product);
    }

    public function preBuild($product)
    {
        $offer = $this->getOffer($product);

        return $offer->preBuild($product);
    }

    public function postBuild($product, $offer_data, $xml)
    {
        $offer = $this->getOffer($product);

        return $offer->postBuild($product, $offer_data, $xml);
    }

    public function getOfferType($product)
    {
        if (!empty($product['main_category'])) {
            $category_id = $product['main_category'];
        } else {
            $category_id = 0;
        }

        if (!empty($product['yml2_offer_type'])) {
            $offer_type = $product['yml2_offer_type'];
        } elseif (!empty($product['category_id']) && !empty($this->options['offer_type_categories'][$product['category_id']])) {
            $offer_type = $this->options['offer_type_categories'][$product['category_id']];
            $product['offer_type_categories'] = $this->options['offer_type_categories'][$product['category_id']];
        } elseif (!empty($this->options['offer_type_categories'][$category_id])) {
            $offer_type = $this->options['offer_type_categories'][$category_id];
            $product['offer_type_categories'] = $this->options['offer_type_categories'][$category_id];
        } else {
            $offer_type = 'simple';
        }

        return $offer_type;
    }

    public function getOffer($product)
    {
        $offer_type = $this->getOfferType($product);

        if (!isset($this->offers[$offer_type])) {
            $offer_class = "\\Tygh\\Ym\\Offers\\" . fn_camelize($offer_type);

            if (class_exists($offer_class)) {
                $offer = $this->offers[$offer_type] = new $offer_class($this->options, $this->log);
            } else {
                throw new \Exception("The wrong offer");
            }

        } else {
            $offer = $this->offers[$offer_type];
        }

        return $offer;
    }
}
