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


use Tygh\Addons\CommerceML\Dto\CategoryDto;
use Tygh\Addons\CommerceML\Dto\CurrencyDto;
use Tygh\Addons\CommerceML\Dto\IdDto;
use Tygh\Addons\CommerceML\Dto\LocalIdDto;
use Tygh\Addons\CommerceML\Dto\PriceTypeDto;
use Tygh\Addons\CommerceML\Dto\ProductDto;
use Tygh\Addons\CommerceML\Dto\TaxDto;
use Tygh\Addons\CommerceML\Dto\TranslatableValueDto;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Storages\ProductStorage;
use Tygh\Common\OperationResult;
use Tygh\Enum\ImagePairTypes;
use Tygh\Enum\ObjectStatuses;
use Tygh\Enum\ProductFeatures;
use Tygh\Enum\YesNo;

/**
 * Class ProductImporter
 *
 * @package Tygh\Addons\CommerceML\Importers
 *
 * phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint
 */
class ProductImporter
{
    /**
     * @var \Tygh\Addons\CommerceML\Importers\CategoryImporter
     */
    private $category_importer;

    /**
     * @var \Tygh\Addons\CommerceML\Importers\ProductFeatureImporter
     */
    private $product_feature_importer;

    /**
     * @var \Tygh\Addons\CommerceML\Storages\ProductStorage
     */
    private $product_storage;

    /**
     * ProductImporter constructor.
     *
     * @param \Tygh\Addons\CommerceML\Importers\CategoryImporter       $category_importer        Category importer
     * @param \Tygh\Addons\CommerceML\Importers\ProductFeatureImporter $product_feature_importer Product feature
     *                                                                                           importer
     * @param \Tygh\Addons\CommerceML\Storages\ProductStorage          $product_storage          Product storage
     */
    public function __construct(
        CategoryImporter $category_importer,
        ProductFeatureImporter $product_feature_importer,
        ProductStorage $product_storage
    ) {
        $this->category_importer = $category_importer;
        $this->product_feature_importer = $product_feature_importer;
        $this->product_storage = $product_storage;
    }

    /**
     * Imports product and related records
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     *
     * @return \Tygh\Common\OperationResult Result instance
     */
    public function import(ProductDto $product, ImportStorage $import_storage)
    {
        $main_result = new OperationResult(true);

        /**
         * Executes before CommerceML product data imported
         * Allows to add additional data to product and import addition entities
         *
         * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
         * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage instance
         * @param \Tygh\Common\OperationResult                   $main_result    Parent category DTO
         */
        fn_set_hook('commerceml_product_importer_import_pre', $product, $import_storage, $main_result);

        if ($main_result->isFailure()) {
            return $main_result;
        }

        if (!$this->importCategories($main_result, $product, $import_storage)) {
            return $main_result;
        }

        if (!$this->importProductFeatures($main_result, $product, $import_storage)) {
            return $main_result;
        }

        if (!$this->importProduct($main_result, $product, $import_storage)) {
            return $main_result;
        }

        $main_result->setData($product->id->local_id);

        return $main_result;
    }

    /**
     * Imports base product data
     *
     * @param \Tygh\Common\OperationResult                   $main_result    Operation result intance
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     *
     * @return bool
     */
    private function importProduct(OperationResult $main_result, ProductDto $product, ImportStorage $import_storage)
    {
        $current_product_data = $this->findProduct($main_result, $product, $import_storage);

        if (!$this->validateProduct($main_result, $product, $import_storage, $current_product_data)) {
            return false;
        }

        $product_data = array_merge($product->properties->getValueMap(['short_description', 'page_title', 'promo_text']), [
            'amount' => $product->quantity,
        ]);

        if (!$product->id->hasLocalId()) {
            $product_data['source_import_key'] = $import_storage->getImport()->import_key;
        }

        $product_data = $this->fillProductCode($product, $import_storage, $product_data);
        $product_data = $this->fillProductName($product, $import_storage, $product_data);
        $product_data = $this->fillProductFullDescription($product, $import_storage, $product_data);
        $product_data = $this->fillProductShortDescription($product, $import_storage, $product_data);
        $product_data = $this->fillProductPageTitle($product, $import_storage, $product_data);
        $product_data = $this->fillProductPromoText($product, $import_storage, $product_data);
        $product_data = $this->fillStatus($product, $import_storage, $product_data, $current_product_data);
        $product_data = $this->fillCategories($product, $import_storage, $product_data, $current_product_data);
        $product_data = $this->fillTaxes($product, $import_storage, $product_data, $current_product_data);
        $product_data = $this->fillPrices($product, $import_storage, $product_data);
        $product_data = $this->fillProductFeatures($product, $import_storage, $product_data, $current_product_data);

        $product_data = array_filter($product_data, static function ($val) {
            return $val !== null;
        });

        if ($product_data && !$this->updateProduct($main_result, $product, $product_data)) {
            return false;
        }

        $this->importProductImages($product, $import_storage, $current_product_data);
        $this->importProductTranslations($product, $import_storage);

        $import_storage->mapEntityId($product);
        $import_storage->removeEntity($product);

        return true;
    }

