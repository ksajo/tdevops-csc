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


namespace Tygh\Addons\CommerceML\Tests\Unit;


use Tygh\Addons\CommerceML\Convertors\CategoryConvertor;
use Tygh\Addons\CommerceML\Convertors\OrderConvertor;
use Tygh\Addons\CommerceML\Convertors\PriceTypeConvertor;
use Tygh\Addons\CommerceML\Convertors\ProductConvertor;
use Tygh\Addons\CommerceML\Convertors\ProductFeatureConvertor;
use Tygh\Addons\CommerceML\Dto\CategoryDto;
use Tygh\Addons\CommerceML\Dto\CurrencyDto;
use Tygh\Addons\CommerceML\Dto\ImportDto;
use Tygh\Addons\CommerceML\Dto\ImportItemDto;
use Tygh\Addons\CommerceML\Dto\LocalIdDto;
use Tygh\Addons\CommerceML\Dto\PriceTypeDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto;
use Tygh\Addons\CommerceML\Dto\TaxDto;
use Tygh\Addons\CommerceML\Dto\TranslatableValueDto;
use Tygh\Addons\CommerceML\Repository\ImportEntityMapRepository;
use Tygh\Addons\CommerceML\Repository\ImportEntityRepository;
use Tygh\Addons\CommerceML\Repository\ImportRemovedEntityRepository;
use Tygh\Addons\CommerceML\Repository\ImportRepository;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Storages\ProductStorage;
use Tygh\Addons\CommerceML\Xml\SimpleXmlElement;
use Tygh\Addons\CommerceML\Xml\XmlParser;
use Tygh\Common\OperationResult;

class StorageBasedTestCase extends BaseXmlTestCase
{
    public function getImportStorage(ImportDto $import = null, array $settings = [])
    {
        if ($import === null) {
            $import = new ImportDto();
            $import->company_id = 1;
            $import->import_key = 'import_key';
        }
        $settings['lang_codes'] = ['en', 'ru'];

        return new TestImportStorage(
            $import,
            new TestImportRepository(),
            new TestImportEntityRepository(),
            new TestImportEntityMapRepository(),
            new TestImportRemovedEntityRepository(),
            $settings
        );
    }

    public function getProductStorage()
    {
        return new TestProductStorage('ru');
    }

    public function getXmlParser()
    {
        return new XmlParser();
    }

    public function getParserCallbacksCatalog()
    {
        return [
            'classifier/properties/property'   => static function (SimpleXmlElement $xml, ImportStorage $import_storage) {
                (new ProductFeatureConvertor())->convert($xml, $import_storage);
            },
            'classifier/groups/group'          => static function (SimpleXmlElement $xml, ImportStorage $import_storage) {
                (new CategoryConvertor(new ProductFeatureConvertor()))->convert($xml, $import_storage);
            },
            'catalog/products/product'         => static function (SimpleXmlElement $xml, ImportStorage $import_storage) {
                (new ProductConvertor(new TranslatableValueDto('variant'), new TranslatableValueDto('brand'), ProductFeatureDto::BRAND_EXTERNAL_ID))->convert($xml, $import_storage, true);
            },
            'packages/prices_types/price_type' => static function (SimpleXmlElement $xml, ImportStorage $import_storage) {
                (new PriceTypeConvertor())->convert($xml, $import_storage);
            },
            'packages/offers/offer'            => static function (SimpleXmlElement $xml, ImportStorage $import_storage) {
                (new ProductConvertor(new TranslatableValueDto('variant'), new TranslatableValueDto('brand'), ProductFeatureDto::BRAND_EXTERNAL_ID))->convert($xml, $import_storage, false);
            }
        ];
    }

    public function getParserCallbacksSale()
    {
        return [
            'document' => static function (SimpleXmlElement $xml, ImportStorage $import_storage) {
                (new OrderConvertor())->convert($xml, $import_storage);
            }
        ];
    }
}

class TestProductStorage extends ProductStorage
{
    public $products = [];

    public $categories = [];

    public $product_features = [];

    public $product_features_values = [];

