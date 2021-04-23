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


namespace Tygh\Addons\CommerceML\Formators;


use Tygh\Addons\CommerceML\Dto\ProductDto;
use Tygh\Addons\CommerceML\Dto\TaxDto;
use Tygh\Addons\CommerceML\Storages\OrderStorage;
use Tygh\Addons\CommerceML\Xml\SimpleXmlElement;
use Tygh\Addons\CommerceML\Xml\XmlWritter;
use Tygh\Enum\TaxApplies;
use XMLWriter;
use Tygh\Addons\CommerceML\Repository\ImportEntityMapRepository;
use Tygh\Addons\CommerceML\Dto\CurrencyDto;
use Tygh\Enum\YesNo;

/**
 * Class OrderFormator
 *
 * @package Tygh\Addons\CommerceML\Formators
 */
class OrderFormator
{
    const STRATEGY_ALL = 'A';

    const STRATEGY_NEW = 'N';

    const ORDER_STATUS_OPEN = 'O';

    /**
     * @var \Tygh\Addons\CommerceML\Storages\OrderStorage
     */
    private $order_storage;

    /**
     * @var \Tygh\Addons\CommerceML\Repository\ImportEntityMapRepository
     */
    private $import_entity_map_repository;

    /**
     * @var array<string, int|string|bool|array>
     */
    private $settings;

    /**
     * @var string
     */
    private $default_currency;

    /**
     * @var array<string, array<string, int|string|float>>
     */
    private $currencies;

    /**
     * @var string
     */
    private $shipping_address_prefix;

    /**
     * @var string
     */
    private $billing_address_prefix;

    /**
     * OrderFormator constructor.
     *
     * @param \Tygh\Addons\CommerceML\Storages\OrderStorage                $order_storage                Order storage
     * @param \Tygh\Addons\CommerceML\Repository\ImportEntityMapRepository $import_entity_map_repository Import entity map repository
     * @param array<string, int|string|bool|array>                         $settings                     Is export orders statuses enabled
     * @param array<string, array<string, int|string|float>>               $currencies                   List currencies
     * @param string                                                       $default_currency             Default currency
     * @param string                                                       $shipping_address_prefix      Shipping address prefix
     * @param string                                                       $billing_address_prefix       Billing address prefix
     */
    public function __construct(
        OrderStorage $order_storage,
        ImportEntityMapRepository $import_entity_map_repository,
        array $settings,
        array $currencies,
        $default_currency,
        $shipping_address_prefix,
        $billing_address_prefix
    ) {
        $this->order_storage = $order_storage;
        $this->import_entity_map_repository = $import_entity_map_repository;
        $this->settings = $settings;
        $this->currencies = $currencies;
        $this->default_currency = $default_currency;
        $this->shipping_address_prefix = $shipping_address_prefix;
        $this->billing_address_prefix = $billing_address_prefix;
    }

