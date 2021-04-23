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
use Tygh\Addons\CommerceML\Dto\ProductFeatureDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto;
use Tygh\Addons\CommerceML\Dto\PropertyDto;
use Tygh\Addons\CommerceML\Dto\RepresentEntityDto;
use Tygh\Addons\CommerceML\Dto\TranslatableValueDto;
use Tygh\Addons\CommerceML\Importers\ProductFeatureImporter;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Storages\ProductStorage;
use Tygh\Addons\CommerceML\Tests\Unit\StorageBasedTestCase;
use Tygh\Common\OperationResult;
use Tygh\Enum\ProductFeatures;

class ProductFeatureImporterTest extends StorageBasedTestCase
{
    public $features = [];

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

        $product_feature1 = $this->getProductFeature1();

        $importer = new ProductFeatureImporter($product_storage);

        $result = $importer->import($product_feature1, $import_storage);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(1, $result->getData());
        $this->assertEquals(1, $product_feature1->getEntityId()->local_id);

        $this->assertEquals(
            [
                '9d67a9cd-baf9-11e6-853e-60a44c5c87dc'                                      => '1',
                '9d67a9cd-baf9-11e6-853e-60a44c5c87dc#9d67a9cf-baf9-11e6-853e-60a44c5c87dc' => '1',
                '9d67a9cd-baf9-11e6-853e-60a44c5c87dc#9d67a9ce-baf9-11e6-853e-60a44c5c87dc' => '2',
                '9d67a9cd-baf9-11e6-853e-60a44c5c87dc#9d67a9d0-baf9-11e6-853e-60a44c5c87dc' => '3',
                '9d67a9cd-baf9-11e6-853e-60a44c5c87dc#' . md5('Другое')                     => '4',
            ],
            $import_storage->getImportEntityMapRepository()->getIdMap()
        );

        $this->assertEquals(
            [
                1 => [
                    'ru' => [
                        'description'      => 'Пол',
                        'local_id'         => '1',
                        'full_description' => 'Описание',
                        'feature_type'     => ProductFeatures::TEXT_SELECTBOX,
                        'company_id'       => 1,
                        'variants'         => [
                            1 => [
                                'variant_id' => '1',
                                'local_id'   => '1',
                                'variant'    => 'Мужской',
                            ],
                            2 => [
                                'variant_id' => '2',
                                'local_id'   => '2',
                                'variant'    => 'Женский'
                            ],
                            3 => [
                                'variant_id' => '3',
                                'local_id'   => '3',
                                'variant'    => 'Унисекс'
                            ],
                            4 => [
                                'variant_id' => '4',
                                'local_id'   => '4',
                                'variant'    => 'Другое'
                            ],
                        ]
                    ],
                    'en' => [
                        'description'      => 'Gender',
                        'full_description' => 'Description',
                        'feature_type'     => ProductFeatures::TEXT_SELECTBOX,
                        'variants'         => [
                            1 => [
                                'variant_id' => '1',
                                'variant'    => 'Male'
                            ],
                            2 => [
                                'variant_id' => '2',
                                'variant'    => 'Female'
                            ],
                            4 => [
                                'variant_id' => '4',
                                'variant'    => 'Other'
                            ],
                        ]
                    ]
                ],
            ],
            $product_storage->product_features
        );

        $this->assertEmpty($import_storage->getImportEntityRepository()->storage);
    }

    public function testImportByFeatureValue()
    {
        $product_storage = $this->getProductStorage();
        $import_storage = $this->getImportStorage();

        $import_storage->saveEntities([$this->getProductFeature1()]);

        $importer = new ProductFeatureImporter($product_storage);

        $feature_value = ProductFeatureValueDto::create(
            IdDto::createByExternalId('9d67a9cd-baf9-11e6-853e-60a44c5c87dc'),
            '9d67a9d0-baf9-11e6-853e-60a44c5c87dc',
            IdDto::createByExternalId('9d67a9cd-baf9-11e6-853e-60a44c5c87dc#9d67a9d0-baf9-11e6-853e-60a44c5c87dc')
        );

        $result = $importer->importByFeatureValue($feature_value, $import_storage);

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(1, $feature_value->feature_id->local_id);
        $this->assertEquals(3, $feature_value->value_id->local_id);
    }

    public function getProductFeature1()
    {
        $product_feature1 = new ProductFeatureDto();
        $product_feature1->id = new IdDto('9d67a9cd-baf9-11e6-853e-60a44c5c87dc');
        $product_feature1->name = TranslatableValueDto::create('Пол', ['en' => 'Gender']);
        $product_feature1->type = 'directory';
        $product_feature1->properties->add(PropertyDto::create('local_id', 1));
        $product_feature1->properties->add(PropertyDto::create('full_description', TranslatableValueDto::create('Описание', ['en' => 'Description'])));
        $product_feature1->variants[] = ProductFeatureVariantDto::create(IdDto::createByExternalId('9d67a9cd-baf9-11e6-853e-60a44c5c87dc#9d67a9cf-baf9-11e6-853e-60a44c5c87dc'), TranslatableValueDto::create('Мужской', ['en' => 'Male']));
        $product_feature1->variants[] = ProductFeatureVariantDto::create(IdDto::createByExternalId('9d67a9cd-baf9-11e6-853e-60a44c5c87dc#9d67a9ce-baf9-11e6-853e-60a44c5c87dc'), TranslatableValueDto::create('Женский', ['en' => 'Female']));
        $product_feature1->variants[] = ProductFeatureVariantDto::create(IdDto::createByExternalId('9d67a9cd-baf9-11e6-853e-60a44c5c87dc#9d67a9d0-baf9-11e6-853e-60a44c5c87dc'), TranslatableValueDto::create('Унисекс'));
        $product_feature1->variants[] = ProductFeatureVariantDto::create(IdDto::createByExternalId('9d67a9cd-baf9-11e6-853e-60a44c5c87dc#' . md5('Другое')), TranslatableValueDto::create('Другое', ['en' => 'Other']));

        /** @var \Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto $variant */
        foreach ($product_feature1->variants as $key => $variant) {
            $variant->properties->add(PropertyDto::create('local_id', ($key + 1)));
        }

        return $product_feature1;
    }
}