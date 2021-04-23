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

use Tygh\Addons\CommerceML\Dto\ImageDto;
use Tygh\Addons\CommerceML\Dto\PriceValueDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto;
use Tygh\Addons\CommerceML\Dto\PropertyDto;
use Tygh\Addons\CommerceML\Dto\TaxDto;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Xml\SimpleXmlElement;
use Tygh\Addons\CommerceML\Dto\ProductDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto;
use Tygh\Addons\CommerceML\Dto\TranslatableValueDto;
use Tygh\Addons\CommerceML\Dto\IdDto;
use Tygh\Enum\YesNo;

/**
 * Class ProductConvertor
 *
 * @package Tygh\Addons\CommerceML\Convertors
 * phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint
 */
class ProductConvertor
{
    /**
     * @var \Tygh\Addons\CommerceML\Dto\TranslatableValueDto
     */
    private $default_variation_product_feature_name;

    /**
     * @var \Tygh\Addons\CommerceML\Dto\TranslatableValueDto
     */
    private $default_brand_product_feature_name;

    /**
     * @var string $brand_external_id
     */
    private $brand_external_id;

    /**
     * ProductConvertor constructor.
     *
     * @param \Tygh\Addons\CommerceML\Dto\TranslatableValueDto $default_variation_product_feature_name Default feature name for product variations
     * @param \Tygh\Addons\CommerceML\Dto\TranslatableValueDto $default_brand_product_feature_name     Default product brand feature name
     * @param string                                           $brand_external_id                      Product brand feature external identifier
     */
    public function __construct(
        TranslatableValueDto $default_variation_product_feature_name,
        TranslatableValueDto $default_brand_product_feature_name,
        $brand_external_id
    ) {
        $this->default_variation_product_feature_name = $default_variation_product_feature_name;
        $this->default_brand_product_feature_name = $default_brand_product_feature_name;
        $this->brand_external_id = $brand_external_id;
    }

    /**
     * Convertes CommerceML element product to ProductDTO
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement   $element              Xml element
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage       Import storage instance
     * @param bool                                           $is_product_creatable Flag if product is creatable, or not
     */
    public function convert(SimpleXmlElement $element, ImportStorage $import_storage, $is_product_creatable = true)
    {
        $entities = [];
        $product = new ProductDto();

        $product->is_creatable = $is_product_creatable;

        $product->id = IdDto::createByExternalId($element->getAsString('id'));

        if ($element->has('@status') && $element->getAsEnumItem('@status', ['delete', 'new']) === 'delete') {
            $product->is_removed = true;
        }

        if ($element->has('amount')) {
            $product->quantity = $element->getAsInt('amount');
        }

        if ($element->has('warehouse')) {
            $product->quantity = 0;

            /**
             * @psalm-suppress PossiblyNullIterator
             */
            foreach ($element->get('warehouse', []) as $item) {
                if (!$item->has('@warehouse_in_stock')) {
                    continue;
                }

                $product->quantity += $item->getAsInt('@warehouse_in_stock');
            }

            if ($product->quantity < 0 && $import_storage->getSetting('allow_negative_amount', false) === false) {
                $product->quantity = 0;
            }
        }

        foreach ($element->getAsStringList('image') as $image_path) {
            $product->images[] = ImageDto::create(ltrim($image_path, '/'));
        }

        /**
         * @psalm-suppress PossiblyNullIterator
         */
        foreach ($element->get('groups', []) as $item) {
            if (!$item->has('id')) {
                continue;
            }

            $product->categories[] = IdDto::createByExternalId($item->getAsString('id'));
        }

        /**
         * @psalm-suppress PossiblyNullIterator
         */
        foreach ($element->get('prices/price', []) as $item) {
            if (!$item->has('price_id') || !$item->has('price_per_item')) {
                continue;
            }

            $product->prices[] = PriceValueDto::create(
                IdDto::createByExternalId($item->getAsString('price_id')),
                $item->getAsFloat('price_per_item'),
                $item->has('currency') ? IdDto::createByExternalId($item->getAsString('currency')) : null
            );
        }

        $this->convertProductName($element, $product, $import_storage);
        $this->convertProductCode($element, $product, $import_storage);
        $this->convertProductDescription($element, $product, $import_storage);
        $this->convertManufacturerToBrandFeatureVariant($element, $product, $import_storage);
        $this->convertPropertiesValues($element, $product, $import_storage);
        $this->convertProductFieldsToProductProperties($element, $product, $import_storage);

        /**
         * @psalm-suppress PossiblyNullIterator
         */
        foreach ($element->get('taxes_rates/tax_rate', []) as $item) {
            if (!$item->has('rate_t')) {
                continue;
            }

            $tax_dto = TaxDto::create(
                IdDto::createByExternalId(md5($item->getAsString('name') . $item->getAsString('rate_t'))),
                sprintf('%s (%s)', $item->getAsString('name'), $item->getAsString('rate_t'))
            );
            $entities[] = $tax_dto;

            $product->taxes[] = $tax_dto->getEntityId();
        }

        list($products, $variation_entities) = $this->convertVariationAttributes($element, $product, $import_storage);

        $entities = array_merge($variation_entities, $entities);

        foreach ($products as $product) {
            /**
             * Executes after CommerceML element product converted to product DTO
             * Allows to modify or extend product DTO
             *
             * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement          $element        Xml element
             * @param \Tygh\Addons\CommerceML\Storages\ImportStorage        $import_storage Import storage instance
             * @param \Tygh\Addons\CommerceML\Dto\ProductDto                $product        Product Dto
             * @param array<\Tygh\Addons\CommerceML\Dto\RepresentEntityDto> $entities       Other entites data
             */
            fn_set_hook('commerceml_product_convertor_convert', $element, $import_storage, $product, $entities);
        }

        $import_storage->saveEntities(array_merge($products, $entities));
    }

