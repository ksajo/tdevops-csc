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

namespace Tygh\Shippings;

use Tygh\Http;
use Tygh\Tools\SecurityHelper;

class RusSdek
{
    public static $url;

    public static function arraySimpleXml($name, $data, $type = 'simple')
    {
        $xml = '<'.$name.' ';
        foreach ($data as $key => $value) {
            if (!empty($value)) {
                $value = SecurityHelper::escapeHtml($value);
                $xml .= $key .'="' . $value .'" ';
            }
        }

        if ($type == 'open') {
            $xml .= '>';
        } else {
            $xml .= '/>';
        }

        return $xml;
    }

    public static function resultXml($response)
    {
        $result = [
            'error'      => false,
            'error_code' => false,
            'msg'        => false,
            'number'     => false,
        ];

        if (!empty($response)) {
            $xml_result = simplexml_load_string($response);

            $attribut = $xml_result->children()->getName();

            $_result = json_decode(json_encode((array) $xml_result), true);

            foreach ($_result[$attribut] as $k) {
                $data = !empty($k['@attributes']) ? $k['@attributes'] : $k;

                if (!empty($data['Msg'])) {
                    if (!empty($data['ErrorCode'])) {
                        $result['error_code'] = $data['ErrorCode'];
                        $result['msg'] = $data['Msg'];
                        fn_set_notification(
                            'E',
                            __('notice'),
                            $data['Msg'],
                            '',
                            $data['ErrorCode']
                        );
                        if ($data['ErrorCode'] == 'ERR_ORDER_NOTFIND' || $data['ErrorCode'] == 'ERR_ORDER_DUBL_EXISTS') {
                            $result['error'] = false;
                        } else {
                            $result['error'] = true;
                        }

                    } else {
                        $result['msg'] = $data['Msg'];
                        fn_set_notification('N', __('notice'), $data['Msg']);
                    }

                } elseif (!empty($data['DispatchNumber'])) {
                    $result['number'] = $data['DispatchNumber'];
                }
            }
        }

        return $result;
    }

    public static function orderStatusXml($data_auth, $order_id = 0, $shipment_id = 0)
    {
        $cities = db_get_hash_array(
            'SELECT a.city, b.sdek_city_code '
            . 'FROM ?:rus_city_descriptions as a '
            . 'LEFT JOIN ?:rus_sdek_cities_link as b '
                . 'ON a.city_id=b.city_id ',
            'sdek_city_code'
        );

        $data_status = array();
        if (!empty($order_id)) {
            unset($data_auth['Number']);
            unset($data_auth['OrderCount']);
        } else {
            $period_sdek = $data_auth['ChangePeriod'];
            unset($data_auth['ChangePeriod']);
        }

        $data_auth['ShowHistory'] = '0';

        $xml = '            ' . RusSdek::arraySimpleXml('StatusReport', $data_auth, 'open');

        if (!empty($order_id)) {
            $order_sdek = array (
                'Number' => $order_id . '_' . $shipment_id,
                'Date' => $data_auth['Date']
            );
            $xml .= '            ' . RusSdek::arraySimpleXml('Order', $order_sdek);
        } elseif (!empty($period_sdek)) {
            $xml .= '            ' . RusSdek::arraySimpleXml('ChangePeriod', $period_sdek);
        }

        $xml .= '            ' . '</StatusReport>';

        $response = RusSdek::xmlRequest(SDEK_URL_INTEGRATION . 'status_report_h.php', $xml, $data_auth);

        if (!empty($response)) {
            $_result = json_decode(json_encode((array) @simplexml_load_string($response)), true);

            if (!empty($_result['Order']) && !isset($_result['Order']['@attributes']['ErrorCode'])) {
                $_result = empty($_result['Order']['@attributes']) ? $_result['Order'] : array($_result['Order']);
                foreach ($_result as $data_order) {
                    $order = explode('_', $data_order['@attributes']['Number']);
                    $d_status = !empty($data_order['Status']['State']) ? $data_order['Status']['State'] : $data_order['Status'];

                    if (!empty($d_status['@attributes'])) {
                        $status[] = $d_status;

                        unset($d_status['@attributes']);

                        if (empty($d_status) && !empty($status)) {
                            $d_status = $status;
                        }
                    }

                    $order_shipment_id = isset($order[1]) ? (int) $order[1] : null;

                    if ($order_shipment_id) {
                        foreach ($d_status as $state) {
                            $city_name = isset($cities[$state['@attributes']['CityCode']]) ? $cities[$state['@attributes']['CityCode']]['city'] : '';

                            $data_status[$order[0] . '_' . $order[1] . '_' . $state['@attributes']['Code']] = array(
                                'status_id' => $state['@attributes']['Code'],
                                'order_id' => $order[0],
                                'shipment_id' => $order[1],
                                'timestamp' => strtotime($state['@attributes']['Date']),
                                'status' => $state['@attributes']['Description'],
                                'city_code' => $state['@attributes']['CityCode'],
                                'city_name' => $city_name,
                                'date' => date("d-m-Y", strtotime($state['@attributes']['Date'])),
                            );
                        }
                    }
                }
            }

            if (isset($_result['Order']['@attributes']['ErrorCode'])) {
                fn_set_notification(
                    'E',
                    __('error'),
                    __('rus_sdek.error_get_order_status', ['[error]' => $_result['Order']['@attributes']['Msg']])
                );
            }
        }

        return $data_status;
    }

