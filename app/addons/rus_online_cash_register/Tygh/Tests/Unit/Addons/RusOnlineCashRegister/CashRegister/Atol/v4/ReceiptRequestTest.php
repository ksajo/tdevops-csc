<?php

namespace Tygh\Tests\Unit\RusOnlineCashRegister\CashRegister\Atol\v4;

use PHPUnit_Framework_TestCase;
use Tygh\Addons\RusOnlineCashRegister\CashRegister\Atol\v4\ReceiptRequest;
use Tygh\Addons\RusOnlineCashRegister\Receipt\Receipt;

class ReceiptRequestTest extends PHPUnit_Framework_TestCase
{
    public $runTestInSeparateProcess = true;
    public $backupGlobals = false;
    public $preserveGlobalState = false;

    protected $inn = '12345667878';
    protected $payment_address = 'г. Москва ул. Ленина д. 20';
    protected $callback_url = 'https://example.com/callback';
    protected $company_email = 'admin@example.com';

    /**
     * @param $receipt
     * @param $expected
     * @dataProvider dpJson
     */
    public function testJson($receipt, $expected)
    {
        $request = new ReceiptRequest($receipt, $this->inn, $this->payment_address, $this->callback_url, $this->company_email);

        $this->assertJsonStringEqualsJsonString($expected, $request->json());
    }

    public function dpJson()
    {
        return array(
            array(
                Receipt::fromArray(array(
                    'object_id' => 10,
                    'object_type' => 'order',
                    'sno' => 'osn',
                    'timestamp' => strtotime('10.01.2017 14:24:56'),
                    'email' => 'customer@example.com',
                    'phone' => '+79073456797',
                    'items' => array(
                        array(
                            'name' => 'ASUS CP6130',
                            'price' => 972.00,
                            'quantity' => 1.0,
                            'tax_type' => 'vat10',
                            'tax_sum' => 10,
                            'discount' => 10
                        ),
                        array(
                            'name' => 'Custom shipping method',
                            'price' => 28.0,
                            'quantity' => 2.0,
                            'tax_type' => 'vat0',
                            'tax_sum' => 5,
                            'discount' => 5
                        )
                    ),
                    'payments' => array(
                        array(
                            'type' => 10,
                            'sum' => 1013.00
                        )
                    ),
                    'payment_method' => Receipt::PAYMENT_METHOD_FULL_PAYMENT,
                )),
                json_encode(array(
                    'timestamp' => '10.01.2017 02:24:56',
                    'external_id' => 'order_10_' . strtotime('10.01.2017 14:24:56'),
                    'service' => array(
                        'callback_url' => $this->callback_url,
                    ),
                    'receipt' => array(
                        'total' => 1013.00,
                        'company' => array(
                            'inn' => $this->inn,
                            'payment_address' => $this->payment_address,
                            'sno' => 'osn',
                            'email' => 'admin@example.com',
                        ),
                        'items' => array(
                            array(
                                'name' => 'ASUS CP6130',
                                'price' => 972.00,
                                'vat' => [
                                    'type' => 'vat10',
                                ],
                                'quantity' => 1.0,
                                'sum' => 962.0,
                                'payment_method' => 'full_payment',
                                'payment_object' => 'payment'
                            ),
                            array(
                                'name' => 'Custom shipping method',
                                'price' => 28.0,
                                'vat' => [
                                    'type' => 'vat0',
                                ],
                                'quantity' => 2.0,
                                'sum' => 28.0 * 2 - 5,
                                'payment_method' => 'full_payment',
                                'payment_object' => 'payment'
                            ),
                        ),
                        'payments' => array(
                            array(
                                'type' => 10,
                                'sum' => 1013.00
                            )
                        ),
                        'client' => array(
                            'email' => 'customer@example.com',
                        ),
                    )
                ))
            ),
            array(
                Receipt::fromArray(array(
                    'object_id' => 10,
                    'object_type' => 'order',
                    'sno' => 'osn',
                    'timestamp' => strtotime('10.01.2017 14:24:56'),
                    'email' => '',
                    'phone' => '+79073456797',
                    'items' => array(
                        array(
                            'name' => 'Невероятно очень длинное название товара, которое не умещается в 64 символа',
                            'price' => 972.00,
                            'quantity' => 1.0,
                            'tax_type' => 'vat10',
                            'tax_sum' => 10,
                            'discount' => 10
                        ),
                        array(
                            'name' => 'Custom shipping method',
                            'price' => 28.0,
                            'quantity' => 2.0,
                            'tax_type' => 'vat0',
                            'tax_sum' => 5,
                            'discount' => 5
                        )
                    ),
                    'payments' => array(
                        array(
                            'type' => 10,
                            'sum' => 1013.00
                        )
                    ),
                    'payment_method' => Receipt::PAYMENT_METHOD_FULL_PAYMENT,
                )),
                json_encode(array(
                    'timestamp' => '10.01.2017 02:24:56',
                    'external_id' => 'order_10_' . strtotime('10.01.2017 14:24:56'),
                    'service' => array(
                        'callback_url' => $this->callback_url,
                    ),
                    'receipt' => array(
                        'total' => 1013.00,
                        'company' => array(
                            'inn' => $this->inn,
                            'payment_address' => $this->payment_address,
                            'sno' => 'osn',
                            'email' => 'admin@example.com',
                        ),
                        'items' => array(
                            array(
                                'name' => 'Невероятно очень длинное название товара, которое не умещаетс...',
                                'price' => 972.00,
                                'vat' => [
                                    'type' => 'vat10',
                                ],
                                'quantity' => 1.0,
                                'sum' => 962.0,
                                'payment_method' => 'full_payment',
                                'payment_object' => 'payment'
                            ),
                            array(
                                'name' => 'Custom shipping method',
                                'price' => 28.0,
                                'vat' => [
                                    'type' => 'vat0',
                                ],
                                'quantity' => 2.0,
                                'sum' => 28.0 * 2 - 5,
                                'payment_method' => 'full_payment',
                                'payment_object' => 'payment'
                            ),
                        ),
                        'payments' => array(
                            array(
                                'type' => 10,
                                'sum' => 1013.00
                            )
                        ),
                        'client' => array(
                            'phone' => '9073456797',
                        ),
                    )
                ))
            )
        );
    }
}

    
