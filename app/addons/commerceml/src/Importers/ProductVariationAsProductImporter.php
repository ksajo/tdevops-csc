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


use Tygh\Addons\CommerceML\Dto\IdDto;
use Tygh\Addons\CommerceML\Dto\ProductDto;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Storages\ProductStorage;
use Tygh\Addons\ProductVariations\Product\FeaturePurposes;
use Tygh\Addons\ProductVariations\Product\Group\Group;
use Tygh\Addons\ProductVariations\Product\Group\GroupFeature;
use Tygh\Addons\ProductVariations\Product\Group\GroupFeatureCollection;
use Tygh\Addons\ProductVariations\Product\Group\GroupProduct;
use Tygh\Addons\ProductVariations\Request\GenerateProductsAndAttachToGroupRequest;
use Tygh\Addons\ProductVariations\Product\Group\Repository as GroupRepository;
use Tygh\Addons\ProductVariations\Service as GroupService;
use Tygh\Common\OperationResult;

/**
 * Class ProductVariationAsProductImporter
 *
 * @package Tygh\Addons\CommerceML\Importers
 */
class ProductVariationAsProductImporter
{
    /**
     * @var \Tygh\Addons\CommerceML\Importers\ProductImporter
     */
    private $product_importer;

    /**
     * @var \Tygh\Addons\CommerceML\Importers\ProductFeatureImporter
     */
    private $product_feature_importer;

    /**
     * @var \Tygh\Addons\ProductVariations\Product\Group\Repository
     */
    private $product_group_repository;

    /**
     * @var \Tygh\Addons\ProductVariations\Service
     */
    private $product_variations_service;

    /**
     * @var \Tygh\Addons\CommerceML\Storages\ProductStorage
     */
    private $product_storage;

    /**
     * ProductVariationAsProductImporter constructor.
     *
     * @param \Tygh\Addons\CommerceML\Importers\ProductImporter        $product_importer           Product importer
     * @param \Tygh\Addons\CommerceML\Importers\ProductFeatureImporter $product_feature_importer   Product feature importer
     * @param \Tygh\Addons\ProductVariations\Product\Group\Repository  $product_group_repository   Product variations group repository
     * @param \Tygh\Addons\ProductVariations\Service                   $product_variations_service Product vatiations service
     * @param \Tygh\Addons\CommerceML\Storages\ProductStorage          $product_storage            Product storage
     *
     * phpcs:disable Squiz.Commenting.FunctionComment.IncorrectTypeHint
     */
    public function __construct(
        ProductImporter $product_importer,
        ProductFeatureImporter $product_feature_importer,
        GroupRepository $product_group_repository,
        GroupService $product_variations_service,
        ProductStorage $product_storage
    ) {
        $this->product_importer = $product_importer;
        $this->product_feature_importer = $product_feature_importer;
        $this->product_group_repository = $product_group_repository;
        $this->product_variations_service = $product_variations_service;
        $this->product_storage = $product_storage;
    }

    /**
     * Imports product as product variation
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     *
     * @return \Tygh\Common\OperationResult
     */
    public function import(ProductDto $product, ImportStorage $import_storage)
    {
        $main_result = new OperationResult(true);

        if (!$this->validateProductProperties($main_result, $product)) {
            return  $main_result;
        }

        $local_id = $import_storage->findEntityLocalId(ProductDto::REPRESENT_ENTITY_TYPE, $product->id);

        if ($local_id->hasValue()) {
            $product->id->local_id = $local_id->asInt();
            $this->processImportWithLocalId($main_result, $product, $import_storage);
            return $main_result;
        }

        if ($product->is_removed) {
            return $main_result;
        }

        $this->processImportWithNewProduct($main_result, $product, $import_storage);

        return $main_result;
    }

    /**
     * Validates base product properties
     *
     * @param \Tygh\Common\OperationResult           $result  Operation result intance
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto $product Product DTO
     *
     * @return bool
     */
    private function validateProductProperties(OperationResult $result, ProductDto $product)
    {
        if (!$product->is_variation) {
            $result->setSuccess(false);
            return false;
        }

        if (empty($product->variation_feature_values)) {
            $result->setSuccess(false);
            return false;
        }

        return true;
    }