    /**
     * Converts attributes related to product variation
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement   $element        Xml element
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     *
     * @return array{0:array<ProductDto>, 1:array<ProductFeatureDto|ProductFeatureVariantDto>}
     */
    private function convertVariationAttributes(SimpleXmlElement $element, ProductDto $product, ImportStorage $import_storage)
    {
        if (!$this->isProductVariation($element)) {
            return [[$product], []];
        }

        $products = [];
        $product_features = [];
        $product_features_variants = [];
        $has_combination_id = $this->hasCombinationId($element);

        $product->parent_id = IdDto::createByExternalId($this->getParentProductId($element));
        $product->is_variation = true;

        /**
         * @psalm-suppress PossiblyNullIterator
         */
        foreach ($element->get('product_features/product_feature', []) as $item) {
            if (!$has_combination_id && $item->has('id')) {
                $product_variant = clone $product;

                $product_variant->id = IdDto::createByExternalId(
                    sprintf('%s#%s', $product_variant->parent_id->external_id, $item->getAsString('id'))
                );

                $feature_external_id = md5($this->default_variation_product_feature_name->default_value);
                $feature_variant_external_id_dto = ProductFeatureConvertor::getVariantExternalId(
                    $feature_external_id,
                    null,
                    $item->getAsString('value')
                );

                $product_variant->variation_feature_values->add(ProductFeatureValueDto::create(
                    IdDto::createByExternalId($feature_external_id),
                    $item->getAsString('value'),
                    $feature_variant_external_id_dto
                ));

                if (isset($product_features[$feature_external_id])) {
                    $product_feature = $product_features[$feature_external_id];
                } else {
                    $product_feature = new ProductFeatureDto();
                    $product_feature->type = ProductFeatureDto::TYPE_DIRECTORY;
                    $product_feature->id = IdDto::createByExternalId($feature_external_id);
                    $product_feature->name = clone $this->default_variation_product_feature_name;
                }

                $variant_dto = ProductFeatureVariantDto::create(
                    $feature_variant_external_id_dto,
                    TranslatableValueDto::create($item->getAsString('value'))
                );
                $product_feature->variants[] = $variant_dto;
                $product_features_variants[] = $variant_dto;

                $product_features[$feature_external_id] = $product_feature;
                $products[] = $product_variant;
            } else {
                if ($item->has('id')) {
                    $feature_external_id = $item->getAsString('id');
                } else {
                    $feature_external_id = md5($item->getAsString('name'));
                }

                if ($product->product_feature_values->has($feature_external_id)) {
                    $feature_variant_external_id_dto = $product->product_feature_values->get($feature_external_id)->value;

                    if (!$feature_variant_external_id_dto instanceof IdDto) {
                        $feature_variant_external_id_dto = ProductFeatureConvertor::getVariantExternalId(
                            $feature_external_id,
                            $feature_variant_external_id_dto,
                            $item->getAsString('value')
                        );
                    }
                } else {
                    $feature_variant_external_id_dto = ProductFeatureConvertor::getVariantExternalId(
                        $feature_external_id,
                        null,
                        $item->getAsString('value')
                    );
                }

                $product->variation_feature_values->add(ProductFeatureValueDto::create(
                    IdDto::createByExternalId($feature_external_id),
                    $item->getAsString('value'),
                    $feature_variant_external_id_dto
                ));

                if (isset($product_features[$feature_external_id])) {
                    $product_feature = $product_features[$feature_external_id];
                } else {
                    $product_feature = new ProductFeatureDto();
                    $product_feature->type = ProductFeatureDto::TYPE_DIRECTORY;
                    $product_feature->id = IdDto::createByExternalId($feature_external_id);
                    $product_feature->name = TranslatableValueDto::create($item->getAsString('name'));
                }

                $variant_dto = ProductFeatureVariantDto::create(
                    $feature_variant_external_id_dto,
                    TranslatableValueDto::create($item->getAsString('value'))
                );

                $product_feature->variants[] = $variant_dto;
                $product_features_variants[] = $variant_dto;

                $product_features[$feature_external_id] = $product_feature;
            }
        }

        if (empty($products)) {
            $products[] = $product;
        }

        foreach ($product_features as $external_id => $item) {
            $exists_variant_ids = [];
            $product_feature = $import_storage->findEntity(ProductFeatureDto::REPRESENT_ENTITY_TYPE, $external_id);

            if (!$product_feature || !$product_feature instanceof ProductFeatureDto) {
                continue;
            }

            foreach ($product_feature->variants as $variant_dto) {
                $exists_variant_ids[$variant_dto->id->getId()] = $variant_dto;
            }

            foreach ($item->variants as $variant_dto) {
                if (isset($exists_variant_ids[$variant_dto->id->getId()])) {
                    continue;
                }

                $product_feature->variants[] = $variant_dto;
                $product_features_variants[] = $variant_dto;
            }

            $product_features[$external_id] = $product_feature;
        }

        return [$products, array_merge(array_values($product_features), $product_features_variants)];
    }