    /**
     * Forms order to commerceml format
     *
     * @param \XMLWriter                      $xml        XML writer
     * @param array<string, int|string|array> $order_data Order data
     *
     * @return \XMLWriter
     */
    public function form(XMLWriter $xml, array $order_data)
    {
        $order_xml = $this->formOrderData($order_data);

        $order_data = $this->order_storage->fillContactInfoFromAddress($order_data);

        $order_xml = array_merge($order_xml, $this->formCustomerData($order_data));

        $discount_rate = 0;

        if (!empty($order_data['subtotal']) && (!empty($order_data['discount']) || !empty($order_data['subtotal_discount']))) {
            list($discount_rate, $discount_xml) = $this->formDiscountData($order_data);

            if (!empty($discount_xml)) {
                $order_xml = array_merge($order_xml, $discount_xml);
            }
        }

        $order_xml = array_merge($order_xml, $this->formProductsData($order_data, $discount_rate));

        if ($this->settings['orders_exporter.export_order_statuses'] === true) {
            /** @var string $order_data['status'] */
            $order_xml[SimpleXmlElement::findAlias('value_fields')][] = $this->formStatus((string) $order_data['status']);
        }

        if ($this->settings['orders_exporter.export_product_options'] === true) {
            $order_xml[SimpleXmlElement::findAlias('value_fields')][] = $this->formProductOptions($order_data['products']);
        }

        $payment = empty($order_data['payment_method']['payment']) ? '-' : $order_data['payment_method']['payment'];
        $shipping = empty($order_data['shipping'][0]['shipping']) ? '-' : $order_data['shipping'][0]['shipping'];

        $order_xml[SimpleXmlElement::findAlias('value_fields')][] = $this->formValueField(SimpleXmlElement::findAlias('payment'), $payment);
        $order_xml[SimpleXmlElement::findAlias('value_fields')][] = $this->formValueField(SimpleXmlElement::findAlias('shipping'), $shipping);

        /**
         * Executes after the order xml data was formed from order data.
         *
         * @param array $order_xml  An array on the basis of which XML will be formed
         * @param array $order_data Order data
         */
        fn_set_hook('commerceml_order_formator_form', $order_xml, $order_data);

        $xml_writer = new XmlWritter($xml);

        return $xml_writer->convertArrayToXml([SimpleXmlElement::findAlias('document') => $order_xml]);
    }

    /**
     * Forms product options to Commerceml format
     *
     * @param array<string, string|int|array> $products Products
     *
     * @return array<string, array<string, string>>
     */
    private function formProductOptions($products)
    {
        $product_options_string = '';

        foreach ($products as $product) {
            if (empty($product['product_options'])) {
                continue;
            }

            $product_options_string .= sprintf('%s:', $product['product']);

            foreach ($product['product_options'] as $option) {
                $product_options_string .= sprintf(' %s [%s],', $option['option_name'], $option['variant_name']);
            }

            $product_options_string .= '; ';
        }

        return $this->formValueField(SimpleXmlElement::findAlias('product_options'), $product_options_string);
    }

    /**
     * Forms status to commerceml format
     *
     * @param string $status Status
     *
     * @return array<array<string, string>>
     */
    private function formStatus($status)
    {
        $data_status = $this->order_storage->getOrderStatusData($status);
        $status = empty($data_status) ? $status : (string) $data_status[$status]['description'];
        $status = empty($status) ? $this::ORDER_STATUS_OPEN : $status;

        return $this->formValueField(SimpleXmlElement::findAlias('status_order'), $status);
    }

    /**
     * Forms value field to commerceml format
     *
     * @param string $name  Name
     * @param string $value Value
     *
     * @return array<string, array<string, string>>
     */
    private function formValueField($name, $value)
    {
        return [
            SimpleXmlElement::findAlias('value_field') => [
                SimpleXmlElement::findAlias('name')  => $name,
                SimpleXmlElement::findAlias('value') => $value,
            ]
        ];
    }

    /**
     * Formss order data to Commerceml format
     *
     * @param array<string, int|string|array> $order_data Order data
     *
     * @return array<string, array<string, array<string, string>>>
     */
    private function formOrderData(array $order_data)
    {
        /** @var string $order_data['secondary_currency'] */
        $order_currency = empty($order_data['secondary_currency']) ? $this->default_currency : (string) $order_data['secondary_currency'];

        $currency = $this->import_entity_map_repository->findEntityIds(CurrencyDto::REPRESENT_ENTITY_TYPE, $order_currency);

        return [
            SimpleXmlElement::findAlias('id')        => $order_data['order_id'],
            SimpleXmlElement::findAlias('number')    => $order_data['order_id'],
            SimpleXmlElement::findAlias('date')      => date('Y-m-d', (int) $order_data['timestamp']),
            SimpleXmlElement::findAlias('time')      => date('H:i:s', (int) $order_data['timestamp']),
            SimpleXmlElement::findAlias('operation') => SimpleXmlElement::findAlias('order'),
            SimpleXmlElement::findAlias('role')      => SimpleXmlElement::findAlias('seller'),
            SimpleXmlElement::findAlias('rate')      => 1,
            SimpleXmlElement::findAlias('total')     => $order_data['total'],
            SimpleXmlElement::findAlias('currency')  => array_shift($currency),
            SimpleXmlElement::findAlias('notes')     => $order_data['notes'],
        ];
    }

