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


class OfferFeatureValue
{
    /** @var string */
    protected $feature_uid;

    /** @var string */
    protected $feature_name;

    /** @var int */
    protected $feature_id;

    /** @var string */
    protected $variant_uid;

    /** @var string */
    protected $variant_name;

    /** @var int */
    protected $variant_id;

    /** @var \Tygh\Commerceml\Dto\Offers\OfferFeatureValue[] */
    protected static $instances = [];

    /**
     * OfferFeatureValue constructor.
     */
    protected function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getFeatureUid()
    {
        return $this->feature_uid;
    }

    /**
     * @param mixed $feature_uid
     */
    public function setFeatureUid($feature_uid)
    {
        $this->feature_uid = $feature_uid;
    }

    /**
     * @return mixed
     */
    public function getFeatureName()
    {
        return $this->feature_name;
    }

    /**
     * @param mixed $feature_name
     */
    public function setFeatureName($feature_name)
    {
        $this->feature_name = $feature_name;
    }

    /**
     * @return mixed
     */
    public function getFeatureId()
    {
        return $this->feature_id;
    }

    /**
     * @param mixed $feature_id
     */
    public function setFeatureId($feature_id)
    {
        $this->feature_id = $feature_id;
    }

    /**
     * @return mixed
     */
    public function getVariantUid()
    {
        return $this->variant_uid;
    }

    /**
     * @param mixed $variant_uid
     */
    public function setVariantUid($variant_uid)
    {
        $this->variant_uid = $variant_uid;
    }

    /**
     * @return mixed
     */
    public function getVariantName()
    {
        return $this->variant_name;
    }

    /**
     * @param mixed $variant_name
     */
    public function setVariantName($variant_name)
    {
        $this->variant_name = $variant_name;
    }

    /**
     * @return mixed
     */
    public function getVariantId()
    {
        return $this->variant_id;
    }

    /**
     * @param mixed $variant_id
     */
    public function setVariantId($variant_id)
    {
        $this->variant_id = $variant_id;
    }

    public static function create($feature_uid, $variant_uid)
    {
        $key = $feature_uid . $variant_uid;

        if (!isset(self::$instances[$key])) {
            $self = new self;

            $self->setFeatureUid($feature_uid);
            $self->setVariantUid($variant_uid);

            self::$instances[$key] = $self;
        }

        return self::$instances[$key];
    }

    public static function clearInstances()
    {
        self::$instances = [];
    }
}