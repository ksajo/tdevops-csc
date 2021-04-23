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

/**
 * Class PropertyDto
 *
 * @package Tygh\Addons\CommerceML\Dto
 */
class PropertyDto
{
    /**
     * @var string Property ID (short_name, variation_code, etc)
     */
    public $property_id;

    /**
     * @var string|float|null|bool|\Tygh\Addons\CommerceML\Dto\ProductPropertyValue
     */
    public $value;

    /**
     * Creates property object
     *
     * @param string                                                                  $property_id Property ID (short_name, variation_code, etc)
     * @param string|float|null|bool|\Tygh\Addons\CommerceML\Dto\ProductPropertyValue $value       Property value
     *
     * @return \Tygh\Addons\CommerceML\Dto\PropertyDto
     */
    public static function create($property_id, $value)
    {
        $object = new self();
        $object->property_id = (string) $property_id;
        $object->value = $value;

        return $object;
    }
}
