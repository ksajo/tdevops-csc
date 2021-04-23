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

namespace Tygh\Shippings\Services;

use Tygh\Shippings\IService;
use Tygh\Http;
use Tygh\Registry;

/**
 * UPS shipping service
 */
class Pecom implements IService
{
    /**
     * Availability multithreading in this module
     *
     * @var bool $_allow_multithreading
     */
    private $_allow_multithreading = false;

    /**
     * Stack for errors occured during the preparing rates process
     *
     * @var array $_error_stack
     */
    private $_error_stack = array();

    private function _internalError($error)
    {
        $this->_error_stack[] = $error;
    }

    /**
     * Checks if shipping service allows to use multithreading
     *
     * @return bool true if allow
     */
    public function allowMultithreading()
    {
        return $this->_allow_multithreading;
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
     * @param  string $resonse Reponse from Shipping service server
     * @return array  Shipping cost and errors
     */
    public function processResponse($response)
    {
        $return = array(
            'cost' => false,
            'error' => false,
        );

        $cost = $this->_getRates($response);
        if (empty($this->_error_stack) && !empty($cost)) {
            $return['cost'] = $cost;
            $this->_getPeriods($response);

        } else {
            $return['error'] = $this->processErrors($response);

        }

        return $return;
    }

    /**
     * Process simple calculate length, width and height
     *
     * @return array
     */
    public function getPackageValues()
    {

        $weight_data = fn_convert_weight_to_imperial_units($this->_shipping_info['package_info']['W']);
        $weight = $weight_data['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000;

        $packages = $this->_shipping_info['package_info']['packages'];
        $shipping_settings = $this->_shipping_info['service_params'];

        $packages = array();
        if (empty($this->_shipping_info['package_info']['packages'])) {
            $packages[] = array(
                'shipping_params' => array(
                    'box_length' => $shipping_settings['length'] / 100,
                    'box_wight'  => $shipping_settings['width'] / 100,
                    'box_height' => $shipping_settings['height'] / 100,
                ),
                'weight' => $weight,
                'cost'   => $this->_shipping_info['package_info']['C'],
            );
        } else {
            $packages = $this->_shipping_info['package_info']['packages'];
        }

        $data = array();
        foreach ($packages as $package) {
            if(isset($package['shipping_params'])) {
                $width  = $package['shipping_params']['box_width'] ? $package['shipping_params']['box_width'] / 100 : $shipping_settings['width'] / 100;
                $length = $package['shipping_params']['box_length'] ? $package['shipping_params']['box_length'] / 100 : $shipping_settings['length'] / 100;
                $height = $package['shipping_params']['box_height'] ? $package['shipping_params']['box_height'] / 100 : $shipping_settings['height'] / 100;

                $weight      = $package['weight'] * Registry::get('settings.General.weight_symbol_grams') / 1000;
                $weight      = max($weight, 0.01);
                $volume_data = $width * $length * $height;
                $volume      = round(max($volume_data, 0.01), 2);

                $package_place = array(
                    $width,
                    $length,
                    $height,
                    $volume,
                    $weight,
                    1,
                    $shipping_settings['package_hard'] == "Y" ? 1 : 0
                );
                $data['places'][] = $package_place;
            } else {
                $width       = $shipping_settings['width'] / 100;
                $length      = $shipping_settings['length'] / 100;
                $height      = $shipping_settings['height'] / 100;
                $volume_data = $width * $length * $height;
                $volume      = round(max($volume_data, 0.01), 2);

                $package_place = array(
                    $width,
                    $length,
                    $height,
                    $volume,
                    $package['weight'] * Registry::get('settings.General.weight_symbol_grams') / 1000,
                    1,
                    $shipping_settings['package_hard'] == "Y" ? 1 : 0
                );
                $data['places'][] = $package_place;
            }
        }
        return $data;
    }

    private function _getRates($response)
    {
        $shipping_info = $this->_shipping_info['service_params'];

        $tarif = $shipping_info['tarif'];

        if (!empty($response[$tarif][2])) {
            $cost = $response[$tarif][2];
        } else {
            $cost = false;
            $this->_internalError(__('rus_pecom.not_rate_delivery'));
        }

        if (!empty($cost)) {
            if ($shipping_info['take'] == 'Y' && !empty($response['take'][2])) {
                $cost += $response['take'][2];
            }

            if ($shipping_info['deliver'] == 'Y' && !empty($response['deliver'][2])) {
                $cost += $response['deliver'][2];
            }

            if ($shipping_info['package_hard'] == 'Y' && !empty($response['ADD'][2])) {
                $cost += $response['ADD'][2];
            }

            if ($shipping_info['pal'] == 'Y' && !empty($response['ADD_2'][2])) {
                $cost += $response['ADD_2'][2];
            }

            if ($shipping_info['insurance'] == 'Y' && !empty($response['ADD_3'][2])) {
                $cost += $response['ADD_3'][2];
            }

            if (!empty($response['alma_auto'][2])) {
                $cost += $response['alma_auto'][2];
            }
        }

        return $cost;
    }

    /**
     * Gets error message from shipping service server
     *
     * @param  string $resonse Reponse from Shipping service server
     * @return string Text of error or false if no errors
     */
    public function processErrors($json_response)
    {
        if (!empty($json_response['rsp']['err'])) {
            $error = $json_response['rsp']['err']['code'] . ': ' . $json_response['rsp']['err']['msg'];
        } else {
            $error = __('service_not_available');
        }

        if (!empty($this->_error_stack)) {
            foreach ($this->_error_stack as $_error) {
                $error .= '; ' . $_error;
            }
        }

        return $error;
    }

    /**
     * Prepare request information
     *
     * @return array Prepared data
     */
    public function getRequestData()
    {
        $shipping_settings = $this->_shipping_info['service_params'];
        $packages = $this->_shipping_info['package_info']['packages'];

        $data = $this->getPackageValues();

        $data['take'] = array(
            'town' => (string) $this->_getCityId('from'),
            'tent' => $shipping_settings['take_tent'] == "Y" ? 1 : 0 ,
            'gidro' => $shipping_settings['take_gidro'] == "Y" ? 1 : 0,
            'speed' => $shipping_settings['take_speed'] == "Y" ? 1 : 0,
            'moscow' => $shipping_settings['take_moscow'],
        );

        $data['deliver'] = array(
            'town' => (string) $this->_getCityId('to'),
            'tent' => $shipping_settings['deliver_tent'] == "Y" ? 1 : 0,
            'gidro' => $shipping_settings['deliver_gidro'] == "Y" ? 1 : 0,
            'speed' => $shipping_settings['deliver_speed'] == "Y" ? 1 : 0,
            'moscow' => $shipping_settings['deliver_moscow'],
        );

        if ($shipping_settings['pal'] == 'Y') {
            $data['pal'] = 1;
        } else {
            $data['pal'] = 0;
        }

        if ($shipping_settings['insurance'] == 'Y') {
            $data['strah'] = $this->_shipping_info['package_info']['C'];
        }

        $url = 'http://calc.pecom.ru/bitrix/components/pecom/calc/ajax.php';
        $request_data = array(
            'method' => 'get',
            'url' => $url,
            'data' => $data,
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
        $extra = array(
            'log_preprocessor' => '\Tygh\Http::unescapeJsonResponse'
        );

        $data = $this->getRequestData();
        $key = md5(serialize($data['data']));
        $pecom_data = fn_get_session_data($key);
        if (empty($pecom_data)) {
            $response = Http::get($data['url'], $data['data'], $extra);
            $response = json_decode($response, true);
            fn_set_session_data($key, $response);
        } else {
            $response = $pecom_data;
        }

        return $response;
    }

    public function _getCityID($type)
    {
        if ($type == 'from') {
            $destination = $this->_shipping_info['package_info']['origination'];
        } elseif ($type == 'to') {
            $destination = $this->_shipping_info['package_info']['location'];
        }

        $city_code = fn_rus_pecom_get_city($destination);

        if (empty($city_code)) {
            $city_code = false;
            $this->_internalError(__('rus_pecom.not_city_code'));
        }

        return $city_code;
    }

    private function _getPeriods($response)
    {
        $delivery_time = '';

        $shipping_info = $this->_shipping_info['service_params'];
        $group_key = $this->_shipping_info['keys']['group_key'];
        $shipping_id = $this->_shipping_info['keys']['shipping_id'];
        $tarif = $shipping_info['tarif'];

        if (!empty($response['periods']) && ($tarif == 'auto')) {
            \Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key][$shipping_id]['periods'] = $response['periods'];
            $response['periods'] = str_replace('<br/>', ' ', $response['periods']);
            $delivery_time = strip_tags($response['periods']);
        }

        if (!empty($response['aperiods']) && ($tarif == 'avia')) {
            $aperiods = explode("<br/>", $response['aperiods']);

            foreach ($aperiods as $aperiod) {
                if (strpos($aperiod, 'ss') !== false) {
                    if ($shipping_info['take'] == 'Y' && $shipping_info['deliver'] == 'N') {
                        $delivery_time = strip_tags($aperiod);
                    }
                }

                if (strpos($aperiod, 'sd') !== false) {
                    if ($shipping_info['take'] == 'N' && $shipping_info['deliver'] == 'N') {
                        $delivery_time = strip_tags($aperiod);
                    }
                }

                if (strpos($aperiod, 'ds') !== false) {
                    if ($shipping_info['take'] == 'N' && $shipping_info['deliver'] == 'Y') {
                        $delivery_time = strip_tags($aperiod);
                    }
                }

                if (strpos($aperiod, 'dd') !== false) {
                    if ($shipping_info['take'] == 'Y' && $shipping_info['deliver'] == 'Y') {
                        $delivery_time = strip_tags($aperiod);
                    }
                }
            }
        }

        if (!empty($delivery_time)) {
            \Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key][$shipping_id]['delivery_time'] = $delivery_time;
        }

        return $delivery_time;
    }

    public function prepareAddress($address)
    {
        
    }

    /**
     * Returns shipping service information
     * @return array information
     */
    public static function getInfo()
    {
        return array(
            'name' => __('carrier_pecom'),
            'tracking_url' => 'http://pecom.ru/services-are/order-status/?code=%s'
        );
    }
}
