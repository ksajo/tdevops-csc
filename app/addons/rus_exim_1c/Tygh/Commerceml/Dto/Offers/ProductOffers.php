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

namespace Tygh\Commerceml\Dto\Offers;


class ProductOffers
{
    /** @var int */
    protected $product_id;

    /** @var string */
    protected $product_uid;

    /** @var string */
    protected $combination_uid;

    /** @var string */
    protected $uid;

    /** @var \Tygh\Commerceml\Dto\Offers\Offer[] */
    protected $offers = [];

    /**
     * @param \Tygh\Commerceml\Dto\Offers\Offer $offer
     */
    public function addOffer(Offer $offer)
    {
        $this->offers[$offer->getUid()] = $offer;
    }

    /**
     * @param string $offer_uid
     *
     * @return \Tygh\Commerceml\Dto\Offers\Offer|null
     */
    public function getOffer($offer_uid)
    {
        return $this->hasOffer($offer_uid) ? $this->offers[$offer_uid] : null;
    }

    /**
     * @param string $offer_uid
     *
     * @return bool
     */
    public function hasOffer($offer_uid)
    {
        return isset($this->offers[$offer_uid]);
    }

    /**
     * @return \Tygh\Commerceml\Dto\Offers\Offer[]
     */
    public function getOffers()
    {
        return $this->offers;
    }

    /**
     * @return string[]
     */
    public function getOfferUids()
    {
        return array_keys($this->offers);
    }

    /**
     * Gets feature uid list
     *
     * @return string[]
     */
    public function getFeatureUids()
    {
        $result = [];

        foreach ($this->offers as $offer) {
            $result = array_merge($result, $offer->getFeatureUids());
        }

        return $result;
    }

    /**
     * Get features
     *
     * @return \Tygh\Commerceml\Dto\Offers\OfferFeature[]
     */
    public function getFeatures()
    {
        $result = [];

        foreach ($this->offers as $offer) {
            foreach ($offer->getFeatures() as $feature) {
                if (!isset($result[$feature->getUid()])) {
                    $result[$feature->getUid()] = $feature;
                }
            }
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * @param int $product_id
     */
    public function setProductId($product_id)
    {
        $this->product_id = $product_id;
    }

    /**
     * @return string
     */
    public function getProductUid()
    {
        return $this->product_uid;
    }

    /**
     * @param string $product_uid
     */
    public function setProductUid($product_uid)
    {
        $this->product_uid = $product_uid;
    }

    /**
     * @return string
     */
    public function getCombinationUid()
    {
        return $this->combination_uid;
    }

    /**
     * @param string $combination_uid
     */
    public function setCombinationUid($combination_uid)
    {
        $this->combination_uid = $combination_uid;
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param string $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    public function updateOfferLocalIds($local_ids_map)
    {
        foreach ($local_ids_map as $uid => $local_id) {
            if ($this->hasOffer($uid)) {
                $this->getOffer($uid)->setLocalId($local_id);
            }
        }
    }
}