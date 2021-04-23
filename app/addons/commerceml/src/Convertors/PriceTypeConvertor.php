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


namespace Tygh\Addons\CommerceML\Convertors;


use Tygh\Addons\CommerceML\Dto\CurrencyDto;
use Tygh\Addons\CommerceML\Dto\IdDto;
use Tygh\Addons\CommerceML\Dto\PriceTypeDto;
use Tygh\Addons\CommerceML\Dto\PropertyDto;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Xml\SimpleXmlElement;

/**
 * Class PriceTypeConvertor
 *
 * @package Tygh\Addons\CommerceML\Convertors
 */
class PriceTypeConvertor
{
    /**
     * Converts CommerceML element price type converted to price type DTO
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement   $element        Xml element
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage instance
     */
    public function convert(SimpleXmlElement $element, ImportStorage $import_storage)
    {
        $entities = [];
        $price_type = new PriceTypeDto();

        $price_type->id = IdDto::createByExternalId($element->getAsString('id'));

        if ($element->has('name')) {
            $price_type->name = $element->getAsString('name');
        }

        if ($element->has('description')) {
            $price_type->properties->add(PropertyDto::create('description', $element->getAsString('description')));
        }

        if ($element->has('currency')) {
            $price_type->properties->add(PropertyDto::create('currency', $element->getAsString('currency')));

            $currency = new CurrencyDto();
            $currency->id = IdDto::createByExternalId($element->getAsString('currency'));
            $currency->name = $element->getAsString('currency');

            $entities[] = $currency;
        }

        /**
         * Executes after CommerceML element price type converted to price type DTO
         * Allows to modify or extend price type DTO
         *
         * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement          $element    Xml element
         * @param \Tygh\Addons\CommerceML\Dto\CategoryDto|null          $price_type Price type DTO
         * @param array<\Tygh\Addons\CommerceML\Dto\RepresentEntityDto> $entities   Other entites data
         */
        fn_set_hook('commerceml_price_type_convertor_convert', $element, $price_type, $entities);

        array_unshift($entities, $price_type);

        $import_storage->saveEntities($entities);
    }
}