    public $default_language_code;

    /**
     * @inheritDoc
     */
    public function __construct($default_language_code)
    {
        $this->default_language_code = $default_language_code;
        parent::__construct($default_language_code);
    }

    /**
     * @inheritDoc
     */
    public function updateProduct($product_data, $product_id, $lang_code = null, $error_message = null)
    {
        $lang_code = $lang_code ? $lang_code : $this->default_language_code;
        if (isset($product_data['local_id'])) {
            $product_id = $product_data['local_id'];
        }

        if (!$product_id) {
            $product_id = count($this->products) + 1;
        }

        if (!isset($this->products[$product_id][$lang_code])) {
            $this->products[$product_id][$lang_code] = [];
        }

        if (isset($this->products[$product_id][$lang_code]['product_features'], $product_data['product_features'])) {
            $product_data['product_features'] = array_replace($this->products[$product_id][$lang_code]['product_features'], $product_data['product_features']);
        }

        $this->products[$product_id][$lang_code] = array_merge($this->products[$product_id][$lang_code], $product_data);

        return new OperationResult(true, $product_id);
    }

    /**
     * @inheritDoc
     */
    public function updateProductFeaturesValues($product_id, array $product_features_values, $error_message = null)
    {
        $this->product_features_values[$product_id] = $product_features_values;
        return new OperationResult(true);
    }

    /**
     * @inheritDoc
     */
    public function updateCategory($category_data, $category_id, $lang_code = null, $error_message = null)
    {
        $lang_code = $lang_code ? $lang_code : $this->default_language_code;
        if (isset($category_data['local_id'])) {
            $category_id = $category_data['local_id'];
        }

        if (!$category_id) {
            $category_id = count($this->categories) + 1;
        }

        if (!isset($this->categories[$category_id][$lang_code])) {
            $this->categories[$category_id][$lang_code] = [];
        }

        $this->categories[$category_id][$lang_code] = array_merge($this->categories[$category_id][$lang_code], $category_data);

        return new OperationResult(true, $category_id);
    }

    /**
     * @inheritDoc
     */
    public function updateProductFeature($product_feature_data, $feature_id, $lang_code, $error_message = null)
    {
        $lang_code = $lang_code ? $lang_code : $this->default_language_code;
        if (isset($product_feature_data['local_id'])) {
            $feature_id = $product_feature_data['local_id'];
        }

        if (!$feature_id) {
            $feature_id = count($this->product_features) + 1;
        }

        if (!isset($this->product_features[$feature_id][$lang_code])) {
            $this->product_features[$feature_id][$lang_code] = [];
        }

        $this->product_features[$feature_id][$lang_code] = array_merge($this->product_features[$feature_id][$lang_code], $product_feature_data);

        return new OperationResult(true, $feature_id);
    }

    /**
     * @inheritDoc
     */
    public function updateProductFeatureVariants(
        $product_feature_data, $feature_id, $lang_code = null, $error_message = null
    ) {
        $lang_code = $lang_code ? $lang_code : $this->default_language_code;
        if (!isset($this->product_features[$feature_id][$lang_code]['variants'])) {
            $this->product_features[$feature_id][$lang_code]['variants'] = [];
        }

        foreach ($product_feature_data['variants'] as &$variant) {
            if (isset($variant['local_id'])) {
                $variant['variant_id'] = $variant['local_id'];
            }

            if (!isset($variant['variant_id'])) {
                $variant['variant_id'] = count($this->product_features[$feature_id][$lang_code]['variants']) + 1;
            }

            $this->product_features[$feature_id][$lang_code]['variants'][$variant['variant_id']] = $variant;
            unset($this->product_features[$feature_id][$lang_code]['variants'][$variant['variant_id']]['import_dto']);
        }
        unset($variant);

        return new OperationResult(true, $product_feature_data);
    }

    /**
     * @inheritDoc
     */
    public function removeProduct($product_id, $error_message = null)
    {
        unset($this->products[$product_id]);
        return new OperationResult(true);
    }

