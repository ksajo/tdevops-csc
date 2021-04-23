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
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Storages\ProductStorage;
use Tygh\Common\OperationResult;

/**
 * Class CategoryImporter
 *
 * @package Tygh\Addons\CommerceML\Importers
 */
class CategoryImporter
{
    /**
     * @var \Tygh\Addons\CommerceML\Storages\ProductStorage
     */
    private $product_storage;

    /**
     * CategoryImporter constructor.
     *
     * @param \Tygh\Addons\CommerceML\Storages\ProductStorage $product_storage Product storage instance
     */
    public function __construct(ProductStorage $product_storage)
    {
        $this->product_storage = $product_storage;
    }

    /**
     * Imports category
     *
     * @param \Tygh\Addons\CommerceML\Dto\CategoryDto        $category       Category DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage instance
     *
     * @return \Tygh\Common\OperationResult
     */
    public function import(CategoryDto $category, ImportStorage $import_storage)
    {
        $result = $this->importParentCategory($category, $import_storage);

        if ($result->isFailure()) {
            return $result;
        }

        $allow_matching_category_by_name = $import_storage->getSetting('allow_matching_category_by_name', false);
        $category_id = $import_storage->findEntityLocalId(CategoryDto::REPRESENT_ENTITY_TYPE, $category->id);
        $result = new OperationResult(true);

        if ($category_id->hasNotValue() && $category->name && $allow_matching_category_by_name === true) {
            $category->id->local_id = $this->product_storage->findCategoryIdByName(
                $category->name->default_value,
                $import_storage->getImport()->company_id
            );

            $result->addMessage('category.found_by_name', __('commerceml.import.message.category.found_by_name', [
                '[id]'       => $category->id->getId(),
                '[local_id]' => $category->id->local_id,
                '[name]'     => $category->name,
            ]));

            $import_storage->mapEntityId($category);
        }

        if ($category_id->hasValue()) {
            $result->setData($category_id->asInt());
            $import_storage->removeEntity($category);

            return $result;
        }

        $result = $this->importCategory($category, $import_storage);

        if ($result->isFailure()) {
            return $result;
        }

        $result->addMessage('category.created', __('commerceml.import.message.category.created', [
            '[id]'       => $category->id->getId(),
            '[local_id]' => $category->id->local_id,
        ]));

        $this->importCategoryTranslations($category, $import_storage);

        $import_storage->mapEntityId($category);
        $import_storage->removeEntity($category);

        $result->setData($category->getEntityId()->local_id);
        $result->setSuccess(true);

        return $result;
    }

    /**
     * Imports parent category
     *
     * @param \Tygh\Addons\CommerceML\Dto\CategoryDto        $category       Category DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage instance
     *
     * @return \Tygh\Common\OperationResult
     */
    private function importParentCategory(CategoryDto $category, ImportStorage $import_storage)
    {
        if (!$category->parent_id) {
            return new OperationResult(true, 0);
        }

        $parent_local_id = $import_storage->findEntityLocalId(CategoryDto::REPRESENT_ENTITY_TYPE, $category->parent_id);

        if ($parent_local_id->hasValue()) {
            $category->parent_id->local_id = $parent_local_id->asInt();
            return new OperationResult(true, $parent_local_id);
        }

        $parent_category = $import_storage->findEntity(CategoryDto::REPRESENT_ENTITY_TYPE, $category->parent_id->getId());

        if (!$parent_category || !$parent_category instanceof CategoryDto) {
            $result = new OperationResult();
            $result->addError('category.parent_category_not_found', __('commerceml.import.error.category.parent_category_not_found', [
                '[id]'        => $category->id->getId(),
                '[parent_id]' => $category->parent_id->getId()
            ]));

            return $result;
        }

        $parent_category_result = $this->import($parent_category, $import_storage);

        if ($parent_category_result->isFailure()) {
            $result = new OperationResult();
            $result->addError('category.parent_category_not_imported', __('commerceml.import.error.category.parent_category_not_imported', [
                '[id]'        => $category->id->getId(),
                '[parent_id]' => $category->parent_id->getId()
            ]));
            $result->merge($parent_category_result);

            return $result;
        }

        $category->parent_id->local_id = (int) $parent_category_result->getData();

        return $parent_category_result;
    }

    /**
     * Imports category data
     *
     * @param \Tygh\Addons\CommerceML\Dto\CategoryDto        $category       Category DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage instance
     *
     * @return \Tygh\Common\OperationResult
     */
    private function importCategory(CategoryDto $category, ImportStorage $import_storage)
    {
        $category_data = array_merge($category->properties->getValueMap(), [
            'company_id' => $import_storage->getImport()->company_id,
            'parent_id'  => $category->parent_id ? (int) $category->parent_id->local_id : 0,
            'category'   => (string) $category->name,
        ]);

        return $this->updateCategory($category, $category_data);
    }

    /**
     * Imports category descriptions
     *
     * @param \Tygh\Addons\CommerceML\Dto\CategoryDto        $category       Category DTO
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage instance
     */
    private function importCategoryTranslations(CategoryDto $category, ImportStorage $import_storage)
    {
        $lang_codes = (array) $import_storage->getSetting('lang_codes', []);

        foreach ($lang_codes as $lang_code) {
            $description_data = array_merge($category->properties->getTranslatableValueMap($lang_code), [
                'category' => $category->name && $category->name->hasTraslate($lang_code) ? $category->name->getTranslate($lang_code) : null,
            ]);
            $description_data = array_filter($description_data);

            if (!$description_data) {
                continue;
            }

            $this->updateCategory($category, $description_data, $lang_code);
        }
    }

    /**
     * Executes update|create category
     *
     * @param CategoryDto                     $category      Category DTO
     * @param array<string, string|int|array> $category_data Category data
     * @param string|null                     $lang_code     Language code
     *
     * @return \Tygh\Common\OperationResult
     */
    private function updateCategory(CategoryDto $category, $category_data, $lang_code = null)
    {
        $result = $this->product_storage->updateCategory(
            $category_data,
            (int) $category->id->local_id,
            $lang_code,
            sprintf('Category %s creating failed', $category->id->getId())
        );

        if (!$category->id->local_id && $result->isSuccess()) {
            $category->id->local_id = (int) $result->getData();
        }

        return $result;
    }
}