    /**
     * Forms customer data to Commerceml format
     *
     * @param array<string, int|string|array> $order_data Order data
     *
     * @return array<string, array<string, array<string, int|array|string>>>
     */
    private function formCustomerData(array $order_data)
    {
        $user_id = empty($order_data['user_id']) ? '0' . $order_data['order_id'] : $order_data['user_id'];
        $unregistered = empty($order_data['user_id']) ? SimpleXmlElement::findAlias('yes') : SimpleXmlElement::findAlias('no');

        $firstname = empty($order_data['firstname']) ? '-' : $order_data['firstname'];
        $lastname = empty($order_data['lastname']) ? '-' : $order_data['lastname'];

        /** @var string $order_data['phone'] */
        $phone = empty($order_data['phone']) ? '-' : (string) $order_data['phone'];

        $company_name = empty($order_data['company']) ? $lastname . ' ' . $firstname : $order_data['company'];

        $zipcode = $this->getContactInfoFromAddress($order_data, 'zipcode');
        $country = $this->getContactInfoFromAddress($order_data, 'country_descr');
        $city = $this->getContactInfoFromAddress($order_data, 'city');
        $address1 = $this->getContactInfoFromAddress($order_data, 'address');
        $address2 = $this->getContactInfoFromAddress($order_data, 'address_2');

        /** @var string $order_data['email'] */
        return [
            SimpleXmlElement::findAlias('contractors') => [
                SimpleXmlElement::findAlias('contractor') => [
                    SimpleXmlElement::findAlias('id') => $user_id,
                    SimpleXmlElement::findAlias('unregistered') => $unregistered,
                    SimpleXmlElement::findAlias('name') => $company_name,
                    SimpleXmlElement::findAlias('role') => SimpleXmlElement::findAlias('seller'),
                    SimpleXmlElement::findAlias('full_name_contractor') => $lastname . ' ' . $firstname,
                    SimpleXmlElement::findAlias('lastname') => $lastname,
                    SimpleXmlElement::findAlias('firstname') => $firstname,
                    SimpleXmlElement::findAlias('address') => [
                        SimpleXmlElement::findAlias('presentation') => $zipcode . ', ' . $country . ', ' . $city . ', ' . $address1 . ', ' . $address2,
                        $this->formAddressField(SimpleXmlElement::findAlias('post_code'), (string) $zipcode),
                        $this->formAddressField(SimpleXmlElement::findAlias('country'), (string) $country),
                        $this->formAddressField(SimpleXmlElement::findAlias('city'), (string) $city),
                        $this->formAddressField(SimpleXmlElement::findAlias('address'), $address1 . ' ' . $address2),
                    ],
                    SimpleXmlElement::findAlias('contacts') => [
                        $this->formContactField(SimpleXmlElement::findAlias('mail'), (string) $order_data['email']),
                        $this->formContactField(SimpleXmlElement::findAlias('work_phone'), $phone),
                    ]
                ]
            ]
        ];
    }

    /**
     * Forms discount data to Commerceml format
     *
     * @param array<string, int|string|array<string, string|int>> $order_data Order data
     *
     * @return array{float|int, array<string, array<string, array<string, string>>>}
     */
    private function formDiscountData(array $order_data)
    {
        $order_subtotal = 0;

        if (!empty($order_data['discount'])) {
            /** @var array<string, int|float|string> $product */
            foreach ((array) $order_data['products'] as $product) {
                $order_subtotal = $order_subtotal + (float) $product['price'];
            }
        }

        if (empty($order_subtotal)) {
            $order_subtotal = $order_data['subtotal'] - $order_data['discount'];
        }

        if ($order_data['subtotal_discount'] <= 0 || $order_data['subtotal_discount'] >= $order_subtotal) {
            return [0, []];
        }

        $discount_rate = (float) $order_data['subtotal_discount'] * 100 / $order_subtotal;

        return [
            (float) $discount_rate,
            [
                SimpleXmlElement::findAlias('discounts') => [
                    SimpleXmlElement::findAlias('discount') => [
                        SimpleXmlElement::findAlias('name') => SimpleXmlElement::findAlias('orders_discount'),
                        SimpleXmlElement::findAlias('total') => $order_data['subtotal_discount'],
                        SimpleXmlElement::findAlias('rate_discounts') => $this->getRoundedUpPrice($discount_rate),
                        SimpleXmlElement::findAlias('in_total') => 'true'
                    ]
                ]
            ]
        ];
    }

