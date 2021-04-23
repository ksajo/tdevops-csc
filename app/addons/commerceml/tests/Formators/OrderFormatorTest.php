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


namespace Tygh\Addons\CommerceML\Tests\Formators;


use Tygh\Addons\CommerceML\Dto\CurrencyDto;
use Tygh\Addons\CommerceML\Formators\OrderFormator;
use Tygh\Addons\CommerceML\Repository\ImportEntityMapRepository;
use Tygh\Addons\CommerceML\Storages\OrderStorage;
use Tygh\Addons\CommerceML\Tests\Unit\StorageBasedTestCase;
use Tygh\Addons\CommerceML\Xml\SimpleXmlElement;

class OrderFormatorTest extends StorageBasedTestCase
{
    protected function setUp()
    {
        $this->requireMockFunction('fn_set_hook');
        parent::setUp();
    }

    public function getOrderFormator(array $settings)
    {
        return new OrderFormator(
            $this->getOrderStorage(),
            $this->getImportEntityMapRepositry(),
            $settings,
            [
                'USD' => [
                    'decimals' => '2',
                ],
                'EUR' => [
                    'decimals' => '2',
                ]
            ],
            'USD',
            's',
            'b'
        );
    }

    public function testForm()
    {
        $order = [
            'order_id'          => '97',
            'company_id'        => '1',
            'user_id'           => '3',
            'total'             => '677.95',
            'subtotal'          => 649.95,
            'discount'          => 0.0,
            'subtotal_discount' => 0.0,
            'payment_surcharge' => 0.0,
            'shipping_ids'      => '1',
            'shipping_cost'     => '28.00',
            'timestamp'         => '1605529684',
            'status'            => 'O',
            'notes'             => '',
            'details'           => '',
            'firstname'         => 'George',
            'lastname'          => 'Nills',
            'company'           => '',
            'b_firstname'       => 'George',
            'b_lastname'        => 'Nills',
            'b_address'         => '60 Centre Street #5',
            'b_address_2'       => '',
            'b_city'            => 'New York',
            'b_county'          => '',
            'b_state'           => 'NY',
            'b_country'         => 'US',
            'b_zipcode'         => '01342',
            'b_phone'           => '+1 646-386-3600',
            's_firstname'       => 'George',
            's_lastname'        => 'Nills',
            's_address'         => '60 Centre Street #5',
            's_city'            => 'New York',
            's_county'          => '',
            's_state'           => 'NY',
            's_country'         => 'US',
            's_zipcode'         => '01342',
            's_phone'           => '+1 646-386-3600',
            's_address_type'    => '',
            'phone'             => '+1 646-386-3600',
            'fax'               => '',
            'url'               => '',
            'email'             => 'dsds@example.com',
            'payment_id'        => '1',
            'tax_exempt'        => 'N',
            'lang_code'         => 'en',
            'repaid'            => '0',
            'validation_code'   => '',
            'localization_id'   => '0',
            'profile_id'        => '2',
            'storefront_id'     => '1',
            'payment_method'    => [
                'payment_id'  => '1',
                'payment'     => 'Credit card',
                'description' => 'Visa, Mastercard, etc...',
            ],
            'products'          => [
                4163016541 => [
                    'item_id'      => '4163016541',
                    'order_id'     => '97',
                    'product_id'   => '126',
                    'product_code' => 'F01262AH0T',
                    'price'        => '129.99',
                    'amount'       => '5',
                    'extra'        => [
                        'product_options'    => [],
                        'unlimited_download' => 'N',
                        'product'            => 'Casio PRIZM fx-CG10',
                        'company_id'         => '1',
                        'is_edp'             => 'N',
                        'edp_shipping'       => 'N',
                        'discount'           => 0,
                        'base_price'         => 129.99,
                        'stored_price'       => 'N',
                    ],
                    'product'            => 'Casio PRIZM fx-CG10',
                    'product_status'     => 'A',
                    'deleted_product'    => false,
                    'discount'           => 0,
                    'company_id'         => '1',
                    'base_price'         => 129.99,
                    'original_price'     => 129.99,
                    'tax_value'          => 0,
                    'subtotal'           => 649.95,
                    'display_subtotal'   => 649.95,
                    'shipped_amount'     => 0,
                    'shipment_amount'    => '5',
                ],
            ],
            'taxes' => [
                6 => [
                    'rate_type'          => 'P',
                    'rate_value'         => '10.000',
                    'price_includes_tax' => 'Y',
                    'tax_subtotal'       => 61.64,
                    'description'        => 'VAT',
                    'applies'            => [
                        'P' => 59.09,
                        'S' => 2.55,
                        'items' => [
                            'S' => [
                                [
                                    1 => true,
                                ]
                            ],
                            'P' => [
                                4163016541 => true,
                            ],
                        ],
                    ],
                ],
            ],
            'tax_subtotal'    => 0,
            'b_country_descr' => 'United States',
            's_country_descr' => 'United States',
            'b_state_descr'   => 'New York',
            's_state_descr'   => 'New York',
            'need_shipping'   => true,
            'shipping'        => [
                [
                    'shipping_id'   => '1',
                    'shipping'      => 'Custom shipping method',
                    'delivery_time' => '3-5 days',
                    'destination'   => 'I',
                    'rate_info'     => [
                        'rate_id'    => '55',
                        'rate_value' => [
                            'C' => [
                                0 => [
                                    'amount'   => '0',
                                    'value'    => 40.0,
                                    'type'     => 'F',
                                    'per_unit' => 'N',
                                ],
                                30 => [
                                    'amount'   => '30',
                                    'value'    => 10.0,
                                    'type'     => 'F',
                                    'per_unit' => 'N',
                                ],
                                50 => [
                                    'amount'   => '50',
                                    'value'    => 5.0,
                                    'type'     => 'F',
                                    'per_unit' => 'N',
                                ],
                            ],
                            'I' => [
                                0 => [
                                    'amount'   => '0',
                                    'value'    => 23.0,
                                    'type'     => 'F',
                                    'per_unit' => 'N',
                                ],
                                10 => [
                                    'amount'   => '10',
                                    'value'    => 15.0,
                                    'type'     => 'F',
                                    'per_unit' => 'N',
                                ],
                            ],
                        ],
                    ],
                    'group_key'     => 0,
                    'rate'          => 28.0,
                    'group_name'    => 'Simtech',
                    'need_shipment' => true,
                ],
            ],
            'shipment_ids'       => [],
            'secondary_currency' => 'USD',
            'display_subtotal'   => 649.95,
            'payment_info'       => [],
            'product_groups'     => [
                [
                    'name'       => 'Simtech',
                    'company_id' => 1,
                    'products'   => [
                        4163016541 => [
                            'product_id'            => 126,
                            'product_code'          => 'F01262AH0T',
                            'product'               => 'Casio PRIZM fx-CG10',
                            'amount'                => 5,
                            'product_options'       => [],
                            'price'                 => 129.99,
                            'stored_price'          => 'Y',
                            'original_amount'       => '5',
                            'original_product_data' => [
                                'cart_id' => '4163016541',
                                'amount'  => '5',
                            ],
                            'extra' => [
                                'product_options'    => [],
                                'unlimited_download' => 'N',
                                'product'            => 'Casio PRIZM fx-CG10',
                                'company_id'         => '1',
                                'is_edp'             => 'N',
                                'edp_shipping'       => 'N',
                                'discount'           => 0,
                                'base_price'         => 129.99,
                                'stored_price'       => 'N',
                            ],
                            'stored_discount' => 'N',
                            'discount'        => 0,
                            'company_id'      => '1',
                            'amount_total'    => 5,
                            'options_type'    => 'P',
                            'exceptions_type' => 'F',
                            'modifiers_price' => 0,
                            'is_edp'          => 'N',
                            'edp_shipping'    => 'N',
                            'promotions'      => [],
                            'base_price'      => 129.99,
                            'display_price'   => 129.99,
                        ],
                    ],
                    'all_free_shipping'    => false,
                    'free_shipping'        => false,
                    'shipping_no_required' => false,
                    'chosen_shippings'     => [
                        0 => [
                            'shipping_id'   => '1',
                            'shipping'      => 'Custom shipping method',
                            'delivery_time' => '3-5 days',
                            'rate_info'     => [
                                'rate_id'    => '1',
                                'rate_value' => [
                                    'C' => [
                                        0 => [
                                            'value' => 40,
                                            'type'  => 'F',
                                        ],
                                        30 => [
                                            'value' => 10,
                                            'type'  => 'F',
                                        ],
                                        50 => [
                                            'value' => 5,
                                            'type'  => 'F',
                                        ],
                                    ],
                                    'I' => [
                                        0 => [
                                            'value' => 23,
                                            'type'  => 'F',
                                        ],
                                        10 => [
                                            'value' => 15,
                                            'type'  => 'F',
                                        ],
                                    ],
                                ],
                            ],
                            'group_key'  => 0,
                            'rate'       => 28.0,
                            'group_name' => 'Simtech',
                        ],
                    ],
                ],
            ],
        ];

        $xml_writer = new \XMLWriter();

        $order_formator = $this->getOrderFormator([
            'orders_exporter.export_order_statuses'  => true,
            'orders_exporter.export_product_options' => true,
            'orders_exporter.export_shipping_fee'    => true
        ]);

        $xml_writer->openMemory();
        $xml_writer->startDocument();
        $xml_writer->startElement(SimpleXmlElement::findAlias('commerceml'));
        $xml_writer = $order_formator->form($xml_writer, $order);
        $xml_writer->endElement();

        $this->assertEquals(
            "<?xml version=\"1.0\"?>\n<КоммерческаяИнформация><Документ><Ид>97</Ид><Номер>97</Номер><Дата>2020-11-16</Дата><Время>12:28:04</Время><ХозОперация>Заказ товара</ХозОперация><Роль>Продавец</Роль><Курс>1</Курс><Сумма>677.95</Сумма><Валюта>USD</Валюта><Комментарий></Комментарий><Контрагенты><Контрагент><Ид>3</Ид><Незарегистрированный>Нет</Незарегистрированный><Наименование>Nills George</Наименование><Роль>Продавец</Роль><ПолноеНаименование>Nills George</ПолноеНаименование><Фамилия>Nills</Фамилия><Имя>George</Имя><Адрес><Представление>01342, United States, New York, 60 Centre Street #5, -</Представление><АдресноеПоле><Тип>Почтовый индекс</Тип><Значение>01342</Значение></АдресноеПоле><АдресноеПоле><Тип>Страна</Тип><Значение>United States</Значение></АдресноеПоле><АдресноеПоле><Тип>Город</Тип><Значение>New York</Значение></АдресноеПоле><АдресноеПоле><Тип>Адрес</Тип><Значение>60 Centre Street #5 -</Значение></АдресноеПоле></Адрес><Контакты><Контакт><Тип>Почта</Тип><Значение>dsds@example.com</Значение></Контакт><Контакт><Тип>ТелефонРабочий</Тип><Значение>+1 646-386-3600</Значение></Контакт></Контакты></Контрагент></Контрагенты><Товары><Товар><Ид>ORDER_DELIVERY</Ид><Наименование>Доставка заказа</Наименование><ЦенаЗаЕдиницу>28</ЦенаЗаЕдиницу><Количество>1</Количество><Сумма>28</Сумма><Коэффициент>1</Коэффициент><БазоваяЕдиница Код=\"796\" НаименованиеПолное=\"&#x448;&#x442;\">шт</БазоваяЕдиница><ЗначенияРеквизитов><ЗначениеРеквизита><Наименование>ВидНоменклатуры</Наименование><Значение>Услуга</Значение></ЗначениеРеквизита><ЗначениеРеквизита><Наименование>ТипНоменклатуры</Наименование><Значение>Услуга</Значение></ЗначениеРеквизита></ЗначенияРеквизитов></Товар><Товар><Ид>126</Ид><Код>126</Код><Артикул>F01262AH0T</Артикул><Наименование>Casio PRIZM fx-CG10</Наименование><ЦенаЗаЕдиницу>129.99</ЦенаЗаЕдиницу><Количество>5</Количество><Коэффициент>1</Коэффициент><БазоваяЕдиница Код=\"796\" НаименованиеПолное=\"&#x448;&#x442;\">шт</БазоваяЕдиница><ЗначенияРеквизитов><ЗначениеРеквизита><Наименование>ВидНоменклатуры</Наименование><Значение>Товар</Значение></ЗначениеРеквизита><ЗначениеРеквизита><Наименование>ТипНоменклатуры</Наименование><Значение>Товар</Значение></ЗначениеРеквизита></ЗначенияРеквизитов><Скидки><Скидка><Наименование>Скидка на товар</Наименование><Сумма>0</Сумма><УчтеноВСумме>true</УчтеноВСумме></Скидка></Скидки><СтавкиНалогов><СтавкаНалога><Наименование>VAT</Наименование><Ставка>10.000</Ставка></СтавкаНалога></СтавкиНалогов><Сумма>649.95</Сумма></Товар></Товары><ЗначенияРеквизитов><ЗначениеРеквизита><Наименование>Статус заказа</Наименование><Значение>Open</Значение></ЗначениеРеквизита><ЗначениеРеквизита><Наименование>Опции товаров</Наименование><Значение></Значение></ЗначениеРеквизита><ЗначениеРеквизита><Наименование>Метод оплаты</Наименование><Значение>Credit card</Значение></ЗначениеРеквизита><ЗначениеРеквизита><Наименование>Способ доставки</Наименование><Значение>Custom shipping method</Значение></ЗначениеРеквизита></ЗначенияРеквизитов></Документ></КоммерческаяИнформация>",
            $xml_writer->outputMemory()
        );

        ////////////////////////////

        $order['order_id'] = '100';
        $order['status'] = 'C';

        $order_formator = $this->getOrderFormator([
            'orders_exporter.export_order_statuses'  => false,
            'orders_exporter.export_product_options' => false,
            'orders_exporter.export_shipping_fee'    => false
        ]);

        $xml_writer->openMemory();
        $xml_writer->startDocument();
        $xml_writer->startElement(SimpleXmlElement::findAlias('commerceml'));
        $xml_writer = $order_formator->form($xml_writer, $order);
        $xml_writer->endElement();

        $this->assertEquals(
            "<?xml version=\"1.0\"?>\n<КоммерческаяИнформация><Документ><Ид>100</Ид><Номер>100</Номер><Дата>2020-11-16</Дата><Время>12:28:04</Время><ХозОперация>Заказ товара</ХозОперация><Роль>Продавец</Роль><Курс>1</Курс><Сумма>677.95</Сумма><Валюта>USD</Валюта><Комментарий></Комментарий><Контрагенты><Контрагент><Ид>3</Ид><Незарегистрированный>Нет</Незарегистрированный><Наименование>Nills George</Наименование><Роль>Продавец</Роль><ПолноеНаименование>Nills George</ПолноеНаименование><Фамилия>Nills</Фамилия><Имя>George</Имя><Адрес><Представление>01342, United States, New York, 60 Centre Street #5, -</Представление><АдресноеПоле><Тип>Почтовый индекс</Тип><Значение>01342</Значение></АдресноеПоле><АдресноеПоле><Тип>Страна</Тип><Значение>United States</Значение></АдресноеПоле><АдресноеПоле><Тип>Город</Тип><Значение>New York</Значение></АдресноеПоле><АдресноеПоле><Тип>Адрес</Тип><Значение>60 Centre Street #5 -</Значение></АдресноеПоле></Адрес><Контакты><Контакт><Тип>Почта</Тип><Значение>dsds@example.com</Значение></Контакт><Контакт><Тип>ТелефонРабочий</Тип><Значение>+1 646-386-3600</Значение></Контакт></Контакты></Контрагент></Контрагенты><Товары><Товар><Ид>126</Ид><Код>126</Код><Артикул>F01262AH0T</Артикул><Наименование>Casio PRIZM fx-CG10</Наименование><ЦенаЗаЕдиницу>129.99</ЦенаЗаЕдиницу><Количество>5</Количество><Коэффициент>1</Коэффициент><БазоваяЕдиница Код=\"796\" НаименованиеПолное=\"&#x448;&#x442;\">шт</БазоваяЕдиница><ЗначенияРеквизитов><ЗначениеРеквизита><Наименование>ВидНоменклатуры</Наименование><Значение>Товар</Значение></ЗначениеРеквизита><ЗначениеРеквизита><Наименование>ТипНоменклатуры</Наименование><Значение>Товар</Значение></ЗначениеРеквизита></ЗначенияРеквизитов><Скидки><Скидка><Наименование>Скидка на товар</Наименование><Сумма>0</Сумма><УчтеноВСумме>true</УчтеноВСумме></Скидка></Скидки><СтавкиНалогов><СтавкаНалога><Наименование>VAT</Наименование><Ставка>10.000</Ставка></СтавкаНалога></СтавкиНалогов><Сумма>649.95</Сумма></Товар></Товары><ЗначенияРеквизитов><ЗначениеРеквизита><Наименование>Метод оплаты</Наименование><Значение>Credit card</Значение></ЗначениеРеквизита><ЗначениеРеквизита><Наименование>Способ доставки</Наименование><Значение>Custom shipping method</Значение></ЗначениеРеквизита></ЗначенияРеквизитов></Документ></КоммерческаяИнформация>",
            $xml_writer->outputMemory()
        );

        ////////////////////////////

        $order['order_id'] = '102';
        $order['products'][4163016541]['product_options'] = [
            15 => [
                'option_id' => '15',
                'product_id' => '0',
                'company_id' => '1',
                'option_type' => 'C',
                'status' => 'A',
                'position' => '1',
                'value' => '53',
                'option_name' => '3G Connectivity',
                'option_text' => '',
                'description' => '',
                'internal_option_name' => 'Nokia 3G',
                'modifier' => '125.000',
                'modifier_type' => 'A',
                'variant_name' => 'Yes',
            ]
        ];

        $order_formator = $this->getOrderFormator([
            'orders_exporter.export_order_statuses'  => false,
            'orders_exporter.export_product_options' => true,
            'orders_exporter.export_shipping_fee'    => false
        ]);

        $xml_writer->openMemory();
        $xml_writer->startDocument();
        $xml_writer->startElement(SimpleXmlElement::findAlias('commerceml'));
        $xml_writer = $order_formator->form($xml_writer, $order);
        $xml_writer->endElement();

        $this->assertEquals(
            "<?xml version=\"1.0\"?>\n<КоммерческаяИнформация><Документ><Ид>102</Ид><Номер>102</Номер><Дата>2020-11-16</Дата><Время>12:28:04</Время><ХозОперация>Заказ товара</ХозОперация><Роль>Продавец</Роль><Курс>1</Курс><Сумма>677.95</Сумма><Валюта>USD</Валюта><Комментарий></Комментарий><Контрагенты><Контрагент><Ид>3</Ид><Незарегистрированный>Нет</Незарегистрированный><Наименование>Nills George</Наименование><Роль>Продавец</Роль><ПолноеНаименование>Nills George</ПолноеНаименование><Фамилия>Nills</Фамилия><Имя>George</Имя><Адрес><Представление>01342, United States, New York, 60 Centre Street #5, -</Представление><АдресноеПоле><Тип>Почтовый индекс</Тип><Значение>01342</Значение></АдресноеПоле><АдресноеПоле><Тип>Страна</Тип><Значение>United States</Значение></АдресноеПоле><АдресноеПоле><Тип>Город</Тип><Значение>New York</Значение></АдресноеПоле><АдресноеПоле><Тип>Адрес</Тип><Значение>60 Centre Street #5 -</Значение></АдресноеПоле></Адрес><Контакты><Контакт><Тип>Почта</Тип><Значение>dsds@example.com</Значение></Контакт><Контакт><Тип>ТелефонРабочий</Тип><Значение>+1 646-386-3600</Значение></Контакт></Контакты></Контрагент></Контрагенты><Товары><Товар><Ид>126</Ид><Код>126</Код><Артикул>F01262AH0T</Артикул><Наименование>Casio PRIZM fx-CG10</Наименование><ЦенаЗаЕдиницу>129.99</ЦенаЗаЕдиницу><Количество>5</Количество><Коэффициент>1</Коэффициент><БазоваяЕдиница Код=\"796\" НаименованиеПолное=\"&#x448;&#x442;\">шт</БазоваяЕдиница><ЗначенияРеквизитов><ЗначениеРеквизита><Наименование>ВидНоменклатуры</Наименование><Значение>Товар</Значение></ЗначениеРеквизита><ЗначениеРеквизита><Наименование>ТипНоменклатуры</Наименование><Значение>Товар</Значение></ЗначениеРеквизита></ЗначенияРеквизитов><Скидки><Скидка><Наименование>Скидка на товар</Наименование><Сумма>0</Сумма><УчтеноВСумме>true</УчтеноВСумме></Скидка></Скидки><СтавкиНалогов><СтавкаНалога><Наименование>VAT</Наименование><Ставка>10.000</Ставка></СтавкаНалога></СтавкиНалогов><Сумма>649.95</Сумма></Товар></Товары><ЗначенияРеквизитов><ЗначениеРеквизита><Наименование>Опции товаров</Наименование><Значение>Casio PRIZM fx-CG10: 3G Connectivity [Yes],; </Значение></ЗначениеРеквизита><ЗначениеРеквизита><Наименование>Метод оплаты</Наименование><Значение>Credit card</Значение></ЗначениеРеквизита><ЗначениеРеквизита><Наименование>Способ доставки</Наименование><Значение>Custom shipping method</Значение></ЗначениеРеквизита></ЗначенияРеквизитов></Документ></КоммерческаяИнформация>",
            $xml_writer->outputMemory()
        );
    }

