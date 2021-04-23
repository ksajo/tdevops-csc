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

use Tygh\Ym\Offers\Audio as BaseAudio;

/**
 * Class Audio
 *
 * @package Tygh\Addons\Retailcrm\Ym\Offers
 */
class Audio extends BaseAudio
{
    /**
     * @inheritdoc
     */
    public function gatherAdditional($product)
    {
        parent::gatherAdditional($product);

        $this->schema[] = 'purchasePrice';
        if (!in_array('name', $this->schema)) {
            $this->schema[] = 'name';
        }

        $this->offer['attr'] = array_merge($this->offer['attr'], Simple::getRetailCrmOfferAttributes($product));
        $this->offer['items'] = Simple::getRetailCrmOfferItem($this->offer['items'], $product);

        return true;
    }
}
