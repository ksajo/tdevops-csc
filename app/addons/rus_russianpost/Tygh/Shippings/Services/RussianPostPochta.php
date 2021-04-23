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

// rus_build_pack dbazhenov

namespace Tygh\Shippings\Services;

use Tygh\Shippings\IService;
use Tygh\Registry;
use Tygh\Http;

/**
 * UPS shipping service
 */
class RussianPostPochta implements IService
{
    /**
     * Number of cents in one ruble
     *
     * @var int
     */
    const CENTS_IN_RUBLE = 100;

    /**
     * Tariffs service url
     *
     * @var string
     */
    const TARIFF_SERVICE_URL = 'https://tariff.pochta.ru/tariff/v1/calculate?json';

    /**
     * Tracking service url
     *
     * @var string
     */
    const TRACKING_SERVICE_URL = 'https://www.pochta.ru/tracking#%s';

    /**
     * The currency in which the carrier calculates shipping costs.
     *
     * @var string $calculation_currency
     */
    public $calculation_currency = 'RUB';

    /**
     * Availability multithreading in this module
     *
     * @var bool $allow_multithreading
     */
    private $allow_multithreading = false;

    /**
     * Maximum allowed requests to Russian Post server
     *
     * @var integer $max_num_requests
     */
    private $max_num_requests = 2;

    /**
     * Timeout requests to Russian Post server
     *
     * @var integer $timeout
     */
    private $timeout = 3;

    /**
     * Stack for errors occured during the preparing rates process
     *
     * @var array $error_stack
     */
    private $error_stack = array();

    /**
     * An array with error codes
     *
     * @var array $code_errors
     */
    private $code_errors = array(
        '1304' => ''
    );

    /**
     * Service will respond with an error instead of 200
     *
     * @var integer $get_error_code_in_response
     */
    private $get_error_code_in_response = 1;

    /**
     * Forces service to give tariffs in closed period
     *
     * @var int
     */
    private $fetch_tariffs_in_closed_period = 1;

    /**
     * Request date format
     *
     * @var int $request_date_format
     */
    private $request_date_format = 'Ymd';

    /**
     * Grams amount in one unit of weight
     *
     * @var int $grams_in_unit
     */
    private $grams_in_unit = null;

    public function __construct()
    {
        $this->grams_in_unit = Registry::get('settings.General.weight_symbol_grams');
    }

    /**
     * Sets data to internal class variable
     *
     * @param array $shipping_info
     */
    public function prepareData($shipping_info)
    {
        $this->_shipping_info = $shipping_info;
    }

    /**
     * Gets shipping cost and information about possible errors
     *
     * @param  string $response Response from Shipping service server
     *
     * @return array  Shipping cost and errors
     */
    public function processResponse($response)
    {
        $return = [
            'cost'  => false,
            'error' => false,
        ];

        $result = (array) json_decode($response, true);

        if (!empty($result['paynds'])) {
            $return['cost'] = $result['paynds'] / self::CENTS_IN_RUBLE;

        } elseif (!empty($result['error'])) {
            $error = implode(', ', $result['error']);
            $return['error'] = $error;

            if (!empty($result['errors'])) {
                foreach ($result['errors'] as $_error) {
                    if (!empty($_error['code']) && isset($this->code_errors[$_error['code']])) {
                        $return['error'] = $return['error'] . '. ' . __('addons.rus_russianpost.cannot_find_object');
                    }
                }
            }

        } else {
            $return['error'] = __('addons.rus_russianpost.server_not_available');
        }

        return $return;
    }

    /**
     * Gets error message from shipping service server
     *
     * @param  string $resonse Reponse from Shipping service server
     * @return string Text of error or false if no errors
     */
    public function processErrors($response)
    {
        preg_match('/<span id=\"lblErrStr\">(.*)<\/span>/i', $response, $matches);

        $error = !empty($matches[1]) ? $matches[1] : __('error_occurred');

        if (!empty($this->error_stack)) {
            foreach ($this->error_stack as $_error) {
                $error .= '; ' . $_error;
            }
        }

        return $error;
    }

    /**
     * Checks if shipping service allows to use multithreading
     *
     * @return bool true if allow
     */
    public function allowMultithreading()
    {
        return $this->allow_multithreading;
    }