    /**
     * Imports product translatable descriptions
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     */
    private function importProductTranslations(ProductDto $product, ImportStorage $import_storage)
    {
        $lang_codes = (array) $import_storage->getSetting('lang_codes', []);

        foreach ($lang_codes as $lang_code) {
            $description_data = $product->properties->getTranslatableValueMap($lang_code, ['short_description', 'page_title', 'promo_text']);
            $description_data = $this->fillProductName($product, $import_storage, $description_data, $lang_code);
            $description_data = $this->fillProductFullDescription($product, $import_storage, $description_data, $lang_code);
            $description_data = $this->fillProductShortDescription($product, $import_storage, $description_data, $lang_code);
            $description_data = $this->fillProductPageTitle($product, $import_storage, $description_data, $lang_code);
            $description_data = $this->fillProductPromoText($product, $import_storage, $description_data, $lang_code);

            $description_data = array_filter($description_data);

            if (!$description_data) {
                continue;
            }

            $this->updateProduct(new OperationResult(), $product, $description_data, $lang_code);
        }
    }

    /**
     * Imports product images
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product              Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage       Import storage
     * @param array<string, mixed>                           $current_product_data Current product data
     */
    private function importProductImages(ProductDto $product, ImportStorage $import_storage, array $current_product_data)
    {
        $strategy = $import_storage->getSetting('catalog_importer.product_image_update_strategy');

        if ($strategy === 'ignore' && !$product->is_new) {
            return;
        }

        $files_dir = rtrim((string) $import_storage->getSetting('upload_dir_path', '/'), '/');
        $current_pair_ids = [];
        $detailed_image_data_list = [];
        $pair_data_list = [];

        if ($current_product_data && $strategy === 'append') {
            $current_pair_ids = array_column($current_product_data['images'], 'pair_id', 'image_path');
        }

        /** @var \Tygh\Addons\CommerceML\Dto\ImageDto $image */
        foreach ($product->images as $image) {
            $filepath = sprintf('%s/%s', $files_dir, ltrim($image->path, '/'));

            if (!file_exists($filepath)) {
                continue;
            }

            $filename = mb_strtolower(basename($image->path));
            $pair_id = isset($current_pair_ids[$filename]) ? $current_pair_ids[$filename] : null;
            $current_image = isset($pair_id, $current_product_data['images'][$pair_id]) ? $current_product_data['images'][$pair_id] : [];

            if (isset($current_image['type'])) {
                $type = $current_image['type'];
            } elseif (empty($current_pair_ids) && empty($pair_data_list)) {
                $type = ImagePairTypes::MAIN;
            } else {
                $type = ImagePairTypes::ADDITIONAL;
            }

            $pair_data_list[] = [
                'pair_id' => $pair_id,
                'type'    => $type,
                'is_new'  => YesNo::YES
            ];

            $detailed_image_data_list[] = [
                'name' => $filename,
                'path' => $filepath,
                'size' => filesize($filepath)
            ];
        }

        if (!$pair_data_list) {
            return;
        }

        $this->product_storage->updateProductImages(
            (int) $product->id->local_id,
            $pair_data_list,
            $detailed_image_data_list
        );

        if (empty($current_product_data['images']) || $strategy !== 'replace') {
            return;
        }

        $current_pair_ids = array_column($current_product_data['images'], 'pair_id', 'image_path');
        $this->product_storage->removeImagePairs($current_pair_ids);
    }

