<?php


namespace Tygh\Tests\Unit\RusTaxes;


use Tygh\Addons\RusTaxes\Receipt\Item;
use Tygh\Addons\RusTaxes\ReceiptFactory;
use Tygh\Addons\RusTaxes\TaxType;
use Tygh\Tests\Unit\ATestCase;

class ReceiptFactoryTest extends ATestCase
{
    public $runTestInSeparateProcess = true;
    public $backupGlobals = false;
    public $preserveGlobalState = false;

    /** @inheritdoc */
    public function setUp()
    {
        $this->requireMockFunction('__');
        $this->requireMockFunction('fn_set_hook');
    }

    /**
     * @param array $order
     * @param $currency
     * @param bool $allocate_discount_by_unit
     * @param $prices_with_taxes
     * @param $total_discount_item_types_filter
     * @param  $priority_item_type
     * @param $expected
     * @dataProvider dpCreateReceiptFromOrder
     */
    public function testCreateReceiptFromOrder(array $order, $currency, $allocate_discount_by_unit, $prices_with_taxes, $total_discount_item_types_filter, $priority_item_type, $expected)
    {
        $service = new ReceiptFactory(
            'RUB',
            array(
                1 => TaxType::VAT_0,
                2 => TaxType::VAT_110,
                3 => TaxType::VAT_10,
                6 => TaxType::VAT_18
            ),
            $prices_with_taxes,
            function ($price, $from, $to) {
                return $price * 10;
            }
        );

        $receipt = $service->createReceiptFromOrder($order, $currency, $allocate_discount_by_unit, $total_discount_item_types_filter, $priority_item_type);

        $this->assertEquals($expected, $receipt->toArray());

        if ($currency !== 'RUB') {
            $this->assertEquals($order['total'] * 10, $receipt->getTotal());
        } else {
            $this->assertEquals($order['total'], $receipt->getTotal());
        }
    }

