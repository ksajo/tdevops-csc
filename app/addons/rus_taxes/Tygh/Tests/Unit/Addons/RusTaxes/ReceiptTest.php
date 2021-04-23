<?php


namespace Tygh\Tests\Unit\RusTaxes;

use PHPUnit_Framework_TestCase;
use Tygh\Addons\RusTaxes\Receipt\Item;
use Tygh\Addons\RusTaxes\Receipt\Receipt;
use Tygh\Addons\RusTaxes\TaxType;


class ReceiptTest extends PHPUnit_Framework_TestCase
{
    public $runTestInSeparateProcess = true;
    public $backupGlobals = false;
    public $preserveGlobalState = false;

    /**
     * @param $items
     * @param $total_discount
     * @param $item_types
     * @param $priority_item_type
     * @param $expected
     *
     * @dataProvider dpSetTotalDiscount
     */
    public function testSetTotalDiscount($items, $total_discount, $item_types, $priority_item_type, $expected)
    {
        $receipt = new Receipt('email@example.com', '+79021111111', $items);
        $receipt->setTotalDiscount($total_discount, $item_types, $priority_item_type);

        $this->assertEquals($expected, $receipt->toArray());
    }

    public function dpSetTotalDiscount()
    {
        return array(
            array(
                array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1', 100, 1, TaxType::VAT_18, 1.8),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2', 200, 2, TaxType::VAT_18, 0),
                    new Item(3, Item::TYPE_SHIPPING, 'Shipping', 'SHIPPING', 300, 1, TaxType::NONE, 0),
                ),
                150.33, [], '',
                array(
                    'email' => 'email@example.com',
                    'phone' => '+79021111111',
                    'items' => array(
                        array(
                            /**
                             * total_discount = 100 / (100 + 200 * 2 + 300) * 150.33 = 18.79
                             */
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 100,
                            'quantity' => 1,
                            'tax_sum' => 1.8,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 18.79
                        ),
                        array(
                            /**
                             * total_discount = 200 * 2 / (200 * 2 + 300) * (150.33 - 18.79) = 75.16
                             */
                            'id' => 2,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 2',
                            'code' => 'PRODUCT2',
                            'price' => 200,
                            'quantity' => 2,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 75.16
                        ),
                        array(
                            /**
                             * total_discount = 300 / (300) * (150.33 - 18.79 - 75.16) = 56.38
                             */
                            'id' => 3,
                            'type' => Item::TYPE_SHIPPING,
                            'name' => 'Shipping',
                            'code' => 'SHIPPING',
                            'price' => 300,
                            'quantity' => 1,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::NONE,
                            'total_discount' => 56.38
                        ),
                    )
                )
            ),
            array(
                array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1', 100, 1, TaxType::VAT_18, 1.8),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2', 200, 2, TaxType::VAT_18, 0),
                    new Item(3, Item::TYPE_SHIPPING, 'Shipping', 'SHIPPING', 300, 1, TaxType::NONE, 0),
                ),
                150.33, [Item::TYPE_PRODUCT], '',
                array(
                    'email' => 'email@example.com',
                    'phone' => '+79021111111',
                    'items' => array(
                        array(
                            /**
                             * total_discount = 100 / (100 + 200 * 2) * 150.33 = 30.06
                             */
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 100,
                            'quantity' => 1,
                            'tax_sum' => 1.8,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 30.06
                        ),
                        array(
                            /**
                             * total_discount = 200 * 2 / (200 * 2) * (150.33 - 30.06) = 120.27
                             */
                            'id' => 2,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 2',
                            'code' => 'PRODUCT2',
                            'price' => 200,
                            'quantity' => 2,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 120.27
                        ),
                        array(
                            'id' => 3,
                            'type' => Item::TYPE_SHIPPING,
                            'name' => 'Shipping',
                            'code' => 'SHIPPING',
                            'price' => 300,
                            'quantity' => 1,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::NONE,
                            'total_discount' => 0
                        ),
                    )
                )
            ),
            array(
                array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1', 100, 1, TaxType::VAT_18, 1.8),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2', 200, 2, TaxType::VAT_18, 0),
                    new Item(3, Item::TYPE_SHIPPING, 'Shipping', 'SHIPPING', 300, 1, TaxType::NONE, 0),
                    new Item(3, Item::TYPE_SURCHARGE, 'Payment surcharge', 'SURCHARGE', 54, 1, TaxType::NONE, 0),
                ),
                45.68, [], Item::TYPE_SURCHARGE,
                array(
                    'email' => 'email@example.com',
                    'phone' => '+79021111111',
                    'items' => array(
                        array(
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 100,
                            'quantity' => 1,
                            'tax_sum' => 1.8,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0.0
                        ),
                        array(
                            'id' => 2,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 2',
                            'code' => 'PRODUCT2',
                            'price' => 200,
                            'quantity' => 2,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0.0
                        ),
                        array(
                            'id' => 3,
                            'type' => Item::TYPE_SHIPPING,
                            'name' => 'Shipping',
                            'code' => 'SHIPPING',
                            'price' => 300,
                            'quantity' => 1,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::NONE,
                            'total_discount' => 0.0
                        ),
                        array(
                            /**
                             * total_discount = 45.68
                             */
                            'id' => 3,
                            'type' => Item::TYPE_SURCHARGE,
                            'name' => 'Payment surcharge',
                            'code' => 'SURCHARGE',
                            'price' => 54,
                            'quantity' => 1,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::NONE,
                            'total_discount' => 45.68
                        ),
                    )
                )
            ),
        );
    }

    /**
     * @param $items
     * @param $expected
     * @dataProvider dpAllocateDiscountByUnit
     */
    public function testAllocateDiscountByUnit($items, $item_types, $expected)
    {
        $receipt = new Receipt('email@example.com', '+79021111111', $items);
        $receipt->allocateDiscountByUnit($item_types);

        $this->assertEquals($expected, $receipt->toArray());
    }

    public function dpAllocateDiscountByUnit()
    {
        return array(
            array(
                array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1',  100, 3, TaxType::VAT_18, 0, 100),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2',  200, 2, TaxType::VAT_18, 0, 10.51),
                    new Item(3, Item::TYPE_SHIPPING, 'Shipping', 'SHIPPING',  300, 1, TaxType::NONE, 0, 0),
                ),
                array(),
                array(
                    'email' => 'email@example.com',
                    'phone' => '+79021111111',
                    'items' => array(
                        array(
                            /**
                             * price = 100 - (100 / 3) = 66.67
                             * remainder += 0.01
                             */
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 66.67,
                            'quantity' => 3,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            /**
                             * price = 200 - (10.51 / 2) = 194.75
                             * remainder += 0.01
                             */
                            'id' => 2,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 2',
                            'code' => 'PRODUCT2',
                            'price' => 194.75,
                            'quantity' => 2,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            /**
                             * price = 300 - (0.01 + 0.01)
                             */
                            'id' => 3,
                            'type' => Item::TYPE_SHIPPING,
                            'name' => 'Shipping',
                            'code' => 'SHIPPING',
                            'price' => 299.98,
                            'quantity' => 1,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::NONE,
                            'total_discount' => 0
                        ),
                    )
                )
            ),
            array(
                array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1',  100, 3, TaxType::VAT_18, 0, 100),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2',  200, 3, TaxType::VAT_18, 0, 10.51),
                    new Item(3, Item::TYPE_SHIPPING, 'Shipping', 'SHIPPING',  300, 1, TaxType::NONE, 0, 0),
                ),
                array(),
                array(
                    'email' => 'email@example.com',
                    'phone' => '+79021111111',
                    'items' => array(
                        array(
                            /**
                             * price = 100 - (100 / 3) = 66.67
                             * remainder += 0.01
                             */
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 66.67,
                            'quantity' => 3,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            /**
                             * price = 200 - (10.51 / 3) = 194.74
                             * remainder += 0.01
                             */
                            'id' => 2,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 2',
                            'code' => 'PRODUCT2',
                            'price' => 196.5,
                            'quantity' => 3,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            /**
                             * price = 300 - (0.01 + 0.01)
                             */
                            'id' => 3,
                            'type' => Item::TYPE_SHIPPING,
                            'name' => 'Shipping',
                            'code' => 'SHIPPING',
                            'price' => 299.98,
                            'quantity' => 1,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::NONE,
                            'total_discount' => 0
                        ),
                    )
                )
            ),
            array(
                array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1',  100, 3, TaxType::VAT_18, 0, 100),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2',  200, 3, TaxType::VAT_18, 0, 10.51),
                ),
                array(),
                array(
                    'email' => 'email@example.com',
                    'phone' => '+79021111111',
                    'items' => array(
                        array(
                            /**
                             * price = 100 - (100 / 3) = 66.67
                             * remainder += 0.01
                             */
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 66.67,
                            'quantity' => 2,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            /**
                             * price = 200 - (10.51 / 3) = 194.74
                             * remainder += 0.01
                             */
                            'id' => 2,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 2',
                            'code' => 'PRODUCT2',
                            'price' => 196.5,
                            'quantity' => 3,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            /**
                             * price = 66.67 - (0.01 + 0.01)
                             */
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 66.65,
                            'quantity' => 1,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                    )
                ),
            ),
            array(
                array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1',  454.03, 3, TaxType::VAT_18, 0, -0.02),
                ),
                array(),
                array(
                    'email' => 'email@example.com',
                    'phone' => '+79021111111',
                    'items' => array(
                        array(
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 454.03,
                            'quantity' => 2,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 454.05,
                            'quantity' => 1,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                    )
                ),
            ),
            array(
                array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1',  454.03, 4, TaxType::VAT_18, 0, -0.02),
                ),
                array(),
                array(
                    'email' => 'email@example.com',
                    'phone' => '+79021111111',
                    'items' => array(
                        array(
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 454.03,
                            'quantity' => 3,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 454.05,
                            'quantity' => 1,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                    )
                ),
            ),
            array(
                array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1',  454.03, 5, TaxType::VAT_18, 0, -0.02),
                ),
                array(),
                array(
                    'email' => 'email@example.com',
                    'phone' => '+79021111111',
                    'items' => array(
                        array(
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 454.03,
                            'quantity' => 4,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 454.05,
                            'quantity' => 1,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                    )
                ),
            ),
            array(
                array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1',  100, 3, TaxType::VAT_18, 0, 100),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2',  200, 3, TaxType::VAT_18, 0, 10.51),
                    new Item(3, Item::TYPE_SHIPPING, 'Shipping', 'SHIPPING',  300, 1, TaxType::NONE, 0, 0),
                ),
                array(Item::TYPE_PRODUCT),
                array(
                    'email' => 'email@example.com',
                    'phone' => '+79021111111',
                    'items' => array(
                        array(
                            /**
                             * price = 100 - (100 / 3) = 66.67
                             * remainder += 0.01
                             */
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 66.67,
                            'quantity' => 2, //-1 for remainder
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            /**
                             * price = 200 - (10.51 / 3) = 194.74
                             * remainder += 0.01
                             */
                            'id' => 2,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 2',
                            'code' => 'PRODUCT2',
                            'price' => 196.5,
                            'quantity' => 3,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 3,
                            'type' => Item::TYPE_SHIPPING,
                            'name' => 'Shipping',
                            'code' => 'SHIPPING',
                            'price' => 300,
                            'quantity' => 1,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::NONE,
                            'total_discount' => 0
                        ),
                        array(
                            /**
                             * price = 100 - (100 / 3) = 66.67 - (0.01 + 0.01)
                             */
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 66.65,
                            'quantity' => 1,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                    )
                )
            ),
        );
    }

    /**
     * @param $items
     * @param $id
     * @param $type
     * @param $expected
     * @dataProvider dpRemoveItem
     */
    public function testRemoveItem($items, $id, $type, $expected)
    {
        $receipt = new Receipt('email@example.com', '+79021111111', $items);
        $receipt->removeItem($id, $type);

        $this->assertEquals($expected, $receipt->toArray());
    }

    public function dpRemoveItem()
    {
        return array(
            array(
                array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1', 100, 1, TaxType::VAT_18, 0),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2', 200, 2, TaxType::VAT_18, 0),
                    new Item(3, Item::TYPE_SHIPPING, 'Shipping', 'SHIPPING', 300, 1, TaxType::NONE, 0),
                ),
                1, Item::TYPE_PRODUCT,
                array(
                    'email' => 'email@example.com',
                    'phone' => '+79021111111',
                    'items' => array(
                        array(
                            'id' => 2,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 2',
                            'code' => 'PRODUCT2',
                            'price' => 200,
                            'quantity' => 2,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 3,
                            'type' => Item::TYPE_SHIPPING,
                            'name' => 'Shipping',
                            'code' => 'SHIPPING',
                            'price' => 300,
                            'quantity' => 1,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::NONE,
                            'total_discount' => 0
                        ),
                    )
                )
            ),
            array(
                array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1', 100, 1, TaxType::VAT_18, 0),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2', 200, 1, TaxType::VAT_18, 0),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2', 200, 2, TaxType::VAT_18, 0),
                    new Item(3, Item::TYPE_SHIPPING, 'Shipping', 'SHIPPING', 300, 1, TaxType::NONE, 0),
                ),
                2, Item::TYPE_PRODUCT,
                array(
                    'email' => 'email@example.com',
                    'phone' => '+79021111111',
                    'items' => array(
                        array(
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 100,
                            'quantity' => 1,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 3,
                            'type' => Item::TYPE_SHIPPING,
                            'name' => 'Shipping',
                            'code' => 'SHIPPING',
                            'price' => 300,
                            'quantity' => 1,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::NONE,
                            'total_discount' => 0
                        ),
                    )
                )
            )
        );
    }

    /**
     * @param $items
     * @param $id
     * @param $type
     * @param $quantity
     * @param $expected
     * @dataProvider dpSetItemQuantity
     */
    public function testSetItemQuantity($items, $id, $type, $quantity, $expected)
    {
        $receipt = new Receipt('email@example.com', '+79021111111', $items);
        $receipt->setItemQuantity($id, $type, $quantity);

        $this->assertEquals($expected, $receipt->toArray());
    }

    public function dpSetItemQuantity()
    {
        return array(
            array(
                array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1', 100, 1, TaxType::VAT_18, 0),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2', 200, 2, TaxType::VAT_18, 0)
                ),
                1, Item::TYPE_PRODUCT, 2,
                array(
                    'email' => 'email@example.com',
                    'phone' => '+79021111111',
                    'items' => array(
                        array(
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 100,
                            'quantity' => 2,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 2,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 2',
                            'code' => 'PRODUCT2',
                            'price' => 200,
                            'quantity' => 2,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                    )
                )
            ),
            array(
                array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1', 100, 3, TaxType::VAT_18, 0),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2', 200, 2, TaxType::VAT_18, 0)
                ),
                1, Item::TYPE_PRODUCT, 1,
                array(
                    'email' => 'email@example.com',
                    'phone' => '+79021111111',
                    'items' => array(
                        array(
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 100,
                            'quantity' => 1,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 2,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 2',
                            'code' => 'PRODUCT2',
                            'price' => 200,
                            'quantity' => 2,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                    )
                )
            ),
            array(
                array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1', 100, 3, TaxType::VAT_18, 0),
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1', 100, 1, TaxType::VAT_18, 0),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2', 200, 2, TaxType::VAT_18, 0)
                ),
                1, Item::TYPE_PRODUCT, 1,
                array(
                    'email' => 'email@example.com',
                    'phone' => '+79021111111',
                    'items' => array(
                        array(
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 100,
                            'quantity' => 1,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 2,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 2',
                            'code' => 'PRODUCT2',
                            'price' => 200,
                            'quantity' => 2,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                    )
                )
            ),
            array(
                array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1', 100, 3, TaxType::VAT_18, 0),
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1', 100, 1, TaxType::VAT_18, 0),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2', 200, 2, TaxType::VAT_18, 0)
                ),
                1, Item::TYPE_PRODUCT, 3,
                array(
                    'email' => 'email@example.com',
                    'phone' => '+79021111111',
                    'items' => array(
                        array(
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 100,
                            'quantity' => 2,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 100,
                            'quantity' => 1,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 2,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 2',
                            'code' => 'PRODUCT2',
                            'price' => 200,
                            'quantity' => 2,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                    )
                )
            ),
            array(
                array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1', 100, 3, TaxType::VAT_18, 0),
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1', 100, 1, TaxType::VAT_18, 0),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2', 200, 2, TaxType::VAT_18, 0)
                ),
                1, Item::TYPE_PRODUCT, 5,
                array(
                    'email' => 'email@example.com',
                    'phone' => '+79021111111',
                    'items' => array(
                        array(
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 100,
                            'quantity' => 4,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 1,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 1',
                            'code' => 'PRODUCT1',
                            'price' => 100,
                            'quantity' => 1,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 2,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 2',
                            'code' => 'PRODUCT2',
                            'price' => 200,
                            'quantity' => 2,
                            'tax_sum' => 0,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                    )
                )
            )
        );
    }

    /**
     * @param $items
     * @param $item_types
     * @param $expected
     * @dataProvider dpGetTotal
     */
    public function testGetTotal($items, $item_types, $expected)
    {
        $receipt = new Receipt('email@example.com', '+79021111111', $items);

        $this->assertEquals($expected, $receipt->getTotal($item_types));
    }

    public function dpGetTotal()
    {
        return array(
            array(
                array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1', 100, 1, TaxType::VAT_18, 0),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2', 200, 1, TaxType::VAT_18, 0),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2', 200, 2, TaxType::VAT_18, 0),
                    new Item(3, Item::TYPE_SHIPPING, 'Shipping', 'SHIPPING', 300, 1, TaxType::NONE, 0),
                ),
                array(),
                1000
            ),
            array(
                array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1', 100, 1, TaxType::VAT_18, 0),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2', 200, 1, TaxType::VAT_18, 0),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2', 200, 2, TaxType::VAT_18, 0),
                    new Item(3, Item::TYPE_SHIPPING, 'Shipping', 'SHIPPING', 300, 1, TaxType::NONE, 0),
                ),
                array(Item::TYPE_PRODUCT),
                700
            ),
        );
    }
}