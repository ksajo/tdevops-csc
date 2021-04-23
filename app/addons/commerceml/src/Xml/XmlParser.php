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

use Tygh\Addons\CommerceML\Xml\Exceptions\XmlParserException;
use XMLReader;

/**
 * Class XmlParser
 *
 * @package Tygh\Addons\CommerceML\Xml
 */
class XmlParser
{
    /**
     * Parses XML file and executes callbacks on declared xml paths
     *
     * @param string                  $file_path XML file path
     * @param array<string, callable> $callbacks XML path to callback map
     *
     * @throws \Tygh\Addons\CommerceML\Xml\Exceptions\XmlParserException If parsing failed.
     */
    public function parse($file_path, array $callbacks)
    {
        $use_internal_errors = libxml_use_internal_errors();
        libxml_clear_errors();
        libxml_use_internal_errors(true);

        try {
            $this->readInternal($file_path, $callbacks);
        } finally {
            libxml_use_internal_errors($use_internal_errors);
        }
    }

    /**
     * Parses XML file and executes callbacks on declared xml paths
     *
     * @param string                  $file_path XML file path
     * @param array<string, callable> $callbacks XML path to callback map
     *
     * @throws \Tygh\Addons\CommerceML\Xml\Exceptions\XmlParserException If parsing failed.
     */
    private function readInternal($file_path, array $callbacks)
    {
        $callbacks = self::normalizeCallbacks($callbacks);
        $xml_reader = new XMLReader();

        if ($xml_reader->open($file_path, null, LIBXML_NOENT | LIBXML_NOCDATA | LIBXML_NOWARNING) === false) {
            XmlParserException::libxmlErrors();
        }

        $xml_reader->read();
        $nodes = [];

        while ($xml_reader->read()) {
            $path = null;

            if ($xml_reader->nodeType === XMLReader::END_ELEMENT) {
                array_pop($nodes);
            } elseif ($xml_reader->nodeType === XMLReader::ELEMENT) {
                array_push($nodes, $xml_reader->name);
                $is_empty = $xml_reader->isEmptyElement;
                $path = SimpleXmlElement::buildPath($nodes);

                $this->executeCallback($xml_reader, $path, $callbacks);

                if ($xml_reader->hasAttributes) {
                    while ($xml_reader->moveToNextAttribute()) {
                        $path = SimpleXmlElement::buildPath($nodes, $xml_reader->name);

                        $this->executeCallback($xml_reader, $path, $callbacks);
                    }
                }

                if ($is_empty) {
                    array_pop($nodes);
                }
            }
        }

        $xml_reader->close();
    }

    /**
     * Finds and executes callback for path
     *
     * @param XMLReader               $xml_reader XML reader instance
     * @param string                  $path       XML path
     * @param array<string, callable> $callbacks  XML path to callback map
     *
     * @throws \Tygh\Addons\CommerceML\Xml\Exceptions\XmlParserException If parsing failed.
     */
    private function executeCallback(XMLReader $xml_reader, $path, array $callbacks)
    {
        if (!isset($callbacks[$path])) {
            return;
        }

        $xml = $xml_reader->readOuterXml();
        $xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?>' . $xml, SimpleXmlElement::class, LIBXML_NOENT | LIBXML_NOCDATA);

        if ($xml === false) {
            XmlParserException::libxmlErrors();
        }

        call_user_func($callbacks[$path], $xml);
        unset($xml);
    }

    /**
     * Normalizes XML path of callbacks
     *
     * @param array<string, callable> $callbacks XML path to callback map
     *
     * @return array<string, callable>
     */
    private function normalizeCallbacks(array $callbacks)
    {
        $result = [];

        foreach ($callbacks as $path => $callable) {
            $result[SimpleXmlElement::normalizePath($path)] = $callable;
        }

        return $result;
    }
}
