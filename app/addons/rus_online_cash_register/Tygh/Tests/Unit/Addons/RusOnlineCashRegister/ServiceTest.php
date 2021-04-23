<?php


namespace Tygh\Tests\Unit\RusOnlineCashRegister;

use PHPUnit_Framework_TestCase;
use Tygh\Addons\RusOnlineCashRegister\Receipt\Receipt;
use Tygh\Addons\RusTaxes\Receipt\Receipt as BaseReceipt;
use Tygh\Addons\RusTaxes\Receipt\Item as BaseItem;
use Tygh\Addons\RusOnlineCashRegister\Service;

class ServiceTest extends PHPUnit_Framework_TestCase
{
    public $runTestInSeparateProcess = true;
    public $backupGlobals = false;
    public $preserveGlobalState = false;


    /**
     * @param $order
     * @param $type
     * @param $expected
     * @dataProvider dpGetReceiptFromOrder
     */
    public function testGetReceiptFromOrder($order, $type, $expected)
    {
        define('TIME', 100000);

        $cash_register = $this->getMockBuilder('\Tygh\Addons\RusOnlineCashRegister\CashRegister\Atol\CashRegister')
            ->disableOriginalConstructor()
            ->getMock();

        $receipt_repository = $this->getMockBuilder('\Tygh\Addons\RusOnlineCashRegister\ReceiptRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $receipt_factory = $this->getMockBuilder('\Tygh\Addons\RusTaxes\ReceiptFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $receipt_factory->method('createReceiptFromOrder')
            ->willReturnCallback(array($this, 'createReceiptFromOrderCallback'));

        $service = new Service(
            $cash_register,
            $receipt_repository,
            $receipt_factory,
            array(
                1 => 1,
                2 => 1,
            ),
            'RUB',
            'Osn'
        );

        $receipt = $service->getReceiptFromOrder($order, $type, Receipt::PAYMENT_METHOD_FULL_PAYMENT);
        $this->assertEquals($expected, $receipt->toArray());
    }

    public function dpGetReceiptFromOrder()
    {
        return array(
            array(
                array(
                    'order_id' => 100,
                    'total' => 666.09,
                    'subtotal_discount' => 87.45,
                    'payment_surcharge' => 10,
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'payment_id' => 2,
                    'payment_method' => array(
                        'payment_id' => 2,
                        'surcharge_title' => 'Surcharge'
                    ),
                    'products' => array(
                        1237327324 => array(
                            'item_id' => '1237327324',
                            'amount' => 1,
                            'price' => 120,
                            'original_price' => 120,
                            'product' => '16GB A Series Walkman Video MP3',
                            'subtotal' => 120,
                            'tax_value' => 18.31
                        ),
                        1706372553 => array(
                            'item_id' => '1706372553',
                            'amount' => 2,
                            'product' => 'X-Box One',
                            'price' => 302.77,
                            'original_price' => 340,
                            'subtotal' => 605.54,
                            'tax_value' => 92.38
                        )
                    ),
                    'taxes' => array(
                        6 => array(
                            'price_includes_tax' => 'Y',
                            'tax_subtotal' => 114.97,
                            'applies' => array(
                                'P_1237327324' => 18.31,
                                'P_1706372553' => 92.38,
                                'S_0_1' => 2.75,
                                'PS_2' => 1.53,
                            )
                        )
                    ),
                    'shipping' => array(
                        array(
                            'shipping_id' => 1,
                            'shipping' => 'Custom shipping method',
                            'rate' => 18
                        )
                    ),
                    'secondary_currency' => 'RUB'
                ),
                Receipt::TYPE_SELL,
                array(
                    'id' => null,
                    'type' => Receipt::TYPE_SELL,
                    'requisites' => null,
                    'uuid' => null,
                    'status' => Receipt::STATUS_WAIT,
                    'status_message' => null,
                    'sno' => 'Osn',
                    'object_id' => 100,
                    'object_type' => 'order',
                    'timestamp' => 100000,
                    'email' => 'customer@example.com',
                    'phone' => '+79021114567',
                    'items' => array(
                        array(
                            'name' => '16GB A Series Walkman Video MP3',
                            'price' => 120,
                            'quantity' => 1.0,
                            'tax_type' => 'vat118',
                            'tax_sum' =>  2.4,
                            'discount' => 13.93
                        ),
                        array(
                            'name' => 'X-Box One',
                            'price' => 340,
                            'quantity' => 2.0,
                            'tax_type' => 'vat118',
                            'tax_sum' =>  0,
                            'discount' => 144.73
                        ),
                        array(
                            'name' => 'shipping',
                            'price' => 18.0,
                            'quantity' => 1.0,
                            'tax_type' => 'vat118',
                            'tax_sum' =>  0,
                            'discount' => 2.09
                        ),
                        array(
                            'name' => 'surcharge',
                            'price' => 10,
                            'quantity' => 1.0,
                            'tax_type' => 'vat118',
                            'tax_sum' =>  0,
                            'discount' => 1.16
                        ),
                    ),
                    'payments' => array(
                        array(
                            'type' => 1,
                            'sum' => 666.09
                        )
                    ),
                    'currency' => 'RUB',
                ),
            )
        );
    }

    public function createReceiptFromOrderCallback($order)
    {
        switch ($order['order_id']) {
            case 100:
                return new BaseReceipt(
                    'customer@example.com',
                    '+79021114567',
                    array(
                        new BaseItem(1, BaseItem::TYPE_PRODUCT, '16GB A Series Walkman Video MP3', 'PRODUCT1', 120, 1, 'vat118', 2.4, 13.93),
                        new BaseItem(2, BaseItem::TYPE_PRODUCT, 'X-Box One', 'PRODUCT2', 340, 2, 'vat118', 0, 144.73),
                        new BaseItem(3, BaseItem::TYPE_SHIPPING, 'shipping', 'SHIPPING', 18.0, 1, 'vat118', 0, 2.09),
                        new BaseItem(3, BaseItem::TYPE_SURCHARGE, 'surcharge', 'SURCHARGE', 10, 1, 'vat118', 0, 1.16),
                    )
                );
                break;
        }
    }
}