    /**
     * Performs import variation with exists product
     *
     * @param \Tygh\Common\OperationResult                   $main_result    Operation result intance
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     */
    private function processImportWithLocalId(OperationResult $main_result, ProductDto $product, ImportStorage $import_storage)
    {
        $product->name = null;
        $local_id = $product->id->local_id;

        if ($product->is_removed) {
            $result = $this->product_storage->removeProduct((int) $local_id);

            $main_result->merge($result);

            if ($result->isFailure()) {
                $main_result->setSuccess(false);
                $main_result->addError('product_variation.variation_not_deleted', __('commerceml.import.error.product_variation.variation_not_deleted', [
                    '[id]'       => $product->id->getId(),
                    '[local_id]' => $local_id,
                ]));
            } else {
                $import_storage->markEntityAsRemoved($product->getEntityType(), $product->id->getId());

                $main_result->addMessage('product_variation.deleted', __('commerceml.import.message.product.deleted', [
                    '[id]'       => $product->id->getId(),
                    '[local_id]' => $local_id,
                ]));
            }

            return;
        }

        $result = $this->product_importer->import($product, $import_storage);

        $main_result->merge($result);
        $main_result->setSuccess($result->isSuccess());
    }

    /**
     * Performs import variation with new product
     *
     * @param \Tygh\Common\OperationResult                   $main_result    Operation result intance
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     *
     * @return bool
     */
    private function processImportWithNewProduct(OperationResult $main_result, ProductDto $product, ImportStorage $import_storage)
    {
        $group = $this->findVariationGroup($product, $import_storage);

        if ($group) {
            if (!$this->createProductVariation($main_result, $product, $import_storage, $group)) {
                return false;
            }
        } else {
            if ($product->parent_id && $import_storage->isEntityMarkedAsRemoved($product->getEntityType(), $product->parent_id->getId())) {
                $main_result->setSuccess(false);
                $main_result->addError('product_variation.parent_product_removed', __('commerceml.import.error.product.parent_product_removed', [
                    '[id]'        => $product->id->getId(),
                    '[parent_id]' => $product->parent_id->getId(),
                ]));

                return false;
            }

            $parent_product_id = $this->findParentProductId($product, $import_storage);

            if ($parent_product_id->hasValue()) {
                $product->id->local_id = $parent_product_id->asInt();
                $product->name = null;
            }
        }

        $result = $this->product_importer->import($product, $import_storage);

        $main_result->merge($result);

        if ($result->isFailure()) {
            $main_result->setSuccess(false);
            return false;
        }

        if (!$group && !$this->createProductVariationsGroup($main_result, $product, $import_storage)) {
            $import_storage->removeMappingByExternalId(ProductDto::REPRESENT_ENTITY_TYPE, $product->id->external_id);
            return false;
        }

        return true;
    }

    /**
     * Validates product variations features
     *
     * @param \Tygh\Common\OperationResult                       $main_result Operation result instance
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto             $product     Product DTO
     * @param \Tygh\Addons\ProductVariations\Product\Group\Group $group       Product variations group
     *
     * @return bool
     */
    private function validateVariationProductFeatures(OperationResult $main_result, ProductDto $product, Group $group)
    {
        if (count($group->getFeatures()) !== count($product->variation_feature_values)) {
            $main_result->addError('product_variation.invalid_features_count', __('commerceml.import.error.product_variation.invalid_features_count', [
                '[group_code]' => $group->getCode()
            ]));
            return false;
        }

        /** @var \Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto $product_feature_value */
        foreach ($product->variation_feature_values as $product_feature_value) {
            $feature_id = (int) $product_feature_value->feature_id->local_id;

            if (!$feature_id || !$group->getFeatures()->hasFeature($feature_id)) {
                $main_result->addError(
                    'product_variation.feature_not_found_at_group',
                    __('commerceml.import.error.product_variation.feature_not_found_at_group', [
                        '[feature_id]' => $product_feature_value->feature_id->getId(),
                        '[group_code]' => $group->getCode()
                    ])
                );

                return false;
            }
        }

        return true;
    }

