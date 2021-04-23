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

use Tygh\Enum\NotificationSeverity;
use Tygh\Tygh;
use Tygh\Http;
use Tygh\Registry;
use Tygh\Shippings\IService;
use Tygh\Shippings\RusPickpoint;


/**
 * Pickpoint shipping service
 */
class Pickpoint implements IService
{
    /**
     * Abailability multithreading in this module
     *
     * @var bool $_allow_multithreading
     */
    private $_allow_multithreading = false;

    /**
     * The currency in which the carrier calculates shipping costs.
     *
     * @var string $calculation_currency
     */
    public $calculation_currency = 'RUB';

    private $version = "1.0";

    /**
     * Stack for errors occured during the preparing rates process
     *
     * @var array $_error_stack
     */
    private $_error_stack = array();

    /**
     * Current Company id environment
     *
     * @var int $company_id
     */
    public $company_id = 0;

    public $sid;

    public $date_zone;

    public $address_pickpoint;
    /**
     * Collects errors during preparing and processing request
     *
     * @param string $error
     */
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
     * Gets error message from shipping service server
     *
     * @param  string $resonse Reponse from Shipping service server
     * @return string Text of error or false if no errors
     */
    public function processErrors($response)
    {
        if (!empty($response['error'])) {
            return $response['error'];
        }

        return false;
    }

    /**
     * Sets data to internal class variable
     *
     * @param array $shipping_info
     */
    public function prepareData($shipping_info)
    {
        $this->_shipping_info = $shipping_info;
        $this->company_id = Registry::get('runtime.company_id');
    }