    /**
     * @return \Tygh\Addons\CommerceML\Storages\OrderStorage
     */
    public function getOrderStorage()
    {
        $order_storage = $this->getMockBuilder(OrderStorage::class)
            ->disableOriginalConstructor()
            ->setMethods(['fillContactInfoFromAddress', 'getOrderStatusData'])
            ->getMock();

        $order_storage->method('fillContactInfoFromAddress')->willReturnCallback(function ($order_data) {
            return $order_data;
        });

        $order_storage->method('getOrderStatusData')->willReturnCallback(function ($status) {
            $data = [
                'O' => [
                    'description' => 'Open'
                ],
                'C' => [
                    'description' => 'Closed'
                ]
            ];

            return !isset($data[$status]) ? false : $data;
        });

        return $order_storage;
    }

    /**
     * @return \Tygh\Addons\CommerceML\Repository\ImportEntityMapRepository
     */
    public function getImportEntityMapRepositry()
    {
        $import_repository = $this->getMockBuilder(ImportEntityMapRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findEntityIds'])
            ->getMock();

        $import_repository->method('findEntityIds')->willReturnCallback(function ($entity_type, $entity_id) {
            return $entity_type === CurrencyDto::REPRESENT_ENTITY_TYPE ? [$entity_id] : false;
        });

        return $import_repository;
    }
}