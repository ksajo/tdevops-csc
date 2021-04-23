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


use Tygh\Addons\CommerceML\Convertors\ProductConvertor;
use Tygh\Addons\CommerceML\Dto\ImageDto;
use Tygh\Addons\CommerceML\Dto\PriceValueDto;
use Tygh\Addons\CommerceML\Dto\ProductDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureValueDto;
use Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto;
use Tygh\Addons\CommerceML\Dto\TaxDto;
use Tygh\Addons\CommerceML\Dto\TranslatableValueDto;
use Tygh\Addons\CommerceML\Dto\IdDto;
use Tygh\Addons\CommerceML\Tests\Unit\StorageBasedTestCase;
use Tygh\Addons\CommerceML\Xml\SimpleXmlElement;

class ProductConvertorXmlTest extends StorageBasedTestCase
{
    public $entities = [];

    protected function setUp()
    {
        $this->requireMockFunction('fn_set_hook');

        parent::setUp();
    }

    public function getConvertor()
    {
        return new ProductConvertor(
            TranslatableValueDto::create('Вариант', ['en' => 'Variant']),
            TranslatableValueDto::create('Производитель', ['en' => 'Manufacturer']),
            ProductFeatureDto::BRAND_EXTERNAL_ID
        );
    }

    /**
     * @param $xml
     * @param $is_product_creatable
     * @param $expected_entities
     * @dataProvider dpConvert
     */
    public function testConvert($xml, $expected_entities, $is_product_creatable, array $entites_in_storage = [], array $settings = [])
    {
        $convertor = $this->getConvertor();
        $import_storage = $this->getImportStorage(null, $settings);

        if ($entites_in_storage) {
            $import_storage->saveEntities($entites_in_storage);
        }

        $convertor->convert(simplexml_load_string($xml, SimpleXmlElement::class, LIBXML_NOCDATA), $import_storage, $is_product_creatable);

        $this->assertEquals($expected_entities, $import_storage->getImportEntityRepository()->getEntites());
    }

    public function dpConvert()
    {
        return [
            $this->get205ImportCase1(),
            $this->get205OfferCase1(),
            $this->get205ImportCase2(),
            $this->get205OfferCase2(),
            $this->get207ImportCase1(),
            $this->get207OfferCase1(),
            $this->get207ImportCase2(),
            $this->get207OfferCase2(),
            $this->get207ImportCase3(),
            $this->get207OfferCase3(),
            $this->get207ImportCase4(),
            $this->get207OfferCase4(),

            $this->getConvertManufacturerCase1(),
            $this->getConvertManufacturerCase2(),

            $this->getConvertProductDescriptionCase1(),
        ];
    }

    public function get205ImportCase1()
    {
        $product1 = new ProductDto();
        $product1->id = new IdDto('0d9efc08-d695-11e8-8324-b010418127da');
        $product1->is_creatable = true;
        $product1->product_code = 'P005';
        $product1->is_removed = true;
        $product1->name = new TranslatableValueDto('Тайтсы GIVOVA SLIM');
        $product1->description = new TranslatableValueDto('<span>Описание</span>');
        $product1->categories[] = new IdDto('16b791bb-816f-11e9-9c72-e0d55e229524');
        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            new IdDto('9d67a9cd-baf9-11e6-853e-60a44c5c87dc'),
            '9d67a9cf-baf9-11e6-853e-60a44c5c87dc',
            new IdDto('9d67a9cd-baf9-11e6-853e-60a44c5c87dc#9d67a9cf-baf9-11e6-853e-60a44c5c87dc')
        ));
        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            new IdDto('9d67a9b6-baf9-11e6-853e-60a44c5c87dc'),
            '9d67a9b8-baf9-11e6-853e-60a44c5c87dc',
            new IdDto('9d67a9b6-baf9-11e6-853e-60a44c5c87dc#9d67a9b8-baf9-11e6-853e-60a44c5c87dc')
        ));
        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            new IdDto('9d67a9c9-baf9-11e6-853e-60a44c5c87dc'),
            '10',
            new IdDto('9d67a9c9-baf9-11e6-853e-60a44c5c87dc#10')
        ));
        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            new IdDto('9d67a9ca-baf9-11e6-853e-60a44c5c87dc'),
            '20',
            new IdDto('9d67a9ca-baf9-11e6-853e-60a44c5c87dc#20')
        ));
        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            new IdDto('b9069efd-ca9c-11e7-8576-60a44c5c87dc'),
            'PR-00000366',
            new IdDto('b9069efd-ca9c-11e7-8576-60a44c5c87dc#PR-00000366')
        ));
        $tax_dto = TaxDto::create(IdDto::createByExternalId(md5('НДСБез НДС')), 'НДС (Без НДС)');
        $product1->taxes[] = $tax_dto->getEntityId();

        $xml = <<<XML
<Товар Статус="удален">
    <Ид>0d9efc08-d695-11e8-8324-b010418127da</Ид>
    <Артикул>P005</Артикул>
    <Наименование>Тайтсы GIVOVA SLIM</Наименование>
    <БазоваяЕдиница Код="796 " НаименованиеПолное="штука" МеждународноеСокращение="PCE">
        <Пересчет>
            <Единица>796</Единица>
            <Коэффициент>1</Коэффициент>
        </Пересчет>
    </БазоваяЕдиница>
    <Группы>
        <Ид>16b791bb-816f-11e9-9c72-e0d55e229524</Ид>
    </Группы>
    <Описание><![CDATA[<span>Описание</span>]]></Описание>
    <ЗначенияСвойств>
        <ЗначенияСвойства>
            <Ид>9d67a9cd-baf9-11e6-853e-60a44c5c87dc</Ид>
            <Значение>9d67a9cf-baf9-11e6-853e-60a44c5c87dc</Значение>
        </ЗначенияСвойства>
        <ЗначенияСвойства>
            <Ид>9d67a9b6-baf9-11e6-853e-60a44c5c87dc</Ид>
            <Значение>9d67a9b8-baf9-11e6-853e-60a44c5c87dc</Значение>
        </ЗначенияСвойства>
        <ЗначенияСвойства>
            <Ид>9d67a9c9-baf9-11e6-853e-60a44c5c87dc</Ид>
            <Значение>10</Значение>
        </ЗначенияСвойства>
        <ЗначенияСвойства>
            <Ид>9d67a9ca-baf9-11e6-853e-60a44c5c87dc</Ид>
            <Значение>20</Значение>
        </ЗначенияСвойства>
        <ЗначенияСвойства>
            <Ид>b9069efd-ca9c-11e7-8576-60a44c5c87dc</Ид>
            <Значение>PR-00000366</Значение>
        </ЗначенияСвойства>
    </ЗначенияСвойств>
    <СтавкиНалогов>
        <СтавкаНалога>
            <Наименование>НДС</Наименование>
            <Ставка>Без НДС</Ставка>
        </СтавкаНалога>
    </СтавкиНалогов>
