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

namespace Tygh\Addons\CommerceML\Tests\Unit\Commands;

use Tygh\Addons\CommerceML\Commands\CreateImportCommand;
use Tygh\Addons\CommerceML\Commands\CreateImportCommandHandler;
use Tygh\Addons\CommerceML\Dto\IdDto;
use Tygh\Addons\CommerceML\Dto\ImportDto;
use Tygh\Addons\CommerceML\Dto\ImportItemDto;
use Tygh\Addons\CommerceML\Dto\ProductDto;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Tests\Unit\BaseXmlTestCase;
use Tygh\Addons\CommerceML\Xml\SimpleXmlElement;
use Tygh\Addons\CommerceML\Xml\XmlParser;

class CreateImportCommandHandlerTest extends BaseXmlTestCase
{
    public $values = [
        'import'   => null,
        'entities' => [],
    ];

    protected function setUp()
    {
        $this->requireMockFunction('fn_set_hook');

        parent::setUp();
    }

    public function testHandle()
    {
        $handler = new CreateImportCommandHandler(
            $this->getImportStorageFactory(),
            $this->getXmlParser(),
            function () {
                return $this->getEntitiesSchema();
            }
        );

        $command = new CreateImportCommand();
        $command->company_id = 1;
        $command->user_id = 1;
        $command->xml_file_paths = [
            __DIR__ . '/../data/offers.xml',
            __DIR__ . '/../data/import.xml',
        ];
        $command->import_type = 'catalog';

        $handler->handle($command);

        $this->assertInstanceOf(ImportDto::class, $this->values['import']);

        $this->assertEquals(100, $this->values['import']->import_id);
        $this->assertTrue($this->values['import']->has_only_changes);
        $this->assertCount(4, $this->values['entities']);

        $this->assertEquals(100, $this->values['entities'][0]->import_id);
        $this->assertEquals('product1', $this->values['entities'][0]->entity_id);
        $this->assertInstanceOf(ProductDto::class, $this->values['entities'][0]->entity);

        $this->assertEquals(100, $this->values['entities'][1]->import_id);
        $this->assertEquals('product2', $this->values['entities'][1]->entity_id);
        $this->assertInstanceOf(ProductDto::class, $this->values['entities'][1]->entity);

        $this->assertEquals(100, $this->values['entities'][2]->import_id);
        $this->assertEquals('product3', $this->values['entities'][2]->entity_id);
        $this->assertInstanceOf(ProductDto::class, $this->values['entities'][2]->entity);

        $this->assertEquals(100, $this->values['entities'][3]->import_id);
        $this->assertEquals('product4', $this->values['entities'][3]->entity_id);
        $this->assertInstanceOf(ProductDto::class, $this->values['entities'][3]->entity);
    }

    /**
     * @return \Tygh\Addons\CommerceML\Xml\XmlParser
     */
    public function getXmlParser()
    {
        $xml_parser = $this->getMockBuilder(XmlParser::class)
            ->disableOriginalConstructor()
            ->setMethods(['parse'])
            ->getMock();

        $xml = <<<XML
<Каталог СодержитТолькоИзменения="true">
    <Ид>4bda4442-08dd-49c3-ae90-587e45ca65ce</Ид>
    <ИдКлассификатора>4bda4442-08dd-49c3-ae90-587e45ca65ce</ИдКлассификатора>
    <Наименование>Основной каталог товаров</Наименование>
</Каталог>
XML;

        /** @var SimpleXmlElement $xml */
        $xml = simplexml_load_string($xml, SimpleXmlElement::class, LIBXML_NOCDATA);

        $xml_parser->method('parse')->willReturnCallback(function ($file_path, array $callbacks) use($xml) {
            if (strpos($file_path, 'import') !== false) {
                call_user_func($callbacks['path/path1'], $xml);
                call_user_func($callbacks['catalog@has_only_changes'], $xml->get('@has_only_changes'));
            } elseif (strpos($file_path, 'offer') !== false) {
                call_user_func($callbacks['path/path2'], $xml);
            }
        });

        return $xml_parser;
    }

    public function getImportStorageFactory()
    {
        return function (ImportDto $import) {
            $this->values['import'] = $import;

            $import_storage = $this->getMockBuilder(ImportStorage::class)
                ->disableOriginalConstructor()
                ->setMethods(['saveEntities', 'saveImport', 'getImport'])
                ->getMock();

            $import_storage->method('saveEntities')->willReturnCallback(function ($import_entities) use ($import) {
                $import_entities = ImportItemDto::createBatchByEntities($import_entities, $import);
                $this->values['entities'] = array_merge($this->values['entities'], $import_entities);
            });

            $import_storage->method('getImport')->willReturn($import);

            $import_storage->method('saveImport')->willReturnCallback(function () use ($import) {
                $import->import_id = 100;
            });

            return $import_storage;
        };
    }

    public function getEntitiesSchema()
    {
        return [
            'path/path1' => function (SimpleXmlElement $xml, ImportStorage $import_storage) {
                $product1 = new ProductDto();
                $product1->id = IdDto::createByExternalId('product1');

                $product2 = new ProductDto();
                $product2->id = IdDto::createByExternalId('product2');

                $import_storage->saveEntities([$product1, $product2]);
            },
            'path/path2' => function (SimpleXmlElement $xml, ImportStorage $import_storage) {
                $product3 = new ProductDto();
                $product3->id = IdDto::createByExternalId('product3');

                $product4 = new ProductDto();
                $product4->id = IdDto::createByExternalId('product4');

                $import_storage->saveEntities([$product3, $product4]);
            },
            'catalog@has_only_changes' => static function (SimpleXmlElement $xml, ImportStorage $import_storage) {
                $import_storage->getImport()->has_only_changes = SimpleXmlElement::normalizeBool((string) $xml);
            }
        ];
    }
}