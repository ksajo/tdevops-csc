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

use Tygh\Addons\CommerceML\ServiceProvider;
use Tygh\Addons\CommerceML\Xml\SimpleXmlElement;
use Tygh\Addons\CommerceML\Storages\ImportStorage;

defined('BOOTSTRAP') or die('Access denied');

/**
 * @var array<string, callable> $schema Declares parser callbacks for xml paths
 */
$schema = [
    'classifier/properties/property'   => static function (SimpleXmlElement $xml, ImportStorage $import_storage) {
        ServiceProvider::getProductFeatureConvertor()->convert($xml, $import_storage);
    },
    'classifier/groups/group'          => static function (SimpleXmlElement $xml, ImportStorage $import_storage) {
        return ServiceProvider::getCategoryConvertor()->convert($xml, $import_storage);
    },
    'catalog/products/product'         => static function (SimpleXmlElement $xml, ImportStorage $import_storage) {
        return ServiceProvider::getProductConvetor()->convert($xml, $import_storage);
    },
    'packages/prices_types/price_type' => static function (SimpleXmlElement $xml, ImportStorage $import_storage) {
        return ServiceProvider::getPriceTypeConvertor()->convert($xml, $import_storage);
    },
    'packages/offers/offer'            => static function (SimpleXmlElement $xml, ImportStorage $import_storage) {
        return ServiceProvider::getProductConvetor()->convert($xml, $import_storage, false);
    },
    'catalog@has_only_changes'         => static function (SimpleXmlElement $xml, ImportStorage $import_storage) {
        $import_storage->getImport()->has_only_changes = SimpleXmlElement::normalizeBool((string) $xml);
    }
];

return $schema;
