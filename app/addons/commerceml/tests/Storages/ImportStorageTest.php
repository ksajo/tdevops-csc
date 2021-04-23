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


namespace Tygh\Addons\CommerceML\Tests\Unit\Storages;


use Tygh\Addons\CommerceML\Dto\CategoryDto;
use Tygh\Addons\CommerceML\Dto\IdDto;
use Tygh\Addons\CommerceML\Dto\ImportDto;
use Tygh\Addons\CommerceML\Dto\ImportItemDto;
use Tygh\Addons\CommerceML\Dto\LocalIdDto;
use Tygh\Addons\CommerceML\Dto\ProductDto;
use Tygh\Addons\CommerceML\Repository\ImportRemovedEntityRepository;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Repository\ImportEntityMapRepository;
use Tygh\Addons\CommerceML\Repository\ImportEntityRepository;
use Tygh\Addons\CommerceML\Repository\ImportRepository;
use Tygh\Tests\Unit\ATestCase;

class ImportStorageTest extends ATestCase
{
    /**
     * @var \Tygh\Addons\CommerceML\Dto\ImportItemDto[]
     */
    public $values = [];

    public function getImportStorage()
    {
        $import = ImportDto::fromArray([
            'import_id' => 100,
        ]);

        return new ImportStorage(
            $import,
            $this->getImportRepositry(),
            $this->getImportEntityRepositry(),
            $this->getImportEntityMapRepositry(),
            $this->getImportRemovedEntityRepositry(),
            []
        );
    }

    public function testSaveEntities()
    {
        $storage = $this->getImportStorage();

        $product1 = new ProductDto();
        $product1->id = IdDto::createByExternalId('product1');

        $product2 = new ProductDto();
        $product2->id = IdDto::createByExternalId('product2');

        $category = new CategoryDto();
        $category->id = IdDto::createByExternalId('category');

        $storage->saveEntities([$product1, $product2, $category]);

        $this->assertCount(3, $this->values);

        $this->assertEquals(ProductDto::REPRESENT_ENTITY_TYPE, $this->values[0]->entity_type);
        $this->assertEquals('product1', $this->values[0]->entity_id);
        $this->assertEquals(100, $this->values[0]->import_id);
        $this->assertInstanceOf(ProductDto::class, $this->values[0]->entity);

        $this->assertEquals(ProductDto::REPRESENT_ENTITY_TYPE, $this->values[1]->entity_type);
        $this->assertEquals('product2', $this->values[1]->entity_id);
        $this->assertEquals(100, $this->values[1]->import_id);
        $this->assertInstanceOf(ProductDto::class, $this->values[1]->entity);

        $this->assertEquals(CategoryDto::REPRESENT_ENTITY_TYPE, $this->values[2]->entity_type);
        $this->assertEquals('category', $this->values[2]->entity_id);
        $this->assertEquals(100, $this->values[2]->import_id);
        $this->assertInstanceOf(CategoryDto::class, $this->values[2]->entity);
    }

    public function testFindEntity()
    {
        $storage = $this->getImportStorage();

        /** @var ProductDto $product */
        $product = $storage->findEntity(ProductDto::REPRESENT_ENTITY_TYPE, 'product1');

        $this->assertInstanceOf(ProductDto::class, $product);
        $this->assertEquals('product1', $product->getEntityId()->getId());

        $product = clone $product;
        $product->price = 100;

        $storage->saveEntities([$product]);

        /** @var ProductDto $product */
        $product = $storage->findEntity(ProductDto::REPRESENT_ENTITY_TYPE, 'product1');

        $this->assertEquals('product1', $product->getEntityId()->getId());
        $this->assertEquals(100, $product->price);
    }

    public function testRemoveEntity()
    {
        $storage = $this->getImportStorage();

        /** @var ProductDto $product */
        $product = $storage->findEntity(ProductDto::REPRESENT_ENTITY_TYPE, 'product1');

        $this->assertNotNull($product);

        $storage->removeEntity($product);

        /** @var ProductDto $product */
        $product = $storage->findEntity(ProductDto::REPRESENT_ENTITY_TYPE, 'product1');

        $this->assertNull($product);
    }