    /**
     * Imports product categories
     *
     * @param \Tygh\Common\OperationResult                   $main_result    Operation result intance
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     *
     * @return bool
     */
    private function importCategories(OperationResult $main_result, ProductDto $product, ImportStorage $import_storage)
    {
        $allow_manage_category = $import_storage->getSetting('allow_manage_categories', true);
        $default_category_id = (int) $import_storage->getSetting('catalog_importer.default_category_id', 0);

        if (
            empty($product->categories)
            && $default_category_id
            && $product->is_creatable
        ) {
            $category_id_dto = IdDto::createByLocalId((string) $default_category_id);
            $product->categories[] = $category_id_dto;

            $main_result->addMessage('product.default_category_used', __('commerceml.import.message.product.no_categories_default_category_used', [
                '[product_id]' => $product->id->getId(),
                '[local_id]'   => $category_id_dto->local_id
            ]));
            return true;
        }

        /** @var \Tygh\Addons\CommerceML\Dto\IdDto $category_id_dto */
        foreach ($product->categories as $category_id_dto) {
            $category_id = $import_storage->findEntityLocalId(
                CategoryDto::REPRESENT_ENTITY_TYPE,
                $category_id_dto,
                $import_storage->getSetting('mapping.category.default_variant', LocalIdDto::VALUE_CREATE)
            );

            if ($category_id->hasValue()) {
                $category_id_dto->local_id = $category_id->asInt();
                continue;
            }

            if ($category_id->isSkipValue()) {
                $main_result->addError('category.skipped', __('commerceml.import.message.category.skipped', [
                    '[id]' => $category_id_dto->getId(),
                ]));
                continue;
            }

            if ($allow_manage_category && $category_id->isCreateValue()) {
                $category = $import_storage->findEntity(CategoryDto::REPRESENT_ENTITY_TYPE, $category_id_dto->getId());

                if ($category && $category instanceof CategoryDto) {
                    $result = $this->category_importer->import($category, $import_storage);

                    $main_result->merge($result);

                    if ($result->isFailure()) {
                        $main_result->setSuccess(false);
                        return false;
                    }

                    $category_id_dto->local_id = $category->id->local_id;
                    continue;
                }
            }

            if ($default_category_id) {
                $category_id_dto->local_id = $default_category_id;

                $main_result->addMessage('product.default_category_used', __('commerceml.import.message.product.default_category_used', [
                    '[id]'       => $category_id_dto->getId(),
                    '[local_id]' => $default_category_id
                ]));

                continue;
            }

            $main_result->setSuccess(false);
            $main_result->addError('product.caregory_not_found', __('commerceml.import.error.product.caregory_not_found', [
                '[id]' => $category_id_dto->getId()
            ]));

            return false;
        }

        return true;
    }

