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

namespace Tygh\Addons\Retailcrm\Ym\Offers;

use Tygh\Ym\Offers\ApparelSimple as BaseApparelSimple;

/**
 * Class ApparelSimple
 *
 * @package Tygh\Addons\Retailcrm\Ym\Offers
 */
class ApparelSimple extends BaseApparelSimple
{
    /**
     * @inheritdoc
     */
    protected function getApparelOffer($product)
    {
        $this->schema[] = 'purchasePrice';
        if (!in_array('name', $this->schema)) {
            $this->schema[] = 'name';
        }

        $this->offer['attr'] = array_merge($this->offer['attr'], Simple::getRetailCrmOfferAttributes($product));
        $this->offer['items'] = Simple::getRetailCrmOfferItem($this->offer['items'], $product);
    }

    /**
     * @inheritdoc
     */
    protected function buildOfferCombination($product, $combination)
    {
        $result = parent::buildOfferCombination($product, $combination);

        if ($result) {
            $this->offer['attr'] = array_merge($this->offer['attr'], Simple::getRetailCrmOfferAttributes($product, $combination));
            $this->offer['items']['name'] = self::getProductCombinationName($product, $combination);
        }

        return $result;
    }

    /**
     * Gets product combination name.
     *
     * @param array $product        Product data
     * @param array $combination    Product combination data
     *
     * @return string
     */
    public static function getProductCombinationName($product, $combination)
    {
        $parts = array($product['product']);

        if (!empty($combination['combination'])) {
            foreach ($combination['combination'] as $option_id => $variant_id) {
                if (isset($product['product_options'][$option_id]['variants'][$variant_id])) {
                    $option = $product['product_options'][$option_id];
                    $variant = $option['variants'][$variant_id];

                    $parts[] = $option['option_name'] . ': ' . $variant['variant_name'];
                }
            }
        }

        return implode(', ', $parts);
    }
}