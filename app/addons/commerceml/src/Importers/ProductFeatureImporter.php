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


namespace Tygh\Addons\CommerceML\Importers;


use Tygh\Addons\CommerceML\Dto\LocalIdDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Storages\ProductStorage;
use Tygh\Common\OperationResult;
use Tygh\Enum\ProductFeatures;

/**
 * Class ProductFeatureImporter
 *
 * @package Tygh\Addons\CommerceML\Importers
 */
class ProductFeatureImporter
{
    /**
     * @var \Tygh\Addons\CommerceML\Storages\ProductStorage
     */
    private $product_storage;

    /**
     * ProductFeatureImporter constructor.
     *
     * @param \Tygh\Addons\CommerceML\Storages\ProductStorage $product_storage Product storage instance
     */
    public function __construct(ProductStorage $product_storage)
    {
        $this->product_storage = $product_storage;
    }

    /**
     * Imports product feature
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductFeatureDto  $product_feature Product feature DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage  Import storage instance
     *
     * @return \Tygh\Common\OperationResult
     */
    public function import(ProductFeatureDto $product_feature, ImportStorage $import_storage)
    {
        $main_result = new OperationResult(true);

        $product_feature_id = $import_storage->findEntityLocalId(ProductFeatureDto::REPRESENT_ENTITY_TYPE, $product_feature->id);

        if ($product_feature_id->hasNotValue()) {
            $result = $this->importProductFeature($product_feature, $import_storage);

            $main_result->merge($result);

            if ($result->isFailure()) {
                $main_result->setSuccess(false);
                return $main_result;
            }

            $main_result->setData($product_feature->id->local_id);
            $main_result->addMessage('product_feature.created', __('commerceml.import.message.product_feature.created', [
                '[id]'       => $product_feature->id->getId(),
                '[local_id]' => $product_feature->id->local_id
            ]));

            $this->importProductFeatureTranslations($product_feature, $import_storage);
        } else {
            $product_feature->id->local_id = $product_feature_id->asInt();
        }

        $this->importProductFeatureVariants($main_result, $product_feature, $import_storage);

        $import_storage->removeEntity($product_feature);
        $import_storage->mapEntityId($product_feature);

        return $main_result;
    }

    /**
     * Import product feature by product feature value
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto $product_feature_value Product feature value DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage     $import_storage        Import storage
     *
     * @return \Tygh\Common\OperationResult
     */
    public function importByFeatureValue(ProductFeatureValueDto $product_feature_value, ImportStorage $import_storage)
    {
        $main_result = new OperationResult(true);
        $allow_manage_features = $import_storage->getSetting('allow_manage_features', true);
        $allow_import_features = $import_storage->getSetting('catalog_importer.allow_import_features', true);

        $product_feature_id = $import_storage->findEntityLocalId(
            ProductFeatureDto::REPRESENT_ENTITY_TYPE,
            $product_feature_value->feature_id,
            $import_storage->getSetting('mapping.feature.default_variant', LocalIdDto::VALUE_CREATE)
        );

        if ($product_feature_id->hasValue()) {
            $product_feature_value->feature_id->local_id = $product_feature_id->asInt();

            if ($allow_manage_features) {
                $product_feature = $import_storage->findEntity(
                    ProductFeatureDto::REPRESENT_ENTITY_TYPE,
                    $product_feature_value->feature_id->getId()
                );

                if ($product_feature && $product_feature instanceof ProductFeatureDto) {
                    /** @var \Tygh\Addons\CommerceML\Dto\ProductFeatureDto $product_feature */
                    $product_feature->getEntityId()->local_id = $product_feature_value->feature_id->local_id;

                    $this->importProductFeatureVariants($main_result, $product_feature, $import_storage);
                }
            }
        } else {
            if ($product_feature_id->isSkipValue()) {
                $main_result->addError('product_feature.skipped', __('commerceml.import.message.product_feature.skipped', [
                    '[id]' => $product_feature_value->feature_id->getId(),
                ]));
                return $main_result;
            }

            if ($allow_manage_features && $product_feature_id->isCreateValue()) {
                $product_feature = $import_storage->findEntity(
                    ProductFeatureDto::REPRESENT_ENTITY_TYPE,
                    $product_feature_value->feature_id->getId()
                );

                if (!$product_feature || !$product_feature instanceof ProductFeatureDto) {
                    $main_result->setSuccess(false);
                    $main_result->addError('product_feature.not_found', __('commerceml.import.error.product_feature.not_found', [
                        '[id]' => $product_feature_value->feature_id->getId(),
                    ]));

                    return $main_result;
                }

                $result = $this->import($product_feature, $import_storage);

                $main_result->merge($result);

                if ($result->isFailure()) {
                    $main_result->setSuccess(false);
                    return $main_result;
                }

                $product_feature_value->feature_id->local_id = $product_feature->id->local_id;
            } elseif (!$allow_import_features) {
                $main_result->addMessage('product_feature.skipped_by_settings', __('commerceml.import.message.product_feature.skipped_by_settings', [
                    '[id]' => $product_feature_value->feature_id->getId(),
                ]));
                return $main_result;
            }
        }

        if ($product_feature_value->value_id !== null) {
            $variant_id = $import_storage->findEntityLocalId(
                ProductFeatureVariantDto::REPRESENT_ENTITY_TYPE,
                $product_feature_value->value_id
            );

            if ($variant_id->hasValue()) {
                $product_feature_value->value_id->local_id = $variant_id->asInt();
            } else {
                $product_feature_value->value_id = null;
            }
        }

        return $main_result;
    }

