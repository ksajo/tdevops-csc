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


namespace Tygh\Addons\Retailcrm\Converters;

use Tygh\Addons\Retailcrm\Settings;

/**
 * The class provides methods to convert customer data from/into retailCRM format.
 *
 * @package Tygh\Addons\Retailcrm\Converters
 */
class OrderConverter
{
    /**
     * Code for initialization surcharge on the crm side.
     */
    const SURCHARGE_CODE = 'payment_surcharge';

    /**
     * @var Settings RetailCRM settings instance.
     */
    private $settings;

    /**
     * OrderConverter constructor.
     *
     * @param Settings $settings RetailCRM settings instance.
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Converts order data to RetailCRM format.
     *
     * @param array $order       Order data.
     * @param mixed $customer_id Customer identifier.
     *
     * @return array
     */
    public function convertToCrmOrder(array $order, $customer_id)
    {
        $result = array(
            'number' => $order['order_id'],
            'externalId' => $order['order_id'],
            'discount' => (float) $order['subtotal_discount'],
            'firstName' => $order['firstname'],
            'lastName' => $order['lastname'],
            'email' => $order['email'],
            'status' => $this->settings->getExternalOrderStatus($order['status']),
            'customer' => array(
                'externalId' => $customer_id,
            ),
            'contragent' => array(
                'contragentType' => 'individual'
            ),
            'orderType' => $this->settings->getOrderType(),
            'orderMethod' => $this->settings->getOrderMethod(),
            'items' => array()
        );

        if (!empty($order['notes'])) {
            $result['customerComment'] = $order['notes'];
        }

        if (!empty($order['phone'])) {
            $result['phone'] = $order['phone'];
        }

        if (!empty($order['payment_id'])) {
            $result['paymentType'] = $this->settings->getExternalPaymentType($order['payment_id']);
        }

        if (!empty($order['shipping'])) {
            $shipping = reset($order['shipping']);

            $result['delivery'] = array(
                'code' => $this->settings->getExternalShippingType($shipping['shipping_id']),
                'cost' => (float) $order['shipping_cost'],
                'address' => array()
            );

            $address_parts = array();

            if (!empty($order['s_zipcode'])) {
                $result['delivery']['address']['index'] = $order['s_zipcode'];
            }

            if (!empty($order['s_country_descr'])) {
                $address_parts[] = $order['s_country_descr'];
            }

            if (!empty($order['s_state_descr'])) {
                $address_parts[] = $order['s_state_descr'];
                $result['delivery']['address']['region'] = $order['s_state_descr'];
            }

            if (!empty($order['s_city'])) {
                $address_parts[] = $order['s_city'];
                $result['delivery']['address']['city'] = $order['s_city'];
            }

            if (!empty($order['s_address'])) {
                $address_parts[] = $order['s_address'];
            }

            if (!empty($order['s_address_2'])) {
                $address_parts[] = $order['s_address_2'];
            }

            $result['delivery']['address']['text'] = implode(', ', $address_parts);
        }

        foreach ($order['products'] as $product) {
            $price = (float) $product['price'];
            $original_price = (float) $product['original_price'];
            $discount = (float) $product['discount'];

            if ($original_price - $discount > $price) {
                $discount += $original_price - $discount - $price;
            }

            $item = array(
                'initialPrice' => $original_price,
                'quantity' => (float) $product['amount'],
                'discount' => $discount,
                'productName' => $product['product'],
                'product_id' => $product['product_id'],
                'offer' => array(
                    'externalId' => $product['product_id']
                )
            );

            if (!empty($product['product_options'])) {
                $combination = array();
                $item['properties'] = array();

                foreach ($product['product_options'] as $product_option) {
                    if ($product_option['inventory'] === 'Y') {
                        $combination[$product_option['option_id']] = $product_option['value'];
                    }

                    if (empty($product_option['variant_name'])) {
                        continue;
                    }
                    $item['properties'][] = array(
                        'code' => $product_option['option_id'],
                        'name' => $product_option['option_name'],
                        'value' => $product_option['variant_name']
                    );
                }

                ksort($combination);

                $external_id_parts = array(
                    $product['product_id']
                );

                foreach ($combination as $option_id => $variant_id) {
                    $external_id_parts[] = $option_id;
                    $external_id_parts[] = $variant_id;
                }

                $item['offer']['externalId'] = implode('_', $external_id_parts);
            }

            $result['items'][] = $item;
        }

        if (!empty($order['payment_surcharge'])) {
            $item = array(
                'initialPrice' => (float) $order['payment_surcharge'],
                'quantity' => (float) 1,
                'productName' => 'Payment surcharge',
                'product_id' => self::SURCHARGE_CODE,
                'offer' => array(
                    'externalId' => self::SURCHARGE_CODE
                )
            );

            $result['items'][] = $item;
        }

        return $result;
    }