</Товар>
XML;
        return [$xml, [$product1, $tax_dto], true];
    }

    public function get205OfferCase1()
    {
        $product1 = new ProductDto();
        $product1->id = new IdDto('0d9efc08-d695-11e8-8324-b010418127da');
        $product1->is_creatable = false;
        $product1->product_code = 'P005';
        $product1->name = new TranslatableValueDto('Тайтсы GIVOVA SLIM');
        $product1->quantity = 2682;
        $product1->prices[] = PriceValueDto::create(
            IdDto::createByExternalId('5bfad036-f57d-11e4-a6b0-94de80a9c64c'),
            2650,
            IdDto::createByExternalId('USD')
        );
        $product1->prices[] = PriceValueDto::create(
            IdDto::createByExternalId('e894c1b4-4830-11e6-aea8-94de80a9c64c'),
            10442.25,
            IdDto::createByExternalId('UAH')
        );
        $product1->prices[] = PriceValueDto::create(
            IdDto::createByExternalId('e894c1b5-4830-11e6-aea8-94de80a9c64c'),
            14004.9,
            IdDto::createByExternalId('UAH')
        );
        $product1->prices[] = PriceValueDto::create(
            IdDto::createByExternalId('0ae5d850-2ebe-11e9-b7a0-06aab7dbc213'),
            2650,
            IdDto::createByExternalId('USD')
        );

        $xml = <<<XML
<Предложение>
    <Ид>0d9efc08-d695-11e8-8324-b010418127da</Ид>
    <Артикул>P005</Артикул>
    <Наименование>Тайтсы GIVOVA SLIM</Наименование>
    <БазоваяЕдиница Код="796 " НаименованиеПолное="штука" МеждународноеСокращение="PCE">
        <Пересчет>
            <Единица>796</Единица>
            <Коэффициент>1</Коэффициент>
        </Пересчет>
    </БазоваяЕдиница>
    <Цены>
        <Цена>
            <Представление> 2 650 USD за PCE</Представление>
            <ИдТипаЦены>5bfad036-f57d-11e4-a6b0-94de80a9c64c</ИдТипаЦены>
            <ЦенаЗаЕдиницу>2650</ЦенаЗаЕдиницу>
            <Валюта>USD</Валюта>
            <Единица>PCE</Единица>
            <Коэффициент>1</Коэффициент>
        </Цена>
        <Цена>
            <Представление> 10 442,25 UAH за PCE</Представление>
            <ИдТипаЦены>e894c1b4-4830-11e6-aea8-94de80a9c64c</ИдТипаЦены>
            <ЦенаЗаЕдиницу>10442.25</ЦенаЗаЕдиницу>
            <Валюта>UAH</Валюта>
            <Единица>PCE</Единица>
            <Коэффициент>1</Коэффициент>
        </Цена>
        <Цена>
            <Представление> 14 004,9 UAH за PCE</Представление>
            <ИдТипаЦены>e894c1b5-4830-11e6-aea8-94de80a9c64c</ИдТипаЦены>
            <ЦенаЗаЕдиницу>14004.9</ЦенаЗаЕдиницу>
            <Валюта>UAH</Валюта>
            <Единица>PCE</Единица>
            <Коэффициент>1</Коэффициент>
        </Цена>
        <Цена>
            <Представление> 2 650 USD за PCE</Представление>
            <ИдТипаЦены>0ae5d850-2ebe-11e9-b7a0-06aab7dbc213</ИдТипаЦены>
            <ЦенаЗаЕдиницу>2650</ЦенаЗаЕдиницу>
            <Валюта>USD</Валюта>
            <Единица>PCE</Единица>
            <Коэффициент>1</Коэффициент>
        </Цена>
    </Цены>
    <Количество>2682</Количество>
    <Склад ИдСклада="5cba3795-f386-11e2-802f-0015e9b8c48d" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="cea2d2ce-f388-11e2-802f-0015e9b8c48d" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="abf5870d-f5c8-11e2-802f-0015e9b8c48d" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="abf58710-f5c8-11e2-802f-0015e9b8c48d" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="9078db1b-ea8b-11e0-95a2-00055d4ef1e7" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="846e6ef2-e212-11e5-a85c-94de80a9c64c" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="854508b9-e6c8-11e5-a85c-94de80a9c64c" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="4609c9f9-32c2-11e0-aef8-0015e9b8c48d" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="163cab5e-35ae-11e0-aefc-0015e9b8c48d" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="03ce4b6e-3ff7-11e0-af05-0015e9b8c48d" КоличествоНаСкладе="3"/>
    <Склад ИдСклада="03ce4b6f-3ff7-11e0-af05-0015e9b8c48d" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="3f86a8a6-4a24-11e0-af0f-0015e9b8c48d" КоличествоНаСкладе="3"/>
    <Склад ИдСклада="50d41482-e4f3-11e0-af8f-0015e9b8c48d" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="4e5f8b1d-47ed-11e1-afde-0015e9b8c48d" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="c2a0e412-4b04-11e1-afe0-0015e9b8c48d" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="6f87e83f-722c-11df-b336-0011955cba6b" КоличествоНаСкладе="2675"/>
    <Склад ИдСклада="08305acc-7303-11df-b338-0011955cba6b" КоличествоНаСкладе="1"/>
    <Склад ИдСклада="1418c670-7307-11df-b338-0011955cba6b" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="a4212b46-730a-11df-b338-0011955cba6b" КоличествоНаСкладе="0"/>
</Предложение>
XML;
        return [$xml, [$product1], false];
    }

    public function get205ImportCase2()
    {
        $product1 = new ProductDto();
        $product1->is_creatable = true;
        $product1->id = IdDto::createByExternalId('8de3d432-9a21-11e7-8b00-94de80a9c64c');
        $product1->name = TranslatableValueDto::create('006 Product');
        $product1->product_code = 'T-006';
        $product1->categories[] = IdDto::createByExternalId('dee6e199-55bc-11d9-848a-00112f43529a');

        $tax_dto = TaxDto::create(IdDto::createByExternalId(md5('НДС10')), 'НДС (10)');
        $product1->taxes[] = $tax_dto->getEntityId();

        $xml = <<<XML
<Товар>
    <Ид>8de3d432-9a21-11e7-8b00-94de80a9c64c</Ид>
    <Артикул>T-006</Артикул>
    <Наименование>006 Product</Наименование>
    <БазоваяЕдиница Код="796" НаименованиеПолное="Штука" МеждународноеСокращение="PCE">
        <Пересчет>
            <Единица>796</Единица>
            <Коэффициент>1</Коэффициент>
        </Пересчет>
    </БазоваяЕдиница>
    <Группы>
        <Ид>dee6e199-55bc-11d9-848a-00112f43529a</Ид>
    </Группы>
    <Описание/>
    <СтавкиНалогов>
        <СтавкаНалога>
            <Наименование>НДС</Наименование>
            <Ставка>10</Ставка>
        </СтавкаНалога>
    </СтавкиНалогов>
</Товар>
XML;
        return [$xml, [$product1, $tax_dto], true];
    }

    public function get205OfferCase2()
    {
        $product1 = new ProductDto();
        $product1->is_creatable = false;
        $product1->id = IdDto::createByExternalId('8de3d432-9a21-11e7-8b00-94de80a9c64c#8de3d433-9a21-11e7-8b00-94de80a9c64c');
        $product1->parent_id = IdDto::createByExternalId('8de3d432-9a21-11e7-8b00-94de80a9c64c');
        $product1->is_variation = true;
        $product1->product_code = 'T-006';
        $product1->quantity = 11;
        $product1->name = TranslatableValueDto::create('006 Product (006 feature1)');
        $product1->variation_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('20797964-af2b-11e7-8b00-94de80a9c64c'),
            'новая',
            IdDto::createByExternalId('20797964-af2b-11e7-8b00-94de80a9c64c#20797965-af2b-11e7-8b00-94de80a9c64c')
        ));
        $product1->variation_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('9993754d-c76e-11e6-8cc8-94de80a9c64c'),
            '1111',
            IdDto::createByExternalId('9993754d-c76e-11e6-8cc8-94de80a9c64c#9993754e-c76e-11e6-8cc8-94de80a9c64c')
        ));
        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('20797964-af2b-11e7-8b00-94de80a9c64c'),
            '20797965-af2b-11e7-8b00-94de80a9c64c',
            IdDto::createByExternalId('20797964-af2b-11e7-8b00-94de80a9c64c#20797965-af2b-11e7-8b00-94de80a9c64c')
        ));
        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('9993754d-c76e-11e6-8cc8-94de80a9c64c'),
            '9993754e-c76e-11e6-8cc8-94de80a9c64c',
            IdDto::createByExternalId('9993754d-c76e-11e6-8cc8-94de80a9c64c#9993754e-c76e-11e6-8cc8-94de80a9c64c')
        ));
        $product1->prices[] = PriceValueDto::create(
            IdDto::createByExternalId('5bfad036-f57d-11e4-a6b0-94de80a9c64c'),
            103,
            IdDto::createByExternalId('USD')
        );

        $variant_1 = ProductFeatureVariantDto::create(IdDto::createByExternalId('20797964-af2b-11e7-8b00-94de80a9c64c#20797965-af2b-11e7-8b00-94de80a9c64c'), TranslatableValueDto::create('новая'));
        $variant_2 = ProductFeatureVariantDto::create(IdDto::createByExternalId('9993754d-c76e-11e6-8cc8-94de80a9c64c#9993754e-c76e-11e6-8cc8-94de80a9c64c'), TranslatableValueDto::create('1111'));

        $product_feature_1 = new ProductFeatureDto();
        $product_feature_1->type = ProductFeatureDto::TYPE_DIRECTORY;
        $product_feature_1->name = TranslatableValueDto::create('Опция для ТВ');
        $product_feature_1->id = IdDto::createByExternalId('20797964-af2b-11e7-8b00-94de80a9c64c');
        $product_feature_1->variants[] = $variant_1;

        $product_feature_2 = new ProductFeatureDto();
        $product_feature_2->type = ProductFeatureDto::TYPE_DIRECTORY;
        $product_feature_2->name = TranslatableValueDto::create('Значение');
        $product_feature_2->id = IdDto::createByExternalId('9993754d-c76e-11e6-8cc8-94de80a9c64c');
        $product_feature_2->variants[] = $variant_2;


        $xml = <<<XML
