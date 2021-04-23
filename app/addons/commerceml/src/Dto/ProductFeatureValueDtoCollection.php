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


namespace Tygh\Addons\CommerceML\Dto;


use ArrayIterator;
use IteratorAggregate;
use Countable;

/**
 * Class ProductFeatureValueDtoCollection
 *
 * @package Tygh\Addons\CommerceML\Dto
 *
 * phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint
 */
class ProductFeatureValueDtoCollection implements IteratorAggregate, Countable
{
    /**
     * @var array<\Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto>
     */
    private $collections = [];

    /**
     * Adds product feature value to collection
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto $feature_value Product feature value instance
     */
    public function add(ProductFeatureValueDto $feature_value)
    {
        $this->collections[$feature_value->feature_id->getId()] = $feature_value;
    }

    /**
     * Checks if collection has the product feature value object
     *
     * @param string $feature_id External or local feature ID
     *
     * @return bool
     */
    public function has($feature_id)
    {
        $feature_id = (string) $feature_id;

        return isset($this->collections[$feature_id]);
    }

    /**
     * Removes product feature value object from collection
     *
     * @param string $feature_id External or local feature ID
     */
    public function remove($feature_id)
    {
        $feature_id = (string) $feature_id;

        unset($this->collections[$feature_id]);
    }

    /**
     * Gets product feature value object from collection
     *
     * @param string     $feature_id    External or local feature ID
     * @param null|mixed $default_value If collection has not property, then method will return new PropertyDto
     *                                  where $default_value used as value on new object
     *
     * @return \Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto
     */
    public function get($feature_id, $default_value = null)
    {
        $feature_id = (string) $feature_id;

        if (!$this->has($feature_id)) {
            return ProductFeatureValueDto::create(IdDto::createByExternalId($feature_id), $default_value);
        }

        return $this->collections[$feature_id];
    }

    /**
     * Gets all product features
     *
     * @return array<\Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto>
     */
    public function getAll()
    {
        return $this->collections;
    }

    /**
     * Merges current collection with $collection
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductFeatureValueDtoCollection $collection Product feature collection instance
     */
    public function mergeWith(ProductFeatureValueDtoCollection $collection)
    {
        foreach ($collection as $item) {
            $this->add($item);
        }
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->collections);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->collections);
    }
}