    /**
     * Forms products data to Commerceml format
     *
     * @param array<string, int|string|array> $order_data    Order data
     * @param float                           $discount_rate Discount rate
     *
     * @return array<string, list<array<int|string, array<array|float|int|string>|int|string>>>
     */
    private function formProductsData(array $order_data, $discount_rate)
    {
        $products_xml = [];

        $taxes_data = [];

        if (!empty($order_data['taxes'])) {
            $taxes_data = $this->formTaxesData($order_data['taxes']);
        }

        if ($this->settings['orders_exporter.export_shipping_fee'] === true && $order_data['shipping_cost'] > 0) {
            $products_xml[] = $this->formProductElement(
                'ORDER_DELIVERY',
                SimpleXmlElement::findAlias('delivery_order'),
                (float) $order_data['shipping_cost']
            );
        }

        if (!empty($order_data['payment_surcharge']) && $order_data['payment_surcharge'] > 0) {
            $products_xml[] = $this->formProductElement(
                'Payment_surcharge',
                SimpleXmlElement::findAlias('payment_surcharge'),
                (float) $order_data['payment_surcharge']
            );
        }

        foreach ((array) $order_data['products'] as $product) {
            $products_xml[][SimpleXmlElement::findAlias('product')] = $this->formProduct($product, $taxes_data, $discount_rate);
        }

        return [SimpleXmlElement::findAlias('products') => $products_xml];
    }

    /**
     * Format taxes data to commerceml format
     *
     * @param array<int, array<string, string|int|float|array>> $taxes_data Taxes data
     *
     * @return array<string, string|int|array>
     */
    private function formTaxesData(array $taxes_data)
    {
        $data = $products_taxes = [];

        foreach ($taxes_data as $key => $tax) {
            $tax_in_total = $tax['price_includes_tax'] === YesNo::YES ? 'true' : 'false';

            $tax_value = $this->import_entity_map_repository->findEntityIds(TaxDto::REPRESENT_ENTITY_TYPE, $key);
            $tax_value = empty($tax_value) ? $tax['rate_value'] : array_shift($tax_value);

            $order_tax = [
                'name'         => $tax['description'],
                'value'        => $tax_value,
                'tax_in_total' => $tax_in_total,
                'rate_value'   => $tax['rate_value']
            ];

            if (!empty($tax['applies']['items'][TaxApplies::PRODUCT])) {
                foreach (array_keys($tax['applies']['items'][TaxApplies::PRODUCT]) as $product_item) {
                    $products_taxes[$product_item][$key] = $order_tax;
                }
            }

            foreach (array_keys((array) $tax['applies']) as $product_item) {
                if (empty($product_item = preg_replace('/^P_/', '$1', $product_item))) {
                    continue;
                }

                $products_taxes[(int) $product_item][$key] = $order_tax;
            }

            $data['products'] = $products_taxes;
            $data['orders'][$key] = $order_tax;
        }

        return $data;
    }

