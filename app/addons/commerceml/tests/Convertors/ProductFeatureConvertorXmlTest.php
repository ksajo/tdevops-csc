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


namespace Tygh\Addons\CommerceML\Tests\Unit\Convertors;

use Tygh\Addons\CommerceML\Convertors\ProductFeatureConvertor;
use Tygh\Addons\CommerceML\Dto\IdDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto;
use Tygh\Addons\CommerceML\Dto\TranslatableValueDto;
use Tygh\Addons\CommerceML\Tests\Unit\StorageBasedTestCase;
use Tygh\Addons\CommerceML\Xml\SimpleXmlElement;

class ProductFeatureConvertorXmlTest extends StorageBasedTestCase
{
    public $entities = [];

    protected function setUp()
    {
        $this->requireMockFunction('fn_set_hook');

        parent::setUp();
    }

    public function getConvertor()
    {
        return new ProductFeatureConvertor();
    }

    /**
     * @param $xml
     * @param $expected_entities
     * @param $settings
     * @dataProvider dpConvert
     */
    public function testConvert($xml, $expected_entities, array $settings = [])
    {
        $convertor = $this->getConvertor();
        $import_storage = $this->getImportStorage(null, $settings);

        $convertor->convert(simplexml_load_string($xml, SimpleXmlElement::class, LIBXML_NOCDATA), $import_storage);

        $this->assertEquals($expected_entities, $import_storage->getImportEntityRepository()->getEntites());
    }

    public function dpConvert()
    {
        return [
            $this->getCase1(),
            $this->getCase2(),
        ];
    }

    private function getCase1()
    {
        $product_feature1 = new ProductFeatureDto();
        $product_feature1->id = new IdDto('9d67a9cd-baf9-11e6-853e-60a44c5c87dc');
        $product_feature1->name = TranslatableValueDto::create('Пол');
        $product_feature1->type = 'directory';
        $product_feature1->variants[] = ProductFeatureVariantDto::create(IdDto::createByExternalId('9d67a9cd-baf9-11e6-853e-60a44c5c87dc#9d67a9cf-baf9-11e6-853e-60a44c5c87dc'), TranslatableValueDto::create('Мужской'));
        $product_feature1->variants[] = ProductFeatureVariantDto::create(IdDto::createByExternalId('9d67a9cd-baf9-11e6-853e-60a44c5c87dc#9d67a9ce-baf9-11e6-853e-60a44c5c87dc'), TranslatableValueDto::create('Женский'));
        $product_feature1->variants[] = ProductFeatureVariantDto::create(IdDto::createByExternalId('9d67a9cd-baf9-11e6-853e-60a44c5c87dc#9d67a9d0-baf9-11e6-853e-60a44c5c87dc'), TranslatableValueDto::create('Унисекс'));
        $product_feature1->variants[] = ProductFeatureVariantDto::create(IdDto::createByExternalId('9d67a9cd-baf9-11e6-853e-60a44c5c87dc#' . md5('Другое')), TranslatableValueDto::create('Другое'));

        $xml = <<<XML
<Свойство>
    <Ид>9d67a9cd-baf9-11e6-853e-60a44c5c87dc</Ид>
    <Наименование>Пол</Наименование>
    <ТипЗначений>Справочник</ТипЗначений>
    <ВариантыЗначений>
        <Справочник>
            <ИдЗначения>9d67a9cf-baf9-11e6-853e-60a44c5c87dc</ИдЗначения>
            <Значение>Мужской</Значение>
        </Справочник>
        <Справочник>
            <ИдЗначения>9d67a9ce-baf9-11e6-853e-60a44c5c87dc</ИдЗначения>
            <Значение>Женский</Значение>
        </Справочник>
        <Справочник>
            <ИдЗначения>9d67a9d0-baf9-11e6-853e-60a44c5c87dc</ИдЗначения>
            <Значение>Унисекс</Значение>
        </Справочник>
        <Справочник>
            <Значение>Другое</Значение>
        </Справочник>
    </ВариантыЗначений>
</Свойство>
XML;

        return [
            $xml,
            array_merge([$product_feature1], $product_feature1->variants)
        ];
    }

    private function getCase2()
    {
        $product_feature1 = new ProductFeatureDto();
        $product_feature1->id = new IdDto('9d68a9cd-baf9-11e6-853e-60a44c5c87dc');
        $product_feature1->name = TranslatableValueDto::create('Бренд');
        $product_feature1->type = ProductFeatureDto::TYPE_DIRECTORY;
        $product_feature1->variants[] = ProductFeatureVariantDto::create(IdDto::createByExternalId('9d68a9cd-baf9-11e6-853e-60a44c5c87dc#9d68a9cf-baf9-11e6-853e-60a44c5c87dc'), TranslatableValueDto::create('Format'));

        $xml = <<<XML
<Свойство>
    <Ид>9d68a9cd-baf9-11e6-853e-60a44c5c87dc</Ид>
    <Наименование>Бренд</Наименование>
    <ТипЗначений>Справочник</ТипЗначений>
    <ВариантыЗначений>
        <Справочник>
            <ИдЗначения>9d68a9cf-baf9-11e6-853e-60a44c5c87dc</ИдЗначения>
            <Значение>Format</Значение>
        </Справочник>
    </ВариантыЗначений>
</Свойство>
XML;

        return [
            $xml,
            array_merge([$product_feature1], $product_feature1->variants),
            [
                'catalog_convertor.brand_source' => 'property',
                'catalog_convertor.brand_property_source' => 'Бренд'
            ]
        ];
    }
}