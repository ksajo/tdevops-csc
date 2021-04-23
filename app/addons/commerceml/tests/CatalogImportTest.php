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


use Tygh\Addons\CommerceML\Commands\CreateImportCommand;
use Tygh\Addons\CommerceML\Commands\CreateImportCommandHandler;
use Tygh\Addons\CommerceML\Dto\CategoryDto;
use Tygh\Addons\CommerceML\Dto\LocalIdDto;
use Tygh\Addons\CommerceML\Dto\ProductDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureDto;
use Tygh\Addons\CommerceML\Importers\CategoryImporter;
use Tygh\Addons\CommerceML\Importers\ProductFeatureImporter;
use Tygh\Addons\CommerceML\Importers\ProductImporter;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Storages\ProductStorage;
use Tygh\Enum\ProductFeatures;
use Tygh\Addons\ProductVariations\Service as ProductVariationsService;
use Tygh\Addons\ProductVariations\Product\Group\Repository as ProductVariationsGroupRepository;

class CatalogImportTest extends StorageBasedTestCase
{
    protected function setUp()
    {
        $this->requireMockFunction('fn_set_hook');
        $this->requireMockFunction('__');

        parent::setUp();
    }

    public function testUpdateProductName()
    {
        $import_storage = $this->getImportStorage(null);
        $product_storage = $this->getProductStorage();
        $handler = $this->getHandler($import_storage);

        $this->import($handler, $import_storage, $product_storage);

        $this->assertEquals('Женские ботфорты', $product_storage->products[1]['ru']['product']);

        $product_storage->products[1]['ru']['product'] = 'Changed product name';

        $import_storage = $import_storage->newInstance([
            'catalog_importer.allow_update_product_name' => true
        ]);
        $this->import($handler, $import_storage, $product_storage);

        $this->assertEquals('Женские ботфорты', $product_storage->products[1]['ru']['product']);
        $product_storage->products[1]['ru']['product'] = 'Changed product name';

        $import_storage = $import_storage->newInstance([
            'catalog_importer.allow_update_product_name' => false
        ]);
        $this->import($handler, $import_storage, $product_storage);

        $this->assertEquals('Changed product name', $product_storage->products[1]['ru']['product']);
    }

    public function testUpdateProductCode()
    {
        $import_storage = $this->getImportStorage(null);
        $product_storage = $this->getProductStorage();
        $handler = $this->getHandler($import_storage);

        $this->import($handler, $import_storage, $product_storage);

        $this->assertEquals('Б-130001', $product_storage->products[1]['ru']['product_code']);

        $product_storage->products[1]['ru']['product_code'] = 'Changed product code';

        $import_storage = $import_storage->newInstance([
            'catalog_importer.allow_update_product_code' => true
        ]);
        $this->import($handler, $import_storage, $product_storage);

        $this->assertEquals('Б-130001', $product_storage->products[1]['ru']['product_code']);
        $product_storage->products[1]['ru']['product_code'] = 'Changed product code';

        $import_storage = $import_storage->newInstance([
            'catalog_importer.allow_update_product_code' => false
        ]);
        $this->import($handler, $import_storage, $product_storage);

        $this->assertEquals('Changed product code', $product_storage->products[1]['ru']['product_code']);
    }

    public function testUpdateProductBrand()
    {
        $import_storage = $this->getImportStorage(null);
        $product_storage = $this->getProductStorage();
        $handler = $this->getHandler($import_storage);

        $this->import($handler, $import_storage, $product_storage);

        $brand_feature_id = null;
        $base_brand_variant_id = null;

        foreach ($product_storage->product_features as $feature_id => $product_feature) {
            if (
                empty($product_feature[$product_storage->default_language_code]['feature_type'])
                || $product_feature[$product_storage->default_language_code]['feature_type'] !== ProductFeatures::EXTENDED
            ) {
                continue;
            }
            $brand_feature_id = $feature_id;

            foreach ($product_feature[$product_storage->default_language_code]['variants'] as $variant) {
                if ($variant['variant'] !== 'ООО Изготовитель') {
                    continue;
                }

                $base_brand_variant_id = $variant['variant_id'];
                break;
            }

            break;
        }

        $this->assertNotNull($brand_feature_id);
        $this->assertNotNull($base_brand_variant_id);
        $this->assertEquals($base_brand_variant_id, $product_storage->products[1]['ru']['product_features'][$brand_feature_id]);

        $product_storage->products[1]['ru']['product_features'][$brand_feature_id] = 500;

        $import_storage = $import_storage->newInstance([]);
        $this->import($handler, $import_storage, $product_storage);

        $this->assertEquals($base_brand_variant_id, $product_storage->products[1]['ru']['product_features'][$brand_feature_id]);
    }

    public function testImportCategory()
    {
        $import_storage = $this->getImportStorage(null, [
            'allow_manage_categories' => true,
            'catalog_importer.allow_import_categories' => true
        ]);
        $product_storage = $this->getProductStorage();
        $handler = $this->getHandler($import_storage);

        $this->import($handler, $import_storage, $product_storage);

        $this->assertNotEmpty($product_storage->products[1]['ru']['category_ids']);

        $import_storage = $this->getImportStorage(null, [
            'allow_manage_categories'                  => false,
            'catalog_importer.allow_import_categories' => false,
            'catalog_importer.default_category_id'     => 100,
            'mapping.category.default_variant'         => LocalIdDto::VALUE_USE_DEFAULT
        ]);
        $product_storage = $this->getProductStorage();
        $handler = $this->getHandler($import_storage);

        $this->import($handler, $import_storage, $product_storage);

        $this->assertNotEmpty($product_storage->products[1]['ru']['category_ids']);
        $this->assertEquals([100], $product_storage->products[1]['ru']['category_ids']);

        $import_storage = $this->getImportStorage(null, [
            'allow_manage_categories'                  => true,
            'catalog_importer.allow_import_categories' => false,
            'catalog_importer.default_category_id'     => 100,
            'mapping.category.default_variant'         => LocalIdDto::VALUE_USE_DEFAULT
        ]);
        $import_storage->getImportEntityMapRepository()->add(
            $import_storage->getImport()->company_id,
            CategoryDto::REPRESENT_ENTITY_TYPE,
            'bd72d90e-55bc-11d9-848a-00112f43529a',
            LocalIdDto::VALUE_CREATE
        );
        $product_storage = $this->getProductStorage();
        $handler = $this->getHandler($import_storage);

        $this->import($handler, $import_storage, $product_storage);

        $this->assertNotEmpty($product_storage->products[1]['ru']['category_ids']);
        $this->assertNotEquals([100], $product_storage->products[1]['ru']['category_ids']);
    }