    /**
     * Imports product variation features
     *
     * @param \Tygh\Common\OperationResult                   $main_result    Operation result instance
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     *
     * @return bool
     */
    private function importVariationProductFeatures(OperationResult $main_result, ProductDto $product, ImportStorage $import_storage)
    {
        /** @var \Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto $product_feature_value */
        foreach ($product->variation_feature_values as $product_feature_value) {
            $result = $this->product_feature_importer->importByFeatureValue($product_feature_value, $import_storage);

            $main_result->merge($result);

            if ($result->isFailure()) {
                $main_result->setSuccess(false);
                return false;
            }
        }

        return true;
    }

    /**
     * Updates product feature values
     *
     * @param \Tygh\Common\OperationResult           $main_result Operation result instance
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto $product     Product DTO
     *
     * @return bool
     */
    private function updateProductFeatureValues(OperationResult $main_result, ProductDto $product)
    {
        $product_features = [];

        /** @var \Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto $product_feature_value */
        foreach ($product->variation_feature_values as $product_feature_value) {
            if (!$product_feature_value->value_id instanceof IdDto) {
                continue;
            }

            $product_features[$product_feature_value->feature_id->local_id] = $product_feature_value->value_id->local_id;
        }

        if ($product_features) {
            $result = $this->product_storage->updateProductFeaturesValues(
                (int) $product->id->local_id,
                $product_features
            );

            $main_result->merge($result);

            if ($result->isFailure()) {
                $main_result->setSuccess(false);
                return false;
            }
        }

        return true;
    }

    /**
     * Creates product variation
     *
     * @param \Tygh\Common\OperationResult                       $main_result    Operation result instance
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto             $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage     $import_storage Import storage
     * @param \Tygh\Addons\ProductVariations\Product\Group\Group $group          Product variations group
     *
     * @return bool
     */
    private function createProductVariation(OperationResult $main_result, ProductDto $product, ImportStorage $import_storage, Group $group)
    {
        $parent_product_id = $this->findParentProductId($product, $import_storage);

        if ($parent_product_id->hasNotValue()) {
            $main_result->addError('product_variation.parent_product_not_found', __('commerceml.import.error.product_variation.parent_product_not_found', [
                '[id]' => $product->id->getId()
            ]));
            return false;
        }

        if (!$this->importVariationProductFeatures($main_result, $product, $import_storage)) {
            return false;
        }

        if (!$this->validateVariationProductFeatures($main_result, $product, $group)) {
            return false;
        }

        $variant_ids = [];

        /** @var \Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto $product_feature_value */
        foreach ($product->variation_feature_values as $product_feature_value) {
            if (!$product_feature_value->value_id instanceof IdDto) {
                continue;
            }
            $variant_id = (int) $product_feature_value->value_id->local_id;
            $variant_ids[] = $variant_id;
        }

        $combination_id = GroupProduct::generateCombinationId($variant_ids);

        if ($group->getProducts()->hasCombinationByCombinationId($combination_id)) {
            $main_result->setSuccess(false);
            $main_result->addError('product_variation.same_variation_exists', __('commerceml.import.error.product_variation.same_variation_exists', [
                '[group_code]' => $group->getCode(),
            ]));
            return false;
        }

        $result = $this->product_variations_service->generateProductsAndAttachToGroup(
            GenerateProductsAndAttachToGroupRequest::create($group->getId(), $parent_product_id->asInt(), [$combination_id])
        );

        if ($result->isSuccess() && $result->getData('group')) {
            /** @var Group $group */
            $group = $result->getData('group');

            $group_product = $group->getProducts()->getProductByCombinationId($combination_id);

            $main_result->merge($result);

            if (!$group_product) {
                $main_result->setSuccess(false);
                $main_result->addError('product_variation.can_not_create_variation', __('commerceml.import.error.product_variation.can_not_create_variation', [
                    '[id]'         => $product->id->getId(),
                    '[group_code]' => $group->getCode(),
                ]));
                return false;
            }

            $product->id->local_id = $group_product->getProductId();
            $product->name = null;

            $import_storage->mapEntityId($product);

            return true;
        }

        return false;
    }