    /**
     * Prepare request information
     *
     * @return array Prepared data
     */
    public function getRequestData()
    {
        $data_url = array (
            'headers' => array('Content-Type: application/json'),
            'timeout' => $this->timeout,
            'log_preprocessor' => '\Tygh\Http::unescapeJsonResponse',
        );

        $weight_data = fn_convert_weight_to_imperial_units($this->_shipping_info['package_info']['W']);
        $shipping_settings = $this->_shipping_info['service_params'];
        $origination = $this->_shipping_info['package_info']['origination'];
        $location = $this->_shipping_info['package_info']['location'];

        $data_post = [];
        $data_post['errorcode'] = $this->get_error_code_in_response;
        $data_post['closed'] = $this->fetch_tariffs_in_closed_period;
        $data_post['object'] = $shipping_settings['object_type'];
        $data_post['date'] = date($this->request_date_format, TIME);

        if (empty($location['zipcode'])) {
            $this->internalError(__('russian_post_empty_zipcode'));
            $location['zipcode'] = null;
        }

        $data_post['from'] = $origination['zipcode'];

        // RussianPost doesn't allow destination index for international shipping
        if (!empty($location['country']) && $location['country'] === 'RU') {
            $data_post['to'] = $location['zipcode'];
        }

        if (!empty($location['country'])) {
            $data_post['country'] = $this->getCountryCode($location['country']);
        }

        $weight = $weight_data['plain'] * $this->grams_in_unit;
        $data_post['weight'] = $weight;

        if (($data_post['weight'] < RUSSIANPOST_MIN_WEIGHT) && !empty($this->_shipping_info['keys']['shipping_id'])) {
            $data_post['weight'] = RUSSIANPOST_MIN_WEIGHT;
        }

        $total_cost = $this->_shipping_info['package_info']['C'];
        $cash_sum = 0;

        if (!empty($shipping_settings['cash_on_delivery'])) {
            $cash_sum = $shipping_settings['cash_on_delivery'];
        }

        if (!empty($cash_sum)) {
            if ($total_cost < $cash_sum) {
                $cash_sum = $total_cost;
            }
        }

        $data_post['sumoc'] = $total_cost * self::CENTS_IN_RUBLE;
        $data_post['sumnp'] = $cash_sum * self::CENTS_IN_RUBLE;

        if (!empty($shipping_settings['sending_package'])) {
            $data_post['pack'] = $shipping_settings['sending_package'];
        }

        $data_post['isavia'] = $shipping_settings['isavia'];
        $data_post['service'] = '';

        if (!empty($shipping_settings['services'])) {
            $services = array();

            foreach ($shipping_settings['services'] as $service) {
                if ($service !== 'N') {
                    $services[] = $service;
                }
            }

            $data_post['service'] = (!empty($services)) ? implode(',', $services) : '';
        }
        if (!empty($shipping_settings['average_quantity_in_packet'])) {
            $data_post['countinpack'] = $shipping_settings['average_quantity_in_packet'];
        }

        $request_data = array(
            'method' => 'get',
            'url' => self::TARIFF_SERVICE_URL,
            'data' => $data_post,
            'data_url' => $data_url
        );

        return $request_data;
    }

    /**
     * Process simple request to shipping service server
     *
     * @return string Server response
     */
    public function getSimpleRates()
    {
        $response = false;

        if (empty($this->error_stack)) {
            $data = $this->getRequestData();
            $response = Http::get($data['url'], $data['data'], $data['data_url']);
        }

        return $response;
    }

    public function prepareAddress($address)
    {
        // no logic required
    }

    /**
     * Returns shipping service information
     *
     * @return array information
     */
    public static function getInfo()
    {
        return array(
            'name' => __('carrier_russian_pochta'),
            'tracking_url' => self::TRACKING_SERVICE_URL,
        );
    }

    /**
     * Saves an error into an array
     *
     * @param  string $error
     * @return bool
     */
    private function internalError($error)
    {
        $this->error_stack[] = $error;

        return true;
    }

    /**
     * Fetches ISO 3166-1 (numeric-3) country code
     *
     * @param  string $country
     * @return string
     */
    private function getCountryCode($country)
    {
        $country_code = '';

        if (!empty($country)) {
            $country_code = db_get_field('SELECT code_N3 FROM ?:countries WHERE code = ?s', $country);
        }

        return $country_code;
    }
}