    public static function cityId($location)
    {
        $result = '';
        $country = 1;
        $state = '';
        $country_code = '';
        $city_ids = array();

        if (!empty($location['country'])) {
            $country = fn_get_country_states($location['country'], true);

            if (!empty($country)) {
                $country_code = $location['country'];
            }
        }

        if (empty($country) || empty($location['city'])) {
            return '';
        }

        if (!empty($location['state'])) {
            $state = fn_rus_sdek_check_state_code($location['state'], $country_code) ? $location['state'] : '';
        }

        $data_cities = fn_rus_cities_get_city_ids($location['city'], $state, $country_code);

        if (empty($data_cities)) {
            if (AREA != 'C') {
                fn_set_notification('E', __('notice'), __('shippings.sdek.admin_city_not_served'));
            }

            return '';

        } else {
            $data_cities = is_array($data_cities) ? $data_cities : array($data_cities);
            $sdek_data = fn_rus_sdek_get_sdek_data($data_cities);

            if (!empty($sdek_data)) {
                if (count($sdek_data) === 1) {
                    $data = reset($sdek_data);
                    $result = $data['sdek_city_code'];

                } elseif(!empty($location['zipcode'])) {
                    $city_ids = array();

                    foreach ($sdek_data as $data) {
                        $city_ids[$data['city_id']] = $data['city_id'];

                        if (empty($data['zipcode'])) {
                            $sdek_city_code = $data['sdek_city_code'];
                            continue;
                        }

                        $zipcodes = explode(',', $data['zipcode']);

                        if (in_array($location['zipcode'], $zipcodes)) {
                            $result = $data['sdek_city_code'];
                        }
                    }

                    if (empty($result) && count($city_ids) === 1 && !empty($sdek_city_code)) {
                        $result = $sdek_city_code;
                    }
                }
            }

            if (empty($result)) {
                if (AREA != 'C') {
                    fn_set_notification('E', __('notice'), __('shippings.sdek.admin_city_select_error'));
                } else {
                    fn_set_notification('E', __('notice'), __('shippings.sdek.city_select_error'));
                }
            }

        }

        return $result;
    }

    public static function xmlRequest($url, $xml, $params_request)
    {
        $url = $url
            . '?account=' . $params_request['Account']
            . '&secure=' . $params_request['Secure']
            . '&datefirst=' . $params_request['Date'];

        $xml_request = array(
            'xml_request' => '<?xml version="1.0" encoding="UTF-8" ?>' . $xml
        );

        $extra = array(
            'timeout' => SDEK_TIMEOUT,
            'log_preprocessor' => '\Tygh\Http::unescapeJsonResponse'
        );

        $response = Http::post($url, $xml_request, $extra);

        return $response;
    }

