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


use Tygh\Addons\CommerceML\Dto\IdDto;
use Tygh\Addons\CommerceML\Dto\OrderDto;
use Tygh\Addons\CommerceML\Dto\OrderProductDto;
use Tygh\Addons\CommerceML\Dto\TranslatableValueDto;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Xml\SimpleXmlElement;

/**
 * Class OrderConvertor
 *
 * @package Tygh\Addons\CommerceML\Convertors
 */
class OrderConvertor
{
    /**
     * Convertes CommerceML element product to ProductDTO
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement   $element        Xml element
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage instance
     */
    public function convert(SimpleXmlElement $element, ImportStorage $import_storage)
    {
        $order = new OrderDto();

        $order->id = IdDto::createByLocalId($element->getAsString('number'));
        $order->id->external_id = $element->getAsString('id');

        /**
         * @psalm-suppress PossiblyNullIterator
         */
        foreach ($element->get('products/product', []) as $item) {
            if ($item->getAsString('name') === SimpleXmlElement::findAlias('delivery_order')) {
                $order->shipping_cost = $item->getAsFloat('total');
                continue;
            }

            $product = new OrderProductDto();

            $product->id = IdDto::createByExternalId($item->getAsString('id'));
            $product->amount = $item->getAsInt('amount');
            $product->price = $item->getAsFloat('price_per_item');
            $product->total_price = $item->getAsFloat('total');

            $order->products[] = $product;

            $this->convertDiscounts($item, $order);

            $order->subtotal += $product->price * $product->amount;
        }

        /**
         * @psalm-suppress PossiblyNullIterator
         */
        foreach ($element->get('value_fields/value_field', []) as $item) {
            if ($item->getAsString('name') !== SimpleXmlElement::findAlias('status_order')) {
                continue;
            }

            $order->status = TranslatableValueDto::create($item->getAsString('value'));
        }

        if ($element->has('date')) {
            $date_time = $element->getAsString('date');

            if ($element->has('time')) {
                $date_time .= ' ' . $element->getAsString('time');
            }

            $order->updated_at = strtotime($date_time);
        }

        /**
         * Executes after CommerceML element converted to order DTO
         * Allows to modify or extend order DTO
         *
         * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement   $element        Xml element
         * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage instance
         * @param \Tygh\Addons\CommerceML\Dto\OrderDto           $order          Order DTO
         */
        fn_set_hook('commerceml_order_convertor_convert', $element, $import_storage, $order);

        $import_storage->saveEntities([$order]);
    }

    /**
     * Converts order discounts
     *
     * @param \Tygh\Addons\CommerceML\Xml\SimpleXmlElement $element Xml element
     * @param \Tygh\Addons\CommerceML\Dto\OrderDto         $order   Order Dto
     */
    private function convertDiscounts(SimpleXmlElement $element, OrderDto $order)
    {
        /**
         * @psalm-suppress PossiblyNullIterator
         */
        foreach ($element->get('discounts/discount', []) as $item) {
            $order->subtotal_discount += $item->getAsFloat('total');
        }
    }
}
