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
use Tygh\Addons\CommerceML\Xml\XmlParser;

class XmlParserTest extends BaseXmlTestCase
{
    public function testParse()
    {
        $file_path = __DIR__ . '/../data/import.xml';
        $values = [];

        $xml_parser = new XmlParser();

        $xml_parser->parse($file_path, [
            'classifier/properties/property' => static function (SimpleXmlElement $element) use(&$values) {
                $values['property'][] = $element->getAsString('id');
            },
            'classifier/groups/group' => static function (SimpleXmlElement $element) use(&$values) {
                $values['group'][] = $element->getAsString('id');
            },
            'catalog/products/product' => static function (SimpleXmlElement $element) use(&$values) {
                $values['product'][] = $element->getAsString('id');
            },
            'catalog@has_only_changes' => static function (SimpleXmlElement $element) use(&$values) {
                $values['has_only_changes'] = SimpleXmlElement::normalizeBool($element);
            }
        ]);

        $this->assertEquals(
            [
                'property' => [
                    '28b6895a-24a9-11e0-aeec-0015e9b8c48d',
                    '9d68a9cd-baf9-11e6-853e-60a44c5c87dc'
                ],
                'group' => [
                    'bd72d90d-55bc-11d9-848a-00112f43529a',
                    'bd72d90d-55bc-11d9-848a-00112f43529c'
                ],
                'product' => [
                    'bd72d910-55bc-11d9-848a-00112f43529a',
                    'bd72d913-55bc-11d9-848a-00112f43529a'
                ],
                'has_only_changes' => false
            ],
            $values
        );
    }
}