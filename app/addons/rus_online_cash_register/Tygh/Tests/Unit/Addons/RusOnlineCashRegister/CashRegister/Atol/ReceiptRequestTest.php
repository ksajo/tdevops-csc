<?php


namespace Tygh\Tests\Unit\RusOnlineCashRegister\CashRegister\Atol;

use PHPUnit_Framework_TestCase;
use Tygh\Addons\RusOnlineCashRegister\CashRegister\Atol\ReceiptRequest;
use Tygh\Addons\RusOnlineCashRegister\Receipt\Receipt;

class ReceiptRequestTest extends PHPUnit_Framework_TestCase
{
    public $runTestInSeparateProcess = true;
    public $backupGlobals = false;
    public $preserveGlobalState = false;

    protected $inn = '12345667878';
    protected $payment_address = 'г. Москва ул. Ленина д. 20';
    protected $callback_url = 'https://example.com/callback';

        /**
     * @param $receipt
     * @param $expected
     * @dataProvider dpJson
     */
    public function testJson($receipt, $expected)
    {
        $request = new ReceiptRequest($receipt, $this->inn, $this->payment_address, $this->callback_url);

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
                )),
                json_encode(array(
                    'timestamp' => '10.01.2017 02:24:56 PM',
                    'external_id' => 'order_10_' . strtotime('10.01.2017 14:24:56'),
                    'service' => array(
                        'callback_url' => $this->callback_url,
                        'inn' => $this->inn,
                        'payment_address' => $this->payment_address,
                    ),
                    'receipt' => array(
                        'total' => 1013.00,
                        'attributes' => array(
                            'sno' => 'osn',
                            'email' => 'customer@example.com',
                            'phone' => '',
                        ),
                        'items' => array(
                            array(
                                'name' => 'ASUS CP6130',
                                'price' => 972.00,
                                'tax' => 'vat10',
                                'quantity' => 1.0,
                                'sum' => 962.0
                            ),
                            array(
                                'name' => 'Custom shipping method',
                                'price' => 28.0,
                                'tax' => 'vat0',
                                'quantity' => 2.0,
                                'sum' => 28.0 * 2 - 5
                            ),
                        ),
                        'payments' => array(
                            array(
                                'type' => 10,
                                'sum' => 1013.00
                            )
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
                )),
                json_encode(array(
                    'timestamp' => '10.01.2017 02:24:56 PM',
                    'external_id' => 'order_10_' . strtotime('10.01.2017 14:24:56'),
                    'service' => array(
                        'callback_url' => $this->callback_url,
                        'inn' => $this->inn,
                        'payment_address' => $this->payment_address,
                    ),
                    'receipt' => array(
                        'total' => 1013.00,
                        'attributes' => array(
                            'sno' => 'osn',
                            'email' => '',
                            'phone' => '9073456797',
                        ),
                        'items' => array(
                            array(
                                'name' => 'Невероятно очень длинное название товара, которое не умещаетс...',
                                'price' => 972.00,
                                'tax' => 'vat10',
                                'quantity' => 1.0,
                                'sum' => 962.0
                            ),
                            array(
                                'name' => 'Custom shipping method',
                                'price' => 28.0,
                                'tax' => 'vat0',
                                'quantity' => 2.0,
                                'sum' => 28.0 * 2 - 5
                            ),
                        ),
                        'payments' => array(
                            array(
                                'type' => 10,
                                'sum' => 1013.00
                            )
                        ),
                    )
                ))
            )
        );
    }
}
