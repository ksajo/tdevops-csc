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
 * The class provides methods to convert customer data into retailCRM format.
 *
 * @package Tygh\Addons\Retailcrm\Converters
 */
class CustomerConverter
{
    /**
     * @var Settings RetailCRM settings instance.
     */
    private $settings;

    /**
     * CustomerConverter constructor.
     *
     * @param Settings $settings RetailCRM settings instance.
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Converts customer data to RetailCRM format.
     *
     * @param array $customer   Customer data
     *
     * @return array
     */
    public function convertToCrmCustomer(array $customer)
    {
        $result = array(
            'externalId' => $this->getExternalId($customer),
            'firstName' => $customer['firstname'],
            'lastName' => $customer['lastname'],
            'email' => $customer['email'],
            'address' => array()
        );

        $address_parts = array();

        if (!empty($customer['s_country_descr'])) {
            $address_parts[] = $customer['s_country_descr'];
        }

        if (!empty($customer['b_zipcode'])) {
            $result['address']['index'] = $customer['b_zipcode'];
        }

        if (!empty($customer['b_state_descr'])) {
            $result['address']['region'] = $customer['b_state_descr'];
            $address_parts[] = $customer['b_state_descr'];
        }

        if (!empty($customer['b_city'])) {
            $result['address']['city'] = $customer['b_city'];
            $address_parts[] = $customer['b_city'];
        }

        if (!empty($customer['b_address'])) {
            $address_parts[] = $customer['b_address'];
        }

        if (!empty($customer['b_address_2'])) {
            $address_parts[] = $customer['b_address_2'];
        }

        $result['address']['text'] = implode(', ', $address_parts);

        if (!empty($customer['phone'])) {
            $result['phones'] = array(
                array('number' => $customer['phone'])
            );
        }

        if (!empty($customer['birthday'])) {
            $result['birthday'] = date('Y-m-d', $customer['birthday']);
        }

        return $result;
    }

    /**
     * Gets customer external identifier.
     *
     * @param array $customer
     *
     * @return string
     */
    public function getExternalId(array $customer)
    {
        if (!empty($customer['user_id'])) {
            return $customer['user_id'];
        }

        return $customer['email'];
    }
}