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

use Tygh\Tygh;
use Tygh\Http;
use Tygh\Registry;
use Tygh\Shippings\IService;
use Tygh\Shippings\IPickupService;

/**
 * Edost shipping service
 */
class Edost implements IService, IPickupService
{
    /**
     * Abailability multithreading in this module
     *
     * @var array $_allow_multithreading
     */
    private $_allow_multithreading = false;

    /**
     * The currency in which the carrier calculates shipping costs.
     *
     * @var string $calculation_currency
     */
    public $calculation_currency = 'RUB';

    /**
     * Timeout requests
     *
     * @var integer $_timeout
     */
    private $_timeout = 5;

    /** @var array $shipping_info Shipping data */
    protected $shipping_info;

    /**
     * Stack for errors occured during the preparing rates process
     *
     * @var array $_error_stack
     */
    private $_error_stack = array();

    protected static $_error_descriptions = array(
        '0' => 'Выбранный метод доставки недоступен',
        '2' => 'Доступ к расчету заблокирован',
        '3' => 'Не верные данные магазина (пароль или идентификатор)',
        '4' => 'Не верные входные параметры',
        '5' => 'Не верный город или страна',
        '6' => 'Внутренняя ошибка сервера расчетов',
        '7' => 'Не заданы компании доставки в настройках магазина',
        '8' => 'Сервер расчета не отвечает',
        '9' => 'Превышен лимит расчетов за день',
        '10' => 'Не верный формат XML',
        '11' => 'Не указан вес',
        '12' => 'Не заданы данные магазина (пароль или идентификатор)',
        '14' => 'Настройки сервера не позволяют отправить запрос на расчет'
    );

    /**
     * Current Company id environment
     *
     * @var int $company_id
     */
    public $company_id = 0;

    /**
     * Returns shipping service information
     *
     * @return array
     */
    public static function getInfo()
    {
        return [
            'name'         => __('carrier_edost'),
            'tracking_url' => 'http://www.edost.ru/tracking.php?n=%s',
        ];
    }

    public function prepareAddress($address)
    {
    }

    /**
     * @inheritdoc
     */
    public function getPickupMinCost()
    {
        $shipping_data = $this->getStoredShippingData();
        return isset($shipping_data['price']) ? $shipping_data['price'] : false;
    }

    /**
     * @inheritdoc
     */
    public function getPickupPoints()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getPickupPointsQuantity()
    {
        $shipping_data = $this->getStoredShippingData();
        return isset($shipping_data['office']) ? count($shipping_data['office']) : false;
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
        // Parse XML message returned by the edost post server.
        $xml = @simplexml_load_string($response);
        $return = false;

        if (!empty($xml)) {
            $status_code = (string) $xml->stat;

            if ($status_code != 1) {
                $return = !empty(self::$_error_descriptions[$status_code]) ? self::$_error_descriptions[$status_code] : 'Ошибка расчета';
            }
        }

        if (empty($return)) {
            $return = self::$_error_descriptions[0];
        }

        if (!empty($this->_error_stack)) {
            foreach ($this->_error_stack as $error) {
                $return .= '; ' . $error;
            }
        }

        return $return;
    }

    /**
     * Sets data to internal class variable
     *
     * @param array $shipping_info
     */
    public function prepareData($shipping_info)
    {
        $this->shipping_info = $shipping_info;
        $this->company_id = Registry::get('runtime.company_id');
    }

