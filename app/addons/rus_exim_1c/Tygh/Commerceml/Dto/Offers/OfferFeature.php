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


class OfferFeature
{
    /** @var string */
    protected $uid;

    /** @var string */
    protected $name;

    /** @var string */
    protected $type;

    /** @var string */
    protected $purpose;

    /** @var int */
    protected $id;

    /** @var \Tygh\Commerceml\Dto\Offers\OfferFeatureValue[] */
    protected $variants = [];

    /** @var \Tygh\Commerceml\Dto\Offers\OfferFeature[] */
    protected static $instances = [];

    /**
     * OfferFeature constructor
     */
    protected function __construct()
    {
    }

    /**
     * @param \Tygh\Commerceml\Dto\Offers\OfferFeatureValue $feature_value
     */
    public function addVariant(OfferFeatureValue $feature_value)
    {
        $this->variants[$feature_value->getVariantUid()] = $feature_value;
    }

    /**
     * @return \Tygh\Commerceml\Dto\Offers\OfferFeatureValue[]
     */
    public function getVariants()
    {
        return $this->variants;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;

        foreach ($this->variants as $variant) {
            $variant->setFeatureId($id);
        }
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

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * @param string $purpose Feature purpose
     */
    public function setPurpose($purpose)
    {
        $this->purpose = $purpose;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    public static function createFromFeatureValue(OfferFeatureValue $feature_value)
    {
        if (!isset(self::$instances[$feature_value->getFeatureUid()])) {
            $self = new self;
            $self->id = $feature_value->getFeatureId();
            $self->name = $feature_value->getFeatureName();
            $self->uid = $feature_value->getFeatureUid();

            self::$instances[$feature_value->getFeatureUid()] = $self;
        }

        self::$instances[$feature_value->getFeatureUid()]->variants[$feature_value->getVariantUid()] = $feature_value;

        return self::$instances[$feature_value->getFeatureUid()];
    }

    public static function clearInstances()
    {
        self::$instances = [];
    }
}