<Предложение>
    <Ид>8de3d432-9a21-11e7-8b00-94de80a9c64c#8de3d433-9a21-11e7-8b00-94de80a9c64c</Ид>
    <Артикул>T-006</Артикул>
    <Наименование>006 Product (006 feature1)</Наименование>
    <БазоваяЕдиница Код="796" НаименованиеПолное="Штука" МеждународноеСокращение="PCE">
        <Пересчет>
            <Единица>796</Единица>
            <Коэффициент>1</Коэффициент>
        </Пересчет>
    </БазоваяЕдиница>
    <ЗначенияСвойств>
        <ЗначенияСвойства>
            <Ид>20797964-af2b-11e7-8b00-94de80a9c64c</Ид>
            <Наименование>Опция для ТВ</Наименование>
            <Значение>20797965-af2b-11e7-8b00-94de80a9c64c</Значение>
        </ЗначенияСвойства>
        <ЗначенияСвойства>
            <Ид>9993754d-c76e-11e6-8cc8-94de80a9c64c</Ид>
            <Наименование>Значение</Наименование>
            <Значение>9993754e-c76e-11e6-8cc8-94de80a9c64c</Значение>
        </ЗначенияСвойства>
    </ЗначенияСвойств>
    <ХарактеристикиТовара>
        <ХарактеристикаТовара>
            <Ид>20797964-af2b-11e7-8b00-94de80a9c64c</Ид>
            <Наименование>Опция для ТВ</Наименование>
            <Значение>новая</Значение>
        </ХарактеристикаТовара>
        <ХарактеристикаТовара>
            <Ид>9993754d-c76e-11e6-8cc8-94de80a9c64c</Ид>
            <Наименование>Значение</Наименование>
            <Значение>1111</Значение>
        </ХарактеристикаТовара>
    </ХарактеристикиТовара>
    <Цены>
        <Цена>
            <Представление> 103 USD за PCE</Представление>
            <ИдТипаЦены>5bfad036-f57d-11e4-a6b0-94de80a9c64c</ИдТипаЦены>
            <ЦенаЗаЕдиницу>103</ЦенаЗаЕдиницу>
            <Валюта>USD</Валюта>
            <Единица>PCE</Единица>
            <Коэффициент>1</Коэффициент>
        </Цена>
    </Цены>
    <Количество>11</Количество>
