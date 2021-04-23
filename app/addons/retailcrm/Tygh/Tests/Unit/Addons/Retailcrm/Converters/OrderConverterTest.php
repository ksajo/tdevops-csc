<?php


namespace Tygh\Tests\Unit\Addons\Retailcrm\Converters;


use Tygh\Addons\Retailcrm\Converters\OrderConverter;
use Tygh\Tests\Unit\ATestCase;

class OrderConverterTest extends ATestCase
{
    public $runTestInSeparateProcess = true;
    public $backupGlobals = false;
    public $preserveGlobalState = false;

    protected $settings;

    public function setUp()
    {
        $this->settings = $this->getMockBuilder('\Tygh\Addons\Retailcrm\Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $this->settings->method('getExternalOrderStatus')->willReturnMap(array(
            array('O', 'open'),
            array('P', 'processed'),
        ));

        $this->settings->method('getExternalPaymentType')->willReturnMap(array(
            array(1, 'custom_payment'),
            array(2, 'credit_card'),
        ));

        $this->settings->method('getExternalShippingType')->willReturnMap(array(
            array(1, 'custom_shipping')
        ));

        $this->settings->method('getInternalSite')->willReturnMap(array(
           array('sandbox-cs-cart', 1)
        ));

        $this->settings->method('getInternalOrderStatus')->willReturnMap(array(
            array('new', 'O')
        ));

        $this->settings->method('getInternalPaymentType')->willReturnMap(array(
            array('bank-card', 1)
        ));

        $this->settings->method('getInternalShippingType')->willReturnMap(array(
            array('ems', 1)
        ));

        $this->settings->method('getOrderType')->willReturn('individual');
        $this->settings->method('getOrderMethod')->willReturn('cart');