    /**
     * Converts product properties values
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement   $element        Xml element
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     */
    private function convertPropertiesValues(SimpleXmlElement $element, ProductDto $product, ImportStorage $import_storage)
    {
        /**
         * @psalm-suppress PossiblyNullIterator
         */
        foreach ($element->get('properties_values/property_values', []) as $item) {
            if (!$item->has('id') || !$item->has('value')) {
                continue;
            }

            $product_feature_value = $item->getAsString('value');
            $product_feature_external_id = IdDto::createByExternalId($item->getAsString('id'));
            $product_feature_value = ProductFeatureValueDto::create(
                $product_feature_external_id,
                $product_feature_value,
                ProductFeatureConvertor::getVariantExternalId(
                    $item->getAsString('id'),
                    $product_feature_value,
                    $product_feature_value
                )
            );

            $this->tryConvertPropertyValueToLocalProperty($item, $product, $product_feature_value, $import_storage);

            if (!$this->isProductPropertyAllowToImport($item, $import_storage)) {
                continue;
            }

            $product->product_feature_values->add($product_feature_value);
        }
    }

    /**
     * Converts product name
     *
     * @param SimpleXmlElement $element        Xml element
     * @param ProductDto       $product        Product DTO
     * @param ImportStorage    $import_storage Import storage
     */
    private function convertProductName(SimpleXmlElement $element, ProductDto $product, ImportStorage $import_storage)
    {
        $product_name_source = (string) $import_storage->getSetting('catalog_convertor.product_name_source', 'name');

        if ($element->hasAndNotEmpty($product_name_source)) {
            $product->name = TranslatableValueDto::create(
                $element->getAsString((string) $import_storage->getSetting('catalog_convertor.product_name_source', 'name'))
            );

            return;
        }

        $product_name = $this->findFieldValue($element, $product_name_source);

        if (empty($product_name)) {
            return;
        }

        $product->name = TranslatableValueDto::create($product_name);
    }