</Предложение>
XML;

        return [$xml, [$product1, $product_feature_1, $product_feature_2, $variant_1, $variant_2], false];
    }

    public function get207ImportCase1()
    {
        $product1 = new ProductDto();
        $product1->is_creatable = true;
        $product1->id = IdDto::createByExternalId('b08229ef-6368-11e8-8544-14dda9ec887b#b08229f2-6368-11e8-8544-14dda9ec887b');
        $product1->parent_id = IdDto::createByExternalId('b08229ef-6368-11e8-8544-14dda9ec887b');
        $product1->is_variation = true;
        $product1->product_code = '2-395';
        $product1->name = TranslatableValueDto::create('10А164 Штани (лосини)');
        $product1->description = TranslatableValueDto::create('Гарна мама');
        $product1->categories[] = IdDto::createByExternalId('d4456c17-635f-11e8-8544-14dda9ec887b');
        $product1->variation_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId(md5('1.Розмір')),
            '104 (3-4 роки)',
            IdDto::createByExternalId(md5('1.Розмір') . '#' . md5('104 (3-4 роки)'))
        ));

        $tax_dto = TaxDto::create(IdDto::createByExternalId(md5('НДС20')), 'НДС (20)');
        $product1->taxes[] = $tax_dto->getEntityId();
        $product1->images[] = ImageDto::create('import_files/b0/b08229ef-6368-11e8-8544-14dda9ec887b.jpeg');

        $variant1 = ProductFeatureVariantDto::create(IdDto::createByExternalId(md5('1.Розмір') . '#' . md5('104 (3-4 роки)')), TranslatableValueDto::create('104 (3-4 роки)'));

        $product_feature1 = new ProductFeatureDto();
        $product_feature1->type = ProductFeatureDto::TYPE_DIRECTORY;
        $product_feature1->id = IdDto::createByExternalId(md5('1.Розмір'));
        $product_feature1->name = TranslatableValueDto::create('1.Розмір');
        $product_feature1->variants[] = $variant1;

        $xml = <<<XML
<Товар>
    <Ид>b08229ef-6368-11e8-8544-14dda9ec887b#b08229f2-6368-11e8-8544-14dda9ec887b</Ид>
    <Штрихкод>102016423951041</Штрихкод>
    <Артикул>2-395</Артикул>
    <Наименование>10А164 Штани (лосини)</Наименование>
    <БазоваяЕдиница Код="2009" НаименованиеПолное="Штука">шт</БазоваяЕдиница>
    <Группы>
        <Ид>d4456c17-635f-11e8-8544-14dda9ec887b</Ид>
    </Группы>
    <Описание>Гарна мама</Описание>
    <Картинка>import_files/b0/b08229ef-6368-11e8-8544-14dda9ec887b.jpeg</Картинка>
    <СтавкиНалогов>
        <СтавкаНалога>
            <Наименование>НДС</Наименование>
            <Ставка>20</Ставка>
        </СтавкаНалога>
    </СтавкиНалогов>
    <ХарактеристикиТовара>
        <ХарактеристикаТовара>
            <Наименование>1.Розмір</Наименование>
            <Значение>104 (3-4 роки)</Значение>
        </ХарактеристикаТовара>
    </ХарактеристикиТовара>
</Товар>
XML;
        return [$xml, [$product1, $product_feature1, $variant1, $tax_dto], true];
    }

    public function get207OfferCase1()
    {
        $product1 = new ProductDto();
        $product1->is_creatable = false;
        $product1->id = IdDto::createByExternalId('b08229ef-6368-11e8-8544-14dda9ec887b#b08229f2-6368-11e8-8544-14dda9ec887b');
        $product1->parent_id = IdDto::createByExternalId('b08229ef-6368-11e8-8544-14dda9ec887b');
        $product1->is_variation = true;
        $product1->product_code = '2-395';
        $product1->name = TranslatableValueDto::create('10А164 Штани (лосини)');
        $product1->quantity = 6;
        $product1->prices[] = PriceValueDto::create(
            IdDto::createByExternalId('2093df3d-2f69-11e9-98d1-14dda9ec887b'),
            66.15,
            IdDto::createByExternalId('грн')
        );
        $product1->prices[] = PriceValueDto::create(
            IdDto::createByExternalId('392b9cc6-43ce-11e9-98d1-14dda9ec887b'),
            125.69,
            IdDto::createByExternalId('грн')
        );

        $xml = <<<XML
<Предложение>
    <Ид>b08229ef-6368-11e8-8544-14dda9ec887b#b08229f2-6368-11e8-8544-14dda9ec887b</Ид>
    <Штрихкод>102016423951041</Штрихкод>
    <Артикул>2-395</Артикул>
    <Наименование>10А164 Штани (лосини)</Наименование>
    <БазоваяЕдиница Код="2009" НаименованиеПолное="Штука">шт</БазоваяЕдиница>
    <Цены>
        <Цена>
            <Представление>66,15 грн за шт</Представление>
            <ИдТипаЦены>2093df3d-2f69-11e9-98d1-14dda9ec887b</ИдТипаЦены>
            <ЦенаЗаЕдиницу>66.15</ЦенаЗаЕдиницу>
            <Валюта>грн</Валюта>
            <Единица>шт</Единица>
            <Коэффициент>1</Коэффициент>
        </Цена>
        <Цена>
            <Представление>125,69 грн за шт</Представление>
            <ИдТипаЦены>392b9cc6-43ce-11e9-98d1-14dda9ec887b</ИдТипаЦены>
            <ЦенаЗаЕдиницу>125.69</ЦенаЗаЕдиницу>
            <Валюта>грн</Валюта>
            <Единица>шт</Единица>
            <Коэффициент>1</Коэффициент>
        </Цена>
    </Цены>
    <Количество>6</Количество>
</Предложение>
XML;
        return [$xml, [$product1], false];
    }

    public function get207ImportCase2()
    {
        $product1 = new ProductDto();
        $product1->is_creatable = true;
        $product1->id = IdDto::createByExternalId('75c55206-01d1-11e8-9381-002590d8abfc#ae134154-01d1-11e8-9381-002590d8abfc');
        $product1->parent_id = IdDto::createByExternalId('75c55206-01d1-11e8-9381-002590d8abfc');
        $product1->is_variation = true;
        $product1->name = TranslatableValueDto::create('Велосипед Формат 6413');
        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('969ff21e-cae6-11e7-9381-002590d8abfc'),
            'http://format.bike/catalog/junior-kids/format-6413-2017/',
            IdDto::createByExternalId('969ff21e-cae6-11e7-9381-002590d8abfc#http://format.bike/catalog/junior-kids/format-6413-2017/')
        ));
        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('brand1c'),
            'Format2',
            IdDto::createByExternalId('brand1c#' . md5('Format2'))
        ));
        $product1->images[] = ImageDto::create('import_files/75/75c5520601d111e89381002590d8abfc_a6b3a4ea6b5711e89e89525400436b93.jpg');
        $product1->categories[] = IdDto::createByExternalId('6e4bef5d-a11d-11e3-9416-002590d8abfd');
        $product1->variation_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId(md5('Вариант')),
            'рама OS, серый/черный матовый',
            IdDto::createByExternalId(md5('Вариант') . '#' . md5('рама OS, серый/черный матовый'))
        ));

        $variant1 = ProductFeatureVariantDto::create(IdDto::createByExternalId(md5('Вариант') . '#' . md5('рама OS, серый/черный матовый')), TranslatableValueDto::create('рама OS, серый/черный матовый'));

        $product_feature1 = new ProductFeatureDto();
        $product_feature1->type = ProductFeatureDto::TYPE_DIRECTORY;
        $product_feature1->id = IdDto::createByExternalId(md5('Вариант'));
        $product_feature1->name = TranslatableValueDto::create('Вариант', ['en' => 'Variant']);
        $product_feature1->variants[] = $variant1;

        $variant2 = ProductFeatureVariantDto::create(IdDto::createByExternalId('brand1c' . '#' . md5('Format2')), TranslatableValueDto::create('Format2'));

        $product_feature2 = new ProductFeatureDto();
        $product_feature2->type = ProductFeatureDto::TYPE_EXTENDED;
        $product_feature2->id = IdDto::createByExternalId('brand1c');
        $product_feature2->name = TranslatableValueDto::create('Производитель', ['en' => 'Manufacturer']);
        $product_feature2->variants[] = $variant2;

        $xml = <<<XML
