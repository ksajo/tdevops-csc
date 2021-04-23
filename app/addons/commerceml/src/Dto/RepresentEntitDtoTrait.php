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
 * Trait RepresentEntitDtoTrait
 *
 * @property \Tygh\Addons\CommerceML\Dto\IdDto $id
 *
 * @package Tygh\Addons\CommerceML\Dto
 */
trait RepresentEntitDtoTrait
{
    /**
     * Gets type of entity (product, product_feature, category, etc)
     *
     * @return string
     */
    public function getEntityType()
    {
        return static::REPRESENT_ENTITY_TYPE;
    }

    /**
     * Gets entity ID
     *
     * @return \Tygh\Addons\CommerceML\Dto\IdDto
     */
    public function getEntityId()
    {
        return $this->id;
    }

    /**
     * Gets entity name
     *
     * @return string
     */
    public function getEntityName()
    {
        $name = isset($this->name) ? $this->name : '';
        $name = isset($this->full_name) ? $this->full_name : $name;

        if ($name instanceof TranslatableValueDto) {
            return $name->default_value;
        }

        return (string) $name;
    }
}