    /**
     * Prepare request information
     *
     * @return array Prepared data
     */
    public function getRequestData()
    {
        $weight_data = fn_convert_weight_to_imperial_units($this->shipping_info['package_info']['W']);
        $shipping_settings = $this->shipping_info['service_params'];
        $origination = $this->shipping_info['package_info']['origination'];
        $location = $this->shipping_info['package_info']['location'];
        $code = $this->shipping_info['service_code'];

        if ($origination['country'] != 'RU') {
            $this->_internalError(__('edost_country_error'));
        }

        if (!isset($location['city']) || empty($location['city'])) {
            $location['city'] = '';
        }

        $_code = $this->_getDestinationCode($location);

        if ($_code === false && !empty($location['city'])) {
            $_code = $location['city'];
        }

        if ($_code == '') {
            $this->_internalError(__('edost_code_error'));
        }

        $url = 'http://www.edost.ru/edost_calc_kln.php';
        $post = array (
            'id' => $shipping_settings['store_id'],
            'p' => $shipping_settings['server_password'],
            'to_city' => $_code,
            'zip' => !empty($location['zipcode']) ? $location['zipcode'] : '',
        );

        $post['weight'] = $weight_data['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000;
        $post['strah'] = $this->shipping_info['package_info']['C'];

        list($length, $width, $height) = $this->getPackageValues();

        $post['ln'] = ($shipping_settings['length'] > $length) ? $shipping_settings['length'] : $length;
        $post['wd'] = ($shipping_settings['width'] > $width) ? $shipping_settings['width'] : $width;
        $post['hg'] = ($shipping_settings['height'] > $height) ? $shipping_settings['height'] : $height;

        $request_data = array(
            'method' => 'post',
            'url' => $url,
            'data' => $post,
        );

        return $request_data;
    }

    /**
     * Process simple calculate length, width and height
     *
     * @return array length, width, height
     */
    public function getPackageValues()
    {
        $packages = $this->shipping_info['package_info']['packages'];

        foreach ($packages as $key => $pack) {
            if (!isset($pack['shipping_params'])) {
                unset($packages[$key]);
            }
        }

        $count = count($packages);
        $maximus = array(
            'length' => 0,
            'width' => 0,
            'height' => 0,
        );
        $volume = 0;

        if ($count == 0) {
            $length = $width = $height = 1;

        } elseif ($count == 1) {
            $ship_params = $packages[0]['shipping_params'];
            $length = !empty($ship_params['box_length']) ? $ship_params['box_length'] : 1 ;
            $width = !empty($ship_params['box_width']) ? $ship_params['box_width'] : 1 ;
            $height = !empty($ship_params['box_height']) ? $ship_params['box_height'] : 1 ;

        } elseif ($count > 1) {
            foreach ($packages as $key => $value) {
                $ship_params = $value['shipping_params'];
                $tmp_length = !empty($ship_params['box_length']) ? $ship_params['box_length'] : 1 ;
                $tmp_width = !empty($ship_params['box_width']) ? $ship_params['box_width'] : 1 ;
                $tmp_height = !empty($ship_params['box_height']) ? $ship_params['box_height'] : 1 ;

                $volume += $tmp_length * $tmp_width * $tmp_height;

                if ($tmp_length > $maximus['length']) {
                    $maximus['length'] = $tmp_length;
                }
                if ($tmp_width > $maximus['width']) {
                    $maximus['width'] = $tmp_width;
                }
                if ($tmp_height > $maximus['height']) {
                    $maximus['height'] = $tmp_height;
                }
            }

            arsort($maximus);
            $length = reset($maximus);
            $width = $height = ceil(sqrt($volume / $length));
        }

        return array($length, $width, $height);
    }

    /**
     * Process simple request to shipping service server
     *
     * @return string Server response
     */
    public function getSimpleRates()
    {
        $data = $this->getRequestData();
        $key = md5(serialize($data['data']));
        $edost_data = fn_get_session_data($key);

        if (empty($edost_data)) {
            $response = Http::post($data['url'], $data['data'], array('timeout' => $this->_timeout));
            fn_set_session_data($key, $response);
        } else {
            $response = $edost_data;
        }

        return $response;
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

        $rates = $this->_getRates($response);
        if (empty($this->_error_stack) && !empty($rates[$this->shipping_info['service_code']])) {
            $this->storeShippingData($this->shipping_info, $this->company_id, $rates);
            $return['cost'] = $rates[$this->shipping_info['service_code']]['price'];
            $return['delivery_time'] = $rates[$this->shipping_info['service_code']]['day'];
        } else {
            $return['error'] = $this->processErrors($response);
        }

        return $return;
    }
    /**
     * Process' server response and gets information in needed format
     *
     * @param  string $response XML server response
     * @return array  Prepared data
     */
    private function _getRates($response)
    {
        $return = array();
        $xml = @simplexml_load_string($response);
        $services = fn_get_schema('edost', 'services', 'php', true);

        if (!empty($xml)) {
            foreach ($xml->tarif as $shipment) {
                $strah = (int) $shipment->strah;

                $tarif_id = (int) $shipment->id;
                $service_code = $tarif_id * 2 + $strah + 299;
                $tarifs[$tarif_id] = $service_code;

                $return[$service_code] = array(
                    'price' => (string) $shipment->price,
                    'pricecash' => (string) $shipment->pricecash,
                    'transfer' => (string) $shipment->transfer,
                    'strah' => (string) $shipment->strah,
                    'id' => $tarif_id,
                    'day' => (string) $shipment->day,
                    'company' => (string) $shipment->company,
                    'name' => (string) $shipment->name
                );

                if (!empty($shipment->pickpointmap)) {
                    $return[$service_code]['city_pickpoint'] = (string) $shipment->pickpointmap;
                }
            }

            if (!empty($xml->office)) {
                foreach ($xml->office as $office) {
                    $office_id = (string) $office->id;
                    $shipment_ids = explode(',', (string) $office->to_tarif);

                    foreach ($shipment_ids as $id) {
                        $service_code_insurance = $tarifs[$id];
                        $service_code = empty($services[$tarifs[$id]]['no_insurance_variant']) ? 0 : $services[$tarifs[$id]]['no_insurance_variant'];

                        foreach (array($service_code, $service_code_insurance) as $service_code_key) {
                            if (!empty($return[$service_code_key])) {
                                $return[$service_code_key]['office'][$office_id] = array(
                                    'office_id' => $office_id,
                                    'name' => (string) $office->name,
                                    'address' => (string) $office->address,
                                    'tel' => (string) $office->tel,
                                    'schedule' => (string) $office->schedule,
                                    'gps' => (string) $office->gps,
                                );
                            }
                        }
                    }
                }
            }
        }

        return $return;
    }

    /**
     * Saves shipping data to session
     *
     * @param  array $shipping_info Shipping data
     * @param  int   $company_id    Selected company identifier
     * @param  array $rates         Previously calculated rates
     *
     * @return bool
     */
    protected function storeShippingData($shipping_info, $company_id, $rates = array())
    {
        if (isset($shipping_info['keys']['group_key']) && !empty($shipping_info['keys']['shipping_id'])) {
            $group_key = $shipping_info['keys']['group_key'];
            $shipping_id = $shipping_info['keys']['shipping_id'];

            $code = $shipping_info['service_code'];

            /* Bad code: We should not use Global variables in the Class methods */

            $price = !empty($rates[$code]['price']) ? $rates[$code]['price'] : 0;
            $pricecash = !empty($rates[$code]['pricecash']) ? $rates[$code]['pricecash'] : 0;
            $transfer = !empty($rates[$code]['transfer']) ? $rates[$code]['transfer'] : 0;

            $shipping_data = array(
                'price' => !empty($rates[$code]['price']) ? $rates[$code]['price'] : 0,
                'pricecash' => $pricecash,
                'pricediff' => ($pricecash > $price) ? $pricecash - $price : 0,
                'transfer' => $transfer,
                'day' => !empty($rates[$code]['day']) ? $rates[$code]['day'] : 0,
            );

            Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key][$shipping_id] = $rates[$code];
            Tygh::$app['session']['cart']['shippings_extra']['rates'][$group_key][$shipping_id] = $shipping_data;
        }

        return true;
    }