    /**
     * Converts product code
     *
     * @param SimpleXmlElement $element        Xml element
     * @param ProductDto       $product        Product DTO
     * @param ImportStorage    $import_storage Import storage
     */
    private function convertProductCode(SimpleXmlElement $element, ProductDto $product, ImportStorage $import_storage)
    {
        $product_code_source = (string) $import_storage->getSetting('catalog_convertor.product_code_source', 'article');

        if ($element->hasAndNotEmpty($product_code_source)) {
            $product->product_code = $element->getAsString($product_code_source);

            return;
        }

        $product_code = $this->findFieldValue($element, $product_code_source);

        if (empty($product_code)) {
            return;
        }

        $product->product_code = $product_code;
    }

    /**
     * Converts product description
     *
     * @param SimpleXmlElement $element        Xml element
     * @param ProductDto       $product        Product DTO
     * @param ImportStorage    $import_storage Import storage
     */
    private function convertProductDescription(SimpleXmlElement $element, ProductDto $product, ImportStorage $import_storage)
    {
        $description_source = (string) $import_storage->getSetting('catalog_convertor.full_description_source', 'description');

        if ($element->hasAndNotEmpty($description_source)) {
            if ($description_source === 'description') {
                $description = nl2br($element->getAsString($description_source));
            } else {
                $description = $element->getAsString($description_source);
            }
            $product->description = TranslatableValueDto::create($description);

            return;
        }

        $description = $this->findFieldValue($element, $description_source);

        if (empty($description)) {
            return;
        }

        $product->description = TranslatableValueDto::create($description);
    }

    /**
     * Convert product fields to product properties
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement   $element        Xml element
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     *
     * @phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
     */
    private function convertProductFieldsToProductProperties(SimpleXmlElement $element, ProductDto $product, ImportStorage $import_storage)
    {
        $short_description_source = (string) $import_storage->getSetting('catalog_convertor.short_description_source', 'none');

        if ($short_description_source !== 'none') {
            $value = null;

            if ($element->hasAndNotEmpty($short_description_source)) {
                $value = $element->getAsString($short_description_source);
            } else {
                $value = $this->findFieldValue($element, $short_description_source);
            }

            if (!empty($value)) {
                $product->properties->add(PropertyDto::create(
                    'short_description',
                    TranslatableValueDto::create($value)
                ));
            }
        }

        $page_title_source = (string) $import_storage->getSetting('catalog_convertor.page_title_source', 'none');

        if ($page_title_source !== 'none') {
            $value = null;

            if ($element->hasAndNotEmpty($page_title_source)) {
                $value = TranslatableValueDto::create($element->getAsString($page_title_source));
            } else {
                $value = $this->findFieldValue($element, $page_title_source);
            }

            if (!empty($value)) {
                $product->properties->add(PropertyDto::create(
                    'page_title',
                    $value
                ));
            }
        }


    }

