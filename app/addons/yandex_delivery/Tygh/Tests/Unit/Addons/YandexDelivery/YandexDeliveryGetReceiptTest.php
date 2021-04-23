<?php


namespace Tygh\Tests\Unit\Addons\YandexDelivery;


use Tygh\Addons\RusTaxes\Receipt\Item;
use Tygh\Addons\RusTaxes\Receipt\Receipt;
use Tygh\Addons\RusTaxes\TaxType;
use Tygh\Tests\Unit\ATestCase;
use Tygh\Tygh;

class YandexDeliveryGetReceiptTest extends ATestCase
{
    public $runTestInSeparateProcess = true;
    public $backupGlobals = false;
    public $preserveGlobalState = false;

    protected $app;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        define('AREA', 'A');
        define('CART_LANGUAGE', 'en');
        define('CART_PRIMARY_CURRENCY', 'RUB');

        $this->requireCore('addons/yandex_delivery/func.php');

        $this->app = Tygh::createApplication();

        $factory = $this->getMockBuilder('\Tygh\Addons\RusTaxes\ReceiptFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $factory->method('createReceiptFromOrder')->willReturnCallback(array($this, 'mockCreateReceiptFromOrder'));

        $this->app['addons.rus_taxes.receipt_factory'] = $factory;
    }

    /**
     * @param $order_info
     * @param $shipment
     * @param $expected
     * @dataProvider dpGetReceipt
     */
    public function testGetReceipt($order_info, $shipment, $expected)
    {
        $receipt = fn_yandex_delivery_get_receipt($order_info, $shipment);

        $this->assertEquals($expected, $receipt->toArray());
    }

    public function dpGetReceipt()
    {
        return array(
            array(
                array(
                    'order_id' => 100,
                    'products' => array(
                        1 => array(
                            'amount' => 1
                        ),
                        2 => array(
                            'amount' => 2
                        ),
                    )
                ),
                array(
                    'products' => array(
                        1 => 1,
                        2 => 2,
                    )
                ),
                array(
                    'email' => 'example@example.com',
                    'phone' => '',
                    'items' => array(
                        array(
                            'id' => 1,
                            'name' => 'Product 1',
                            'type' => Item::TYPE_PRODUCT,
                            'code' => 'PRODUCT1',
                            'price' => 100,
                            'quantity' => 1,
                            'tax_sum' => 18,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 2,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 2',
                            'code' => 'PRODUCT2',
                            'price' => 100,
                            'quantity' => 2,
                            'tax_sum' => 18,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 3,
                            'type' => Item::TYPE_SHIPPING,
                            'name' => 'Shipping',
                            'code' => 'SHIPPING',
                            'price' => 100,
                            'quantity' => 1,
                            'tax_sum' => 18,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 4,
                            'type' => Item::TYPE_SURCHARGE,
                            'name' => 'Surcharge',
                            'code' => 'SURCHARGE',
                            'price' => 100,
                            'quantity' => 1,
                            'tax_sum' => 18,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        )
                    )
                )
            ),
            array(
                array(
                    'order_id' => 101,
                    'products' => array(
                        1 => array(
                            'amount' => 1
                        ),
                        2 => array(
                            'amount' => 2
                        ),
                    )
                ),
                array(
                    'products' => array(
                        1 => 1,
                        2 => 2,
                    )
                ),
                array(
                    'email' => 'example@example.com',
                    'phone' => '',
                    'items' => array(
                        array(
                            'id' => 1,
                            'name' => 'Product 1',
                            'type' => Item::TYPE_PRODUCT,
                            'code' => 'PRODUCT1',
                            'price' => 101, //+1 from remainder
                            'quantity' => 1,
                            'tax_sum' => 18,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 2,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 2',
                            'code' => 'PRODUCT2',
                            'price' => 100,
                            'quantity' => 2,
                            'tax_sum' => 18,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 3,
                            'type' => Item::TYPE_SHIPPING,
                            'name' => 'Shipping',
                            'code' => 'SHIPPING',
                            'price' => 100,
                            'quantity' => 1,
                            'tax_sum' => 18,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 4,
                            'type' => Item::TYPE_SURCHARGE,
                            'name' => 'Surcharge',
                            'code' => 'SURCHARGE',
                            'price' => 100,
                            'quantity' => 1,
                            'tax_sum' => 18,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        )
                    )
                )
            ),
            array(
                array(
                    'order_id' => 102,
                    'products' => array(
                        1 => array(
                            'amount' => 9
                        ),
                        2 => array(
                            'amount' => 2
                        ),
                    )
                ),
                array(
                    'products' => array(
                        1 => 9,
                        2 => 2,
                    )
                ),
                array(
                    'email' => 'example@example.com',
                    'phone' => '',
                    'items' => array(
                        array(
                            'id' => 1,
                            'name' => 'Product 1',
                            'type' => Item::TYPE_PRODUCT,
                            'code' => 'PRODUCT1',
                            'price' => 100,
                            'quantity' => 9,
                            'tax_sum' => 18,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 2,
                            'type' => Item::TYPE_PRODUCT,
                            'name' => 'Product 2',
                            'code' => 'PRODUCT2',
                            'price' => 100,
                            'quantity' => 2,
                            'tax_sum' => 18,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 3,
                            'type' => Item::TYPE_SHIPPING,
                            'name' => 'Shipping',
                            'code' => 'SHIPPING',
                            'price' => 100,
                            'quantity' => 1,
                            'tax_sum' => 18,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 4,
                            'type' => Item::TYPE_SURCHARGE,
                            'name' => 'Surcharge',
                            'code' => 'SURCHARGE',
                            'price' => 105, //+5 from remainder
                            'quantity' => 1,
                            'tax_sum' => 18,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        )
                    )
                )
            ),
            array(
                array(
                    'order_id' => 103,
                    'products' => array(
                        1 => array(
                            'amount' => 8
                        )
                    )
                ),
                array(
                    'products' => array(
                        1 => 8
                    )
                ),
                array(
                    'email' => 'example@example.com',
                    'phone' => '',
                    'items' => array(
                        array(
                            'id' => 1,
                            'name' => 'Product 1',
                            'type' => Item::TYPE_PRODUCT,
                            'code' => 'PRODUCT1',
                            'price' => 63,
                            'quantity' => 7,
                            'tax_sum' => 18,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 3,
                            'type' => Item::TYPE_SHIPPING,
                            'name' => 'Shipping',
                            'code' => 'SHIPPING',
                            'price' => 190,
                            'quantity' => 1,
                            'tax_sum' => 18,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        ),
                        array(
                            'id' => 1,
                            'name' => 'Product 1',
                            'type' => Item::TYPE_PRODUCT,
                            'code' => 'PRODUCT1',
                            'price' => 66,
                            'quantity' => 1,
                            'tax_sum' => 18,
                            'tax_type' => TaxType::VAT_18,
                            'total_discount' => 0
                        )
                    )
                )
            )
        );
    }