<Товар>
    <Ид>75c55206-01d1-11e8-9381-002590d8abfc</Ид>
    <Артикул/>
    <Наименование>Велосипед Формат 6413</Наименование>
    <БазоваяЕдиница Код="796 " НаименованиеПолное="штука">
        <Пересчет>
            <Единица>796</Единица>
            <Коэффициент>1</Коэффициент>
        </Пересчет>
    </БазоваяЕдиница>
    <Группы>
        <Ид>6e4bef5d-a11d-11e3-9416-002590d8abfd</Ид>
    </Группы>
    <Описание/>
    <Картинка>import_files/75/75c5520601d111e89381002590d8abfc_a6b3a4ea6b5711e89e89525400436b93.jpg</Картинка>
    <Изготовитель>
        <Ид>b34dc9bc-d944-11e5-039b-002590d8abfc</Ид>
        <Наименование>Format2</Наименование>
    </Изготовитель>
    <ЗначенияСвойств>
        <ЗначенияСвойства>
            <Ид>969ff21e-cae6-11e7-9381-002590d8abfc</Ид>
            <Наименование>Сайт: описание (Справочник "Номенклатура" (Общие))</Наименование>
            <Значение>http://format.bike/catalog/junior-kids/format-6413-2017/</Значение>
        </ЗначенияСвойства>
    </ЗначенияСвойств>
    <ХарактеристикиТовара>
        <ХарактеристикаТовара>
            <Ид>ae134154-01d1-11e8-9381-002590d8abfc</Ид>
            <Наименование>рама OS, серый/черный матовый</Наименование>
            <Значение>рама OS, серый/черный матовый</Значение>
        </ХарактеристикаТовара>
    </ХарактеристикиТовара>
</Товар>
XML;

        return [$xml, [$product_feature2, $variant2, $product1, $product_feature1, $variant1], true];
    }

    public function get207OfferCase2()
    {
        $product1 = new ProductDto();
        $product1->is_creatable = false;
        $product1->id = IdDto::createByExternalId('75c55206-01d1-11e8-9381-002590d8abfc#ae134154-01d1-11e8-9381-002590d8abfc');
        $product1->parent_id = IdDto::createByExternalId('75c55206-01d1-11e8-9381-002590d8abfc');
        $product1->is_variation = true;
        $product1->name = TranslatableValueDto::create('Велосипед Формат 6413 (рама OS, серый/черный матовый)');
        $product1->quantity = 2;
        $product1->prices[] = PriceValueDto::create(
            IdDto::createByExternalId('b5a71667-9c5f-11e3-9416-002590d8abfd'),
            21210,
            IdDto::createByExternalId('RUB')
        );

        $xml = <<<XML
<Предложение>
    <Ид>75c55206-01d1-11e8-9381-002590d8abfc#ae134154-01d1-11e8-9381-002590d8abfc</Ид>
    <Артикул/>
    <Наименование>Велосипед Формат 6413 (рама OS, серый/черный матовый)</Наименование>
    <БазоваяЕдиница Код="796 " НаименованиеПолное="штука">
        <Пересчет>
            <Единица>796</Единица>
            <Коэффициент>1</Коэффициент>
        </Пересчет>
    </БазоваяЕдиница>
    <Цены>
        <Цена>
            <Представление> 21 210 RUB за </Представление>
            <ИдТипаЦены>b5a71667-9c5f-11e3-9416-002590d8abfd</ИдТипаЦены>
            <ЦенаЗаЕдиницу>21210</ЦенаЗаЕдиницу>
            <Валюта>RUB</Валюта>
            <Коэффициент>1</Коэффициент>
        </Цена>
    </Цены>
    <Количество>2</Количество>
</Предложение>
XML;
        return [$xml, [$product1], false];
    }

    public function get207ImportCase3()
    {
        $product1 = new ProductDto();
        $product1->is_creatable = true;
        $product1->id = IdDto::createByExternalId('81fd1ccb-fa7f-11e7-a636-fcaa14ba6a8b#81fd1ccc-fa7f-11e7-a636-fcaa14ba6a8b');
        $product1->parent_id = IdDto::createByExternalId('81fd1ccb-fa7f-11e7-a636-fcaa14ba6a8b');
        $product1->is_variation = true;
        $product1->name = TranslatableValueDto::create('Тестовый товар 12345');
        $product1->categories[] = IdDto::createByExternalId('e3608070-e0fa-11e7-ac61-fcaa14ba6a8b');
        $product1->variation_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId(md5('Вариант')),
            '1',
            IdDto::createByExternalId(md5('Вариант') . '#' . md5('1'))
        ));

        $product2 = new ProductDto();
        $product2->is_creatable = true;
        $product2->id = IdDto::createByExternalId('81fd1ccb-fa7f-11e7-a636-fcaa14ba6a8b#81fd1ccd-fa7f-11e7-a636-fcaa14ba6a8b');
        $product2->parent_id = IdDto::createByExternalId('81fd1ccb-fa7f-11e7-a636-fcaa14ba6a8b');
        $product2->is_variation = true;
        $product2->name = TranslatableValueDto::create('Тестовый товар 12345');
        $product2->categories[] = IdDto::createByExternalId('e3608070-e0fa-11e7-ac61-fcaa14ba6a8b');
        $product2->variation_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId(md5('Вариант')),
            '2',
            IdDto::createByExternalId(md5('Вариант') . '#' . md5('2'))
        ));

        $variant1 = ProductFeatureVariantDto::create(IdDto::createByExternalId(md5('Вариант') . '#' . md5('1')), TranslatableValueDto::create('1'));
        $variant2 = ProductFeatureVariantDto::create(IdDto::createByExternalId(md5('Вариант') . '#' . md5('2')), TranslatableValueDto::create('2'));

        $product_feature1 = new ProductFeatureDto();
        $product_feature1->id = IdDto::createByExternalId(md5('Вариант'));
        $product_feature1->type = ProductFeatureDto::TYPE_DIRECTORY;
        $product_feature1->name = TranslatableValueDto::create('Вариант', ['en' => 'Variant']);
        $product_feature1->variants[] = $variant1;
        $product_feature1->variants[] = $variant2;

        $xml = <<<XML