    public static function pvzOffices($params)
    {
        $offices = array();
        $result = Http::get(SDEK_URL_INTEGRATION . 'pvzlist.php', $params, array('timeout' => SDEK_TIMEOUT));
        if (!empty($result)) {
            $xml = @simplexml_load_string($result);
            if (!empty($xml)) {
                $count = count($xml->Pvz);
                if ($count != 0) {
                    $offices = array();
                    if ($count == 1) {
                        foreach($xml->Pvz->attributes() as $_key => $_value){
                            $code = (string) $xml->Pvz['Code'];
                            $offices[$code][$_key] = (string) $_value;
                        }
                    } else {
                        foreach($xml->Pvz as $key => $office) {
                            $code = (string) $office['Code'];
                            foreach($office->attributes() as $_key => $_value){
                                $offices[$code][$_key] = (string) $_value;
                            }
                        }
                    }
                }
            }
        }

        return $offices;
    }

    public static function addStatusOrders($date_status)
    {

        if (empty($date_status)) {
            return false;
        }

        $sdek_history = array();
        $n_status = array();
        $_data_status = db_get_array('SELECT * FROM ?:rus_sdek_status');
        foreach ($_data_status as $_status) {
            $id_history = $_status['order_id'] . '_' . $_status['shipment_id'];

            $n_status = array(
                'status_id' => $_status['status_id'],
                'order_id' => $_status['order_id'],
                'shipment_id' => $_status['shipment_id'],
                'timestamp' => $_status['timestamp'],
                'status' => $_status['status'],
                'city_code' => $_status['city_code'],
                'id' => $_status['id']
            );

            if (!empty($sdek_history[$id_history])) {
                if ($sdek_history[$id_history]['timestamp'] < $_status['timestamp']) {
                    $sdek_history[$id_history] = $n_status;
                }
            } else {
                $sdek_history[$id_history] = $n_status;
            }
        }

        $new_statuses = array();
        $n_status = array();
        foreach ($date_status as $d_status) {
            $status_id = db_get_row(
                'SELECT id FROM ?:rus_sdek_status'
                . ' WHERE status_id = ?i and order_id = ?i and shipment_id = ?i ',
                $d_status['status_id'],
                $d_status['order_id'],
                $d_status['shipment_id']
            );

            $n_status = array(
                'status_id' => $d_status['status_id'],
                'order_id' => $d_status['order_id'],
                'shipment_id' => $d_status['shipment_id'],
                'timestamp' => $d_status['timestamp'],
                'status' => $d_status['status'],
                'city_code' => $d_status['city_code']
            );

            if (empty($status_id)) {
                $new_statuses[] = $n_status;
            }

            $n_status['id'] = empty($d_status['id']) ? '' : $d_status['status_id'];

            $id_history = $n_status['order_id'] . '_' . $n_status['shipment_id'];

            if (!empty($sdek_history[$id_history])) {
                if ($sdek_history[$id_history]['timestamp'] < $n_status['timestamp']) {
                    $sdek_history[$id_history] = $n_status;
                }
            } else {
                $sdek_history[$id_history] = $n_status;
            }
        }

        if (!empty($new_statuses)) {
            db_query('INSERT INTO ?:rus_sdek_status ?m', $new_statuses);
        }

        $d_status_ids = db_get_hash_multi_array(
            'SELECT order_id, shipment_id, id'
            . ' FROM ?:rus_sdek_history_status',
            array('order_id', 'shipment_id')
        );

        foreach ($sdek_history as $k_history => $_history) {
            $sdek_history[$k_history]['id'] = '';

            if (!empty($d_status_ids[$_history['order_id']][$_history['shipment_id']]['id'])) {
                $sdek_history[$k_history]['id'] = $d_status_ids[$_history['order_id']][$_history['shipment_id']]['id'];
            }
        }

        if (!empty($sdek_history)) {
            db_query('REPLACE INTO ?:rus_sdek_history_status ?m', $sdek_history);
        }
    }

    public static function dataAuth($params_shipping)
    {
        $data_auth = array();
        $data_shipping = fn_get_shipping_info($params_shipping['shipping_id'], DESCR_SL);

        if (!empty($data_shipping['service_params']['authlogin'])) {
            $account = $data_shipping['service_params']['authlogin'];
            $secure_password = $data_shipping['service_params']['authpassword'];

            if (!empty($secure_password) && !empty($account)) {
                $secure = md5($params_shipping['Date'] . '&' . $secure_password);
                $data_auth['Date'] = $params_shipping['Date'];
                $data_auth['Account'] = $account;
                $data_auth['Secure'] = $secure;
            } else {
                fn_set_notification('E', __('notice'), __('shippings.sdek.account_password_error'));
            }
        }

        return $data_auth;
    }
}