    /**
     * Creates product variations group
     *
     * @param \Tygh\Common\OperationResult                   $main_result    Operation result instance
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     *
     * @return bool
     */
    private function createProductVariationsGroup(OperationResult $main_result, ProductDto $product, ImportStorage $import_storage)
    {
        if (!$this->importVariationProductFeatures($main_result, $product, $import_storage)) {
            return false;
        }

        if (!$this->updateProductFeatureValues($main_result, $product)) {
            return false;
        }

        $group_feature_collection = new GroupFeatureCollection();
        $product_id = (int) $product->id->local_id;
        $parent_product_id = $this->findParentProductId($product, $import_storage);
        $product_ids = [$product_id];

        if ($parent_product_id->hasValue()) {
            array_unshift($product_ids, $parent_product_id->asInt());
            $product_ids = array_unique($product_ids);
        }

        /** @var \Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto $feature_value */
        foreach ($product->variation_feature_values as $feature_value) {
            $feature_id = (int) $feature_value->feature_id->local_id;
            $feature_data = $this->product_storage->findProductFeature($feature_id);

            $feature_purpose = (isset($feature_data['purpose']) && $feature_data['purpose'] === FeaturePurposes::CREATE_CATALOG_ITEM)
                ? FeaturePurposes::CREATE_CATALOG_ITEM
                : FeaturePurposes::CREATE_VARIATION_OF_CATALOG_ITEM;

            $group_feature_collection->addFeature(GroupFeature::create($feature_id, $feature_purpose));
        }

        $result = $this->product_variations_service->createGroup(
            $product_ids,
            $product->variation_group_code,
            $group_feature_collection
        );

        $main_result->merge($result);

        $product_statuses = $result->getData('products_status', []);
        $product_status = isset($product_statuses[$product_id]) ? $product_statuses[$product_id] : null;

        if (
            $product_id !== $parent_product_id->asInt()
            && (!$product_status || Group::isResultError($product_status))
        ) {
            $this->product_storage->removeProduct($product_id);
            $main_result->addError(
                'product_variation.can_not_create_variation_group',
                __('commerceml.import.error.product_variation.can_not_create_variation_group', [
                    '[id]'         => $product->id->getId(),
                ])
            );

            return false;
        }

        if ($parent_product_id->hasNotValue() && $result->isSuccess()) {
            $import_storage->mapEntityIdByParams(ProductDto::REPRESENT_ENTITY_TYPE, $product->parent_id->external_id, $product_id);
        }

        if ($result->isFailure()) {
            $main_result->setSuccess(false);
            return false;
        }

        return true;
    }

    /**
     * Finds parent local product ID
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     *
     * @return \Tygh\Addons\CommerceML\Dto\LocalIdDto
     */
    private function findParentProductId(ProductDto $product, ImportStorage $import_storage)
    {
        $parent_product_id = $import_storage->findEntityLocalId(
            $product->getEntityType(),
            $product->parent_id
        );

        $product->parent_id->local_id = $parent_product_id->asInt();

        return $parent_product_id;
    }

    /**
     * Finds variation group
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     *
     * @return \Tygh\Addons\ProductVariations\Product\Group\Group|null
     */
    private function findVariationGroup(ProductDto $product, ImportStorage $import_storage)
    {
        $group_id = 0;
        $group = null;
        $parent_product_id = $this->findParentProductId($product, $import_storage);

        if ($parent_product_id->hasValue()) {
            $group_id = $this->product_group_repository->findGroupIdByProductId($parent_product_id->asInt());
        }

        if (!$group_id && $product->variation_group_code) {
            $group_id = $this->product_group_repository->findGroupIdByCode($product->variation_group_code);
        }

        if ($group_id) {
            $group = $this->product_group_repository->findGroupById($group_id);
        }

        return $group;
    }
}