<Товар>
    <Ид>81fd1ccb-fa7f-11e7-a636-fcaa14ba6a8b</Ид>
    <Артикул/>
    <Наименование>Тестовый товар 12345</Наименование>
    <БазоваяЕдиница Код="2009" НаименованиеПолное="Штука" МеждународноеСокращение="шт.">
        <Пересчет>
            <Единица>200</Единица>
            <Коэффициент>1</Коэффициент>
        </Пересчет>
    </БазоваяЕдиница>
    <Группы>
        <Ид>e3608070-e0fa-11e7-ac61-fcaa14ba6a8b</Ид>
    </Группы>
    <Описание/>
    <ХарактеристикиТовара>
        <ХарактеристикаТовара>
            <Ид>81fd1ccc-fa7f-11e7-a636-fcaa14ba6a8b</Ид>
            <Наименование>1</Наименование>
            <Значение>1</Значение>
        </ХарактеристикаТовара>
        <ХарактеристикаТовара>
            <Ид>81fd1ccd-fa7f-11e7-a636-fcaa14ba6a8b</Ид>
            <Наименование>2</Наименование>
            <Значение>2</Значение>
        </ХарактеристикаТовара>
    </ХарактеристикиТовара>
</Товар>
XML;
        return [$xml, [$product1, $product2, $product_feature1, $variant1, $variant2], true];
    }

    public function get207OfferCase3()
    {
        $product1 = new ProductDto();
        $product1->is_creatable = false;
        $product1->id = IdDto::createByExternalId('81fd1ccb-fa7f-11e7-a636-fcaa14ba6a8b#81fd1ccd-fa7f-11e7-a636-fcaa14ba6a8b');
        $product1->parent_id = IdDto::createByExternalId('81fd1ccb-fa7f-11e7-a636-fcaa14ba6a8b');
        $product1->is_variation = true;
        $product1->name = TranslatableValueDto::create('Тестовый товар 12345 (2)');
        $product1->quantity = 0;
        $product1->prices[] = PriceValueDto::create(
            IdDto::createByExternalId('1103ccec-de75-11e7-a5f6-fcaa14ba6a8b'),
            124,
            IdDto::createByExternalId('UAH')
        );

        $xml = <<<XML
<Предложение>
    <Ид>81fd1ccb-fa7f-11e7-a636-fcaa14ba6a8b#81fd1ccd-fa7f-11e7-a636-fcaa14ba6a8b</Ид>
    <Артикул/>
    <Наименование>Тестовый товар 12345 (2)</Наименование>
    <БазоваяЕдиница Код="2009" НаименованиеПолное="Штука" МеждународноеСокращение="шт.">
        <Пересчет>
            <Единица>200</Единица>
            <Коэффициент>1</Коэффициент>
        </Пересчет>
    </БазоваяЕдиница>
    <Цены>
        <Цена>
            <Представление> 124 UAH за шт.</Представление>
            <ИдТипаЦены>1103ccec-de75-11e7-a5f6-fcaa14ba6a8b</ИдТипаЦены>
            <ЦенаЗаЕдиницу>124</ЦенаЗаЕдиницу>
            <Валюта>UAH</Валюта>
            <Единица>шт.</Единица>
            <Коэффициент>1</Коэффициент>
        </Цена>
    </Цены>
</Предложение>
XML;
        return [$xml, [$product1], false];
    }

    public function get207ImportCase4()
    {
        $product1 = new ProductDto();
        $product1->is_creatable = true;
        $product1->id = IdDto::createByExternalId('a518088a-3414-11e8-958e-525400436b93#b749cf70-3414-11e8-958e-525400436b93');
        $product1->parent_id = IdDto::createByExternalId('a518088a-3414-11e8-958e-525400436b93');
        $product1->is_variation = true;
        $product1->name = TranslatableValueDto::create('Велосипед Стелс Navigator 400 V арт.V040');
        $product1->categories[] = IdDto::createByExternalId('6e4bef5d-a11d-11e3-9416-002590d8abfd');
        $product1->variation_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId(md5('Вариант')),
            'рама 12", серый/зеленый',
            IdDto::createByExternalId(md5('Вариант') . '#' . md5('рама 12", серый/зеленый'))
        ));
        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('a8c742c6-cae6-11e7-9381-002590d8abfc'),
            'https://stels-rf.ru/files/products/navigator-400-v-24-v040-matt_dark_blue-red.1875x1300.jpg',
            IdDto::createByExternalId('a8c742c6-cae6-11e7-9381-002590d8abfc#https://stels-rf.ru/files/products/navigator-400-v-24-v040-matt_dark_blue-red.1875x1300.jpg')
        ));
        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('51cd5c98-a79d-11e3-9417-002590d8abfd'),
            'cefcc889-a79d-11e3-9417-002590d8abfd',
            IdDto::createByExternalId('51cd5c98-a79d-11e3-9417-002590d8abfd#cefcc889-a79d-11e3-9417-002590d8abfd')
        ));
        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('brand1c'),
            'Stels',
            IdDto::createByExternalId('brand1c#' . md5('Stels'))
        ));

        $product2 = new ProductDto();
        $product2->is_creatable = true;
        $product2->id = IdDto::createByExternalId('a518088a-3414-11e8-958e-525400436b93#e0e7a224-47c6-11e8-a287-525400436b93');
        $product2->parent_id = IdDto::createByExternalId('a518088a-3414-11e8-958e-525400436b93');
        $product2->is_variation = true;
        $product2->name = TranslatableValueDto::create('Велосипед Стелс Navigator 400 V арт.V040');
        $product2->categories[] = IdDto::createByExternalId('6e4bef5d-a11d-11e3-9416-002590d8abfd');
        $product2->variation_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId(md5('Вариант')),
            'рама 12", темно-синий/красный',
            IdDto::createByExternalId(md5('Вариант') . '#' . md5('рама 12", темно-синий/красный'))
        ));
        $product2->product_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('a8c742c6-cae6-11e7-9381-002590d8abfc'),
            'https://stels-rf.ru/files/products/navigator-400-v-24-v040-matt_dark_blue-red.1875x1300.jpg',
            IdDto::createByExternalId('a8c742c6-cae6-11e7-9381-002590d8abfc#https://stels-rf.ru/files/products/navigator-400-v-24-v040-matt_dark_blue-red.1875x1300.jpg')
        ));
        $product2->product_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('51cd5c98-a79d-11e3-9417-002590d8abfd'),
            'cefcc889-a79d-11e3-9417-002590d8abfd',
            IdDto::createByExternalId('51cd5c98-a79d-11e3-9417-002590d8abfd#cefcc889-a79d-11e3-9417-002590d8abfd')
        ));
        $product2->product_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('brand1c'),
            'Stels',
            IdDto::createByExternalId('brand1c#' . md5('Stels'))
        ));

        $variant1 = ProductFeatureVariantDto::create(IdDto::createByExternalId(md5('Вариант') . '#' . md5('рама 12", серый/зеленый')), TranslatableValueDto::create('рама 12", серый/зеленый'));
        $variant2 = ProductFeatureVariantDto::create(IdDto::createByExternalId(md5('Вариант') . '#' . md5('рама 12", темно-синий/красный')), TranslatableValueDto::create('рама 12", темно-синий/красный'));

        $product_feature1 = new ProductFeatureDto();
        $product_feature1->id = IdDto::createByExternalId(md5('Вариант'));
        $product_feature1->type = ProductFeatureDto::TYPE_DIRECTORY;
        $product_feature1->name = TranslatableValueDto::create('Вариант', ['en' => 'Variant']);
        $product_feature1->variants[] = $variant1;
        $product_feature1->variants[] = $variant2;

        $variant3 = ProductFeatureVariantDto::create(IdDto::createByExternalId('brand1c' . '#' . md5('Stels')), TranslatableValueDto::create('Stels'));

        $product_feature2 = new ProductFeatureDto();
        $product_feature2->type = ProductFeatureDto::TYPE_EXTENDED;
        $product_feature2->id = IdDto::createByExternalId('brand1c');
        $product_feature2->name = TranslatableValueDto::create('Производитель', ['en' => 'Manufacturer']);
        $product_feature2->variants[] = $variant3;

        $xml = <<<XML