    /**
     * Convert retailCRM order data to store format.
     *
     * @param array $order RetailCRM order data
     *
     * @return array|bool
     */
    public function convertToShopOrder(array $order)
    {
        $company_id = $this->settings->getInternalSite($order['site']);

        $status = $this->settings->getInternalOrderStatus($order['status']);

        if (!$company_id) {
            return false;
        }

        if (!isset($order['discountPercent'])) {
            $order['discountPercent'] = 0;
        }

        if (!isset($order['discount'])) {
            $order['discount'] = 0;
        }

        $result = array(
            'order_id' => !empty($order['externalId']) ? $order['externalId'] : null,
            'user_id' => !empty($order['customer']['externalId']) ? $order['customer']['externalId'] : 0,
            'firstname' => isset($order['firstName']) ? $order['firstName'] : '',
            's_firstname' => isset($order['firstName']) ? $order['firstName'] : '',
            'b_firstname' => isset($order['firstName']) ? $order['firstName'] : '',
            'lastname' => isset($order['lastName']) ? $order['lastName'] : '',
            's_lastname' => isset($order['lastName']) ? $order['lastName'] : '',
            'b_lastname' => isset($order['lastName']) ? $order['lastName'] : '',
            'company_id' => $company_id,
            'timestamp' => strtotime($order['createdAt']),
            'total' => $order['totalSumm'],
            'subtotal_discount' => $order['summ'] * $order['discountPercent'] / 100 + $order['discount'],
            'discount' => 0,
            'payment_surcharge' => 0,
            'shipping_cost' => isset($order['delivery']['cost']) ? $order['delivery']['cost'] : 0,
            'stored_discount' => 'Y',
            'user_data' => array(
                'firstname' => isset($order['firstName']) ? $order['firstName'] : '',
                'lastname' => isset($order['lastName']) ? $order['lastName'] : '',
                's_firstname' => isset($order['firstName']) ? $order['firstName'] : '',
                's_lastname' => isset($order['lastName']) ? $order['lastName'] : '',
                'b_firstname' => isset($order['firstName']) ? $order['firstName'] : '',
                'b_lastname' => isset($order['lastName']) ? $order['lastName'] : ''
            ),
            'products' => array()
        );

        if ($status) {
            $result['status'] = $status;
        } elseif (empty($order['externalId'])) {
            $result['status'] = 'O';
        }

        if (!empty($order['email'])) {
            $result['email'] = $result['user_data']['email'] = $result['email'] = $order['email'];
        } elseif (!empty($order['customer']['email'])) {
            $result['email'] = $result['user_data']['email'] = $result['email'] = $order['customer']['email'];
        } else {
            $result['email'] = $result['user_data']['email'] = $result['email'] = $order['id'] . '@example.com';
        }

        if (!empty($order['customerComment'])) {
            $result['notes'] = $order['customerComment'];
        }

        if (!empty($order['managerComment'])) {
            $result['details'] = $order['managerComment'];
        }

        if (!empty($order['delivery'])) {
            $result['shipping_ids'] = isset($order['delivery']['code'])
                ? $this->settings->getInternalShippingType($order['delivery']['code'])
                : '';

            if (!empty($order['delivery']['address'])) {
                $result['s_country'] = $result['b_country'] = $order['delivery']['address']['countryIso'];
                $result['user_data']['s_country'] = $result['user_data']['b_country'] = $order['delivery']['address']['countryIso'];

                if (isset($order['delivery']['address']['region'])) {
                    $result['s_state'] = $result['b_state'] = $order['delivery']['address']['region'];
                    $result['user_data']['s_state'] = $result['user_data']['b_state'] = $order['delivery']['address']['region'];
                }

                if (isset($order['delivery']['address']['city'])) {
                    $result['s_city'] = $result['b_city'] = $order['delivery']['address']['city'];
                    $result['user_data']['s_city'] = $result['user_data']['b_city'] = $order['delivery']['address']['city'];
                }

                if (isset($order['delivery']['address']['text'])) {
                    $result['s_address'] = $result['b_address'] = $order['delivery']['address']['text'];
                    $result['user_data']['s_address'] = $result['user_data']['b_address'] = $order['delivery']['address']['text'];
                }

                if (isset($order['delivery']['address']['index'])) {
                    $result['s_zipcode'] = $result['b_zipcode'] = $order['delivery']['address']['index'];
                    $result['user_data']['s_zipcode'] = $result['user_data']['b_zipcode'] = $order['delivery']['address']['index'];
                }
            }
        }

        if (isset($order['phone'])) {
            $result['user_data']['phone'] = $result['phone'] = $order['phone'];
            $result['user_data']['s_phone'] = $result['user_data']['b_phone'] = $order['phone'];
            $result['s_phone'] = $result['b_phone'] = $order['phone'];
        }

        if (isset($order['paymentType'])) {
            $result['payment_id'] = $this->settings->getInternalPaymentType($order['paymentType']);
        }

        if (!empty($order['items'])) {
            foreach ($order['items'] as $item) {
                $product = array(
                    'base_price' => $item['initialPrice'],
                    'original_price' => $item['initialPrice'],
                    'price' => $item['initialPrice'],
                    'company_id' => $company_id,
                    'product' => $item['offer']['name'],
                    'amount' => $item['quantity'],
                    'original_amount' => $item['quantity'],
                    'product_id' => null,
                    'stored_price' => 'Y',
                    'stored_discount' => 'Y',
                    'is_edp' => 'N',
                    'extra' => array(
                        'is_edp' => 'N',
                    ),
                );

                $product['discount'] = ($item['initialPrice'] * $item['discountPercent'] / 100) + $item['discount'];

                if (!empty($item['offer']['externalId'])) {

                    if ($item['offer']['externalId'] == self::SURCHARGE_CODE) {
                        $result['payment_surcharge'] = $item['initialPrice'];
                        $result['total'] = $result['total'] - $result['payment_surcharge'];

                        continue;
                    }

                    $parts = explode('_', $item['offer']['externalId']);
                    $product['product_id'] = array_shift($parts);

                    if (!empty($parts)) {
                        $product['extra']['product_options'] = array();

                        while ($parts) {
                            $option_id = array_shift($parts);
                            $variant_id = array_shift($parts);

                            if ($option_id && $variant_id) {
                                $product['extra']['product_options'][$option_id] = $variant_id;
                            }
                        }

                        $product['product_options'] = $product['extra']['product_options'];
                    }
                }

                $result['discount'] += $product['discount'];
                $result['products'][] = $product;
            }
        }

        return $result;
    }
}