    public function dpCreateReceiptFromOrder()
    {
        return array(
            array(
                /**
                 * Calculation tax by subtotal, tax included to price
                 * Shipping
                 * allocate_discount_by_unit = false
                 */
                array(
                    'total' => 460.27,
                    'subtotal_discount' => 0,
                    'payment_surcharge' => 0,
                    'shipping_cost' => 28.0,
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'products' => array(
                        '822274303' => array(
                            'item_id' => '822274303',
                            'product_id' => '12',
                            'product' => '100g Pants',
                            'product_code' => 'U0012O5AF0',
                            'price' => 30,
                            'amount' => 2
                        ),
                        '1706372553' => array(
                            'item_id' => '1706372553',
                            'product_id' => '248',
                            'product' => 'X-Box One',
                            'product_code' => 'U0012O5AF1',
                            'price' => 372.27,
                            'amount' => 1
                        ),
                    ),
                    'taxes' => array(
                        6 => array(
                            'price_includes_tax' => 'Y',
                            'tax_subtotal' => 8,
                            'applies' => array(
                                'P' => 5.45,
                                'S' => 2.55,
                                'items' => array(
                                    'S' => array(
                                        array(
                                            1 => true
                                        )
                                    ),
                                    'P' => array(
                                        '822274303' => true
                                    )
                                )
                            )
                        )
                    )
                ),
                'RUB', false, false, [], '',
                array(
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'items' => array(
                        array(
                            /**
                             * tax_sum = 5.45 * 30.0 / (30.0 * 2) = 2.72
                             */
                            'id' => 822274303,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 30.0,
                            'quantity' => 2.0,
                            'name' => '100g Pants',
                            'code' => 'U0012O5AF0',
                            'tax_sum' => 2.72,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0.0
                        ),
                        array(
                            'id' => 1706372553,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 372.27,
                            'quantity' => 1.0,
                            'name' => 'X-Box One',
                            'code' => 'U0012O5AF1',
                            'tax_sum' => 0,
                            'tax_type' => TaxType::NONE,
                            'total_discount' => 0.0
                        ),
                        array(
                            'id' => 0,
                            'type' => Item::TYPE_SHIPPING,
                            'price' => 28.0,
                            'quantity' => 1.0,
                            'name' => 'shipping',
                            'code' => 'SHIPPING',
                            'tax_sum' => 2.55,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0.0
                        ),
                    )
                )
            ),
            array(
                /**
                 * Calculation tax by subtotal, tax not included to price
                 * Shipping
                 * allocate_discount_by_unit = false
                 */
                array(
                    'total' => 469.07,
                    'subtotal_discount' => 0,
                    'payment_surcharge' => 0,
                    'shipping_cost' => 28.0,
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'products' => array(
                        '822274303' => array(
                            'item_id' => '822274303',
                            'product_id' => '12',
                            'product' => '100g Pants',
                            'product_code' => 'U0012O5AF0',
                            'price' => 30,
                            'amount' => 2
                        ),
                        '1706372553' => array(
                            'item_id' => '1706372553',
                            'product_id' => '248',
                            'product' => 'X-Box One',
                            'product_code' => 'U0012O5AF1',
                            'price' => 372.27,
                            'amount' => 1
                        ),
                    ),
                    'taxes' => array(
                        6 => array(
                            'price_includes_tax' => 'N',
                            'tax_subtotal' => 8.8,
                            'applies' => array(
                                'P' => 6,
                                'S' => 2.8,
                                'items' => array(
                                    'S' => array(
                                        array(
                                            1 => true
                                        )
                                    ),
                                    'P' => array(
                                        '822274303' => true
                                    )
                                )
                            )
                        )
                    )
                ),
                'RUB', false, false, [], '',
                array(
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'items' => array(
                        array(
                            /**
                             * tax_sum = 6 * 30.0 / (30.0 * 2) = 3
                             */
                            'id' => 822274303,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 33.0,
                            'quantity' => 2.0,
                            'name' => '100g Pants',
                            'code' => 'U0012O5AF0',
                            'tax_sum' => 3,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0.0
                        ),
                        array(
                            'id' => 1706372553,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 372.27,
                            'quantity' => 1.0,
                            'name' => 'X-Box One',
                            'code' => 'U0012O5AF1',
                            'tax_sum' => 0,
                            'tax_type' => TaxType::NONE,
                            'total_discount' => 0.0
                        ),
                        array(
                            'id' => 0,
                            'type' => Item::TYPE_SHIPPING,
                            'price' => 30.8,
                            'quantity' => 1.0,
                            'name' => 'shipping',
                            'code' => 'SHIPPING',
                            'tax_sum' => 2.8,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0.0
                        ),
                    )
                )
            ),
            array(
                /**
                 * Calculation tax by subtotal, tax not included to price
                 * Shipping
                 * Payment surcharge
                 * Order discount
                 * Product discount
                 * allocate_discount_by_unit = false
                 */
                array(
                    'total' => 257.27,
                    'subtotal_discount' => 19.8,
                    'payment_surcharge' => 27.68,
                    'shipping_cost' => 28.0,
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'products' => array(
                        822274303 => array(
                            'product' => '100g Pants',
                            'product_code' => 'U0012O5AF0',
                            'price' => 27.00,
                            'amount' => 3
                        ),
                        1237327324 => array(
                            'price' => 117.00,
                            'amount' => 1,
                            'product' => '16GB A Series Walkman Video MP3',
                            'product_code' => 'U0012O5AF1',
                        )
                    ),
                    'taxes' => array(
                        6 => array(
                            'price_includes_tax' => 'N',
                            'tax_subtotal' => 23.39,
                            'applies' => array(
                                'P' => 17.82,
                                'S' => 2.8,
                                'PS' => 2.77,
                                'items' => array(
                                    'S' => array(
                                        array(
                                            1 => true,
                                        )
                                    ),
                                    'P' => array(
                                        822274303 => true,
                                        1237327324 => true,
                                    ),
                                    'PS' => array(
                                        2 => true
                                    )
                                )
                            )
                        )
                    )
                ),
                'RUB', false, false, [], '',
                array(
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'items' => array(
                        array(
                            /**
                             * tax_sum = 17.82 * 27.00 / (27.00 * 3 + 117.00) = 2.43
                             * price = 27.00 + 2.43 = 29.43
                             * total_discount = 29.43 * 3 / (29.43 * 3 + 127.53 + 30.45 + 30.8) * 19.8 = 6.30
                             */
                            'id' => 822274303,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 29.43,
                            'quantity' => 3.0,
                            'name' => '100g Pants',
                            'code' => 'U0012O5AF0',
                            'tax_sum' => 2.43,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 6.30
                        ),
                        array(
                            /**
                             * tax_sum = 17.82 * 117.00 / (27.00 * 3 + 117.00) = 10.53
                             * price = 117.00 + 17.82 * 117.00 / (27.00 * 3 + 117.00) = 127.53
                             * total_discount = 127.53 / (127.53 + 30.45 + 30.8) * (19.8 - 6.30) = 9.11
                             */
                            'id' => 1237327324,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 127.53,
                            'quantity' => 1.0,
                            'name' => '16GB A Series Walkman Video MP3',
                            'code' => 'U0012O5AF1',
                            'tax_sum' => 10.53,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 9.11
                        ),
                        array(
                            /**
                             * price = 27.68 + 2.77
                             * total_discount = 30.45 / (30.45 + 30.8) * (19.8 - 6.30 - 9.11) = 2.18
                             */
                            'id' => 0,
                            'type' => Item::TYPE_SURCHARGE,
                            'price' => 30.45,
                            'quantity' => 1.0,
                            'name' => 'payment_surcharge',
                            'code' => 'PS',
                            'tax_sum' => 2.77,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 2.18
                        ),
                        array(
                            /**
                             * total_discount = 30.8 / (30.8) * (19.8 - 6.30 - 9.11 - 2.18) = 2.21
                             */
                            'id' => 0,
                            'type' => Item::TYPE_SHIPPING,
                            'price' => 30.8,
                            'quantity' => 1.0,
                            'name' => 'shipping',
                            'code' => 'SHIPPING',
                            'tax_sum' => 2.8,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 2.21
                        ),
                    )
                )
            ),
            array(
                /**
                 * Calculation tax by subtotal, tax not included to price
                 * Shipping
                 * Payment surcharge
                 * Order discount
                 * Product discount
                 * allocate_discount_by_unit = true
                 */
                array(
                    'total' => 257.27,
                    'subtotal_discount' => 19.8,
                    'payment_surcharge' => 27.68,
                    'shipping_cost' => 28.0,
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'products' => array(
                        822274303 => array(
                            'product' => '100g Pants',
                            'product_code' => 'U0012O5AF0',
                            'price' => 27.00,
                            'amount' => 3
                        ),
                        1237327324 => array(
                            'price' => 117.00,
                            'amount' => 1,
                            'product' => '16GB A Series Walkman Video MP3',
                            'product_code' => 'U0012O5AF1',
                        )
                    ),
                    'taxes' => array(
                        6 => array(
                            'price_includes_tax' => 'N',
                            'tax_subtotal' => 23.39,
                            'applies' => array(
                                'P' => 17.82,
                                'S' => 2.8,
                                'PS' => 2.77,
                                'items' => array(
                                    'S' => array(
                                        array(
                                            1 => true,
                                        )
                                    ),
                                    'P' => array(
                                        822274303 => true,
                                        1237327324 => true,
                                    ),
                                    'PS' => array(
                                        2 => true
                                    )
                                )
                            )
                        )
                    )
                ),
                'RUB', true, false, [], '',
                array(
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'items' => array(
                        array(
                            /**
                             * tax_sum = 17.82 * 27.00 / (27.00 * 3 + 117.00) = 2.43
                             * price = 27.00 + 17.82 * 27.00 / (27.00 * 3 + 117.00) = 29.43
                             * total_discount = 29.43 * 3 / (29.43 * 3 + 127.53 + 30.45 + 30.8) * 19.8 = 6.30
                             * price_with_discount = 29.43 - 6.30 / 3 = 27.33
                             * discount_remainder += 0
                             */
                            'id' => 822274303,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 27.33,
                            'quantity' => 3.0,
                            'name' => '100g Pants',
                            'code' => 'U0012O5AF0',
                            'tax_sum' => 2.43,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            /**
                             * tax_sum = 17.82 * 117.00 / (27.00 * 3 + 117.00) = 10.53
                             * price = 117.00 + 17.82 * 117.00 / (27.00 * 3 + 117.00) = 127.53
                             * total_discount = 127.53 / (127.53 + 30.45 + 30.8) * (19.8 - 6.30) = 9.11
                             * price_with_discount = 127.53 - 9.11 / 1 = 118.42
                             * discount_remainder += 0
                             */
                            'id' => 1237327324,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 118.42,
                            'quantity' => 1.0,
                            'name' => '16GB A Series Walkman Video MP3',
                            'code' => 'U0012O5AF1',
                            'tax_sum' => 10.53,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            /**
                             * price = 27.68 + 2.77
                             * total_discount = 30.45 / (30.45 + 30.8) * (19.8 - 6.30 - 9.11) = 2.18
                             * price_with_discount = 30.45 - 2.18 = 28.27
                             */
                            'id' => 0,
                            'type' => Item::TYPE_SURCHARGE,
                            'price' => 28.27,
                            'quantity' => 1.0,
                            'name' => 'payment_surcharge',
                            'code' => 'PS',
                            'tax_sum' => 2.77,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            /**
                             * total_discount = 30.8 / (30.8) * (19.8 - 6.30 - 9.11 - 2.18) = 2.21
                             * price_with_discount = 30.8 - 2.21 = 28.59
                             */
                            'id' => 0,
                            'type' => Item::TYPE_SHIPPING,
                            'price' => 28.59,
                            'quantity' => 1.0,
                            'name' => 'shipping',
                            'code' => 'SHIPPING',
                            'tax_sum' => 2.8,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                    )
                )
            ),
            array(
                /**
                 * Calculation tax by unit price, tax not included to price
                 * Shipping
                 * Payment surcharge
                 * Order discount
                 * Product discount
                 * allocate_discount_by_unit = false
                 */
                array(
                    'total' => 459.27,
                    'subtotal_discount' => 37.8,
                    'payment_surcharge' => 45.88,
                    'shipping_cost' => 30.8,
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'products' => array(
                        822274303 => array(
                            'product' => '100g Pants',
                            'product_code' => 'U0012O5AF0',
                            'price' => 27.00,
                            'amount' => 1
                        ),
                        1237327324 => array(
                            'price' => 117.00,
                            'amount' => 3,
                            'product' => '16GB A Series Walkman Video MP3',
                            'product_code' => 'U0012O5AF1',
                        )
                    ),
                    'taxes' => array(
                        6 => array(
                            'price_includes_tax' => 'N',
                            'tax_subtotal' => 45.19,
                            'applies' => array(
                                'P_822274303' => 2.7,
                                'P_1237327324' => 35.1,
                                'S_0_1' => 2.8,
                                'PS_2' => 4.59
                            )
                        )
                    )
                ),
                'RUB', false, false, [], '',
                array(
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'items' => array(
                        array(
                            /**
                             * price = 27.00 + 2.7 = 29.7
                             * total_discount = 29.7 / (29.7 + 128.7 * 3 + 50.47 + 30.8) * 37.8 = 2.25
                             */
                            'id' => 822274303,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 29.7,
                            'quantity' => 1.0,
                            'name' => '100g Pants',
                            'code' => 'U0012O5AF0',
                            'tax_sum' => 2.7,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 2.25
                        ),
                        array(
                            /**
                             * tax_sum = 35.1 / 3 = 11.7
                             * price = 117.00 + 35.1 / 3 = 128.7
                             * total_discount = 128.7 * 3 / (128.7 * 3 + 50.47 + 30.8) * (37.8 - 2.25) = 29.36
                             */
                            'id' => 1237327324,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 128.7,
                            'quantity' => 3.0,
                            'name' => '16GB A Series Walkman Video MP3',
                            'code' => 'U0012O5AF1',
                            'tax_sum' => 11.7,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 29.36
                        ),
                        array(
                            /**
                             * price = 45.88 + 4.59
                             * total_discount = 50.47 / (50.47 + 30.8) * (37.8 - 2.25 - 29.36) = 3.84
                             */
                            'id' => 0,
                            'type' => Item::TYPE_SURCHARGE,
                            'price' => 50.47,
                            'quantity' => 1.0,
                            'name' => 'payment_surcharge',
                            'code' => 'PS',
                            'tax_sum' => 4.59,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 3.84
                        ),
                        array(
                            /**
                             * total_discount = 30.8 / (30.8) * (37.8 - 2.25 - 29.36 - 3.84) = 2.35
                             */
                            'id' => 0,
                            'type' => Item::TYPE_SHIPPING,
                            'price' => 30.8,
                            'quantity' => 1.0,
                            'name' => 'shipping',
                            'code' => 'SHIPPING',
                            'tax_sum' => 2.8,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 2.35
                        ),
                    )
                )
            ),
            array(
                /**
                 * Calculation tax by unit price, tax not included to price
                 * Shipping
                 * Payment surcharge
                 * Order discount
                 * Product discount
                 * allocate_discount_by_unit = false
                 * prices_with_taxes = true
                 */
                array(
                    'total' => 459.27,
                    'subtotal_discount' => 37.8,
                    'payment_surcharge' => 50.47,
                    'shipping_cost' => 30.8,
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'products' => array(
                        822274303 => array(
                            'product' => '100g Pants',
                            'product_code' => 'U0012O5AF0',
                            'price' => 27.00,
                            'amount' => 1
                        ),
                        1237327324 => array(
                            'price' => 117.00,
                            'amount' => 3,
                            'product' => '16GB A Series Walkman Video MP3',
                            'product_code' => 'U0012O5AF1'
                        )
                    ),
                    'taxes' => array(
                        6 => array(
                            'price_includes_tax' => 'N',
                            'tax_subtotal' => 45.19,
                            'applies' => array(
                                'P_822274303' => 2.7,
                                'P_1237327324' => 35.1,
                                'S_0_1' => 2.8,
                                'PS_2' => 4.59
                            )
                        )
                    )
                ),
                'RUB', false, true, [], '',
                array(
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'items' => array(
                        array(
                            /**
                             * price = 27.00 + 2.7 = 29.7
                             * total_discount = 29.7 / (29.7 + 128.7 * 3 + 50.47 + 30.8) * 37.8 = 2.25
                             */
                            'id' => 822274303,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 29.7,
                            'quantity' => 1.0,
                            'name' => '100g Pants',
                            'code' => 'U0012O5AF0',
                            'tax_sum' => 2.7,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 2.25
                        ),
                        array(
                            /**
                             * tax_sum = 35.1 / 3 = 11.7
                             * price = 117.00 + 35.1 / 3 = 128.7
                             * total_discount = 128.7 * 3 / (128.7 * 3 + 50.47 + 30.8) * (37.8 - 2.25) = 29.36
                             */
                            'id' => 1237327324,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 128.7,
                            'quantity' => 3.0,
                            'name' => '16GB A Series Walkman Video MP3',
                            'code' => 'U0012O5AF1',
                            'tax_sum' => 11.7,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 29.36
                        ),
                        array(
                            /**
                             * price = 45.88 + 4.59
                             * total_discount = 50.47 / (50.47 + 30.8) * (37.8 - 2.25 - 29.36) = 3.84
                             */
                            'id' => 0,
                            'type' => Item::TYPE_SURCHARGE,
                            'price' => 50.47,
                            'quantity' => 1.0,
                            'name' => 'payment_surcharge',
                            'code' => 'PS',
                            'tax_sum' => 4.59,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 3.84
                        ),
                        array(
                            /**
                             * total_discount = 30.8 / (30.8) * (37.8 - 2.25 - 29.36 - 3.84) = 2.35
                             */
                            'id' => 0,
                            'type' => Item::TYPE_SHIPPING,
                            'price' => 30.8,
                            'quantity' => 1.0,
                            'name' => 'shipping',
                            'code' => 'SHIPPING',
                            'tax_sum' => 2.8,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 2.35
                        ),
                    )
                )
            ),
            array(
                /**
                 * Calculation tax by subtotal, tax not included to price
                 * Order discount
                 * Product discount
                 * allocate_discount_by_unit = false
                 * prices_with_taxes = false
                 */
                array(
                    'total' => 374.22,
                    'subtotal_discount' => 37.8,
                    'payment_surcharge' => 0,
                    'shipping_cost' => 0.0,
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'products' => array(
                        822274303 => array(
                            'product' => '100g Pants',
                            'product_code' => 'U0012O5AF0',
                            'price' => 27.00,
                            'amount' => 1
                        ),
                        1237327324 => array(
                            'price' => 117.00,
                            'amount' => 3,
                            'product' => '16GB A Series Walkman Video MP3',
                            'product_code' => 'U0012O5AF1',
                        )
                    ),
                    'taxes' => array(
                        6 => array(
                            'price_includes_tax' => 'N',
                            'tax_subtotal' => 34.02,
                            'applies' => array(
                                'P' => 34.02,
                                'S' => 0,
                                'items' => array(
                                    'S' => array(
                                        array()
                                    ),
                                    'P' => array(
                                        822274303 => true,
                                        1237327324 => true,
                                    )
                                )
                            )
                        )
                    )
                ),
                'RUB', false, false, [], '',
                array(
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'items' => array(
                        array(
                            /**
                             * tax_sum = 34.02 * 27.00 / (27.00 + 117.00 * 3) = 2.43
                             * price = 27.00 + 34.02 * 27.00 / (27.00 + 117.00 * 3) = 29.43
                             * total_discount = 29.43 / (29.43 + 127.53 * 3) * 37.8 = 2.7
                             */
                            'id' => 822274303,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 29.43,
                            'quantity' => 1.0,
                            'name' => '100g Pants',
                            'code' => 'U0012O5AF0',
                            'tax_sum' => 2.43,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 2.7
                        ),
                        array(
                            /**
                             * tax_sum = 34.02 * 117.00 / (27.00 + 117.00 * 3) = 10.53
                             * price = 117.00 + 34.02 * 117.00 / (27.00 + 117.00 * 3) = 127.53
                             * total_discount = 127.53 * 3 / (127.53 * 3) * (37.8 - 2.7) = 35.1
                             */
                            'id' => 1237327324,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 127.53,
                            'quantity' => 3.0,
                            'name' => '16GB A Series Walkman Video MP3',
                            'code' => 'U0012O5AF1',
                            'tax_sum' => 10.53,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 35.1
                        )
                    )
                )
            ),
            array(
                /**
                 * Calculation tax by subtotal, tax not included to price
                 * Order discount
                 * Product discount
                 * allocate_discount_by_unit = true
                 * prices_with_taxes = false
                 */
                array(
                    'total' => 374.72,
                    'subtotal_discount' => 37.3,
                    'payment_surcharge' => 0,
                    'shipping_cost' => 0.0,
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'products' => array(
                        822274303 => array(
                            'product' => '100g Pants',
                            'product_code' => 'U0012O5AF0',
                            'price' => 27.00,
                            'amount' => 1
                        ),
                        1237327324 => array(
                            'price' => 117.00,
                            'amount' => 3,
                            'product' => '16GB A Series Walkman Video MP3',
                            'product_code' => 'U0012O5AF1'
                        )
                    ),
                    'taxes' => array(
                        6 => array(
                            'price_includes_tax' => 'N',
                            'tax_subtotal' => 34.02,
                            'applies' => array(
                                'P' => 34.02,
                                'S' => 0,
                                'items' => array(
                                    'S' => array(
                                        array()
                                    ),
                                    'P' => array(
                                        822274303 => true,
                                        1237327324 => true,
                                    )
                                )
                            )
                        )
                    )
                ),
                'RUB', true, false, [], '',
                array(
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'items' => array(
                        array(
                            /**
                             * tax_sum = 34.02 * 27.00 / (27.00 + 117.00 * 3) = 2.43
                             * price = 27.00 + 34.02 * 27.00 / (27.00 + 117.00 * 3) = 29.43
                             * total_discount = 29.43 / (29.43 + 127.53 * 3) * 37.3 = 2.66
                             * price_with_discount = 29.43 - 2.66 = 26.77 - 0.02 (discount_remainder) = 26.75
                             * discount_remainder += 0
                             */
                            'id' => 822274303,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 26.75,
                            'quantity' => 1.0,
                            'name' => '100g Pants',
                            'code' => 'U0012O5AF0',
                            'tax_sum' => 2.43,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            /**
                             * tax_sum = 34.02 * 117.00 / (27.00 + 117.00 * 3) = 10.53
                             * price = 117.00 + 34.02 * 117.00 / (27.00 + 117.00 * 3) = 127.53
                             * total_discount = 127.53 * 3 / (127.53 * 3) * (37.3 - 2.66) = 34.64
                             * price_with_discount = 127.53 - 34.64 / 3 = 115.99
                             * discount_remainder += 0.02 (allocate to products)
                             */
                            'id' => 1237327324,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 115.99,
                            'quantity' => 3.0,
                            'name' => '16GB A Series Walkman Video MP3',
                            'code' => 'U0012O5AF1',
                            'tax_sum' => 10.53,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        )
                    )
                )
            ),
            array(
                /**
                 * Calculation tax by subtotal, tax not included to price
                 * allocate_discount_by_unit = true
                 * prices_with_taxes = false
                 */
                array(
                    'total' => 1362.11,
                    'subtotal_discount' => 0,
                    'payment_surcharge' => 0,
                    'shipping_cost' => 0.0,
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'products' => array(
                        1706372553 => array(
                            'price' => 372.27,
                            'amount' => 3,
                            'product' => 'X-Box One',
                            'product_code' => 'U0012O5AF1'
                        )
                    ),
                    'taxes' => array(
                        6 => array(
                            'price_includes_tax' => 'N',
                            'tax_subtotal' => 111.28,
                            'applies' => array(
                                'P' => 111.28,
                                'S' => 0,
                                'items' => array(
                                    'S' => array(
                                        array()
                                    ),
                                    'P' => array(
                                        1706372553 => true,
                                    )
                                )
                            )
                        ),
                        7 => array(
                            'price_includes_tax' => 'N',
                            'tax_subtotal' => 134.02,
                            'applies' => array(
                                'P' => 134.02,
                                'S' => 0,
                                'items' => array(
                                    'S' => array(
                                        array()
                                    ),
                                    'P' => array(
                                        1706372553 => true,
                                    )
                                )
                            )
                        ),
                    )
                ),
                'RUB', true, false, [], '',
                array(
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'items' => array(
                        array(
                            /**
                             * tax_sum = 111.28 / 3 + 134.02 / 3 = 81.76
                             * price = 372.27 + 111.28 / 3 + 134.02 / 3 = 372.27 + 37.09 + 44.67 = 454.03
                             * discount_remainder = 1362.11 - (454.03 * 3) = 0.02
                             */
                            'id' => 1706372553,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 454.03,
                            'quantity' => 2.0,
                            'name' => 'X-Box One',
                            'code' => 'U0012O5AF1',
                            'tax_sum' => 81.76,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            /**
                             * price = 372.27 + 111.28 / 3 + 134.02 / 3 = 372.27 + 37.09 + 44.67 = 454.03 + 0.02
                             */
                            'id' => 1706372553,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 454.05,
                            'quantity' => 1.0,
                            'name' => 'X-Box One',
                            'code' => 'U0012O5AF1',
                            'tax_sum' => 81.76,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                    )
                )
            ),
            array(
                /**
                 * Calculation tax by subtotal, tax not included to price
                 * Order discount
                 * Product discount
                 * allocate_discount_by_unit = false
                 * prices_with_taxes = false
                 * convert currency
                 */
                array(
                    'total' => 374.22,
                    'subtotal_discount' => 37.8,
                    'payment_surcharge' => 0,
                    'shipping_cost' => 0.0,
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'products' => array(
                        822274303 => array(
                            'product' => '100g Pants',
                            'product_code' => 'U0012O5AF0',
                            'price' => 27.00,
                            'amount' => 1
                        ),
                        1237327324 => array(
                            'price' => 117.00,
                            'amount' => 3,
                            'product' => '16GB A Series Walkman Video MP3',
                            'product_code' => 'U0012O5AF1'
                        )
                    ),
                    'taxes' => array(
                        6 => array(
                            'price_includes_tax' => 'N',
                            'tax_subtotal' => 34.02,
                            'applies' => array(
                                'P' => 34.02,
                                'S' => 0,
                                'items' => array(
                                    'S' => array(
                                        array()
                                    ),
                                    'P' => array(
                                        822274303 => true,
                                        1237327324 => true,
                                    )
                                )
                            )
                        )
                    )
                ),
                'USD', false, false, [], '',
                array(
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'items' => array(
                        array(
                            /**
                             * tax_sum = 34.02 * 27.00 / (27.00 + 117.00 * 3) = 2.43 * 10
                             * price = 27.00 + 34.02 * 27.00 / (27.00 + 117.00 * 3) = 29.43 * 10
                             * total_discount = 29.43 / (29.43 + 127.53 * 3) * 37.8 = 2.7 * 10
                             */
                            'id' => 822274303,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 294.3,
                            'quantity' => 1.0,
                            'name' => '100g Pants',
                            'code' => 'U0012O5AF0',
                            'tax_sum' => 24.3,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 27.0
                        ),
                        array(
                            /**
                             * tax_sum = 34.02 * 117.00 / (27.00 + 117.00 * 3) = 10.53 * 10
                             * price = 117.00 + 34.02 * 117.00 / (27.00 + 117.00 * 3) = 127.53 * 10
                             * total_discount = 127.53 * 3 / (127.53 * 3) * (37.8 - 2.7) = 35.1 * 10
                             */
                            'id' => 1237327324,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 1275.3,
                            'quantity' => 3.0,
                            'name' => '16GB A Series Walkman Video MP3',
                            'code' => 'U0012O5AF1',
                            'tax_sum' => 105.3,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 351.0
                        )
                    )
                )
            ),
            array(
                /**
                 * Calculation tax by subtotal, tax included to price
                 * Shipping
                 * Gift certificates
                 * allocate_discount_by_unit = false
                 */
                array(
                    'total' => 490.27,
                    'subtotal_discount' => 0,
                    'payment_surcharge' => 0,
                    'shipping_cost' => 28.0,
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'products' => array(
                        '822274303' => array(
                            'item_id' => '822274303',
                            'product_id' => '12',
                            'product' => '100g Pants',
                            'product_code' => 'U0012O5AF0',
                            'price' => 30,
                            'amount' => 2
                        ),
                        '1706372553' => array(
                            'item_id' => '1706372553',
                            'product_id' => '248',
                            'product' => 'X-Box One',
                            'product_code' => 'U0012O5AF1',
                            'price' => 372.27,
                            'amount' => 1
                        ),
                    ),
                    'gift_certificates' => array(
                        '822274303' => array(
                            'gift_cert_id' => 1,
                            'amount' => 30,
                            'gift_cert_code' => 'GIFT_CERT_CODE1',
                        )
                    ),
                    'taxes' => array(
                        6 => array(
                            'price_includes_tax' => 'Y',
                            'tax_subtotal' => 8,
                            'applies' => array(
                                'P' => 5.45,
                                'S' => 2.55,
                                'items' => array(
                                    'S' => array(
                                        array(
                                            1 => true
                                        )
                                    ),
                                    'P' => array(
                                        '822274303' => true
                                    )
                                )
                            )
                        )
                    )
                ),
                'RUB', false, false, [], '',
                array(
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'items' => array(
                        /**
                         * tax_sum = 5.45 * 30.0 / (30.0 * 2) = 2.72
                         */
                        array(
                            'id' => 822274303,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 30.0,
                            'quantity' => 2.0,
                            'name' => '100g Pants',
                            'code' => 'U0012O5AF0',
                            'tax_sum' => 2.72,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0.0
                        ),
                        array(
                            'id' => 1706372553,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 372.27,
                            'quantity' => 1.0,
                            'name' => 'X-Box One',
                            'code' => 'U0012O5AF1',
                            'tax_sum' => 0,
                            'tax_type' => TaxType::NONE,
                            'total_discount' => 0.0
                        ),
                        array(
                            'id' => 0,
                            'type' => Item::TYPE_SHIPPING,
                            'price' => 28.0,
                            'quantity' => 1.0,
                            'name' => 'shipping',
                            'code' => 'SHIPPING',
                            'tax_sum' => 2.55,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0.0
                        ),
                        array(
                            'id' => 822274303,
                            'type' => Item::TYPE_GIFT_CERTIFICATE,
                            'price' => 30.0,
                            'quantity' => 1.0,
                            'name' => 'gift_certificate',
                            'code' => 'GIFT_CERT_CODE1',
                            'tax_sum' => 0,
                            'tax_type' => TaxType::NONE,
                            'total_discount' => 0.0
                        ),
                    )
                )
            ),
            array(
                /**
                 * Calculation tax by subtotal, tax included to price
                 * Shipping without tax
                 * allocate_discount_by_unit = false
                 */
                array(
                    'total' => 460.27,
                    'subtotal_discount' => 0,
                    'payment_surcharge' => 0,
                    'shipping_cost' => 28.0,
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'products' => array(
                        '822274303' => array(
                            'item_id' => '822274303',
                            'product_id' => '12',
                            'product' => '100g Pants',
                            'product_code' => 'U0012O5AF0',
                            'price' => 30,
                            'amount' => 2
                        ),
                        '1706372553' => array(
                            'item_id' => '1706372553',
                            'product_id' => '248',
                            'product' => 'X-Box One',
                            'product_code' => 'U0012O5AF1',
                            'price' => 372.27,
                            'amount' => 1
                        ),
                    ),
                    'taxes' => array(
                        6 => array(
                            'price_includes_tax' => 'Y',
                            'tax_subtotal' => 8,
                            'applies' => array(
                                'P' => 5.45,
                                'S' => 0,
                                'items' => array(
                                    'S' => array(),
                                    'P' => array(
                                        '822274303' => true
                                    )
                                )
                            )
                        )
                    )
                ),
                'RUB', false, false, [], '',
                array(
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'items' => array(
                        array(
                            /**
                             * tax_sum = 5.45 * 30.0 / (30.0 * 2) = 2.72
                             */
                            'id' => 822274303,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 30.0,
                            'quantity' => 2.0,
                            'name' => '100g Pants',
                            'code' => 'U0012O5AF0',
                            'tax_sum' => 2.72,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0.0
                        ),
                        array(
                            'id' => 1706372553,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 372.27,
                            'quantity' => 1.0,
                            'name' => 'X-Box One',
                            'code' => 'U0012O5AF1',
                            'tax_sum' => 0,
                            'tax_type' => TaxType::NONE,
                            'total_discount' => 0.0
                        ),
                        array(
                            'id' => 0,
                            'type' => Item::TYPE_SHIPPING,
                            'price' => 28.0,
                            'quantity' => 1.0,
                            'name' => 'shipping',
                            'code' => 'SHIPPING',
                            'tax_sum' => 0,
                            'tax_type' => TaxType::NONE,
                            'total_discount' => 0.0
                        ),
                    )
                )
            ),
            array(
                /**
                 * Calculation tax by subtotal, tax included to price
                 * Free shipping
                 * allocate_discount_by_unit = false
                 */
                array(
                    'total' => 7999.60,
                    'subtotal_discount' => 0,
                    'payment_surcharge' => 0,
                    'shipping_cost' => '0.00',
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'products' => array(
                        '3954105058' => array(
                            'item_id' => '3954105058',
                            'product_id' => '180',
                            'product' => '18-55mm Портретный объектив',
                            'product_code' => 'U0012O5AF0',
                            'price' => 7999.60,
                            'amount' => 1
                        )
                    ),
                    'taxes' => array(
                        6 => array(
                            'price_includes_tax' => 'Y',
                            'tax_subtotal' => 1220.28,
                            'applies' => array(
                                'P' => 1220.28,
                                'S' => 0,
                                'items' => array(
                                    'S' => array(),
                                    'P' => array(
                                        '3954105058' => true
                                    )
                                )
                            )
                        )
                    )
                ),
                'RUB', false, false, [], '',
                array(
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'items' => array(
                        array(
                            /**
                             * tax_sum = 5.45 * 30.0 / (30.0 * 2) = 2.72
                             */
                            'id' => 3954105058,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 7999.60,
                            'quantity' => 1.0,
                            'name' => '18-55mm Портретный объектив',
                            'code' => 'U0012O5AF0',
                            'tax_sum' => 1220.28,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0.0
                        )
                    )
                )
            ),
            array(
                /**
                 * Calculation tax by subtotal, tax not included to price
                 * Shipping
                 * Payment surcharge
                 * Order discount
                 * Product discount
                 * allocate_discount_by_unit = false
                 * total_discount_item_types_filter = array('product')
                 */
                array(
                    'total' => 257.27,
                    'subtotal_discount' => 19.8,
                    'payment_surcharge' => 27.68,
                    'shipping_cost' => 28.0,
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'products' => array(
                        822274303 => array(
                            'product' => '100g Pants',
                            'product_code' => 'U0012O5AF0',
                            'price' => 27.00,
                            'amount' => 3
                        ),
                        1237327324 => array(
                            'price' => 117.00,
                            'amount' => 1,
                            'product' => '16GB A Series Walkman Video MP3',
                            'product_code' => 'U0012O5AF1',
                        )
                    ),
                    'taxes' => array(
                        6 => array(
                            'price_includes_tax' => 'N',
                            'tax_subtotal' => 23.39,
                            'applies' => array(
                                'P' => 17.82,
                                'S' => 2.8,
                                'PS' => 2.77,
                                'items' => array(
                                    'S' => array(
                                        array(
                                            1 => true,
                                        )
                                    ),
                                    'P' => array(
                                        822274303 => true,
                                        1237327324 => true,
                                    ),
                                    'PS' => array(
                                        2 => true
                                    )
                                )
                            )
                        )
                    )
                ),
                'RUB', false, false, [Item::TYPE_PRODUCT], '',
                array(
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'items' => array(
                        array(
                            /**
                             * tax_sum = 17.82 * 27.00 / (27.00 * 3 + 117.00) = 2.43
                             * price = 27.00 + 2.43 = 29.43
                             * total_discount = 29.43 * 3 / (29.43 * 3 + 127.53) * 19.8 = 8.1
                             */
                            'id' => 822274303,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 29.43,
                            'quantity' => 3.0,
                            'name' => '100g Pants',
                            'code' => 'U0012O5AF0',
                            'tax_sum' => 2.43,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 8.1
                        ),
                        array(
                            /**
                             * tax_sum = 17.82 * 117.00 / (27.00 * 3 + 117.00) = 10.53
                             * price = 117.00 + 17.82 * 117.00 / (27.00 * 3 + 117.00) = 127.53
                             * total_discount = 127.53 / (127.53) * (19.8 - 8.1) = 11.7
                             */
                            'id' => 1237327324,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 127.53,
                            'quantity' => 1.0,
                            'name' => '16GB A Series Walkman Video MP3',
                            'code' => 'U0012O5AF1',
                            'tax_sum' => 10.53,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 11.7
                        ),
                        array(
                            /**
                             * price = 27.68 + 2.77
                             */
                            'id' => 0,
                            'type' => Item::TYPE_SURCHARGE,
                            'price' => 30.45,
                            'quantity' => 1.0,
                            'name' => 'payment_surcharge',
                            'code' => 'PS',
                            'tax_sum' => 2.77,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 0,
                            'type' => Item::TYPE_SHIPPING,
                            'price' => 30.8,
                            'quantity' => 1.0,
                            'name' => 'shipping',
                            'code' => 'SHIPPING',
                            'tax_sum' => 2.8,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                    )
                )
            ),
            array(
                /**
                 * Calculation tax by subtotal, tax not included to price, discount divided only on products
                 * Shipping
                 * Payment surcharge
                 * Order discount
                 * Product discount
                 * allocate_discount_by_unit = false
                 * total_discount_item_types_filter = [],
                 * priority_item_type = product
                 */
                array(
                    'total' => 257.27,
                    'subtotal_discount' => 19.8,
                    'payment_surcharge' => 27.68,
                    'shipping_cost' => 28.0,
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'products' => array(
                        822274303 => array(
                            'product' => '100g Pants',
                            'product_code' => 'U0012O5AF0',
                            'price' => 27.00,
                            'amount' => 3
                        ),
                        1237327324 => array(
                            'price' => 117.00,
                            'amount' => 1,
                            'product' => '16GB A Series Walkman Video MP3',
                            'product_code' => 'U0012O5AF1',
                        )
                    ),
                    'taxes' => array(
                        6 => array(
                            'price_includes_tax' => 'N',
                            'tax_subtotal' => 23.39,
                            'applies' => array(
                                'P' => 17.82,
                                'S' => 2.8,
                                'PS' => 2.77,
                                'items' => array(
                                    'S' => array(
                                        array(
                                            1 => true,
                                        )
                                    ),
                                    'P' => array(
                                        822274303 => true,
                                        1237327324 => true,
                                    ),
                                    'PS' => array(
                                        2 => true
                                    )
                                )
                            )
                        )
                    )
                ),
                'RUB', false, false, [], Item::TYPE_PRODUCT,
                array(
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'items' => array(
                        array(
                            /**
                             * tax_sum = 17.82 * 27.00 / (27.00 * 3 + 117.00) = 2.43
                             * price = 27.00 + 2.43 = 29.43
                             * total_discount = 29.43 * 3 / (29.43 * 3 + 127.53) * 19.8 = 8.1
                             */
                            'id' => 822274303,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 29.43,
                            'quantity' => 3.0,
                            'name' => '100g Pants',
                            'code' => 'U0012O5AF0',
                            'tax_sum' => 2.43,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 8.1
                        ),
                        array(
                            /**
                             * tax_sum = 17.82 * 117.00 / (27.00 * 3 + 117.00) = 10.53
                             * price = 117.00 + 17.82 * 117.00 / (27.00 * 3 + 117.00) = 127.53
                             * total_discount = 127.53 / (127.53) * (19.8 - 8.1) = 11.7
                             */
                            'id' => 1237327324,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 127.53,
                            'quantity' => 1.0,
                            'name' => '16GB A Series Walkman Video MP3',
                            'code' => 'U0012O5AF1',
                            'tax_sum' => 10.53,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 11.7
                        ),
                        array(
                            /**
                             * price = 27.68 + 2.77
                             */
                            'id' => 0,
                            'type' => Item::TYPE_SURCHARGE,
                            'price' => 30.45,
                            'quantity' => 1.0,
                            'name' => 'payment_surcharge',
                            'code' => 'PS',
                            'tax_sum' => 2.77,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 0,
                            'type' => Item::TYPE_SHIPPING,
                            'price' => 30.8,
                            'quantity' => 1.0,
                            'name' => 'shipping',
                            'code' => 'SHIPPING',
                            'tax_sum' => 2.8,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                    )
                )
            ),
            array(
                /**
                 * Calculation tax by subtotal, tax not included to price, discount divided only on shipping
                 * Shipping
                 * Payment surcharge
                 * Order discount
                 * Product discount
                 * allocate_discount_by_unit = true
                 * total_discount_item_types_filter = [],
                 * priority_item_type = shipping
                 *
                 */
                array(
                    'total' => 257.27,
                    'subtotal_discount' => 19.8,
                    'payment_surcharge' => 27.68,
                    'shipping_cost' => 28.0,
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'products' => array(
                        822274303 => array(
                            'product' => '100g Pants',
                            'product_code' => 'U0012O5AF0',
                            'price' => 27.00,
                            'amount' => 3
                        ),
                        1237327324 => array(
                            'price' => 117.00,
                            'amount' => 1,
                            'product' => '16GB A Series Walkman Video MP3',
                            'product_code' => 'U0012O5AF1',
                        )
                    ),
                    'taxes' => array(
                        6 => array(
                            'price_includes_tax' => 'N',
                            'tax_subtotal' => 23.39,
                            'applies' => array(
                                'P' => 17.82,
                                'S' => 2.8,
                                'PS' => 2.77,
                                'items' => array(
                                    'S' => array(
                                        array(
                                            1 => true,
                                        )
                                    ),
                                    'P' => array(
                                        822274303 => true,
                                        1237327324 => true,
                                    ),
                                    'PS' => array(
                                        2 => true
                                    )
                                )
                            )
                        )
                    )
                ),
                'RUB', false, true, [], Item::TYPE_SHIPPING,
                array(
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'items' => array(
                        array(
                            /**
                             * tax_sum = 17.82 * 27.00 / (27.00 * 3 + 117.00) = 2.43
                             * price = 27.00 + 2.43 = 29.43
                             */
                            'id' => 822274303,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 29.43,
                            'quantity' => 3.0,
                            'name' => '100g Pants',
                            'code' => 'U0012O5AF0',
                            'tax_sum' => 2.43,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0.0
                        ),
                        array(
                            /**
                             * tax_sum = 17.82 * 117.00 / (27.00 * 3 + 117.00) = 10.53
                             * price = 117.00 + 17.82 * 117.00 / (27.00 * 3 + 117.00) = 127.53
                             */
                            'id' => 1237327324,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 127.53,
                            'quantity' => 1.0,
                            'name' => '16GB A Series Walkman Video MP3',
                            'code' => 'U0012O5AF1',
                            'tax_sum' => 10.53,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0.0
                        ),
                        array(
                            /**
                             * price = 27.68 + 2.77
                             */
                            'id' => 0,
                            'type' => Item::TYPE_SURCHARGE,
                            'price' => 30.45,
                            'quantity' => 1.0,
                            'name' => 'payment_surcharge',
                            'code' => 'PS',
                            'tax_sum' => 2.77,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            /**
                             * price = 28.0 + 2.8 = 30.8
                             * total_discount = 19.8
                             */
                            'id' => 0,
                            'type' => Item::TYPE_SHIPPING,
                            'price' => 30.8,
                            'quantity' => 1.0,
                            'name' => 'shipping',
                            'code' => 'SHIPPING',
                            'tax_sum' => 2.8,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 19.8
                        ),
                    )
                )
            ),
            array(
                /**
                 * Calculation tax by subtotal, tax not included to price, discount is more than priority items total
                 * Shipping
                 * Payment surcharge
                 * Order discount
                 * Product discount
                 * allocate_discount_by_unit = false
                 * total_discount_item_types_filter = [],
                 * priority_item_type = product
                 */
                array(
                    'total' => 27.07,
                    'subtotal_discount' => 250,
                    'payment_surcharge' => 27.68,
                    'shipping_cost' => 28.0,
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'products' => array(
                        822274303 => array(
                            'product' => '100g Pants',
                            'product_code' => 'U0012O5AF0',
                            'price' => 27.00,
                            'amount' => 3
                        ),
                        1237327324 => array(
                            'price' => 117.00,
                            'amount' => 1,
                            'product' => '16GB A Series Walkman Video MP3',
                            'product_code' => 'U0012O5AF1',
                        )
                    ),
                    'taxes' => array(
                        6 => array(
                            'price_includes_tax' => 'N',
                            'tax_subtotal' => 23.39,
                            'applies' => array(
                                'P' => 17.82,
                                'S' => 2.8,
                                'PS' => 2.77,
                                'items' => array(
                                    'S' => array(
                                        array(
                                            1 => true,
                                        )
                                    ),
                                    'P' => array(
                                        822274303 => true,
                                        1237327324 => true,
                                    ),
                                    'PS' => array(
                                        2 => true
                                    )
                                )
                            )
                        )
                    )
                ),
                'RUB', false, false, [], Item::TYPE_PRODUCT,
                array(
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'items' => array(
                        array(
                            /**
                             * tax_sum = 17.82 * 27.00 / (27.00 * 3 + 117.00) = 2.43
                             * price = 27.00 + 2.43 = 29.43
                             * total_discount = 29.43 * 3 / (29.43 * 3 + 127.53 + 30.45 + 30.8) * 250 = 29.43 * 3 / 277.07 * 250 = 79.66
                             */
                            'id' => 822274303,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 29.43,
                            'quantity' => 3.0,
                            'name' => '100g Pants',
                            'code' => 'U0012O5AF0',
                            'tax_sum' => 2.43,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 79.66
                        ),
                        array(
                            /**
                             * tax_sum = 17.82 * 117.00 / (27.00 * 3 + 117.00) = 10.53
                             * price = 117.00 + 17.82 * 117.00 / (27.00 * 3 + 117.00) = 127.53
                             * total_discount = 127.53 / (127.53 + 30.45 + 30.8) * (250 - 79.66) = 115.07
                             */
                            'id' => 1237327324,
                            'type' => Item::TYPE_PRODUCT,
                            'price' => 127.53,
                            'quantity' => 1.0,
                            'name' => '16GB A Series Walkman Video MP3',
                            'code' => 'U0012O5AF1',
                            'tax_sum' => 10.53,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 115.07
                        ),
                        array(
                            /**
                             * price = 27.68 + 2.77
                             * total_discount = 30.45 / (30.45 + 30.8) * (250 - 79.66 - 115.07) = 30.45 / 61.25 * 55.27 = 27.04
                             */
                            'id' => 0,
                            'type' => Item::TYPE_SURCHARGE,
                            'price' => 30.45,
                            'quantity' => 1.0,
                            'name' => 'payment_surcharge',
                            'code' => 'PS',
                            'tax_sum' => 2.77,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 27.47
                        ),
                        array(
                            /**
                             * price = 28.0 + 2.8 = 30.8
                             * total_discount = 30.8 / 30.8 * (55.27 - 27.47) = 115.07
                             */
                            'id' => 0,
                            'type' => Item::TYPE_SHIPPING,
                            'price' => 30.8,
                            'quantity' => 1.0,
                            'name' => 'shipping',
                            'code' => 'SHIPPING',
                            'tax_sum' => 2.8,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 27.8
                        ),
                    )
                )
            ),
        );
    }
}