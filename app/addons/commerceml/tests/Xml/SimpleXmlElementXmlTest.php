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


namespace Tygh\Addons\CommerceML\Tests\Unit\Xml;


use Tygh\Addons\CommerceML\Tests\Unit\BaseXmlTestCase;
use Tygh\Addons\CommerceML\Xml\SimpleXmlElement;

class SimpleXmlElementXmlTest extends BaseXmlTestCase
{
    public function testBase()
    {
        /** @var \Tygh\Addons\CommerceML\Xml\SimpleXmlElement $xml */
        $xml = simplexml_load_string($this->getXml(), SimpleXmlElement::class, LIBXML_NOCDATA);

        $this->assertSame('0d9efc08-d695-11e8-8324-b010418127da', $xml->getAsString('id'));
        $this->assertSame('P005', $xml->getAsString('article'));
        $this->assertSame('<span>Описание</span>', $xml->getAsString('description'));
        $this->assertSame('<span>Описание</span>', $xml->getAsString('Описание'));
        $this->assertSame('<span>Описание</span>', (string) $xml->{'Описание'});
        $this->assertSame('Тайтсы GIVOVA SLIM', $xml->getAsString('name'));
        $this->assertSame(796, $xml->getAsInt('base_unit/recount/unit'));
        $this->assertSame(796.0, $xml->getAsFloat('base_unit@code'));
        $this->assertSame('delete', $xml->getAsEnumItem('@status', ['delete', 'new'], ''));
        $this->assertEmpty($xml->getAsString('bar'));
        $this->assertTrue($xml->has('bar'));
        $this->assertFalse($xml->hasAndNotEmpty('bar'));
    }

    public function getXml()
    {
        return <<<XML
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
    <Штрихкод/>
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
    }


}