    /**
     * Creates XML product element
     *
     * @param string $id    Identifier
     * @param string $name  Name
     * @param float  $value Value
     *
     * @return array<string, string|int|array>
     */
    private function formProductElement($id, $name, $value)
    {
        return [
            SimpleXmlElement::findAlias('product') => [
                SimpleXmlElement::findAlias('id')             => $id,
                SimpleXmlElement::findAlias('name')           => $name,
                SimpleXmlElement::findAlias('price_per_item') => (float) $value,
                SimpleXmlElement::findAlias('amount')         => 1,
                SimpleXmlElement::findAlias('total')          => (float) $value,
                SimpleXmlElement::findAlias('multiply')       => 1,
                SimpleXmlElement::findAlias('base_unit')      => [
                    'attribute' => [
                        SimpleXmlElement::findAlias('code') => '796',
                        SimpleXmlElement::findAlias('full_name_unit') => SimpleXmlElement::findAlias('item'),
                        'text' => SimpleXmlElement::findAlias('item')
                    ]
                ],
                SimpleXmlElement::findAlias('value_fields')   => [
                    $this->formValueField(SimpleXmlElement::findAlias('spec_nomenclature'), SimpleXmlElement::findAlias('service')),
                    $this->formValueField(SimpleXmlElement::findAlias('type_nomenclature'), SimpleXmlElement::findAlias('service')),
                ]
            ]
        ];
    }

    /**
     * Forms product to commercecml format
     *
     * @param array<string, int|string|array>                                   $product       Product data
     * @param array<string|int, int|string|array<int|string, array|string|int>> $taxes_data    Taxes data
     * @param float                                                             $discount_rate Discount rate
     *
     * @return array<string, float|int|string|array>
     */
    private function formProduct(array $product, array $taxes_data, $discount_rate)
    {
        $discounts_xml = [];
        $product_discount = 0;
        $product_subtotal = $product['subtotal'];

        $external_product_id = $this->import_entity_map_repository->findEntityIds(ProductDto::REPRESENT_ENTITY_TYPE, (int) $product['product_id']);
        $external_product_id = empty($external_product_id) ? $product['product_id'] : array_shift($external_product_id);

        $product_xml = [
            SimpleXmlElement::findAlias('id')             => $external_product_id,
            SimpleXmlElement::findAlias('code')           => $external_product_id,
            SimpleXmlElement::findAlias('article')        => $product['product_code'],
            SimpleXmlElement::findAlias('name')           => $product['product'],
            SimpleXmlElement::findAlias('price_per_item') => $product['base_price'],
            SimpleXmlElement::findAlias('amount')         => $product['amount'],
            SimpleXmlElement::findAlias('multiply')       => 1,
            SimpleXmlElement::findAlias('base_unit')      => [
                'attribute' => [
                    SimpleXmlElement::findAlias('code') => '796',
                    SimpleXmlElement::findAlias('full_name_unit') => SimpleXmlElement::findAlias('item'),
                    'text' => SimpleXmlElement::findAlias('item')
                ]
            ],
            SimpleXmlElement::findAlias('value_fields')   => [
                $this->formValueField(SimpleXmlElement::findAlias('spec_nomenclature'), SimpleXmlElement::findAlias('product')),
                $this->formValueField(SimpleXmlElement::findAlias('type_nomenclature'), SimpleXmlElement::findAlias('product')),
            ]
        ];

        if (!empty($discount_rate)) {
            if (!isset($product_xml[SimpleXmlElement::findAlias('discounts')])) {
                $product_xml[SimpleXmlElement::findAlias('discounts')] = [];
            }

            $product_subtotal = (float) $product['price'] * (int) $product['amount'];
            $product_discount = $product_subtotal * $discount_rate / 100;

            if ($product_subtotal > $product_discount) {
                $discounts_xml = [
                    SimpleXmlElement::findAlias('discounts') => [
                        SimpleXmlElement::findAlias('discount') => [
                            SimpleXmlElement::findAlias('name') => SimpleXmlElement::findAlias('product_discount'),
                            SimpleXmlElement::findAlias('total') => $this->getRoundedUpPrice($product_discount),
                            SimpleXmlElement::findAlias('in_total') => 'false',
                        ]
                    ]
                ];
            }
        }

        if (isset($product['discount'])) {
            if (empty($discounts_xml)) {
                $discounts_xml = [
                    SimpleXmlElement::findAlias('discounts') => []
                ];
            }

            $discounts_xml[SimpleXmlElement::findAlias('discounts')][] = [
                SimpleXmlElement::findAlias('discount') => [
                    SimpleXmlElement::findAlias('name') => SimpleXmlElement::findAlias('product_discount'),
                    SimpleXmlElement::findAlias('total') => $product['discount'],
                    SimpleXmlElement::findAlias('in_total') => 'true'
                ]
            ];
        }

        if (!empty($discounts_xml)) {
            $product_xml = array_merge($product_xml, $discounts_xml);
        }

        if (!empty($taxes_data['products'][(int) $product['item_id']])) {
            $taxes_xml = [];
            $tax_value = 0;
            $subtotal = (float) $product['subtotal'] - $product_discount;

            foreach ((array) $taxes_data['products'][(int) $product['item_id']] as $product_tax) {
                if (empty($taxes_xml)) {
                    $taxes_xml[SimpleXmlElement::findAlias('taxes_rates')] = [];
                }

                $taxes_xml[SimpleXmlElement::findAlias('taxes_rates')][] = [
                    SimpleXmlElement::findAlias('tax_rate') => [
                        SimpleXmlElement::findAlias('name') => $product_tax['name'],
                        SimpleXmlElement::findAlias('rate_t') => $product_tax['value']
                    ]
                ];

                if ($product_tax['tax_in_total'] !== 'false') {
                    continue;
                }

                $tax_value = $tax_value + ($subtotal * $product_tax['rate_value'] / 100);
            }

            if (!empty($taxes_xml)) {
                $product_xml = array_merge($product_xml, $taxes_xml);
            }

            $product_subtotal = (float) $product['subtotal'] + $this->getRoundedUpPrice($tax_value);
        }

        $product_xml[SimpleXmlElement::findAlias('total')] = $product_subtotal;

        return $product_xml;
    }

