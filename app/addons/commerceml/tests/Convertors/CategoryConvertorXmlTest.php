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


use Tygh\Addons\CommerceML\Convertors\CategoryConvertor;
use Tygh\Addons\CommerceML\Convertors\ProductFeatureConvertor;
use Tygh\Addons\CommerceML\Dto\CategoryDto;
use Tygh\Addons\CommerceML\Dto\IdDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto;
use Tygh\Addons\CommerceML\Dto\TranslatableValueDto;
use Tygh\Addons\CommerceML\Tests\Unit\StorageBasedTestCase;
use Tygh\Addons\CommerceML\Xml\SimpleXmlElement;

class CategoryConvertorXmlTest extends StorageBasedTestCase
{
    protected function setUp()
    {
        $this->requireMockFunction('fn_set_hook');

        parent::setUp();
    }

    public function getConvertor()
    {
        return new CategoryConvertor(new ProductFeatureConvertor());
    }

    /**
     * @param $xml
     * @param $expected_entities
     * @dataProvider dpConvert
     */
    public function testConvert($xml, $expected_entities)
    {
        $convertor = $this->getConvertor();
        $import_storage = $this->getImportStorage();

        $convertor->convert(simplexml_load_string($xml, SimpleXmlElement::class, LIBXML_NOCDATA), $import_storage);

        $this->assertEquals($expected_entities, $import_storage->getImportEntityRepository()->getEntites());
    }

    public function dpConvert()
    {
        $category1 = new CategoryDto();
        $category1->id = IdDto::createByExternalId('104e79a4-816f-11e9-9c72-e0d55e229524');
        $category1->name = TranslatableValueDto::create(' 2. Футбол');
        $category1->full_name = ' 2. Футбол';

        $category2 = new CategoryDto();
        $category2->id = IdDto::createByExternalId('104e79a9-816f-11e9-9c72-e0d55e229524');
        $category2->name = TranslatableValueDto::create('GIVOVA');
        $category2->full_name = ' 2. Футбол/GIVOVA';
        $category2->parent_id = IdDto::createByExternalId('104e79a4-816f-11e9-9c72-e0d55e229524');

        $category3 = new CategoryDto();
        $category3->id = IdDto::createByExternalId('104e79d9-816f-11e9-9c72-e0d55e229524');
        $category3->name = TranslatableValueDto::create(' 4. Тренировочные костюмы');
        $category3->full_name = ' 2. Футбол/GIVOVA/ 4. Тренировочные костюмы';
        $category3->parent_id = IdDto::createByExternalId('104e79a9-816f-11e9-9c72-e0d55e229524');

        $category4 = new CategoryDto();
        $category4->id = IdDto::createByExternalId('16b791bb-816f-11e9-9c72-e0d55e229524');
        $category4->name = TranslatableValueDto::create('13. Рашгарды, тайтсы, подтрусники');
        $category4->full_name = ' 2. Футбол/GIVOVA/13. Рашгарды, тайтсы, подтрусники';
        $category4->parent_id = IdDto::createByExternalId('104e79a9-816f-11e9-9c72-e0d55e229524');

        $product_feature1 = new ProductFeatureDto();
        $product_feature1->id = new IdDto('9d67a9cd-baf9-11e6-853e-60a44c5c87dc');
        $product_feature1->name = TranslatableValueDto::create('Пол');
        $product_feature1->type = 'directory';
        $product_feature1->variants[] = ProductFeatureVariantDto::create(IdDto::createByExternalId('9d67a9cd-baf9-11e6-853e-60a44c5c87dc#9d67a9cf-baf9-11e6-853e-60a44c5c87dc'), TranslatableValueDto::create('Мужской'));
        $product_feature1->variants[] = ProductFeatureVariantDto::create(IdDto::createByExternalId('9d67a9cd-baf9-11e6-853e-60a44c5c87dc#9d67a9ce-baf9-11e6-853e-60a44c5c87dc'), TranslatableValueDto::create('Женский'));
        $product_feature1->variants[] = ProductFeatureVariantDto::create(IdDto::createByExternalId('9d67a9cd-baf9-11e6-853e-60a44c5c87dc#9d67a9d0-baf9-11e6-853e-60a44c5c87dc'), TranslatableValueDto::create('Унисекс'));
        $product_feature1->variants[] = ProductFeatureVariantDto::create(IdDto::createByExternalId('9d67a9cd-baf9-11e6-853e-60a44c5c87dc#' . md5('Другое')), TranslatableValueDto::create('Другое'));

        $xml = <<<XML
<Группа>
    <Ид>104e79a4-816f-11e9-9c72-e0d55e229524</Ид>
    <Наименование> 2. Футбол</Наименование>
    <Группы>
        <Группа>
            <Ид>104e79a9-816f-11e9-9c72-e0d55e229524</Ид>
            <Наименование>GIVOVA</Наименование>
            <Группы>
                <Группа>
                    <Ид>104e79d9-816f-11e9-9c72-e0d55e229524</Ид>
                    <Наименование> 4. Тренировочные костюмы</Наименование>
                </Группа>
                <Группа>
                    <Ид>16b791bb-816f-11e9-9c72-e0d55e229524</Ид>
                    <Наименование>13. Рашгарды, тайтсы, подтрусники</Наименование>
                    <Свойства>
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
                    </Свойства>
                </Группа>
            </Группы>
        </Группа>
    </Группы>
</Группа>
XML;

        return [
            [
                $xml,
                array_merge([$category1, $category2, $category3, $category4, $product_feature1], $product_feature1->variants),
            ]
        ];
    }
}