    /**
     * Imports product feature data
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductFeatureDto  $product_feature Product feature DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage  Import storage
     *
     * @return \Tygh\Common\OperationResult
     */
    private function importProductFeature(ProductFeatureDto $product_feature, ImportStorage $import_storage)
    {
        $feature_data = array_merge($product_feature->properties->getValueMap(), [
            'company_id'   => $import_storage->getImport()->company_id,
            'description'  => $product_feature->name ? (string) $product_feature->name->default_value : '',
            'feature_type' => $this->getFeatureType($product_feature)
        ]);

        $result = $this->product_storage->updateProductFeature(
            $feature_data,
            0,
            null,
            sprintf('Creating product feature %s failed', $product_feature->getEntityId()->getId())
        );

        if ($result->isSuccess()) {
            $product_feature->id->local_id = (int) $result->getData();
        }

        return $result;
    }

    /**
     * Imports product feature translations
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductFeatureDto  $product_feature Product feature DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage  Import storage instance
     */
    private function importProductFeatureTranslations(ProductFeatureDto $product_feature, ImportStorage $import_storage)
    {
        $lang_codes = (array) $import_storage->getSetting('lang_codes', []);

        foreach ($lang_codes as $lang_code) {
            $description_data = array_merge($product_feature->properties->getTranslatableValueMap($lang_code), [
                'description'  => $product_feature->name ? $product_feature->name->getTranslate($lang_code) : '',
            ]);
            $description_data = array_filter($description_data);

            if (empty($description_data)) {
                continue;
            }

            $description_data['feature_type'] = $this->getFeatureType($product_feature);

            $this->product_storage->updateProductFeature(
                $description_data,
                (int) $product_feature->getEntityId()->local_id,
                $lang_code
            );
        }
    }