    public function testImportFeature()
    {
        $import_storage = $this->getImportStorage(null, [
            'allow_manage_features' => true,
            'catalog_importer.allow_import_features' => true
        ]);
        $product_storage = $this->getProductStorage();
        $handler = $this->getHandler($import_storage);

        $this->import($handler, $import_storage, $product_storage);

        $this->assertArrayHasKey('Бренд', $product_storage->getProductFeatureMap());
        $this->assertArrayHasKey('Format', $product_storage->getProductFeatureVariantMap()['Бренд']);
        $this->assertArrayHasKey('Zend', $product_storage->getProductFeatureVariantMap()['Бренд']);


        $import_storage = $this->getImportStorage(null, [
            'allow_manage_features' => false,
            'catalog_importer.allow_import_features' => false
        ]);
        $product_storage = $this->getProductStorage();
        $handler = $this->getHandler($import_storage);

        $this->import($handler, $import_storage, $product_storage);

        $this->assertArrayNotHasKey('Бренд', $product_storage->getProductFeatureMap());
        $this->assertArrayNotHasKey('Бренд', $product_storage->getProductFeatureVariantMap());

        $import_storage = $this->getImportStorage(null, [
            'allow_manage_features' => true,
            'catalog_importer.allow_import_features' => false
        ]);
        $import_storage->getImportEntityMapRepository()->add(
            $import_storage->getImport()->company_id,
            ProductFeatureDto::REPRESENT_ENTITY_TYPE,
            '9d68a9cd-baf9-11e6-853e-60a44c5c87dc',
            LocalIdDto::VALUE_CREATE
        );
        $product_storage = $this->getProductStorage();
        $handler = $this->getHandler($import_storage);

        $this->import($handler, $import_storage, $product_storage);

        $feature_map = $product_storage->getProductFeatureMap();
        $feature_variant_map = $product_storage->getProductFeatureVariantMap();

        $this->assertArrayHasKey('Бренд', $feature_map);
        $this->assertArrayHasKey('Format', $feature_variant_map['Бренд']);
        $this->assertArrayHasKey('Zend', $feature_variant_map['Бренд']);
        $this->assertEquals($feature_variant_map['Бренд']['Format'], $product_storage->products[1]['ru']['product_features'][$feature_map['Бренд']]);

        $import_storage = $this->getImportStorage(null, [
            'allow_manage_features' => true,
            'catalog_importer.allow_import_features' => true
        ]);
        $import_storage->getImportEntityMapRepository()->add(
            $import_storage->getImport()->company_id,
            ProductFeatureDto::REPRESENT_ENTITY_TYPE,
            '9d68a9cd-baf9-11e6-853e-60a44c5c87dc',
            LocalIdDto::VALUE_SKIP
        );
        $product_storage = $this->getProductStorage();
        $handler = $this->getHandler($import_storage);

        $this->import($handler, $import_storage, $product_storage);

        $feature_map = $product_storage->getProductFeatureMap();
        $feature_variant_map = $product_storage->getProductFeatureVariantMap();

        $this->assertArrayNotHasKey('Бренд', $feature_map);
        $this->assertArrayNotHasKey('Бренд', $feature_variant_map);
        $this->assertCount(2, $product_storage->products[1]['ru']['product_features']);
    }

    private function getHandler(ImportStorage $import_storage)
    {
        return new CreateImportCommandHandler(
            function () use ($import_storage) {
                return $import_storage;
            },
            $this->getXmlParser(),
            function () {
                return $this->getParserCallbacksCatalog();
            }
        );
    }

    private function import(CreateImportCommandHandler $handler, ImportStorage $import_storage, ProductStorage $product_storage)
    {
        $command = new CreateImportCommand();
        $command->company_id = 1;
        $command->user_id = 1;
        $command->xml_file_paths = [
            __DIR__ . '/data/import.xml',
        ];
        $command->import_type = 'catalog';

        $handler->handle($command);

        $product_importer = (new ProductImporter(
            new CategoryImporter($product_storage),
            new ProductFeatureImporter($product_storage),
            $product_storage
        ));

        foreach ($import_storage->getQueue(ProductDto::REPRESENT_ENTITY_TYPE, '1') as $import_item) {
            if (!isset($import_item->entity) || !$import_item->entity instanceof ProductDto) {
                continue;
            }

            $product_importer->import($import_item->entity, $import_storage);
        }
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ProductVariationsService
     */
    public function getProductVariationService()
    {
        $mock = $this->getMockBuilder(ProductVariationsService::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateProductsAndAttachToGroup', 'createGroup'])
            ->getMock();

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ProductVariationsGroupRepository
     */
    public function getProductGroupRepository()
    {
        $mock = $this->getMockBuilder(ProductVariationsGroupRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }
}