    public function testFindLocalId()
    {
        $storage = $this->getImportStorage();

        $product1 = new ProductDto();
        $product1->id = IdDto::createByExternalId('product1');
        $product1->id->local_id = 12;

        $this->assertEquals(12, $storage->findEntityLocalId(ProductDto::REPRESENT_ENTITY_TYPE, $product1->id)->asInt());

        $product1 = new ProductDto();
        $product1->id = IdDto::createByExternalId('product2');
        $product1->id->local_id = 13;

        $storage->mapEntityId($product1);

        $product1 = new ProductDto();
        $product1->id = IdDto::createByExternalId('product2');

        $this->assertEquals(13, $storage->findEntityLocalId(ProductDto::REPRESENT_ENTITY_TYPE, $product1->id)->asInt());
    }

    /**
     * @return \Tygh\Addons\CommerceML\Repository\ImportEntityRepository
     */
    public function getImportEntityRepositry()
    {
        $product1 = new ProductDto();
        $product1->id = IdDto::createByExternalId('product1');

        $data = [
            '100_product_product1' => ImportItemDto::fromArray([
                'import_id'   => 100,
                'entity_type' => ProductDto::REPRESENT_ENTITY_TYPE,
                'entity_id'   => 'product1',
                'entity'      => $product1
            ])
        ];

        $import_entity_repository = $this->getMockBuilder(ImportEntityRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['batchSave', 'findEntityData', 'remove'])
            ->getMock();

        $import_entity_repository->method('batchSave')->willReturnCallback(function ($import_entities) {
            $this->values = array_merge($this->values, $import_entities);
        });

        $import_entity_repository->method('findEntityData')->willReturnCallback(function ($import_id, $entity_type, $entity_id) use (&$data) {
            $key = sprintf('%s_%s_%s', $import_id, $entity_type, $entity_id);

            return isset($data[$key]) ? $data[$key] : null;
        });

        $import_entity_repository->method('remove')->willReturnCallback(function ($import_id, $entity_type, $entity_id) use (&$data) {
            $key = sprintf('%s_%s_%s', $import_id, $entity_type, $entity_id);

            unset($data[$key]);
        });

        return $import_entity_repository;
    }

    /**
     * @return \Tygh\Addons\CommerceML\Repository\ImportRepository
     */
    public function getImportRepositry()
    {
        $import_repository = $this->getMockBuilder(ImportRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $import_repository->method('save')->willReturnCallback(function (ImportDto $import_dto) {
            $import_dto->import_id = 100;
            $this->values['import'] = $import_dto;

            return $import_dto;
        });

        return $import_repository;
    }

    /**
     * @return \Tygh\Addons\CommerceML\Repository\ImportEntityMapRepository
     */
    public function getImportEntityMapRepositry()
    {
        $data = [];

        $import_repository = $this->getMockBuilder(ImportEntityMapRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findLocalId', 'add'])
            ->getMock();

        $import_repository->method('findLocalId')->willReturnCallback(function ($company_id, $entity_type, $entity_id) use (&$data) {
            $key = sprintf('%s_%s_%s', $company_id, $entity_type, $entity_id);
            return isset($data[$key]) ? $data[$key] : null;
        });

        $import_repository->method('add')->willReturnCallback(function ($company_id, $entity_type, $entity_id, $local_id) use (&$data) {
            $key = sprintf('%s_%s_%s', $company_id, $entity_type, $entity_id);
            $data[$key] = $local_id;
        });

        return $import_repository;
    }

    /**
     * @return \Tygh\Addons\CommerceML\Repository\ImportRemovedEntityRepository
     */
    public function getImportRemovedEntityRepositry()
    {
        $repository = $this->getMockBuilder(ImportRemovedEntityRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['remove', 'add', 'exists'])
            ->getMock();

        $repository->method('exists')->willReturnCallback(function ($company_id, $entity_type, $entity_id) {
            return false;
        });

        return $repository;
    }
}