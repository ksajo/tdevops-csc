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


use Tygh\Addons\CommerceML\Dto\CategoryDto;
use Tygh\Addons\CommerceML\Dto\IdDto;
use Tygh\Addons\CommerceML\Dto\ImportDto;
use Tygh\Addons\CommerceML\Dto\PropertyDto;
use Tygh\Addons\CommerceML\Dto\TranslatableValueDto;
use Tygh\Addons\CommerceML\Importers\CategoryImporter;
use Tygh\Addons\CommerceML\Tests\Unit\StorageBasedTestCase;

class CategoryImporterTest extends StorageBasedTestCase
{
    public $categories = [];

    public $entities = [];

    public $entities_map = [];

    protected function setUp()
    {
        $this->requireMockFunction('__');

        parent::setUp();
    }

    public function testImport()
    {
        $product_storage = $this->getProductStorage();
        $import_storage = $this->getImportStorage();

        $importer = new CategoryImporter($product_storage);

        $category3 = new CategoryDto();
        $category3->id = IdDto::createByExternalId('16b791bb-816f-11e9-9c72-e0d55e229524');
        $category3->name = TranslatableValueDto::create('13. Рашгарды, тайтсы, подтрусники');
        $category3->full_name = ' 2. Футбол/GIVOVA/13. Рашгарды, тайтсы, подтрусники';
        $category3->parent_id = IdDto::createByExternalId('104e79a9-816f-11e9-9c72-e0d55e229524');
        $category3->properties->add(PropertyDto::create('local_id', 3));

        $result = $importer->import($category3, $import_storage);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(3, $result->getData());
        $this->assertEquals(3, $category3->getEntityId()->local_id);

        $this->assertEquals(
            [
                '104e79a4-816f-11e9-9c72-e0d55e229524' => 1,
                '104e79a9-816f-11e9-9c72-e0d55e229524' => 2,
                '16b791bb-816f-11e9-9c72-e0d55e229524' => 3,
            ],
            $import_storage->getImportEntityMapRepository()->getIdMap()
        );

        $this->assertEquals(
            [
                1 => [
                    'ru' => [
                        'category' => ' 2. Футбол',
                        'local_id' => 1,
                        'meta' => 'Дополнительное описание',
                        'parent_id' => 0,
                        'company_id' => 1
                    ],
                    'en' => [
                        'category' => ' 2. Football',
                        'meta' => 'Additional description'
                    ]
                ],
                2 => [
                    'ru' => [
                        'category' => 'GIVOVA',
                        'local_id' => 2,
                        'parent_id' => 1,
                        'company_id' => 1
                    ],
                ],
                3 => [
                    'ru' => [
                        'category' => '13. Рашгарды, тайтсы, подтрусники',
                        'local_id' => 3,
                        'parent_id' => 2,
                        'company_id' => 1
                    ],
                ],
            ],
            $product_storage->categories
        );

        $this->assertEmpty($import_storage->getImportEntityRepository()->storage);
    }

    public function getImportStorage(ImportDto $import = null, array $settings = [])
    {
        $category1 = new CategoryDto();
        $category1->id = IdDto::createByExternalId('104e79a4-816f-11e9-9c72-e0d55e229524');
        $category1->name = TranslatableValueDto::create(' 2. Футбол', ['en' => ' 2. Football']);
        $category1->full_name = ' 2. Футбол';
        $category1->properties->add(PropertyDto::create('local_id', 1));
        $category1->properties->add(PropertyDto::create('meta', TranslatableValueDto::create('Дополнительное описание', ['en' => 'Additional description'])));

        $category2 = new CategoryDto();
        $category2->id = IdDto::createByExternalId('104e79a9-816f-11e9-9c72-e0d55e229524');
        $category2->name = TranslatableValueDto::create('GIVOVA');
        $category2->full_name = ' 2. Футбол/GIVOVA';
        $category2->parent_id = IdDto::createByExternalId('104e79a4-816f-11e9-9c72-e0d55e229524');
        $category2->properties->add(PropertyDto::create('local_id', 2));

        $category3 = new CategoryDto();
        $category3->id = IdDto::createByExternalId('16b791bb-816f-11e9-9c72-e0d55e229524');
        $category3->name = TranslatableValueDto::create('13. Рашгарды, тайтсы, подтрусники');
        $category3->full_name = ' 2. Футбол/GIVOVA/13. Рашгарды, тайтсы, подтрусники';
        $category3->parent_id = IdDto::createByExternalId('104e79a9-816f-11e9-9c72-e0d55e229524');
        $category3->properties->add(PropertyDto::create('local_id', 3));

        $storage = parent::getImportStorage($import, $settings);

        $storage->saveEntities([
            $category1,
            $category2,
            $category3
        ]);

        return $storage;
    }
}