    /**
     * @inheritDoc
     */
    public function updateProductImages($product_id, array $pair_data_list, array $detailed_image_data_list)
    {
        if (!isset($this->products[$product_id][$this->default_language_code]['images'])) {
            $this->products[$product_id][$this->default_language_code]['images'] = [];
        }

        foreach ($pair_data_list as $key => $pair_data) {
            $detailed_image_data = $detailed_image_data_list[$key];

            $pair_id = isset($pair_data['pair_id']) ? $pair_data['pair_id'] : count($this->products[$product_id][$this->default_language_code]['images']) + 1;

            $this->products[$product_id][$this->default_language_code]['images'][$pair_id] = [
                'pair_id'     => $pair_id,
                'image_path'  => $detailed_image_data['path'],
                'object_id'   => $product_id,
                'object_type' => 'product',
                'detailed_id' => $pair_id,
                'type'        => $pair_data['type']
            ];
        }

        return new OperationResult(true);
    }

    /**
     * @inheritDoc
     */
    public function removeImagePairs(array $pair_ids)
    {
        foreach ($this->products as &$product) {
            foreach ($pair_ids as $pair_id) {
                if (!isset($product[$this->default_language_code]['images'][$pair_id])) {
                    continue;
                }
                unset($product[$this->default_language_code]['images'][$pair_id]);
            }
        }
        unset($product);
    }

