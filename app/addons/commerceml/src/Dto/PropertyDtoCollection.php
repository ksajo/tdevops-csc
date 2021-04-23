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
 * Class PropertyDtoCollection
 *
 * @package Tygh\Addons\CommerceML\Dto
 *
 * phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint
 */
class PropertyDtoCollection implements IteratorAggregate, Countable
{
    /**
     * @var array<\Tygh\Addons\CommerceML\Dto\PropertyDto>
     */
    private $collections = [];

    /**
     * Adds property to collection
     *
     * @param \Tygh\Addons\CommerceML\Dto\PropertyDto $property Propery instance
     */
    public function add(PropertyDto $property)
    {
        $this->collections[$property->property_id] = $property;
    }

    /**
     * Checks if collection has the property object
     *
     * @param string $property_id Property ID (short_name, variation_code, etc)
     *
     * @return bool
     */
    public function has($property_id)
    {
        $property_id = (string) $property_id;

        return isset($this->collections[$property_id]);
    }

    /**
     * Removes property object from collection
     *
     * @param string $property_id Property ID (short_name, variation_code, etc)
     */
    public function remove($property_id)
    {
        $property_id = (string) $property_id;

        unset($this->collections[$property_id]);
    }

    /**
     * Gets property object from collection
     *
     * @param string     $property_id   Property ID (short_name, variation_code, etc)
     * @param null|mixed $default_value If collection has not property, then method will return new PropertyDto
     *                                  where $default_value used as value on new object
     *
     * @return \Tygh\Addons\CommerceML\Dto\PropertyDto
     */
    public function get($property_id, $default_value = null)
    {
        $property_id = (string) $property_id;

        if (!$this->has($property_id)) {
            return PropertyDto::create($property_id, $default_value);
        }

        return $this->collections[$property_id];
    }

    /**
     * Gets values map
     *
     * @param array<string> $exclude_list Exclude list
     *
     * @return array<string, string|array<int|string, int|string>|mixed>
     */
    public function getValueMap(array $exclude_list = [])
    {
        $map = [];

        /** @var \Tygh\Addons\CommerceML\Dto\PropertyDto $property */
        foreach ($this->collections as $property) {
            if ($exclude_list && in_array($property->property_id, $exclude_list, true)) {
                continue;
            }

            $map[$property->property_id] = ($property->value instanceof ProductPropertyValue)
                ? $property->value->getPropertyValue()
                : (string) $property->value;
        }

        return $map;
    }

    /**
     * Gets translatable values map
     *
     * @param string        $lang_code    Language code (en, ru, etc)
     * @param array<string> $exclude_list Exclude list
     *
     * @return array<string, string>
     */
    public function getTranslatableValueMap($lang_code, array $exclude_list = [])
    {
        $map = [];

        /** @var \Tygh\Addons\CommerceML\Dto\PropertyDto $property */
        foreach ($this->collections as $property) {
            if (!$property->value instanceof TranslatableValueDto || !$property->value->hasTraslate($lang_code)) {
                continue;
            }
            if ($exclude_list && in_array($property->property_id, $exclude_list, true)) {
                continue;
            }

            $map[$property->property_id] = (string) $property->value->getTranslate($lang_code);
        }

        return $map;
    }

    /**
     * Merges current collection with $collection
     *
     * @param \Tygh\Addons\CommerceML\Dto\PropertyDtoCollection $collection Property collection instance
     */
    public function mergeWith(PropertyDtoCollection $collection)
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