<Товар>
    <Ид>a518088a-3414-11e8-958e-525400436b93</Ид>
    <Артикул/>
    <Наименование>Велосипед Стелс Navigator 400 V арт.V040</Наименование>
    <БазоваяЕдиница Код="796 " НаименованиеПолное="штука">
        <Пересчет>
            <Единица>796</Единица>
            <Коэффициент>1</Коэффициент>
        </Пересчет>
    </БазоваяЕдиница>
    <Группы>
        <Ид>6e4bef5d-a11d-11e3-9416-002590d8abfd</Ид>
    </Группы>
    <Описание/>
    <Изготовитель>
        <Ид>023aa5a4-ad3e-11e3-941a-002590d8abfd</Ид>
        <Наименование>Stels</Наименование>
    </Изготовитель>
    <ЗначенияСвойств>
        <ЗначенияСвойства>
            <Ид>a8c742c6-cae6-11e7-9381-002590d8abfc</Ид>
            <Наименование>Сайт: фото (Справочник "Номенклатура" (Общие))</Наименование>
            <Значение>https://stels-rf.ru/files/products/navigator-400-v-24-v040-matt_dark_blue-red.1875x1300.jpg</Значение>
        </ЗначенияСвойства>
        <ЗначенияСвойства>
            <Ид>51cd5c98-a79d-11e3-9417-002590d8abfd</Ид>
            <Наименование>Размер колеса (вело)</Наименование>
            <Значение>cefcc889-a79d-11e3-9417-002590d8abfd</Значение>
        </ЗначенияСвойства>
    </ЗначенияСвойств>
    <ХарактеристикиТовара>
        <ХарактеристикаТовара>
            <Ид>b749cf70-3414-11e8-958e-525400436b93</Ид>
            <Наименование>рама 12", серый/зеленый</Наименование>
            <Значение>рама 12", серый/зеленый</Значение>
        </ХарактеристикаТовара>
        <ХарактеристикаТовара>
            <Ид>e0e7a224-47c6-11e8-a287-525400436b93</Ид>
            <Наименование>рама 12", темно-синий/красный</Наименование>
            <Значение>рама 12", темно-синий/красный</Значение>
        </ХарактеристикаТовара>
    </ХарактеристикиТовара>
</Товар>
XML;

        return [$xml, [$product_feature2, $variant3, $product1, $product2, $product_feature1, $variant1, $variant2], true];
    }

    public function get207OfferCase4()
    {
        $product1 = new ProductDto();
        $product1->is_creatable = false;
        $product1->id = IdDto::createByExternalId('a518088a-3414-11e8-958e-525400436b93#b749cf70-3414-11e8-958e-525400436b93');
        $product1->parent_id = IdDto::createByExternalId('a518088a-3414-11e8-958e-525400436b93');
        $product1->is_variation = true;
        $product1->name = TranslatableValueDto::create('Велосипед Стелс Navigator 400 V арт.V040 (рама 12", серый/зеленый)');
        $product1->prices[] = PriceValueDto::create(
            IdDto::createByExternalId('b5a71667-9c5f-11e3-9416-002590d8abfd'),
            12180,
            IdDto::createByExternalId('RUB')
        );
        $product1->quantity = 1;

        $xml = <<<XML
<Предложение>
    <Ид>a518088a-3414-11e8-958e-525400436b93#b749cf70-3414-11e8-958e-525400436b93</Ид>
    <Артикул/>
    <Наименование>Велосипед Стелс Navigator 400 V арт.V040 (рама 12", серый/зеленый)</Наименование>
    <БазоваяЕдиница Код="796 " НаименованиеПолное="штука">
        <Пересчет>
            <Единица>796</Единица>
            <Коэффициент>1</Коэффициент>
        </Пересчет>
    </БазоваяЕдиница>
    <Цены>
        <Цена>
            <Представление> 12 180 RUB за </Представление>
            <ИдТипаЦены>b5a71667-9c5f-11e3-9416-002590d8abfd</ИдТипаЦены>
            <ЦенаЗаЕдиницу>12180</ЦенаЗаЕдиницу>
            <Валюта>RUB</Валюта>
            <Коэффициент>1</Коэффициент>
        </Цена>
    </Цены>
    <Склад ИдСклада="1cc5f280-4478-11e7-7184-002590d8abfc" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929ad-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="1"/>
    <Склад ИдСклада="485929ae-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929af-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929b0-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929b1-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929b2-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929b3-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929b4-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929b5-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929b6-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929b7-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929b8-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929b9-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929bb-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929bd-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929bf-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929c1-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929c3-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929c5-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929c7-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929c9-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929cb-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929cd-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929cf-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929d1-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929d3-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929d5-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929d7-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929d9-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929da-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="485929db-7c51-11e3-84ae-bcaec5aa20af" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="216fecbe-f440-11e7-9381-002590d8abfc" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="347f9fe4-135e-11e6-b494-002590d8abfc" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="c5892988-135e-11e6-b494-002590d8abfc" КоличествоНаСкладе="0"/>
    <Склад ИдСклада="b195ecda-0d9d-11e5-f499-002590d8abfc" КоличествоНаСкладе="0"/>
</Предложение>
XML;
        return [$xml, [$product1], false];
    }

    public function getConvertManufacturerCase1()
    {
        $product1 = new ProductDto();
        $product1->is_creatable = true;
        $product1->id = IdDto::createByExternalId('75c55206-01d1-11e8-9381-002590d8abfc');
        $product1->name = TranslatableValueDto::create('Велосипед Формат');
        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('brand1c'),
            TranslatableValueDto::create('Format'),
            IdDto::createByExternalId('brand1c' . '#' . md5('Format'))
        ));
        $product1->categories[] = IdDto::createByExternalId('6e4bef5d-a11d-11e3-9416-002590d8abfd');

        $variant1 = ProductFeatureVariantDto::create(IdDto::createByExternalId('brand1c' . '#' . md5('Format')), TranslatableValueDto::create('Format'));

        $product_feature1 = new ProductFeatureDto();
        $product_feature1->type = ProductFeatureDto::TYPE_EXTENDED;
        $product_feature1->id = IdDto::createByExternalId('brand1c');
        $product_feature1->name = TranslatableValueDto::create('Производитель', ['en' => 'Manufacturer']);
        $product_feature1->variants[] = $variant1;

        $xml = <<<XML