    /**
     * Imports product features
     *
     * @param \Tygh\Common\OperationResult                   $main_result    Operation result intance
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     *
     * @return bool
     */
    private function importProductFeatures(OperationResult $main_result, ProductDto $product, ImportStorage $import_storage)
    {
        /** @var \Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto $product_feature_value */
        foreach ($product->product_feature_values as $product_feature_value) {
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
     * Executes update|create product
     *
     * @param \Tygh\Common\OperationResult           $main_result  Operation result intance
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto $product      Product DTO
     * @param array<string, mixed>                   $product_data Product data
     * @param string|null                            $lang_code    Language code
     *
     * @return bool
     */
    private function updateProduct(OperationResult $main_result, ProductDto $product, array $product_data, $lang_code = null)
    {
        $product_id = (int) $product->id->local_id;

        $result = $this->product_storage->updateProduct(
            $product_data,
            $product_id,
            $lang_code,
            sprintf('Product %s creating failed', $product->id->getId())
        );

        $main_result->merge($result);
        $main_result->setSuccess($result->isSuccess());

        if ($result->isFailure()) {
            return false;
        }

        $product->id->local_id = (int) $result->getData();

        if ($product_id) {
            $main_result->addMessage('product.updated', __('commerceml.import.message.product.updated', [
                '[id]'       => $product->id->getId(),
                '[local_id]' => $product->id->local_id,
                '[price]'    => isset($product_data['price']) ? $product_data['price'] : '-',
                '[amount]'   => isset($product_data['amount']) ? $product_data['amount'] : '-',
            ]));
        } else {
            $main_result->addMessage('product.created', __('commerceml.import.message.product.created', [
                '[id]'       => $product->id->getId(),
                '[local_id]' => $product->id->local_id,
                '[price]'    => isset($product_data['price']) ? $product_data['price'] : '-',
                '[amount]'   => isset($product_data['amount']) ? $product_data['amount'] : '-',
            ]));
        }

        return true;
    }

    /**
     * Fills product prices
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     * @param array<string, mixed>                           $product_data   Product data
     *
     * @return array<string, mixed>
     */
    private function fillPrices(ProductDto $product, ImportStorage $import_storage, array $product_data)
    {
        if ($product->price !== null) {
            $product_data['price'] = $product->price;
        } elseif (!$product->id->local_id) {
            $product_data['price'] = 0;
        }

        if ($product->list_price !== null) {
            $product_data['list_price'] = $product->list_price;
        } elseif (!$product->id->local_id) {
            $product_data['list_price'] = 0;
        }

        $product_data['prices'] = [];

        foreach ($product->prices as $price) {
            $local_price_type_id = $import_storage->findEntityLocalId(PriceTypeDto::REPRESENT_ENTITY_TYPE, $price->price_type_id);

            if ($local_price_type_id->hasNotValue()) {
                continue;
            }

            if ($price->currency_code) {
                $currency_code = $import_storage->findEntityLocalId(CurrencyDto::REPRESENT_ENTITY_TYPE, $price->currency_code);

                if ($currency_code->hasValue()) {
                    $currency = $this->product_storage->findCurrency($currency_code->asString());
                } else {
                    $currency = null;
                }

                if ($currency) {
                    $price->price *= (float) $currency['coefficient'];
                }
            }

            $local_price_type_id_parts = PriceTypeDto::parseLocalId($local_price_type_id->asString());
            $local_price_type = reset($local_price_type_id_parts);

            if ($local_price_type === PriceTypeDto::TYPE_BASE_PRICE) {
                $product_data['price'] = $price->price;
            } elseif ($local_price_type === PriceTypeDto::TYPE_LIST_PRICE) {
                $product_data['list_price'] = $price->price;
            } elseif ($local_price_type === PriceTypeDto::TYPE_USERGROUP && !empty($local_price_type_id_parts[1])) {
                $product_data['prices'][] = [
                    'price'        => $price->price,
                    'usergroup_id' => $local_price_type_id_parts[1],
                    'lower_limit'  => 1
                ];
            }
        }

        return $product_data;
    }

    /**
     * Fills product status
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product              Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage       Import storage
     * @param array<string, mixed>                           $product_data         Product data
     * @param array<string, mixed>                           $current_product_data Current product data
     *
     * @return array<string, mixed>
     */
    private function fillStatus(ProductDto $product, ImportStorage $import_storage, array $product_data, array $current_product_data)
    {
        if ($product->status) {
            $product_data['status'] = $product->status;
        } elseif (!$product->id->hasLocalId()) {
            $product_data['status'] = $import_storage->getSetting('catalog_importer.new_product_status', ObjectStatuses::ACTIVE);
        }

        if (
            isset($product_data['amount'])
            && empty($product_data['amount'])
            && $import_storage->getSetting('catalog_importer.hide_out_of_stock_products', false) === true
        ) {
            $product_data['status'] = ObjectStatuses::HIDDEN;
        }

        return $product_data;
    }

    /**
     * Fills product code
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     * @param array<string, mixed>                           $product_data   Product data
     *
     * @return array<string, mixed>
     */
    private function fillProductCode(ProductDto $product, ImportStorage $import_storage, array $product_data)
    {
        if ($product->product_code === null) {
            return $product_data;
        }

        if ($product->is_new || $import_storage->getSetting('catalog_importer.allow_update_product_code', false)) {
            $product_data['product_code'] = $product->product_code;
        }

        return $product_data;
    }

    /**
     * Fills product name
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     * @param array<string, mixed>                           $product_data   Product data
     * @param string|null                                    $lang_code      Language code
     *
     * @return array<string, mixed>
     */
    private function fillProductName(ProductDto $product, ImportStorage $import_storage, array $product_data, $lang_code = null)
    {
        if ($product->name === null) {
            return $product_data;
        }

        if ($lang_code) {
            $product_name = $product->name->hasTraslate($lang_code) ? $product->name->getTranslate($lang_code) : null;
        } else {
            $product_name = $product->name->default_value;
        }

        if ($product_name === null) {
            return $product_data;
        }

        if ($product->is_new || $import_storage->getSetting('catalog_importer.allow_update_product_name', false)) {
            $product_data['product'] = $product_name;
        }

        return $product_data;
    }

    /**
     * Fills product full description
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     * @param array<string, mixed>                           $product_data   Product data
     * @param string|null                                    $lang_code      Language code
     *
     * @return array<string, mixed>
     */
    private function fillProductFullDescription(ProductDto $product, ImportStorage $import_storage, array $product_data, $lang_code = null)
    {
        if ($product->description === null) {
            return $product_data;
        }

        if ($lang_code) {
            $product_description = $product->description->hasTraslate($lang_code) ? $product->description->getTranslate($lang_code) : null;
        } else {
            $product_description = $product->description->default_value;
        }

        if ($product_description === null) {
            return $product_data;
        }

        if ($product->is_new || $import_storage->getSetting('catalog_importer.allow_update_product_full_description', false)) {
            $product_data['full_description'] = $product_description;
        }

        return $product_data;
    }

    /**
     * Fills product short description
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     * @param array<string, mixed>                           $product_data   Product data
     * @param string|null                                    $lang_code      Language code
     *
     * @return array<string, mixed>
     */
    private function fillProductShortDescription(ProductDto $product, ImportStorage $import_storage, array $product_data, $lang_code = null)
    {
        if (!$product->properties->has('short_description')) {
            return $product_data;
        }

        $short_description_property = $product->properties->get('short_description');

        if ($short_description_property->value === null || !$short_description_property->value instanceof TranslatableValueDto) {
            return $product_data;
        }

        if ($lang_code) {
            $product_short_description = $short_description_property->value->hasTraslate($lang_code)
                ? $short_description_property->value->getTranslate($lang_code)
                : null;
        } else {
            $product_short_description = $short_description_property->value->default_value;
        }

        if ($product_short_description === null) {
            return $product_data;
        }

        if ($product->is_new || $import_storage->getSetting('catalog_importer.allow_update_product_short_description', false)) {
            $product_data['short_description'] = $product_short_description;
        }

        return $product_data;
    }

    /**
     * Fills product page title
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     * @param array<string, mixed>                           $product_data   Product data
     * @param string|null                                    $lang_code      Language code
     *
     * @return array<string, mixed>
     */
    private function fillProductPageTitle(ProductDto $product, ImportStorage $import_storage, array $product_data, $lang_code = null)
    {
        if (!$product->properties->has('page_title')) {
            return $product_data;
        }

        $page_title_property = $product->properties->get('page_title');

        if ($page_title_property->value === null || !$page_title_property->value instanceof TranslatableValueDto) {
            return $product_data;
        }

        if ($lang_code) {
            $product_page_title = $page_title_property->value->hasTraslate($lang_code)
                ? $page_title_property->value->getTranslate($lang_code)
                : null;
        } else {
            $product_page_title = $page_title_property->value->default_value;
        }

        if ($product_page_title === null) {
            return $product_data;
        }

        if ($product->is_new || $import_storage->getSetting('catalog_importer.allow_update_product_page_title', false)) {
            $product_data['page_title'] = $product_page_title;
        }

        return $product_data;
    }

    /**
     * Fills product promo text
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     * @param array<string, mixed>                           $product_data   Product data
     * @param string|null                                    $lang_code      Language code
     *
     * @return array<string, mixed>
     */
    private function fillProductPromoText(ProductDto $product, ImportStorage $import_storage, array $product_data, $lang_code = null)
    {
        if (!$product->properties->has('promo_text')) {
            return $product_data;
        }

        $promo_text_property = $product->properties->get('promo_text');

        if ($promo_text_property->value === null || !$promo_text_property->value instanceof TranslatableValueDto) {
            return $product_data;
        }

        if ($lang_code) {
            $product_promo_text = $promo_text_property->value->hasTraslate($lang_code)
                ? $promo_text_property->value->getTranslate($lang_code)
                : null;
        } else {
            $product_promo_text = $promo_text_property->value->default_value;
        }

        if ($product_promo_text === null) {
            return $product_data;
        }

        if ($product->is_new || $import_storage->getSetting('catalog_importer.allow_update_product_promotext', false)) {
            $product_data['promo_text'] = $product_promo_text;
        }

        return $product_data;
    }

    /**
     * Fills product categories
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product              Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage       Import storage
     * @param array<string, mixed>                           $product_data         Product data
     * @param array<string, mixed>                           $current_product_data Current product data
     *
     * @return array<string, mixed>
     */
    private function fillCategories(ProductDto $product, ImportStorage $import_storage, array $product_data, array $current_product_data)
    {
        $category_ids = [];

        /** @var \Tygh\Addons\CommerceML\Dto\IdDto $category_id */
        foreach ($product->categories as $category_id) {
            if (!$category_id->hasLocalId()) {
                continue;
            }

            $category_ids[] = $category_id->local_id;
        }

        if (!$category_ids) {
            return $product_data;
        }

        $strategy = $import_storage->getSetting('catalog_importer.product_category_update_strategy', 'append');

        if ($product->is_new) {
            $strategy = 'replace';
        }

        switch ($strategy) {
            case 'append':
                if ($current_product_data) {
                    $product_data['category_ids'] = array_unique(array_merge($current_product_data['category_ids'], $category_ids));
                } else {
                    $product_data['main_category'] = reset($category_ids);
                    $product_data['category_ids'] = $category_ids;
                }
                break;
            case 'replace_main':
                $product_data['main_category'] = reset($category_ids);

                if ($current_product_data) {
                    $product_data['category_ids'] = array_unique(array_merge(
                        array_diff($current_product_data['category_ids'], [$current_product_data['main_category']]),
                        $category_ids
                    ));
                } else {
                    $product_data['category_ids'] = $category_ids;
                }
                break;
            case 'replace':
                $product_data['main_category'] = reset($category_ids);
                $product_data['category_ids'] = $category_ids;
                break;
            default:
            case 'ignore':
                break;
        }

        return $product_data;
    }

    /**
     * Fills product taxes
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product              Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage       Import storage
     * @param array<string, mixed>                           $product_data         Product data
     * @param array<string, mixed>                           $current_product_data Current product data
     *
     * @return array<string, mixed>
     */
    private function fillTaxes(ProductDto $product, ImportStorage $import_storage, array $product_data, array $current_product_data)
    {
        $tax_ids = [];

        /** @var \Tygh\Addons\CommerceML\Dto\IdDto $tax_id */
        foreach ($product->taxes as $tax_id) {
            $local_id = $import_storage->findEntityLocalId(TaxDto::REPRESENT_ENTITY_TYPE, $tax_id);

            if ($local_id->hasNotValue()) {
                continue;
            }

            $tax_ids[] = $local_id->asInt();
        }

        if ($tax_ids) {
            if ($current_product_data) {
                $product_data['tax_ids'] = array_unique(array_merge(explode(',', $current_product_data['tax_ids']), $tax_ids));
            } else {
                $product_data['tax_ids'] = $tax_ids;
            }
        }

        return $product_data;
    }

    /**
     * Fills product features
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product              Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage       Import storage
     * @param array<string, mixed>                           $product_data         Product data
     * @param array<string, mixed>                           $current_product_data Current product data
     *
     * @return array<string, mixed>
     */
    private function fillProductFeatures(ProductDto $product, ImportStorage $import_storage, array $product_data, array $current_product_data)
    {
        $product_feature_values = [];

        /** @var \Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto $product_feature_value */
        foreach ($product->product_feature_values as $product_feature_value) {
            if (!$product_feature_value->feature_id->hasLocalId()) {
                continue;
            }

            if ($product_feature_value->value_id instanceof IdDto && !$product_feature_value->value_id->hasLocalId()) {
                continue;
            }

            $feature_id = (int) $product_feature_value->feature_id->local_id;

            if ($product_feature_value->value_id) {
                $value = $product_feature_value->value_id->local_id;
            } else {
                $value = $product_feature_value->value;
            }

            $product_feature_values[$feature_id] = $value;
        }

        if ($product_feature_values) {
            $product_data['product_features'] = $product_feature_values;
        }

        return $product_data;
    }

    /**
     * Finds product
     *
     * @param \Tygh\Common\OperationResult                   $main_result    Operation result intance
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product        Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage
     *
     * @return array<string, mixed>
     */
    private function findProduct(OperationResult $main_result, ProductDto $product, ImportStorage $import_storage)
    {
        $current_product_data = [];
        $import = $import_storage->getImport();
        $allow_matching_product_by_product_code = $import_storage->getSetting('catalog_importer.allow_matching_product_by_product_code', false);

        $product->is_new = true;
        $product->id->local_id = $import_storage->findEntityLocalId(ProductDto::REPRESENT_ENTITY_TYPE, $product->id)->asInt();

        if (!$product->id->local_id && $product->product_code && $allow_matching_product_by_product_code === true) {
            $product->id->local_id = $this->product_storage->findProductIdByProductCode($product->product_code, $import->company_id);

            $main_result->addMessage('product.found_by_product_code', __('commerceml.import.message.product.found_by_product_code', [
                '[id]'           => $product->id->getId(),
                '[local_id]'     => $product->id->local_id,
                '[product_code]' => $product->product_code,
            ]));
        }

        if ($product->id->local_id) {
            $current_product_data = $this->product_storage->getRawProductData((int) $product->id->local_id);

            if (empty($current_product_data)) {
                $product->id->removeLocalId();
                $import_storage->mapEntityId($product);

                $main_result->addMessage(
                    'product.local_id_exists_but_product_not_found',
                    __('commerceml.import.message.product.local_id_exists_but_product_not_found', [
                        '[id]'           => $product->id->getId(),
                        '[local_id]'     => $product->id->local_id,
                    ])
                );
            } else {
                $product->is_new = false;
            }
        }

        return $current_product_data;
    }

    /**
     * Validates product
     *
     * @param \Tygh\Common\OperationResult                   $main_result          Operation result intance
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto         $product              Product DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage       Import storage
     * @param array<string, mixed>                           $current_product_data Product data
     *
     * @return bool
     */
    private function validateProduct(OperationResult $main_result, ProductDto $product, ImportStorage $import_storage, array $current_product_data)
    {
        $import = $import_storage->getImport();
        $import_mode = $import_storage->getSetting('catalog_importer.import_mode', 'all');

        if ($product->is_removed && $current_product_data) {
            $result = $this->product_storage->removeProduct((int) $product->id->local_id);

            $main_result->merge($result);
            $main_result->setSuccess($result->isSuccess());

            if ($result->isSuccess()) {
                $import_storage->markEntityAsRemoved($product->getEntityType(), $product->id->getId());

                $main_result->addMessage('product.deleted', __('commerceml.import.message.product.deleted', [
                    '[id]'       => $product->id->getId(),
                    '[local_id]' => $product->id->local_id,
                ]));
            }

            return false;
        }

        if ($product->is_removed) {
            $import_storage->markEntityAsRemoved($product->getEntityType(), $product->id->getId());
            $main_result->addMessage('product.skipped', __('commerceml.import.message.product.not_import_deleted', [
                '[id]'       => $product->id->getId(),
                '[local_id]' => $product->id->local_id,
            ]));
            return false;
        }

        if (
            empty($current_product_data)
            && (!$product->is_creatable || !$this->isProductCreatable($product))
        ) {
            if ($import_storage->isEntityMarkedAsRemoved($product->getEntityType(), $product->id->getId())) {
                $main_result->addMessage('product.skipped', __('commerceml.import.message.product.marked_as_removed', [
                    '[id]' => $product->id->getId(),
                ]));
            } else {
                $main_result->setSuccess(false);
                $main_result->addError('product.skipped', __('commerceml.import.message.product.is_not_creatable', [
                    '[id]' => $product->id->getId()
                ]));
            }

            return false;
        }

        if (
            isset($current_product_data['source_import_key'])
            && $current_product_data['source_import_key'] !== $import->import_key
            && $import_mode === 'only_new'
        ) {
            $main_result->setSuccess(false);
            $main_result->addError(
                'product.exists_and_skipped_by_import_mode',
                __('commerceml.import.error.product_exists_and_skipped_by_import_mode', [
                    '[id]'   => $product->id->getId()
                ])
            );

            return false;
        }

        if (!$product->id->local_id && $import_mode === 'only_existing') {
            $main_result->setSuccess(false);
            $main_result->addError(
                'product.not_exists_and_skipped_by_import_mode',
                __('commerceml.import.error.product_not_exists_and_skipped_by_import_mode', [
                    '[id]'   => $product->id->getId()
                ])
            );

            return false;
        }

        return true;
    }

    /**
     * Checks if product createable
     *
     * @param \Tygh\Addons\CommerceML\Dto\ProductDto $product Product Dto
     *
     * @return bool
     */
    private function isProductCreatable(ProductDto $product)
    {
        return !empty($product->name)
            && !empty($product->categories);
    }
}