    /**
     * Prepare request information
     *
     * @return array Prepared data
     */
    public function getRequestData()
    {
        $data = array();
        $key_data = array();
        $pickpoint_info = $this->_shipping_info['service_params']['pickpoint_info'];
        $login = RusPickpoint::login($pickpoint_info);
        $url = RusPickpoint::url($pickpoint_info);
        $extra_data = RusPickpoint::$extra_data;

        $delivery_mode = empty($this->_shipping_info['service_params']['delivery_mode']) ? '' : $this->_shipping_info['service_params']['delivery_mode'];

        $group_key = (isset($this->_shipping_info['keys']['group_key'])) ? $this->_shipping_info['keys']['group_key'] : 0;
        $shipping_id = (isset($this->_shipping_info['keys']['shipping_id'])) ? $this->_shipping_info['keys']['shipping_id'] : 0;

        if ($login) {
            $pickpoint_office = (!empty(Tygh::$app['session']['cart']['pickpoint_office'])) ? Tygh::$app['session']['cart']['pickpoint_office'] : array();
            $weight_data = fn_convert_weight_to_imperial_units($this->_shipping_info['package_info']['W']);
            $shipping_settings = $this->_shipping_info['service_params'];
            $weight = round($weight_data['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000, 3);

            $location = !empty($this->_shipping_info['package_info']['location']) ? $this->_shipping_info['package_info']['location'] : '';
            $origination = !empty($this->_shipping_info['package_info']['origination']) ? $this->_shipping_info['package_info']['origination'] : '';

            $from_state = fn_get_state_name($origination['state'], $origination['country'], 'RU');

            $length = !empty($shipping_settings['pickpoint_length']) ? $shipping_settings['pickpoint_length'] : 10;
            $width = !empty($shipping_settings['pickpoint_width']) ? $shipping_settings['pickpoint_width'] : 10;
            $height = !empty($shipping_settings['pickpoint_height']) ? $shipping_settings['pickpoint_height'] : 10;

            if (!empty($this->_shipping_info['package_info']['packages'])) {
                $packages = $this->_shipping_info['package_info']['packages'];
                $packages_count = count($packages);
                $pickpoint_weight = $pickpoint_length = $pickpoint_width = $pickpoint_height = 0;
                if ($packages_count > 0) {
                    foreach ($packages as $id => $package) {
                        $package_length = empty($package['shipping_params']['box_length']) ? $length : $package['shipping_params']['box_length'];
                        $package_width = empty($package['shipping_params']['box_width']) ? $width : $package['shipping_params']['box_width'];
                        $package_height = empty($package['shipping_params']['box_height']) ? $height : $package['shipping_params']['box_height'];
                        $weight_ar = fn_convert_weight_to_imperial_units($package['weight']);
                        $package_weight = round($weight_ar['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000, 3);

                        $pickpoint_weight = $pickpoint_weight + $package_weight;
                        $pickpoint_length = $pickpoint_length + $package_length;
                        $pickpoint_width = $pickpoint_width + $package_width;
                        $pickpoint_height = $pickpoint_height + $package_height;
                    }
                    $length = $pickpoint_length;
                    $width = $pickpoint_width;
                    $height = $pickpoint_height;
                    $weight = $pickpoint_weight;
                }
            } else {
                $packages_count = 1;
                $weight = round($weight_data['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000, 3);
            }

            $sid = RusPickpoint::$sid;
            $data_zone = array(
                'SessionId' => $sid,
                'FromCity' => $origination['city']
            );
            $url_zone = $url . 'getzone';

            $pickpoint_id = '';
            if (!empty($pickpoint_office[$group_key][$shipping_id]['pickpoint_id'])) {
                $pickpoint_id = $pickpoint_office[$group_key][$shipping_id]['pickpoint_id'];
                $this->address_pickpoint = $pickpoint_office[$group_key][$shipping_id]['address_pickpoint'];

                $data_zone['ToPT'] = $pickpoint_id;
                $pickpoint_zone = RusPickpoint::zonesPickpoint($url_zone, $data_zone, $extra_data, $delivery_mode);
                if (!empty($pickpoint_zone)) {
                    if ($pickpoint_zone['delivery_min'] == $pickpoint_zone['delivery_max']) {
                        $this->date_zone = $pickpoint_zone['delivery_max'] . ' ' . __('days');
                    } else {
                        $this->date_zone = $pickpoint_zone['delivery_min'] . '-' . $pickpoint_zone['delivery_max'] . ' ' . __('days');
                    }
                }
            } else {
                $city = (!empty($location['city'])) ? $location['city'] : '';
                $this->address_pickpoint = RusPickpoint::findPostamatPickpoint($pickpoint_id, $city);
                if ($this->address_pickpoint) {
                    $data_zone['ToPT'] = $pickpoint_id;
                    $pickpoint_zone = RusPickpoint::zonesPickpoint($url_zone, $data_zone, $extra_data, $delivery_mode);
                    if (!empty($pickpoint_zone)) {
                        $pickpoint_id = (!empty($pickpoint_zone['to_pt'])) ? $pickpoint_zone['to_pt'] : '';
                        if ($pickpoint_zone['delivery_min'] === $pickpoint_zone['delivery_max']) {
                            $this->date_zone = $pickpoint_zone['delivery_max'] . ' ' . __('days');
                        } else {
                            $this->date_zone = $pickpoint_zone['delivery_min'] . '-' . $pickpoint_zone['delivery_max'] . ' ' . __('days');
                        }
                    }
                }
            }

            if (!empty($pickpoint_id) && !empty($this->address_pickpoint)) {
                Tygh::$app['session']['cart']['pickpoint_office'][$group_key][$shipping_id]['pickpoint_id'] = $pickpoint_id;
                Tygh::$app['session']['cart']['pickpoint_office'][$group_key][$shipping_id]['address_pickpoint'] = $this->address_pickpoint;
                Tygh::$app['session']['cart']['pickpoint_office'][$group_key][$shipping_id]['pickup_data'] = RusPickpoint::getPickpointPostamatById($pickpoint_id);
            }

            $data = array(
                'SessionId' => $sid,
                'IKN' => $pickpoint_info['ikn'],
                'FromCity' => $origination['city'],
                'FromRegion' => $from_state,
                'PTNumber' => $pickpoint_id,
                'EncloseCount' => $packages_count,
                'Length' => $length,
                'Depth' => $height,
                'Width' => $width,
                'Weight' => $weight
            );

            $key_data = $data;
            unset($key_data['SessionId']);
        } else {
            $this->_internalError(RusPickpoint::$last_error);
            if (!empty(RusPickpoint::$last_error)) {
                fn_set_notification(NotificationSeverity::ERROR, __('notice'), RusPickpoint::$last_error);
            }
        }

        $request_data = array(
            'method' => 'post',
            'url' => $url . 'calctariff',
            'data' => json_encode($data),
            'extra_data' => $extra_data,
            'key_data' => $key_data
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
        $data = $this->getRequestData();
        return Http::post($data['url'], $data['data'], $data['extra_data']);
    }

    /**
     * Gets shipping cost and information about possible errors
     *
     * @param string $response Response from Shipping service server
     *
     * @return array<string, bool|int|string|array<string, int|string>> Shipping cost and errors
     */
    public function processResponse($response)
    {
        $return = [
            'cost' => false,
            'error' => false,
        ];

        $data_services = json_decode($response, true);
        if (isset($data_services['Error']) && ((int) $data_services['Error'] === 1) && !empty($data_services['ErrorMessage'])) {
            $this->_internalError($data_services['ErrorMessage']);
        } elseif (isset($data_services['Error']) && !empty($data_services['Error'])) {
            $this->_internalError($data_services['Error']);
        } elseif (isset($data_services['Services'])) {
            $_result = [];
            $shipping_settings = $this->_shipping_info['service_params'];
            $delivery_mode = empty($shipping_settings['delivery_mode']) ? 'Standart' : $shipping_settings['delivery_mode'];

            foreach ($data_services['Services'] as $service) {
                $discount = empty($service['Discount']) ? 0 : $service['Tariff'] * $service['Discount'] / 100;
                $cost = $service['Tariff'] + $service['NDS'] - $discount;
                if (empty($_result[$service['DeliveryMode']])) {
                    $_result[$service['DeliveryMode']] = [
                        'Tariff_Type' => '',
                        'Total_Dost' => 0,
                        'Total_DopUsl' => 0,
                        'Cost' => 0,
                    ];
                }
                $_result[$service['DeliveryMode']]['Tariff_Type'] = $service['Name'];
                $_result[$service['DeliveryMode']]['Total_Dost'] += $service['Tariff'];
                $_result[$service['DeliveryMode']]['Total_DopUsl'] += $service['NDS'];
                $_result[$service['DeliveryMode']]['Cost'] += $cost;
            }
            if (count($_result) > 1 && !empty($_result[$delivery_mode])) {
                $pickpoint = $_result[$delivery_mode];
            } else {
                $pickpoint = reset($_result);
            }
            if (isset($pickpoint)) {
                $this->fillSessionPostamat($pickpoint);
                $return['cost'] = $pickpoint['Cost'];
                $return['delivery_time'] = $this->date_zone;
                $return['tariff'] = $pickpoint;
            }
        }
        if (!empty($this->_error_stack)) {
            foreach ($this->_error_stack as $error) {
                $return['error'] .= $error . '; ';
            }
        }
        RusPickpoint::logout();

        return $return;
    }

    private function fillSessionPostamat($pickpoint_postamat)
    {
        if (isset($this->_shipping_info['keys']['group_key']) && isset($this->_shipping_info['keys']['shipping_id'])) {
            $group_key = $this->_shipping_info['keys']['group_key'];
            $shipping_id = $this->_shipping_info['keys']['shipping_id'];
            Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key][$shipping_id]['pickpoint_postamat'] = $pickpoint_postamat;
        }

        return true;
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
            'name' => __('carrier_pickpoint'),
            'tracking_url' => 'https://pickpoint.ru'
        );
    }
}