    /**
     * Fetches stored data from session
     *
     * @return array
     */
    protected function getStoredShippingData()
    {
        $group_id = isset($this->shipping_info['keys']['group_key']) ? $this->shipping_info['keys']['group_key'] : 0;
        $shipping_id = isset($this->shipping_info['keys']['shipping_id']) ? $this->shipping_info['keys']['shipping_id'] : 0;
        if (isset(Tygh::$app['session']['cart']['shippings_extra']['data'][$group_id][$shipping_id])) {
            return Tygh::$app['session']['cart']['shippings_extra']['data'][$group_id][$shipping_id];
        }

        return [];
    }

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
     * Gets numeric representation of Country/Region/City
     *
     * @param  array $destination Country, Region, City of geographic place
     * @return int   Numeric representation
     */
    private function _getDestinationCode($destination)
    {
        $state = $country = '';

        foreach ($destination as $destination_id => $value) {
            $destination[$destination_id] = strtolower($value);
        }

        if (!empty($destination['state'])) {
            $state = $destination['state'];
        }

        if (!empty($destination['country'])) {
            $country = $destination['country'];
        }

        $cities_ids = fn_rus_cities_get_city_ids($destination['city'], $state, $country);
        if (empty($cities_ids)) {
            return '';
        }

        $cities = fn_rus_edost_get_codes($cities_ids);

        if (count($cities) == 1) {
            $result = reset($cities);

        } elseif (count($cities) < 1) {
            if (AREA != 'C') {
                fn_set_notification('E', __('notice'), __('shippings.edost.admin_city_not_served'));
            }

            return '';

        } else {
            if (AREA != 'C') {
                fn_set_notification('E', __('notice'), __('shippings.edost.admin_city_select_error'));
            } else {
                fn_set_notification('E', __('notice'), __('shippings.edost.city_select_error'));
            }

            return '';
        }

        return $result;
    }
}