    /**
     * @inheritDoc
     */
    public function findProductFeatures(array $product_feautre_ids = [])
    {
        $result = [];

        foreach ($product_feautre_ids as $feautre_id) {
            if (!isset($this->product_features[$feautre_id])) {
                continue;
            }

            $result[$feautre_id] = $this->product_features[$feautre_id][$this->default_language_code];
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getRawProductData($product_id)
    {
        return isset($this->products[$product_id][$this->default_language_code]) ? $this->products[$product_id][$this->default_language_code] : [];
    }

    /**
     * @inheritDoc
     */
    public function findProductIdByProductCode($product_code, $company_id)
    {
        foreach ($this->products as $product) {
            if (!isset($product[$this->default_language_code]) || !isset($product[$this->default_language_code]['product_code'])) {
                continue;
            }

            if ($product[$this->default_language_code]['product_code'] == $product_code) {
                return $product[$this->default_language_code];
            }
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function findCategoryIdByName($name, $company_id)
    {
        foreach ($this->categories as $category) {
            if (!isset($category[$this->default_language_code]) || !isset($category[$this->default_language_code]['category'])) {
                continue;
            }

            if ($category[$this->default_language_code]['category'] == $name) {
                return $category[$this->default_language_code];
            }
        }

        return [];
    }

    /**
     * Finds currency data by code
     *
     * @param string $currency_code Currency code
     *
     * @return array{currency_code: string, description: string, coefficient: float}|null
     */
    public function findCurrency($currency_code)
    {
        $currencies = [
            'RUB' => [
                'currency_code' => 'RUB',
                'description' => 'RUB',
                'coefficient' => 1
            ],
            'USD' => [
                'currency_code' => 'USD',
                'description' => 'USD',
                'coefficient' => 69.9
            ],
        ];

        return isset($currencies[$currency_code]) ? (array) $currencies[$currency_code] : null;
    }

    public function getProductFeatureMap()
    {
        $map = [];

        foreach ($this->product_features as $id => $product_feature) {
            $map[$product_feature['ru']['description']] = $id;
        }

        return $map;
    }

    public function getProductFeatureVariantMap()
    {
        $map = [];

        foreach ($this->product_features as $id => $product_feature) {
            if (empty($product_feature['ru']['variants'])) {
                continue;
            }

            foreach ($product_feature['ru']['variants'] as $variant_id => $variant) {
                $map[$product_feature['ru']['description']][$variant['variant']] = $variant_id;
            }
        }

        return $map;
    }
}

class TestImportStorage extends ImportStorage
{
    private $import;
    private $import_repository;
    private $import_entity_repository;
    private $import_entity_map_repository;
    private $import_removed_entity_repository;

    /**
     * @inheritDoc
     */
    public function __construct(
        ImportDto $import,
        TestImportRepository $import_repository,
        TestImportEntityRepository $import_entity_repository,
        TestImportEntityMapRepository $import_entity_map_repository,
        TestImportRemovedEntityRepository $import_removed_entity_repository,
        array $settings = []
    ) {
        $this->import = $import;
        $this->import_repository = $import_repository;
        $this->import_entity_repository = $import_entity_repository;
        $this->import_entity_map_repository = $import_entity_map_repository;
        $this->import_removed_entity_repository = $import_removed_entity_repository;

        $mappable_entity_types = [
            CategoryDto::REPRESENT_ENTITY_TYPE,
            TaxDto::REPRESENT_ENTITY_TYPE,
            PriceTypeDto::REPRESENT_ENTITY_TYPE,
            ProductFeatureDto::REPRESENT_ENTITY_TYPE,
            ProductFeatureVariantDto::REPRESENT_ENTITY_TYPE,
            CurrencyDto::REPRESENT_ENTITY_TYPE
        ];

        parent::__construct($import, $import_repository, $import_entity_repository, $import_entity_map_repository, $import_removed_entity_repository, $mappable_entity_types, $settings);
    }

    /**
     * @return \Tygh\Addons\CommerceML\Repository\ImportRemovedEntityRepository|\Tygh\Addons\CommerceML\Tests\Unit\TestImportRemovedEntityRepository
     */
    public function getImportRemoveEntityRepository()
    {
        return $this->import_removed_entity_repository;
    }

    /**
     * @return \Tygh\Addons\CommerceML\Repository\ImportEntityMapRepository|\Tygh\Addons\CommerceML\Tests\Unit\TestImportEntityMapRepository
     */
    public function getImportEntityMapRepository()
    {
        return $this->import_entity_map_repository;
    }

    /**
     * @return \Tygh\Addons\CommerceML\Repository\ImportEntityRepository|\Tygh\Addons\CommerceML\Tests\Unit\TestImportEntityRepository
     */
    public function getImportEntityRepository()
    {
        return $this->import_entity_repository;
    }

    /**
     * @return \Tygh\Addons\CommerceML\Repository\ImportRepository|\Tygh\Addons\CommerceML\Tests\Unit\TestImportRepository
     */
    public function getImportRepository()
    {
        return $this->import_repository;
    }

    public function newInstance(array $settings)
    {
        return new self(
            $this->getImport(),
            $this->getImportRepository(),
            $this->getImportEntityRepository(),
            $this->getImportEntityMapRepository(),
            $this->getImportRemoveEntityRepository(),
            $settings
        );
    }
}

class TestImportRepository extends ImportRepository
{
    public $storage = [];

    /**
     * @inheritDoc
     */
    public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    public function save(ImportDto $import)
    {
        if (!$import->import_id) {
            $import->import_id = count($this->storage) + 1;
        }

        $this->storage[$import->import_id] = $import;

        return $import;
    }

    /**
     * @inheritDoc
     */
    public function findById($import_id)
    {
        return isset($this->storage[$import_id]) ? $this->storage[$import_id] : null;
    }

    /**
     * @inheritDoc
     */
    public function remove($import_id)
    {
        unset($this->storage[$import_id]);
    }
}

class TestImportRemovedEntityRepository extends ImportRemovedEntityRepository
{
    public $storage = [];

    /**
     * @inheritDoc
     */
    public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    public function add($company_id, $entity_type, $entity_id)
    {
        $this->storage[] = [
            'company_id'  => $company_id,
            'entity_type' => $entity_type,
            'entity_id'   => $entity_id,
        ];
    }

    /**
     * @inheritDoc
     */
    public function remove($company_id, $entity_type, $entity_id)
    {
        foreach ($this->storage as $key => $item) {
            if ($item['company_id'] == $company_id && $item['entity_type'] == $entity_type && $item['entity_id'] == $entity_id) {
                unset($this->storage[$key]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function exists($company_id, $entity_type, $entity_id)
    {
        foreach ($this->storage as $key => $item) {
            if ($item['company_id'] == $company_id && $item['entity_type'] == $entity_type && $item['entity_id'] == $entity_id) {
                return true;
            }
        }

        return false;
    }
}

class TestImportEntityRepository extends ImportEntityRepository
{
    /** @var ImportItemDto[] */
    public $storage = [];

    public $index = 0;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    public function save(ImportItemDto $entity)
    {
        $this->storage[] = $entity;
    }

    /**
     * @inheritDoc
     */
    public function batchSave(array $entities)
    {
        foreach ($entities as $entity) {
            $this->save($entity);
        }
    }

    /**
     * @inheritDoc
     */
    public function findEntityData($import_id, $entity_type, $entity_id)
    {
        foreach ($this->storage as $item) {
            if ($item->import_id == $import_id && $item->entity_type == $entity_type && $item->entity_id == $entity_id) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function removeByImportId($import_id)
    {
        foreach ($this->storage as $key => $item) {
            if ($item->import_id == $import_id) {
                unset($this->storage[$key]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function remove($import_id, $entity_type, $entity_id)
    {
        foreach ($this->storage as $key => $item) {
            if ($item->import_id == $import_id && $item->entity_type == $entity_type && $item->entity_id == $entity_id) {
                unset($this->storage[$key]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function findNextRecord($import_id, $entity_type, $process_id)
    {
        foreach ($this->storage as $key => $item) {
            if ($item->import_id == $import_id && $item->entity_type == $entity_type && $key > $this->index) {
                $this->index = $key;
                return $item;
            }
        }

        $this->index = 0;
        return null;
    }

    public function getEntites()
    {
        $result = [];

        foreach ($this->storage as $item) {
            $result[] = $item->entity;
        }

        return $result;
    }
}

class TestImportEntityMapRepository extends ImportEntityMapRepository
{
    public $storage = [];

    /**
     * @inheritDoc
     */
    public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    public function findAll(array $params)
    {
        return [[], []];
    }

    /**
     * @inheritDoc
     */
    public function findEntityIds($entity_type, $local_id, $company_id = null)
    {
        $result = [];

        foreach ($this->storage as $key => $item) {
            if (
                $item['entity_type'] == $entity_type
                && ($item['company_id'] == $company_id || $company_id === null)
                && $item['local_id'] == $local_id
            ) {
                $result[] = $item['entity_id'];
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function findLocalId($company_id, $entity_type, $entity_id)
    {
        foreach ($this->storage as $key => $item) {
            if (
                $item['entity_type'] == $entity_type
                && $item['company_id'] == $company_id
                && $item['entity_id'] == $entity_id
            ) {
                return $item['local_id'] ?: null;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function add($company_id, $entity_type, $entity_id, $local_id, $entity_name = null)
    {
        $key = $company_id . $entity_type . $entity_id;

        $this->storage[$key] = [
            'company_id'  => $company_id,
            'entity_type' => $entity_type,
            'entity_id'   => $entity_id,
            'local_id'    => $local_id,
            'entity_name' => $entity_name,
        ];
    }

    /**
     * @inheritDoc
     */
    public function batchAdd(array $records)
    {
        foreach ($records as $record) {
            $this->storage[] = $record;
        }
    }

    /**
     * @inheritDoc
     */
    public function updateTimestamp($company_id, $entity_type, $entity_id, $timestamp = null)
    {
    }

    /**
     * @inheritDoc
     */
    public function removeByLocalId($entity_type, $local_id)
    {
        $this->removeByLocalIds($entity_type, (array) $local_id);
    }

    /**
     * @inheritDoc
     */
    public function removeByLocalIds($entity_type, array $local_ids)
    {
        foreach ($this->storage as $key => $item) {
            if ($item['entity_type'] == $entity_type && in_array($item['local_id'], $local_ids)) {
                unset($this->storage[$key]);
            }
        }
    }

    public function getIdMap()
    {
        $result = [];

        foreach ($this->storage as $item) {
            $result[$item['entity_id']] = $item['local_id'];
        }

        return $result;
    }
}
