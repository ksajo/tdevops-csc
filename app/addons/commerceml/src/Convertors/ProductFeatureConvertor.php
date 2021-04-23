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


use Tygh\Addons\CommerceML\Dto\IdDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto;
use Tygh\Addons\CommerceML\Dto\PropertyDto;
use Tygh\Addons\CommerceML\Dto\TranslatableValueDto;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Xml\SimpleXmlElement;

/**
 * Class ProductFeatureConvertor
 *
 * @package Tygh\Addons\CommerceML\Convertors
 */
class ProductFeatureConvertor
{
    /**
     * Converts CommerceML element property to product feature DTO
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement   $element        Xml element
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage instance
     */
    public function convert(SimpleXmlElement $element, ImportStorage $import_storage)
    {
        $entities = [];
        $product_feature = new ProductFeatureDto();

        $product_feature->id = IdDto::createByExternalId($element->getAsString('id'));
        $product_feature->name = TranslatableValueDto::create($element->getAsString('name', ''));
        $product_feature->is_multiple = $element->getAsBool('multiple', false);
        $product_feature->type = $element->getAsEnumItem(
            'type_field',
            [
                ProductFeatureDto::TYPE_STRING,
                ProductFeatureDto::TYPE_NUMBER,
                ProductFeatureDto::TYPE_DATE_TIME,
                ProductFeatureDto::TYPE_DIRECTORY,
            ],
            ProductFeatureDto::TYPE_STRING
        );

        if ($element->has('description')) {
            $product_feature->properties->add(PropertyDto::create(
                'description',
                TranslatableValueDto::create($element->getAsString('description'))
            ));
        }

        /**
         * @psalm-suppress PossiblyNullIterator
         */
        foreach ($element->get('variants_values/directory', []) as $variant) {
            $object = ProductFeatureVariantDto::create(
                self::getVariantExternalId(
                    $product_feature->id->external_id,
                    $variant->getAsString('id_value'),
                    $variant->getAsString('value')
                ),
                TranslatableValueDto::create($variant->getAsString('value'))
            );

            $product_feature->variants[] = $object;
            $entities[] = $object;
        }

        /**
         * Executes after CommerceML element property converted to product feature DTO
         * Allows to modify or extend product feature DTO
         *
         * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement          $element         Xml element
         * @param \Tygh\Addons\CommerceML\Storages\ImportStorage        $import_storage  Import storage instance
         * @param \Tygh\Addons\CommerceML\Dto\ProductFeatureDto         $product_feature Product feature DTO
         * @param array<\Tygh\Addons\CommerceML\Dto\RepresentEntityDto> $entities        Other entites data
         */
        fn_set_hook('commerceml_product_feature_convertor_convert', $element, $import_storage, $product_feature, $entities);

        array_unshift($entities, $product_feature);

        $import_storage->saveEntities($entities);
    }

    /**
     * Gets product feature variant external ID
     *
     * @param string      $feature_id   Product feature external ID
     * @param string|null $variant_id   Product feature variant external ID
     * @param string|null $variant_name Product feature variant name
     *
     * @return \Tygh\Addons\CommerceML\Dto\IdDto
     */
    public static function getVariantExternalId($feature_id, $variant_id, $variant_name = null)
    {
        if (empty($variant_id)) {
            $variant_id = md5((string) $variant_name);
        }

        return IdDto::createByExternalId(sprintf('%s#%s', $feature_id, $variant_id));
    }
}
