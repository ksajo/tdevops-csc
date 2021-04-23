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


class ProductFeatureVariantDto implements RepresentEntityDto, RepresentSubEntityDto
{
    use RepresentEntitDtoTrait;

    const REPRESENT_ENTITY_TYPE = 'product_feature_variant';

    /**
     * @var \Tygh\Addons\CommerceML\Dto\IdDto
     */
    public $id;

    /**
     * @var \Tygh\Addons\CommerceML\Dto\TranslatableValueDto|null
     */
    public $name;

    /**
     * @var \Tygh\Addons\CommerceML\Dto\PropertyDtoCollection
     */
    public $properties;

    /**
     * ProductFeatureVariantDto constructor.
     */
    public function __construct()
    {
        $this->properties = new PropertyDtoCollection();
    }

    /**
     * @param \Tygh\Addons\CommerceML\Dto\IdDto|null                $id   Vairant ID
     * @param \Tygh\Addons\CommerceML\Dto\TranslatableValueDto|null $name Variant name
     *
     * @return \Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto
     */
    public static function create(IdDto $id = null, TranslatableValueDto $name = null)
    {
        $object = new self();

        if ($id) {
            $object->id = $id;
        } elseif ($name) {
            $object->id = IdDto::createByExternalId(md5($name->default_value));
        }

        $object->name = $name;

        return $object;
    }

    /**
     * @inheritDoc
     */
    public static function getParentEntityType()
    {
        return ProductFeatureDto::REPRESENT_ENTITY_TYPE;
    }

    /**
     * @inheritDoc
     */
    public function getParentExternalId()
    {
        $id_parts = explode('#', $this->getEntityId()->external_id);
        return (string) reset($id_parts);
    }
}
