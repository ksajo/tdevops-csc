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


namespace Tygh\Addons\CommerceML\Tests\Importers;


use Tygh\Addons\CommerceML\Dto\IdDto;
use Tygh\Addons\CommerceML\Dto\ProductDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto;
use Tygh\Addons\CommerceML\Dto\PropertyDto;
use Tygh\Addons\CommerceML\Dto\TranslatableValueDto;
use Tygh\Addons\CommerceML\Importers\CategoryImporter;
use Tygh\Addons\CommerceML\Importers\ProductFeatureImporter;
use Tygh\Addons\CommerceML\Importers\ProductImporter;
use Tygh\Addons\CommerceML\Importers\ProductVariationAsProductImporter;
use Tygh\Addons\CommerceML\Tests\Unit\StorageBasedTestCase;
use Tygh\Addons\CommerceML\Tests\Unit\TestProductStorage;
use Tygh\Addons\ProductVariations\Product\Group\Group;
use Tygh\Addons\ProductVariations\Product\Group\GroupFeatureCollection;
use Tygh\Addons\ProductVariations\Product\Group\GroupFeatureValue;
use Tygh\Addons\ProductVariations\Product\Group\GroupProduct;
use Tygh\Addons\ProductVariations\Product\Group\GroupProductCollection;
use Tygh\Addons\ProductVariations\Product\Group\Repository;
use Tygh\Addons\ProductVariations\Request\GenerateProductsAndAttachToGroupRequest;
use Tygh\Addons\ProductVariations\Service;
use Tygh\Common\OperationResult;

class ProductVariationImporterTest extends StorageBasedTestCase
{
    /**
     * @var Group[]
     */
    private $groups = [];

    protected function setUp()
    {
        $this->requireMockFunction('fn_set_hook');
        $this->requireMockFunction('__');

        parent::setUp();
    }