    /**
     * Checks if offer|product is product variation
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement $element Xml element
     *
     * @return bool
     */
    private function isProductVariation(SimpleXmlElement $element)
    {
        if ($this->hasCombinationId($element)) {
            return true;
        }

        /**
         * @psalm-suppress PossiblyNullIterator
         */
        foreach ($element->get('product_features/product_feature', []) as $item) {
            if ($item->has('id')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets parent product entity ID
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement $element Xml element
     *
     * @return string
     */
    private function getParentProductId(SimpleXmlElement $element)
    {
        /**
         * @psalm-suppress PossiblyNullArgument
         */
        $id_parts = explode('#', $element->getAsString('id'));

        return reset($id_parts);
    }

    /**
     * Checks if offer|product ID has combination ID
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement $element Xml element
     *
     * @return bool
     */
    private function hasCombinationId(SimpleXmlElement $element)
    {
        /**
         * @psalm-suppress PossiblyNullArgument
         */
        $id_parts = explode('#', $element->getAsString('id'));

        if (!empty($id_parts[1])) {
            return true;
        }

        return false;
    }

    /**
     * Gets list of the local product properties
     *
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     *
     * @return array<string, array{properties:array<mixed>, type: string}>
     */
    private function getLocalProductProperies(ImportStorage $import_storage)
    {
        $list = [
            'promo_text' => [
                'properties' => (array) $import_storage->getSetting('catalog_convertor.promo_text_property_source'),
                'type'       => 'string',
            ],
            'weight' => [
                'properties' => (array) $import_storage->getSetting('catalog_convertor.weight_property_source_list', []),
                'type'       => 'float'
            ],
            'free_shipping' => [
                'properties' => (array) $import_storage->getSetting('catalog_convertor.free_shipping_property_source_list', []),
                'type'       => 'yesno'
            ],
            'shipping_freight' => [
                'properties' => (array) $import_storage->getSetting('catalog_convertor.shipping_cost_property_source_list', []),
                'type'       => 'float'
            ],
            'min_items_in_box' => [
                'properties' => (array) $import_storage->getSetting('catalog_convertor.number_of_items_property_source_list', []),
                'type'       => 'int',
            ],
            'max_items_in_box' => [
                'properties' => (array) $import_storage->getSetting('catalog_convertor.number_of_items_property_source_list', []),
                'type'       => 'int',
            ],
            'box_length' => [
                'properties' => (array) $import_storage->getSetting('catalog_convertor.box_length_property_source_list', []),
                'type'       => 'float',
            ],
            'box_width' => [
                'properties' => (array) $import_storage->getSetting('catalog_convertor.box_width_property_source_list', []),
                'type'       => 'float',
            ],
            'box_height' => [
                'properties' => (array) $import_storage->getSetting('catalog_convertor.box_height_property_source_list', []),
                'type'       => 'float',
            ]
        ];

        $list = array_filter($list, static function (array $item) {
            return !empty($item['properties']);
        });

        return $list;
    }

    /**
     * Converts product properties from 1c to local product properties
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement       $element               Xml element
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto             $product               Product dto instance
     * @param \Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto $product_feature_value Product feature value dto instance
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage     $import_storage        Import storage
     *
     * @return bool
     */
    private function tryConvertPropertyValueToLocalProperty(
        SimpleXmlElement $element,
        ProductDto $product,
        ProductFeatureValueDto $product_feature_value,
        ImportStorage $import_storage
    ) {
        $local_properties_data = $this->getLocalProductProperies($import_storage);

        if (!$local_properties_data) {
            return false;
        }

        $property_name = $this->findProductPropertyName($element, $import_storage);

        if (!$property_name) {
            return false;
        }

        $property_name = mb_strtolower(trim($property_name));
        $matched_properties = [];

        foreach ($local_properties_data as $property_id => $property_data) {
            foreach ($property_data['properties'] as $name) {
                $name = mb_strtolower(trim($name));

                if ($property_name !== $name) {
                    continue;
                }

                $matched_properties[$property_id] = $property_data;
            }
        }

        if (empty($matched_properties)) {
            return false;
        }

        if ($product_feature_value->value_id) {
            /** @var ProductFeatureVariantDto $variant */
            $variant = $import_storage->findEntity(ProductFeatureVariantDto::REPRESENT_ENTITY_TYPE, $product_feature_value->value_id->getId());
        } else {
            $variant = null;
        }

        foreach ($matched_properties as $property_id => $property_data) {
            if ($variant && $variant->name) {
                $value = $variant->name->default_value;
            } else {
                $value = (string) $product_feature_value->value;
            }

            if (!$value) {
                continue;
            }

            if ($property_data['type'] === 'float') {
                $value = (float) str_replace(',', '.', $value);
            } elseif ($property_data['type'] === 'yesno') {
                $value = YesNo::toId(SimpleXmlElement::normalizeBool($value));
            } elseif ($property_data['type'] === 'int') {
                $value = (int) $value;
            }

            $product->properties->add(PropertyDto::create(
                $property_id,
                $value
            ));
        }

        return true;
    }

    /**
     * Converts product manufacturer to product feature brand variant
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement   $element        Xml element
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product dto instance
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     *
     * @return bool
     */
    private function convertManufacturerToBrandFeatureVariant(
        SimpleXmlElement $element,
        ProductDto $product,
        ImportStorage $import_storage
    ) {
        if (!$element->has('manufacturer')) {
            return false;
        }

        $manufacturer = ProductFeatureVariantDto::create(
            IdDto::createByExternalId($this->brand_external_id . '#' . md5($element->getAsString('manufacturer/name'))),
            TranslatableValueDto::create($element->getAsString('manufacturer/name'))
        );

        if (!$import_storage->findEntity(ProductFeatureVariantDto::REPRESENT_ENTITY_TYPE, $manufacturer->getEntityId()->getId())) {
            /** @var ProductFeatureDto $product_brand_feature */
            $product_brand_feature = $import_storage->findEntity(ProductFeatureDto::REPRESENT_ENTITY_TYPE, $this->brand_external_id);

            if (!$product_brand_feature) {
                $product_brand_feature = new ProductFeatureDto();
                $product_brand_feature->type = ProductFeatureDto::TYPE_EXTENDED;
                $product_brand_feature->id = IdDto::createByExternalId($this->brand_external_id);
                $product_brand_feature->name = $this->default_brand_product_feature_name;
            }

            $product_brand_feature->variants[] = $manufacturer;

            $import_storage->saveEntities([$product_brand_feature, $manufacturer]);
        }

        $product->product_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId($this->brand_external_id),
            $element->getAsString('manufacturer/name'),
            $manufacturer->getEntityId()
        ));

        return true;
    }

    /**
     * Checks if property is allowed to import
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement   $element        Xml element
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     *
     * @return bool
     */
    private function isProductPropertyAllowToImport(SimpleXmlElement $element, ImportStorage $import_storage)
    {
        $allow_list = $import_storage->getSetting('catalog_convertor.property_allowlist', []);
        $block_list = $import_storage->getSetting('catalog_convertor.property_blocklist', []);

        if (empty($block_list) && empty($allow_list)) {
            return true;
        }

        $name = $this->findProductPropertyName($element, $import_storage);

        if (!$name) {
            return false;
        }

        $propert_name = mb_strtolower(trim($name));

        foreach ($block_list as $item) {
            if ($propert_name === mb_strtolower(trim($item))) {
                return false;
            }
        }

        foreach ($allow_list as $item) {
            if ($propert_name === mb_strtolower(trim($item))) {
                return true;
            }
        }

        return empty($allow_list);
    }

    /**
     * Finds product property name
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement   $element        Xml element
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     *
     * @return string|null
     */
    private function findProductPropertyName(SimpleXmlElement $element, ImportStorage $import_storage)
    {
        if ($element->has('name')) {
            return (string) $element->getAsString('name');
        }

        $product_feature = $import_storage->findEntity(ProductFeatureDto::REPRESENT_ENTITY_TYPE, (string) $element->getAsString('id'));

        if (!$product_feature) {
            return null;
        }

        return $product_feature->getEntityName();
    }

    /**
     * Find field value by field name
     *
     * @param SimpleXmlElement $element    Xml element
     * @param string           $field_name Field name
     *
     * @return string|false
     */
    private function findFieldValue(SimpleXmlElement $element, $field_name)
    {
        /**
         * @psalm-suppress PossiblyNullIterator
         */
        foreach ($element->get('value_fields/value_field', []) as $item) {
            if (trim($item->getAsString('name')) === trim(SimpleXmlElement::findAlias($field_name))) {
                return (string) $item->getAsString('value');
            }
        }

        return false;
    }
}
