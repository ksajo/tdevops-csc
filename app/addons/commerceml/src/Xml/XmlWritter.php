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


namespace Tygh\Addons\CommerceML\Xml;


use XMLWriter;

/**
 * Class XmlWritter
 *
 * @package Tygh\Addons\CommerceML\Xml
 */
class XmlWritter
{
    /**
     * @var \XMLWriter
     */
    private $xml_writer;

    /**
     * XmlWritter constructor.
     *
     * @param \XMLWriter $xml_writer XMLWriter
     */
    public function __construct(XMLWriter $xml_writer)
    {
        $this->xml_writer = $xml_writer;
    }

    /**
     * Converts array to XML
     *
     * @param array<string, int|string|array> $data Array to convert
     *
     * @return \XMLWriter
     */
    public function convertArrayToXml(array $data)
    {
        if (empty($data)) {
            return $this->xml_writer;
        }

        foreach ($data as $name_tag => $data_tag) {
            if (is_numeric($name_tag)) {
                $this->convertArrayToXml($data_tag);
                continue;
            }

            if ($name_tag === 'attribute') {
                foreach ((array) $data_tag as $k_attribute => $v_attribute) {
                    if ($k_attribute === 'text') {
                        $this->xml_writer->text($v_attribute);
                        continue;
                    }

                    $this->xml_writer->writeAttribute($k_attribute, $v_attribute);
                }

                continue;
            }

            if (is_array($data_tag)) {
                $this->xml_writer->startElement($name_tag);
                $this->convertArrayToXml($data_tag);
                $this->xml_writer->endElement();
                continue;
            }

            $name_tag = str_replace(' ', '', $name_tag);
            $this->xml_writer->writeElement($name_tag, (string) $data_tag);
        }

        return $this->xml_writer;
    }
}