        parent::setUp();
    }

    /**
     * @param $order
     * @param $customer_id
     * @param $expected
     * @dataProvider dpConvertToCrmOrder
     */
    public function testConvertToCrmOrder($order, $customer_id, $expected)
    {
        $converter = new OrderConverter($this->settings);

        $this->assertEquals($expected, $converter->convertToCrmOrder($order, $customer_id));
    }

    public function dpConvertToCrmOrder()
    {
        return array(
            array(
                array(
                    'order_id' => 98,
                    'discount' => 0,
                    'subtotal_discount' => 0,
                    'shipping_cost' => 33.00,
                    'status' => 'O',
                    'notes' => 'client notes',
                    'firstname' => 'Jon',
                    'lastname' => 'Black',
                    'phone' => '+7(901)111-34-56',
                    'email' => 'customer@example.com',
                    's_address' => '44 Main street',
                    's_address_2' => '7',
                    's_city' => 'Boston',
                    's_state' => 'MA',
                    's_country' => 'US',
                    's_zipcode' => '02134',
                    's_state_descr' => 'Massachusetts',
                    's_country_descr' => 'United States',
                    'payment_id' => 1,
                    'products' => array(
                        array(
                            'item_id' => '4090599972',
                            'product_id' => 12,
                            'product_code' => 'U0012O5AF0',
                            'price' => 30.00,
                            'original_price' => 30.00,
                            'amount' => 1,
                            'product' => '100g Pants',
                            'discount' => 0
                        ),
                        array(
                            'item_id' => '1060745282',
                            'product_id' => 17,
                            'product_code' => 'G0017HS88V',
                            'price' => 11.16,
                            'original_price' => 11.16,
                            'amount' => 1,
                            'product' => '101 Things Everyone Should Know About Economics A Down and Dirty Guide to Everything from Securities and Derivatives to Interest Rates and Hedge Funds—And What They Mean For You',
                            'discount' => 0
                        )
                    ),
                    'shipping' => array(
                        array(
                            'shipping_id' => 1,
                            'shipping' => 'Custom shipping method',
                        )
                    )
                ),
                '17',
                array(
                    'number' => 98,
                    'externalId' => 98,
                    'discount' => 0.0,
                    'firstName' => 'Jon',
                    'lastName' => 'Black',
                    'phone' => '+7(901)111-34-56',
                    'email' => 'customer@example.com',
                    'customerComment' => 'client notes',
                    'status' => 'open',
                    'customer' => array(
                        'externalId' => '17',
                    ),
                    'contragent' => array(
                        'contragentType' => 'individual'
                    ),
                    'orderType' => 'individual',
                    'orderMethod' => 'cart',
                    'items' => array(
                        array(
                            'initialPrice' => 30.00,
                            'quantity' => 1,
                            'discount' => 0.0,
                            'productName' => '100g Pants',
                            'product_id' => 12,
                            'offer' => array(
                                'externalId' => '12'
                            )
                        ),
                        array(
                            'initialPrice' => 11.16,
                            'quantity' => 1,
                            'discount' => 0.0,
                            'productName' => '101 Things Everyone Should Know About Economics A Down and Dirty Guide to Everything from Securities and Derivatives to Interest Rates and Hedge Funds—And What They Mean For You',
                            'product_id' => 17,
                            'offer' => array(
                                'externalId' => '17'
                            )
                        ),
                    ),
                    'paymentType' => 'custom_payment',
                    'delivery' => array(
                        'code' => 'custom_shipping',
                        'cost' => 33.00,
                        'address' => array(
                            'index' => '02134',
                            'region' => 'Massachusetts',
                            'city' => 'Boston',
                            'text' => 'United States, Massachusetts, Boston, 44 Main street, 7',
                        )
                    )
                ),
            ),
            array(
                array(
                    'order_id' => 104,
                    'total' => 73.36,
                    'subtotal' => 50.4,
                    'discount' => 5.6,
                    'subtotal_discount' => 5.04,
                    'shipping_cost' => 28.0,
                    'status' => 'P',
                    'notes' => null,
                    'firstname' => 'Wayne',
                    'lastname' => 'Rooney',
                    'phone' => '+7(902)123-45-60',
                    'email' => 'wayne@gmail.com',
                    'payment_id' => 2,
                    's_address' => '44 Main street',
                    's_address_2' => '7',
                    's_city' => 'Boston',
                    's_state' => 'MA',
                    's_country' => 'US',
                    's_zipcode' => '02134',
                    's_state_descr' => 'Massachusetts',
                    's_country_descr' => 'United States',
                    'products' => array(
                        822274303 => array(
                            'item_id' => '822274303',
                            'product_id' => '12',
                            'price' => 25.20,
                            'amount' => 2,
                            'discount' => 2.8,
                            'original_price' => 28,
                            'subtotal' => 50.4,
                            'product' => '100g Pants',
                            'product_options' => array(
                                array(
                                    'option_id' => 4,
                                    'value' => 17,
                                    'inventory' => 'Y',
                                    'option_name' => 'Color',
                                    'variant_name' => 'Black/White/White',
                                ),
                                array(
                                    'option_id' => 3,
                                    'value' => 12,
                                    'inventory' => 'Y',
                                    'option_name' => 'Size',
                                    'variant_name' => 'Small',
                                ),
                                array(
                                    'option_id' => 5,
                                    'value' => 15,
                                    'inventory' => 'N',
                                    'option_name' => 'Notes',
                                    'variant_name' => 'note',
                                )
                            )
                        )
                    ),
                    'shipping' => array(
                        array(
                            'shipping_id' => 1,
                            'shipping' => 'Custom shipping method',
                        )
                    )
                ),
                '3',
                array(
                    'number' => 104,
                    'externalId' => 104,
                    'discount' => 5.04,
                    'firstName' => 'Wayne',
                    'lastName' => 'Rooney',
                    'phone' => '+7(902)123-45-60',
                    'email' => 'wayne@gmail.com',
                    'status' => 'processed',
                    'customer' => array(
                        'externalId' => '3',
                    ),
                    'contragent' => array(
                        'contragentType' => 'individual'
                    ),
                    'orderType' => 'individual',
                    'orderMethod' => 'cart',
                    'items' => array(
                        array(
                            'initialPrice' => 28,
                            'quantity' => 2,
                            'discount' => 2.8,
                            'productName' => '100g Pants',
                            'product_id' => '12',
                            'offer' => array(
                                'externalId' => '12_3_12_4_17'
                            ),
                            'properties' => array(
                                array(
                                    'code' => 4,
                                    'name' => 'Color',
                                    'value' => 'Black/White/White',
                                ),
                                array(
                                    'code' => 3,
                                    'name' => 'Size',
                                    'value' => 'Small',
                                ),
                                array(
                                    'code' => 5,
                                    'name' => 'Notes',
                                    'value' => 'note',
                                ),
                            )
                        )
                    ),
                    'paymentType' => 'credit_card',
                    'delivery' => array(
                        'code' => 'custom_shipping',
                        'cost' => 28.0,
                        'address' => array(
                            'index' => '02134',
                            'region' => 'Massachusetts',
                            'city' => 'Boston',
                            'text' => 'United States, Massachusetts, Boston, 44 Main street, 7',
                        )
                    )
                )
            ),
            array( //With free products
                array(
                    'order_id' => 104,
                    'total' => 73.36,
                    'subtotal' => 50.4,
                    'discount' => 5.6,
                    'subtotal_discount' => 5.04,
                    'shipping_cost' => 28.0,
                    'status' => 'P',
                    'notes' => null,
                    'firstname' => 'Wayne',
                    'lastname' => 'Rooney',
                    'phone' => '+7(902)123-45-60',
                    'email' => 'wayne@gmail.com',
                    'payment_id' => 2,
                    's_address' => '44 Main street',
                    's_address_2' => '7',
                    's_city' => 'Boston',
                    's_state' => 'MA',
                    's_country' => 'US',
                    's_zipcode' => '02134',
                    's_state_descr' => 'Massachusetts',
                    's_country_descr' => 'United States',
                    'products' => array(
                        822274303 => array(
                            'item_id' => '822274303',
                            'product_id' => '12',
                            'price' => 25.20,
                            'amount' => 2,
                            'discount' => 2.8,
                            'original_price' => 28,
                            'subtotal' => 50.4,
                            'product' => '100g Pants',
                            'product_options' => array(
                                array(
                                    'option_id' => 4,
                                    'value' => 17,
                                    'inventory' => 'Y',
                                    'option_name' => 'Color',
                                    'variant_name' => 'Black/White/White',
                                ),
                                array(
                                    'option_id' => 3,
                                    'value' => 12,
                                    'inventory' => 'Y',
                                    'option_name' => 'Size',
                                    'variant_name' => 'Small',
                                ),
                                array(
                                    'option_id' => 5,
                                    'value' => 15,
                                    'inventory' => 'N',
                                    'option_name' => 'Notes',
                                    'variant_name' => 'note',
                                )
                            )
                        ),
                        604697196 => array(
                            'item_id' => '604697196',
                            'product_id' => '17',
                            'price' => 0.00,
                            'amount' => 1,
                            'discount' => 0,
                            'original_price' => 11.16,
                            'subtotal' => 0,
                            'product' => '101 Things Everyone Should Know About Economics A Down and Dirty Guide to Everything from Securities and Derivatives to Interest Rates and Hedge Funds—And What They Mean For You',
                            'extra' => array(
                                'exclude_from_calculate' => true
                            )
                        )
                    ),
                    'shipping' => array(
                        array(
                            'shipping_id' => 1,
                            'shipping' => 'Custom shipping method',
                        )
                    )
                ),
                '3',
                array(
                    'number' => 104,
                    'externalId' => 104,
                    'discount' => 5.04,
                    'firstName' => 'Wayne',
                    'lastName' => 'Rooney',
                    'phone' => '+7(902)123-45-60',
                    'email' => 'wayne@gmail.com',
                    'status' => 'processed',
                    'customer' => array(
                        'externalId' => '3',
                    ),
                    'contragent' => array(
                        'contragentType' => 'individual'
                    ),
                    'orderType' => 'individual',
                    'orderMethod' => 'cart',
                    'items' => array(
                        array(
                            'initialPrice' => 28,
                            'quantity' => 2,
                            'discount' => 2.8,
                            'productName' => '100g Pants',
                            'product_id' => '12',
                            'offer' => array(
                                'externalId' => '12_3_12_4_17'
                            ),
                            'properties' => array(
                                array(
                                    'code' => 4,
                                    'name' => 'Color',
                                    'value' => 'Black/White/White',
                                ),
                                array(
                                    'code' => 3,
                                    'name' => 'Size',
                                    'value' => 'Small',
                                ),
                                array(
                                    'code' => 5,
                                    'name' => 'Notes',
                                    'value' => 'note',
                                ),
                            )
                        ),
                        array(
                            'initialPrice' => 11.16,
                            'quantity' => 1,
                            'discount' => 11.16,
                            'productName' => '101 Things Everyone Should Know About Economics A Down and Dirty Guide to Everything from Securities and Derivatives to Interest Rates and Hedge Funds—And What They Mean For You',
                            'product_id' => '17',
                            'offer' => array(
                                'externalId' => '17'
                            ),
                        )
                    ),
                    'paymentType' => 'credit_card',
                    'delivery' => array(
                        'code' => 'custom_shipping',
                        'cost' => 28.0,
                        'address' => array(
                            'index' => '02134',
                            'region' => 'Massachusetts',
                            'city' => 'Boston',
                            'text' => 'United States, Massachusetts, Boston, 44 Main street, 7',
                        )
                    )
                )
            ),
            array( //with payment surcharge
                array(
                    'order_id' => 105,
                    'discount' => 0,
                    'subtotal_discount' => 0,
                    'shipping_cost' => 33.00,
                    'status' => 'O',
                    'notes' => 'client notes',
                    'firstname' => 'Jon',
                    'lastname' => 'Black',
                    'phone' => '+7(901)111-34-56',
                    'email' => 'customer@example.com',
                    's_address' => '44 Main street',
                    's_address_2' => '7',
                    's_city' => 'Boston',
                    's_state' => 'MA',
                    's_country' => 'US',
                    's_zipcode' => '02134',
                    's_state_descr' => 'Massachusetts',
                    's_country_descr' => 'United States',
                    'payment_id' => 1,
                    'payment_surcharge' => 11.5,
                    'products' => array(
                        array(
                            'item_id' => '4090599972',
                            'product_id' => 12,
                            'product_code' => 'U0012O5AF0',
                            'price' => 30.00,
                            'original_price' => 30.00,
                            'amount' => 1,
                            'product' => '100g Pants',
                            'discount' => 0
                        ),
                        array(
                            'item_id' => '1060745282',
                            'product_id' => 17,
                            'product_code' => 'G0017HS88V',
                            'price' => 11.16,
                            'original_price' => 11.16,
                            'amount' => 1,
                            'product' => '101 Things Everyone Should Know About Economics A Down and Dirty Guide to Everything from Securities and Derivatives to Interest Rates and Hedge Funds—And What They Mean For You',
                            'discount' => 0
                        )
                    ),
                    'shipping' => array(
                        array(
                            'shipping_id' => 1,
                            'shipping' => 'Custom shipping method',
                        )
                    )
                ),
                '3',
                array(
                    'number' => 105,
                    'externalId' => 105,
                    'discount' => 0.0,
                    'firstName' => 'Jon',
                    'lastName' => 'Black',
                    'phone' => '+7(901)111-34-56',
                    'email' => 'customer@example.com',
                    'customerComment' => 'client notes',
                    'status' => 'open',
                    'customer' => array(
                        'externalId' => '3',
                    ),
                    'contragent' => array(
                        'contragentType' => 'individual'
                    ),
                    'orderType' => 'individual',
                    'orderMethod' => 'cart',
                    'items' => array(
                        array(
                            'initialPrice' => 30.00,
                            'quantity' => 1,
                            'discount' => 0.0,
                            'productName' => '100g Pants',
                            'product_id' => 12,
                            'offer' => array(
                                'externalId' => '12'
                            )
                        ),
                        array(
                            'initialPrice' => 11.16,
                            'quantity' => 1,
                            'discount' => 0.0,
                            'productName' => '101 Things Everyone Should Know About Economics A Down and Dirty Guide to Everything from Securities and Derivatives to Interest Rates and Hedge Funds—And What They Mean For You',
                            'product_id' => 17,
                            'offer' => array(
                                'externalId' => '17'
                            )
                        ),
                        array(
                            'initialPrice' => 11.5,
                            'quantity' => 1,
                            'productName' => 'Payment surcharge',
                            'product_id' => 'payment_surcharge',
                            'offer' => array(
                                'externalId' => 'payment_surcharge'
                            )
                        ),
                    ),
                    'paymentType' => 'custom_payment',
                    'delivery' => array(
                        'code' => 'custom_shipping',
                        'cost' => 33.00,
                        'address' => array(
                            'index' => '02134',
                            'region' => 'Massachusetts',
                            'city' => 'Boston',
                            'text' => 'United States, Massachusetts, Boston, 44 Main street, 7',
                        )
                    )
                ),
            ),
        );
    }

    /**
     * @param array $order
     * @param $expected
     * @dataProvider dpConvertToShopOrder
     */
    public function testConvertToShopOrder(array $order, $expected)
    {
        $converter = new OrderConverter($this->settings);

        $this->assertEquals($expected, $converter->convertToShopOrder($order));
    }

    public function dpConvertToShopOrder()
    {
        return array(
            array(
                array(
                    'id' => 58,
                    'orderType' => 'eshop-individual',
                    'orderMethod' => 'phone',
                    'countryIso' => 'RU',
                    'createdAt' => '2017-04-18 15:38:37',
                    'summ' => 49,
                    'totalSumm' => 90.1,
                    'discount' => 10,
                    'discountPercent' => 10,
                    'lastName' => 'Смолов',
                    'firstName' => 'Федор',
                    'email' => 'smolov@example.com',
                    'phone' => '5555555',
                    'customerComment' => 'Комментарии клиента',
                    'managerComment' => 'Комментарии оператора',
                    'site' => 'sandbox-cs-cart',
                    'status' => 'new',
                    'paymentType' => 'bank-card',
                    'customer' => array(
                        'firstName' => 'Игорь',
                        'lastName' => 'Смольников',
                        'email' => 'igor@example.com',
                        'phones' => array(
                            array('number' => '5555555')
                        ),
                        'address' => array(
                            'index' => '123123',
                            'countryIso' => 'RU',
                            'region' => 'Ульяновская область',
                            'city' => 'Ульяновск',
                            'text' => 'ул. Самарская д. 12345',
                        )
                    ),
                    'items' => array(
                        array(
                            'initialPrice' => 30,
                            'discount' => 0,
                            'discountPercent' => 10,
                            'quantity' => 1,
                            'offer' => array(
                                'externalId' => '12_3_12_4_17',
                                'name' => '100g Pants',
                            )
                        ),
                        array(
                            'initialPrice' => 30,
                            'discount' => 5,
                            'discountPercent' => 10,
                            'quantity' => 1,
                            'offer' => array(
                                'externalId' => '12_3_15_4_17',
                                'name' => '100g Pants',
                            )
                        )
                    ),
                    'delivery' => array(
                        'code' => 'ems',
                        'cost' => 56,
                        'address' => array(
                            'index' => '4567',
                            'countryIso' => 'RU',
                            'region' => 'Ульяновская область',
                            'city' => 'Ульяновск',
                            'text' => 'ул. Рябикова, д. 134'
                        )
                    )
                ),
                array(
                    'order_id' => null,
                    'user_id' => 0,
                    'firstname' => 'Федор',
                    's_firstname' => 'Федор',
                    'b_firstname' => 'Федор',
                    'lastname' => 'Смолов',
                    's_lastname' => 'Смолов',
                    'b_lastname' => 'Смолов',
                    'email' => 'smolov@example.com',
                    'phone' => '5555555',
                    'company_id' => 1,
                    'status' => 'O',
                    'timestamp' => strtotime('2017-04-18 15:38:37'),
                    'total' => 90.1,
                    'subtotal_discount' => 14.90,
                    'discount' => 11,
                    'stored_discount' => 'Y',
                    'payment_surcharge' => 0,
                    'shipping_cost' => 56,
                    'payment_id' => 1,
                    'notes' => 'Комментарии клиента',
                    'details' => 'Комментарии оператора',
                    'user_data' => array(
                        'firstname' => 'Федор',
                        'lastname' => 'Смолов',
                        'email' => 'smolov@example.com',
                        'phone' => '5555555',
                        's_firstname' => 'Федор',
                        's_lastname' => 'Смолов',
                        'b_firstname' => 'Федор',
                        'b_lastname' => 'Смолов',
                        's_country' => 'RU',
                        'b_country' => 'RU',
                        's_state' => 'Ульяновская область',
                        'b_state' => 'Ульяновская область',
                        's_city' => 'Ульяновск',
                        'b_city' => 'Ульяновск',
                        's_address' => 'ул. Рябикова, д. 134',
                        'b_address' => 'ул. Рябикова, д. 134',
                        's_zipcode' => '4567',
                        'b_zipcode' => '4567',
                        's_phone' => '5555555',
                        'b_phone' => '5555555',
                    ),
                    'shipping_ids' => 1,
                    's_country' => 'RU',
                    'b_country' => 'RU',
                    's_state' => 'Ульяновская область',
                    'b_state' => 'Ульяновская область',
                    's_city' => 'Ульяновск',
                    'b_city' => 'Ульяновск',
                    's_address' => 'ул. Рябикова, д. 134',
                    'b_address' => 'ул. Рябикова, д. 134',
                    's_zipcode' => '4567',
                    'b_zipcode' => '4567',
                    's_phone' => '5555555',
                    'b_phone' => '5555555',
                    'products' => array(
                        array(
                            'base_price' => 30,
                            'original_price' => 30,
                            'price' => 30,
                            'discount' => 3,
                            'company_id' => 1,
                            'product' => '100g Pants',
                            'amount' => 1,
                            'original_amount' => 1,
                            'product_id' => 12,
                            'stored_price' => 'Y',
                            'stored_discount' => 'Y',
                            'is_edp' => 'N',
                            'extra' => array(
                                'is_edp' => 'N',
                                'product_options' => array(
                                    3 => 12,
                                    4 => 17
                                )
                            ),
                            'product_options' => array(
                                3 => 12,
                                4 => 17
                            )
                        ),
                        array(
                            'base_price' => 30,
                            'original_price' => 30,
                            'price' => 30,
                            'discount' => 8,
                            'company_id' => 1,
                            'product' => '100g Pants',
                            'amount' => 1,
                            'original_amount' => 1,
                            'product_id' => 12,
                            'stored_price' => 'Y',
                            'stored_discount' => 'Y',
                            'is_edp' => 'N',
                            'extra' => array(
                                'is_edp' => 'N',
                                'product_options' => array(
                                    3 => 15,
                                    4 => 17
                                )
                            ),
                            'product_options' => array(
                                3 => 15,
                                4 => 17
                            )
                        ),
                    )
                ),
            ),
            array( //with payment surcharge
                array(
                    'id' => 62,
                    'orderType' => 'eshop-individual',
                    'orderMethod' => 'phone',
                    'countryIso' => 'RU',
                    'createdAt' => '2017-04-18 15:38:37',
                    'summ' => 49,
                    'totalSumm' => 90.1,
                    'discount' => 10,
                    'discountPercent' => 10,
                    'lastName' => 'Смолов',
                    'firstName' => 'Федор',
                    'email' => 'smolov@example.com',
                    'phone' => '5555555',
                    'customerComment' => 'Комментарии клиента',
                    'managerComment' => 'Комментарии оператора',
                    'site' => 'sandbox-cs-cart',
                    'status' => 'new',
                    'paymentType' => 'bank-card',
                    'customer' => array(
                        'firstName' => 'Игорь',
                        'lastName' => 'Смольников',
                        'email' => 'igor@example.com',
                        'phones' => array(
                            array('number' => '5555555')
                        ),
                        'address' => array(
                            'index' => '123123',
                            'countryIso' => 'RU',
                            'region' => 'Ульяновская область',
                            'city' => 'Ульяновск',
                            'text' => 'ул. Самарская д. 12345',
                        )
                    ),
                    'items' => array(
                        array(
                            'initialPrice' => 30,
                            'discount' => 0,
                            'discountPercent' => 10,
                            'quantity' => 1,
                            'offer' => array(
                                'externalId' => '12_3_12_4_17',
                                'name' => '100g Pants',
                            )
                        ),
                        array(
                            'initialPrice' => 30,
                            'discount' => 5,
                            'discountPercent' => 10,
                            'quantity' => 1,
                            'offer' => array(
                                'externalId' => '12_3_15_4_17',
                                'name' => '100g Pants',
                            )
                        ),
                        array(
                            'initialPrice' => 23,
                            'discount' => 0,
                            'discountPercent' => 0,
                            'quantity' => 1,
                            'offer' => array(
                                'externalId' => 'payment_surcharge',
                                'name' => 'Payment surcharge',
                            )
                        ),
                    ),
                    'delivery' => array(
                        'code' => 'ems',
                        'cost' => 33,
                        'address' => array(
                            'index' => '4567',
                            'countryIso' => 'RU',
                            'region' => 'Ульяновская область',
                            'city' => 'Ульяновск',
                            'text' => 'ул. Рябикова, д. 134'
                        )
                    )
                ),
                array(
                    'order_id' => null,
                    'user_id' => 0,
                    'firstname' => 'Федор',
                    's_firstname' => 'Федор',
                    'b_firstname' => 'Федор',
                    'lastname' => 'Смолов',
                    's_lastname' => 'Смолов',
                    'b_lastname' => 'Смолов',
                    'email' => 'smolov@example.com',
                    'phone' => '5555555',
                    'company_id' => 1,
                    'status' => 'O',
                    'timestamp' => strtotime('2017-04-18 15:38:37'),
                    'total' => 67.1,
                    'subtotal_discount' => 14.90,
                    'discount' => 11,
                    'stored_discount' => 'Y',
                    'payment_surcharge' => 23,
                    'shipping_cost' => 33,
                    'payment_id' => 1,
                    'notes' => 'Комментарии клиента',
                    'details' => 'Комментарии оператора',
                    'user_data' => array(
                        'firstname' => 'Федор',
                        'lastname' => 'Смолов',
                        'email' => 'smolov@example.com',
                        'phone' => '5555555',
                        's_firstname' => 'Федор',
                        's_lastname' => 'Смолов',
                        'b_firstname' => 'Федор',
                        'b_lastname' => 'Смолов',
                        's_country' => 'RU',
                        'b_country' => 'RU',
                        's_state' => 'Ульяновская область',
                        'b_state' => 'Ульяновская область',
                        's_city' => 'Ульяновск',
                        'b_city' => 'Ульяновск',
                        's_address' => 'ул. Рябикова, д. 134',
                        'b_address' => 'ул. Рябикова, д. 134',
                        's_zipcode' => '4567',
                        'b_zipcode' => '4567',
                        's_phone' => '5555555',
                        'b_phone' => '5555555',
                    ),
                    'shipping_ids' => 1,
                    's_country' => 'RU',
                    'b_country' => 'RU',
                    's_state' => 'Ульяновская область',
                    'b_state' => 'Ульяновская область',
                    's_city' => 'Ульяновск',
                    'b_city' => 'Ульяновск',
                    's_address' => 'ул. Рябикова, д. 134',
                    'b_address' => 'ул. Рябикова, д. 134',
                    's_zipcode' => '4567',
                    'b_zipcode' => '4567',
                    's_phone' => '5555555',
                    'b_phone' => '5555555',
                    'products' => array(
                        array(
                            'base_price' => 30,
                            'original_price' => 30,
                            'price' => 30,
                            'discount' => 3,
                            'company_id' => 1,
                            'product' => '100g Pants',
                            'amount' => 1,
                            'original_amount' => 1,
                            'product_id' => 12,
                            'stored_price' => 'Y',
                            'stored_discount' => 'Y',
                            'is_edp' => 'N',
                            'extra' => array(
                                'is_edp' => 'N',
                                'product_options' => array(
                                    3 => 12,
                                    4 => 17
                                )
                            ),
                            'product_options' => array(
                                3 => 12,
                                4 => 17
                            )
                        ),
                        array(
                            'base_price' => 30,
                            'original_price' => 30,
                            'price' => 30,
                            'discount' => 8,
                            'company_id' => 1,
                            'product' => '100g Pants',
                            'amount' => 1,
                            'original_amount' => 1,
                            'product_id' => 12,
                            'stored_price' => 'Y',
                            'stored_discount' => 'Y',
                            'is_edp' => 'N',
                            'extra' => array(
                                'is_edp' => 'N',
                                'product_options' => array(
                                    3 => 15,
                                    4 => 17
                                )
                            ),
                            'product_options' => array(
                                3 => 15,
                                4 => 17
                            )
                        ),
                    )
                ),
            )
        );
    }
}