    public function testImport()
    {
        $import_storage = $this->getImportStorage();
        $product_storage = $this->getProductStorage();

        $category_importer = new CategoryImporter($product_storage);
        $product_feature_importer = new ProductFeatureImporter($product_storage);

        $product_importer = new ProductImporter(
            $category_importer,
            $product_feature_importer,
            $product_storage
        );

        $product_group_repository = $this->getProductGroupRepository();
        $product_variation_service = $this->getProductVariationService($product_storage);

        $importer = new ProductVariationAsProductImporter(
            $product_importer,
            $product_feature_importer,
            $product_group_repository,
            $product_variation_service,
            $product_storage
        );

        $variant1 = ProductFeatureVariantDto::create(
            IdDto::createByExternalId('28b6895a-24a9-11e0-aeec-0015e9b8c48d#1'),
            TranslatableValueDto::create('Зеленый')
        );
        $variant1->properties->add(PropertyDto::create('local_id', 1));

        $variant2 = ProductFeatureVariantDto::create(
            IdDto::createByExternalId('28b6895a-24a9-11e0-aeec-0015e9b8c48d#2'),
            TranslatableValueDto::create('Красный')
        );
        $variant2->properties->add(PropertyDto::create('local_id', 2));

        $product_feature = new ProductFeatureDto();
        $product_feature->id = IdDto::createByExternalId('28b6895a-24a9-11e0-aeec-0015e9b8c48d');
        $product_feature->name = TranslatableValueDto::create('Цвет');
        $product_feature->properties->add(PropertyDto::create('local_id', 1));
        $product_feature->variants[] = $variant1;
        $product_feature->variants[] = $variant2;

        $parent_product = new ProductDto();
        $parent_product->is_creatable = true;
        $parent_product->id = IdDto::createByExternalId('bd72d910-55bc-11d9-848a-00112f43529a');
        $parent_product->id->local_id = 1;
        $parent_product->name = TranslatableValueDto::create('Тайтсы GIVOVA SLIM');
        $parent_product->categories[] = new IdDto('16b791bb-816f-11e9-9c72-e0d55e229524', '1');

        $product1 = new ProductDto();
        $product1->is_variation = true;
        $product1->id = IdDto::createByExternalId('bd72d910-55bc-11d9-848a-00112f43529a#1');
        $product1->name = TranslatableValueDto::create('Тайтсы GIVOVA SLIM (Цвет зеленый)');
        $product1->parent_id = IdDto::createByExternalId('bd72d910-55bc-11d9-848a-00112f43529a');
        $product1->variation_feature_values->add(new ProductFeatureValueDto(
            IdDto::createByLocalId('28b6895a-24a9-11e0-aeec-0015e9b8c48d'),
            TranslatableValueDto::create('Зеленый'),
            IdDto::createByExternalId('28b6895a-24a9-11e0-aeec-0015e9b8c48d#1')
        ));

        $product2 = new ProductDto();
        $product2->is_variation = true;
        $product2->id = IdDto::createByExternalId('bd72d910-55bc-11d9-848a-00112f43529a#2');
        $product2->name = TranslatableValueDto::create('Тайтсы GIVOVA SLIM (Цвет красный)');
        $product2->parent_id = IdDto::createByExternalId('bd72d910-55bc-11d9-848a-00112f43529a');
        $product2->variation_feature_values->add(new ProductFeatureValueDto(
            IdDto::createByLocalId('28b6895a-24a9-11e0-aeec-0015e9b8c48d'),
            TranslatableValueDto::create('Красный'),
            IdDto::createByExternalId('28b6895a-24a9-11e0-aeec-0015e9b8c48d#2')
        ));

        $import_storage->saveEntities([
            $variant1,
            $variant2,
            $product_feature,
            $parent_product
        ]);

        $product_feature_importer->import($product_feature, $import_storage);
        $product_importer->import($parent_product, $import_storage);

        $result = $importer->import($product1, $import_storage);

        $this->assertTrue($result->isSuccess());

        $result = $importer->import($product2, $import_storage);

        $this->assertTrue($result->isSuccess());
        $this->assertCount(2, $product_storage->products);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Service
     */
    public function getProductVariationService(TestProductStorage $product_storage)
    {
        $mock = $this->getMockBuilder(Service::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateProductsAndAttachToGroup', 'createGroup'])
            ->getMock();

        $mock->method('createGroup')->willReturnCallback(function ($product_ids, $code, GroupFeatureCollection $group_feature_collection) {
            $result = new OperationResult(true);
            $gropu_product_collection = new GroupProductCollection();
            $products_status = [];

            foreach ($product_ids as $product_id) {
                $products_status[$product_id] = Group::RESULT_ADDED;

                if ($product_id == 1) {
                    $gropu_product_collection->addProduct(GroupProduct::create($product_id, 0, 1, []));
                } else {
                    $gropu_product_collection->addProduct(GroupProduct::create($product_id, 0, 1, []));
                }
            }

            $result->setData($products_status, 'products_status');

            $id = count($this->groups) + 1;
            $group = Group::createFromArray([
                'id' => $id,
                'code' => 'CODE_' . $id,
                'features' => $group_feature_collection->toArray(),
                'products' => $gropu_product_collection->toArray()
            ]);

            $this->groups[$id] = $group;

            return $result;
        });

        $mock->method('generateProductsAndAttachToGroup')->willReturnCallback(function (GenerateProductsAndAttachToGroupRequest $request) use ($product_storage) {
            $result = new OperationResult(true);
            $group = $this->groups[$request->getGroupId()];
            $product_id = 100;

            foreach ($request->getCombinationIds() as $combination_id) {
                $variant_ids = explode('_', $combination_id);
                $feature_values = [];
                $features = $group->getFeatures();

                foreach ($variant_ids as $variant_id) {
                    $feature = $features->current();
                    $features->next();

                    $feature_values[] = GroupFeatureValue::create($feature->getFeatureId(), $feature->getFeaturePurpose(), $variant_id);
                }

                $group_product = GroupProduct::create($product_id, $request->getBaseProductId(), 1, $feature_values);

                $group->attachProduct($group_product);
                $parent_product = $product_storage->products[$request->getBaseProductId()];
                $product_storage->updateProduct($parent_product, $product_id);

                $product_id++;
            }

            $result->setData($group, 'group');
            return $result;
        });

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Repository
     */
    public function getProductGroupRepository()
    {
        $mock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findGroupIdByProductId', 'findGroupById'])
            ->getMock();

        $mock->method('findGroupIdByProductId')->willReturnCallback(function ($product_id) {
            foreach ($this->groups as $group) {
                if ($group->hasProduct($product_id)) {
                    return $group->getId();
                }
            }

            return null;
        });

        $mock->method('findGroupById')->willReturnCallback(function ($group_id) {
            return isset($this->groups[$group_id]) ? $this->groups[$group_id] : null;
        });

        return $mock;
    }
}