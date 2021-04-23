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


namespace Tygh\Addons\CommerceML\Convertors;


use Tygh\Addons\CommerceML\Dto\CategoryDto;
use Tygh\Addons\CommerceML\Dto\IdDto;
use Tygh\Addons\CommerceML\Dto\PropertyDto;
use Tygh\Addons\CommerceML\Dto\TranslatableValueDto;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Xml\SimpleXmlElement;

/**
 * Class CategoryConvertor
 *
 * @package Tygh\Addons\CommerceML\Convertors
 */
class CategoryConvertor
{
    /**
     * @var \Tygh\Addons\CommerceML\Convertors\ProductFeatureConvertor
     */
    private $product_feature_convertor;

    /**
     * CategoryConvertor constructor.
     *
     * @param \Tygh\Addons\CommerceML\Convertors\ProductFeatureConvertor $product_feature_convertor Product feature convertor instance
     */
    public function __construct(ProductFeatureConvertor $product_feature_convertor)
    {
        $this->product_feature_convertor = $product_feature_convertor;
    }

    /**
     * Convertes CommerceML element group to CategoryDTO
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement   $element        Xml element
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage instance
     */
    public function convert(SimpleXmlElement $element, ImportStorage $import_storage)
    {
        $this->convertCategory($element, $import_storage);
    }

    /**
     * Convertes recursively CommerceML element group to CategoryDTO
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement   $element         Xml element
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage  Import storage instance
     * @param \Tygh\Addons\CommerceML\Dto\CategoryDto|null   $parent_category Parent category DTO
     */
    private function convertCategory(SimpleXmlElement $element, ImportStorage $import_storage, CategoryDto $parent_category = null)
    {
        $entities = [];
        $category = new CategoryDto();

        $category->id = IdDto::createByExternalId($element->getAsString('id'));
        $category->name = TranslatableValueDto::create($element->getAsString('name'));

        if ($parent_category) {
            $category->full_name = sprintf('%s/%s', $parent_category->full_name, $category->name->default_value);
            $category->parent_id = $parent_category->getEntityId();
        } else {
            $category->full_name = $category->name->default_value;
        }

        if ($element->has('description')) {
            $category->properties->add(PropertyDto::create(
                'description',
                TranslatableValueDto::create($element->getAsString('description'))
            ));
        }

        /**
         * Executes after CommerceML element group converted to category DTO
         * Allows to modify or extend category DTO
         *
         * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement          $element         Xml element
         * @param \Tygh\Addons\CommerceML\Storages\ImportStorage        $import_storage  Import storage instance
         * @param \Tygh\Addons\CommerceML\Dto\CategoryDto|null          $parent_category Parent category DTO
         * @param \Tygh\Addons\CommerceML\Dto\CategoryDto               $category        Category DTO
         * @param array<\Tygh\Addons\CommerceML\Dto\RepresentEntityDto> $entities        Other entites data
         */
        fn_set_hook('commerceml_category_convertor_convert', $element, $import_storage, $parent_category, $category, $entities);

        array_unshift($entities, $category);

        $import_storage->saveEntities($entities);

        /**
         * @psalm-suppress PossiblyNullIterator
         */
        foreach ($element->get('properties/property', []) as $property) {
            $this->product_feature_convertor->convert($property, $import_storage);
        }

        foreach ($element->get('groups/group', []) as $group) {
            $this->convertCategory($group, $import_storage, $category);
        }
    }
}