    /**
     * Format commerceml array of address field
     *
     * @param string $type  Type of address field
     * @param string $value Value of address field
     *
     * @return array<string, array<string, string>>
     */
    private function formAddressField($type, $value)
    {
        return [
            SimpleXmlElement::findAlias('address_field') => [
                SimpleXmlElement::findAlias('type') => $type,
                SimpleXmlElement::findAlias('value') => $value
            ]
        ];
    }

    /**
     * Format commerceml array of contact field
     *
     * @param string $type  Type of contact field
     * @param string $value Value of contact field
     *
     * @return array<string, array<string, string>>
     */
    private function formContactField($type, $value)
    {
        return [
            SimpleXmlElement::findAlias('contact') => [
                SimpleXmlElement::findAlias('type') => $type,
                SimpleXmlElement::findAlias('value') => $value
            ]
        ];
    }

    /**
     * Searches for the field with the specified name with the b or s prefix; returns the value of that field.
     *
     * @param array<string, int|string|array> $order_data The array with the order data.
     * @param string                          $field_name The name of the field to search for.
     *
     * @return string The value of the found field.
     */
    private function getContactInfoFromAddress(array $order_data, $field_name)
    {
        /** @var array<string, int|string> $order_data */
        $main_address = $this->shipping_address_prefix . '_' . $field_name;
        $alt_address = $this->billing_address_prefix . '_' . $field_name;

        /** @var string $order_data[string] */
        if (!empty($order_data[$main_address])) {
            $data_field = trim((string) $order_data[$main_address]);
        } elseif (!empty($order_data[$alt_address])) {
            $data_field = trim((string) $order_data[$alt_address]);
        }

        if (empty($data_field)) {
            $data_field = '-';
        }

        return $data_field;
    }

    /**
     * Gets the float value rounded to the number of digits after decimal point specified for the primary currency.
     *
     * @param float $value Value
     *
     * @return float
     */
    private function getRoundedUpPrice($value)
    {
        return round($value, (int) $this->currencies[$this->default_currency]['decimals']);
    }
}