    public function mockCreateReceiptFromOrder($order)
    {
        $receipt = null;

        switch ($order['order_id']) {
            case 100:
                $receipt = new Receipt('example@example.com', '', array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1', 100, 1, TaxType::VAT_18, 18, 0),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2', 100, 2, TaxType::VAT_18, 18, 0),
                    new Item(3, Item::TYPE_SHIPPING, 'Shipping', 'SHIPPING', 100, 1, TaxType::VAT_18, 18, 0),
                    new Item(4, Item::TYPE_SURCHARGE, 'Surcharge', 'SURCHARGE', 100, 1, TaxType::VAT_18, 18, 0),
                    new Item(5, Item::TYPE_GIFT_CERTIFICATE, 'Gift', 'GIFT', 100, 1, TaxType::VAT_18, 18, 0),
                ));
                break;
            case 101:
                $receipt = new Receipt('example@example.com', '', array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1', 100.5, 1, TaxType::VAT_18, 18, 0),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2', 100.6, 2, TaxType::VAT_18, 18, 0),
                    new Item(3, Item::TYPE_SHIPPING, 'Shipping', 'SHIPPING', 100, 1, TaxType::VAT_18, 18, 0),
                    new Item(4, Item::TYPE_SURCHARGE, 'Surcharge', 'SURCHARGE', 100, 1, TaxType::VAT_18, 18, 0)
                ));
                break;
            case 102:
                $receipt = new Receipt('example@example.com', '', array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1', 100.5, 9, TaxType::VAT_18, 18, 0),
                    new Item(2, Item::TYPE_PRODUCT, 'Product 2', 'PRODUCT2', 100.6, 2, TaxType::VAT_18, 18, 0),
                    new Item(3, Item::TYPE_SHIPPING, 'Shipping', 'SHIPPING', 100, 1, TaxType::VAT_18, 18, 0),
                    new Item(4, Item::TYPE_SURCHARGE, 'Surcharge', 'SURCHARGE', 100, 1, TaxType::VAT_18, 18, 0)
                ));
                break;
            case 103:
                $receipt = new Receipt('example@example.com', '', array(
                    new Item(1, Item::TYPE_PRODUCT, 'Product 1', 'PRODUCT1', 63.4, 8, TaxType::VAT_18, 18, 0),
                    new Item(3, Item::TYPE_SHIPPING, 'Shipping', 'SHIPPING', 190, 1, TaxType::VAT_18, 18, 0),
                ));
                break;
        }

        return $receipt;
    }
}