    /**
     * Imports product feature variants
     *
     * @param \Tygh\Common\OperationResult                   $main_result     Result instance
     * @param \Tygh\Addons\CommerceML\Dto\ProductFeatureDto  $product_feature Product feature DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage  Import storage instance
     */
    private function importProductFeatureVariants(OperationResult $main_result, ProductFeatureDto $product_feature, ImportStorage $import_storage)
    {
        $lang_codes = (array) $import_storage->getSetting('lang_codes', []);
        $variants = [];

        /** @var \Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto $variant */
        foreach ($product_feature->variants as $variant) {
            if (!$variant instanceof ProductFeatureVariantDto) {
                continue;
            }

            $variant_id = $import_storage->findEntityLocalId(
                ProductFeatureVariantDto::REPRESENT_ENTITY_TYPE,
                $variant->id,
                $import_storage->getSetting('mapping.feature_variant.default_variant', LocalIdDto::VALUE_CREATE)
            );

            if ($variant_id->hasValue()) {
                $variant->id->local_id = $variant_id->asInt();
                $import_storage->removeEntity($variant);
                continue;
            }

            if ($variant_id->isSkipValue()) {
                $main_result->addError('product_feature.variant_skipped', __('commerceml.import.message.product_feature.variant_skipped', [
                    '[id]' => $variant->id->getId(),
                ]));
                continue;
            }

            $variants[] = array_merge($variant->properties->getValueMap(), [
                'variant'    => $variant->name ? $variant->name->default_value : '',
                'import_dto' => $variant
            ]);
        }

        if (empty($variants)) {
            return;
        }

        $product_feature_data = [
            'feature_type' => $this->getFeatureType($product_feature),
            'variants'     => $variants
        ];

        $result = $this->product_storage->updateProductFeatureVariants(
            $product_feature_data,
            (int) $product_feature->getEntityId()->local_id
        );

        if ($result->isFailure()) {
            $main_result->setSuccess(false);
            $main_result->merge($result);
            return;
        }

        $product_feature_data = $result->getData();
        $variants = $product_feature_data['variants'];

        foreach ($lang_codes as $lang_code) {
            $variants_descriptions = [];

            foreach ($variants as $variant) {
                if (empty($variant['variant_id'])) {
                    continue;
                }

                /** @var \Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto $variant_dto */
                $variant_dto = $variant['import_dto'];

                $variants_description = array_merge($variant_dto->properties->getTranslatableValueMap($lang_code), [
                    'variant' => $variant_dto->name ? $variant_dto->name->getTranslate($lang_code) : ''
                ]);
                $variants_description = array_filter($variants_description);

                if (!$variants_description) {
                    continue;
                }

                $variants_descriptions[] = array_merge($variants_description, [
                    'variant_id' => $variant['variant_id']
                ]);
            }

            if (empty($variants_descriptions)) {
                continue;
            }

            $product_feature_data = [
                'feature_type' => $this->getFeatureType($product_feature),
                'variants'     => $variants_descriptions
            ];

            $this->product_storage->updateProductFeatureVariants(
                $product_feature_data,
                (int) $product_feature->getEntityId()->local_id,
                $lang_code
            );
        }

        foreach ($variants as $key => $variant) {
            if (empty($variant['variant_id'])) {
                continue;
            }

            /** @var \Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto $variant_dto */
            $variant_dto = $variant['import_dto'];
            $variant_dto->getEntityId()->local_id = $variant['variant_id'];

            $import_storage->removeEntity($variant_dto);
            $import_storage->mapEntityId($variant_dto);

            $main_result->addMessage('product_feature.variant_created_' . $key, __('commerceml.import.message.product_feature.variant_created', [
                '[id]'         => $variant_dto->id->getId(),
                '[feature_id]' => $product_feature->id->getId(),
                '[local_id]'   => $variant_dto->id->local_id
            ]));
        }
    }

    /**
     * Gets product feature type
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductFeatureDto $product_feature Product feature entity dto
     *
     * @return string
     */
    private function getFeatureType(ProductFeatureDto $product_feature)
    {
        if ($product_feature->is_multiple) {
            return ProductFeatures::MULTIPLE_CHECKBOX;
        }

        if ($product_feature->type === ProductFeatureDto::TYPE_STRING) {
            return ProductFeatures::TEXT_FIELD;
        }

        if ($product_feature->type === ProductFeatureDto::TYPE_NUMBER) {
            return ProductFeatures::NUMBER_FIELD;
        }

        if ($product_feature->type === ProductFeatureDto::TYPE_DATE_TIME) {
            return ProductFeatures::DATE;
        }

        if ($product_feature->type === ProductFeatureDto::TYPE_DIRECTORY) {
            return ProductFeatures::TEXT_SELECTBOX;
        }

        if ($product_feature->type === ProductFeatureDto::TYPE_EXTENDED) {
            return ProductFeatures::EXTENDED;
        }

        return ProductFeatures::TEXT_FIELD;
    }
}