<Товар>
    <Ид>75c55206-01d1-11e8-9381-002590d8abfc</Ид>
    <Артикул/>
    <Наименование>Велосипед Формат</Наименование>
    <БазоваяЕдиница Код="796 " НаименованиеПолное="штука">
        <Пересчет>
            <Единица>796</Единица>
            <Коэффициент>1</Коэффициент>
        </Пересчет>
    </БазоваяЕдиница>
    <Группы>
        <Ид>6e4bef5d-a11d-11e3-9416-002590d8abfd</Ид>
    </Группы>
    <Описание/>
    <Изготовитель>
        <Ид>b34dc9bc-d944-11e5-039b-002590d8abfc</Ид>
        <Наименование>Format</Наименование>
    </Изготовитель>
</Товар>
XML;
        return [$xml, [$product_feature1, $variant1, $product1], true, [], [
            'catalog_convertor.brand_source' => 'manufacturer'
        ]];
    }

    public function getConvertManufacturerCase2()
    {
        $brand_feature1 = new ProductFeatureDto();
        $brand_feature1->type = ProductFeatureDto::TYPE_EXTENDED;
        $brand_feature1->id = IdDto::createByExternalId('brand1c');
        $brand_feature1->name = TranslatableValueDto::create('Бренд', ['en' => 'Brand']);
        $brand_feature1->variants[] = ProductFeatureVariantDto::create(IdDto::createByExternalId('brand1c' . '#' . md5('Stels')), TranslatableValueDto::create('Stels'));
        $brand_feature1->variants[] = ProductFeatureVariantDto::create(IdDto::createByExternalId('brand1c' . '#' . md5('Forward')), TranslatableValueDto::create('Forward'));
        $brand_feature1->variants[] = ProductFeatureVariantDto::create(IdDto::createByExternalId('brand1c' . '#' . md5('Велосипедмастер')), TranslatableValueDto::create('Велосипедмастер'));

        $product1 = new ProductDto();
        $product1->is_creatable = true;
        $product1->id = IdDto::createByExternalId('75c55206-01d1-11e8-9381-002590d8abfc');
        $product1->name = TranslatableValueDto::create('Велосипед Формат');
        $product1->product_feature_values->add(ProductFeatureValueDto::create(
            IdDto::createByExternalId('brand1c'),
            TranslatableValueDto::create('Format'),
            IdDto::createByExternalId('brand1c' . '#' . md5('Format'))
        ));
        $product1->categories[] = IdDto::createByExternalId('6e4bef5d-a11d-11e3-9416-002590d8abfd');

        $variant1 = ProductFeatureVariantDto::create(IdDto::createByExternalId('brand1c' . '#' . md5('Format')), TranslatableValueDto::create('Format'));

        $brand_feature2 = clone $brand_feature1;
        $brand_feature2->variants[] = $variant1;

        $xml = <<<XML
<Товар>
    <Ид>75c55206-01d1-11e8-9381-002590d8abfc</Ид>
    <Артикул/>
    <Наименование>Велосипед Формат</Наименование>
    <БазоваяЕдиница Код="796 " НаименованиеПолное="штука">
        <Пересчет>
            <Единица>796</Единица>
            <Коэффициент>1</Коэффициент>
        </Пересчет>
    </БазоваяЕдиница>
    <Группы>
        <Ид>6e4bef5d-a11d-11e3-9416-002590d8abfd</Ид>
    </Группы>
    <Описание/>
    <Изготовитель>
        <Ид>b34dc9bc-d944-11e5-039b-002590d8abfc</Ид>
        <Наименование>Format</Наименование>
    </Изготовитель>
</Товар>
XML;
        return [
            $xml,
            array_merge([$brand_feature1], $brand_feature1->variants, [$brand_feature2], [$variant1], [$product1]),
            true,
            array_merge([$brand_feature1], $brand_feature1->variants),
            ['catalog_convertor.brand_source' => 'manufacturer']
        ];
    }

    public function getConvertProductDescriptionCase1()
    {
        $product1 = new ProductDto();
        $product1->is_creatable = true;
        $product1->id = IdDto::createByExternalId('8de3d432-9a21-11e7-8b00-94de80a9c64c');
        $product1->name = TranslatableValueDto::create('006 Product');
        $product1->description = TranslatableValueDto::create("Текст с переносом строки.<br />\nТекст с переносом строки.");
        $product1->product_code = 'T-006';
        $product1->categories[] = IdDto::createByExternalId('dee6e199-55bc-11d9-848a-00112f43529a');

        $xml = <<<XML
<Товар>
    <Ид>8de3d432-9a21-11e7-8b00-94de80a9c64c</Ид>
    <Артикул>T-006</Артикул>
    <Наименование>006 Product</Наименование>
    <БазоваяЕдиница Код="796" НаименованиеПолное="Штука" МеждународноеСокращение="PCE">
        <Пересчет>
            <Единица>796</Единица>
            <Коэффициент>1</Коэффициент>
        </Пересчет>
    </БазоваяЕдиница>
    <Группы>
        <Ид>dee6e199-55bc-11d9-848a-00112f43529a</Ид>
    </Группы>
    <Описание><![CDATA[Текст с переносом строки.
Текст с переносом строки.]]></Описание>
</Товар>
XML;
        return [$xml, [$product1], true];
    }
}
