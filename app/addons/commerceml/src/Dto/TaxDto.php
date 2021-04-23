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
 * Class TaxDto
 *
 * @package Tygh\Addons\CommerceML\Dto
 */
class TaxDto implements RepresentEntityDto
{
    use RepresentEntitDtoTrait;

    const REPRESENT_ENTITY_TYPE = 'tax';

    /**
     * @var \Tygh\Addons\CommerceML\Dto\IdDto
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var \Tygh\Addons\CommerceML\Dto\PropertyDtoCollection
     */
    public $properties;

    /**
     * TaxDto constructor.
     */
    public function __construct()
    {
        $this->properties = new PropertyDtoCollection();
    }

    /**
     * Creates tax instance
     *
     * @param \Tygh\Addons\CommerceML\Dto\IdDto $id   Tax ID
     * @param string                            $name Tax name
     *
     * @return \Tygh\Addons\CommerceML\Dto\TaxDto
     */
    public static function create(IdDto $id, $name)
    {
        $self = new self();

        $self->id = $id;
        $self->name = (string) $name;

        return $self;
    }
}
