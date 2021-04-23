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

use Tygh\Enum\YesNo;
use Tygh\Http;

class RusPickpoint
{
    public static $sid;
    public static $login;
    public static $icn;
    public static $url;
    public static $last_error;

    public static $extra_data = array (
        'headers' => array('Content-Type: application/json'),
        'timeout' => 3,
    );

    /**
     * Returns URL address for Pickpoint API server.
     *
     * @param array<string, string> $pickpoint_info Shipping method settings.
     *
     * @return string URL address for Pickpoint API server.
     */
    public static function url(array $pickpoint_info = null)
    {
        $url_path = 'http://';
        if (empty($pickpoint_info)) {
            $url_path = fn_get_storefront_protocol();
            return $url_path . '://e-solution.pickpoint.ru/api/';
        }

        if (YesNo::toBool($pickpoint_info['secure_protocol'])) {
            $url_path = 'https://';
        }

        if ($pickpoint_info['server'] === 'test') {
            $url = self::$url = $url_path . 'e-solution.pickpoint.ru/apitest/';
        } else {
            $url = self::$url = $url_path . 'e-solution.pickpoint.ru/api/';
        }

        return $url;
    }

    /**
     * Performs login operation to Pickpoint remote service.
     *
     * @param array<string, string> $pickpoint_info Shipping method settings.
     *
     * @return bool Result of login operation.
     */
    public static function login(array $pickpoint_info)
    {
        $return = false;

        $login = self::$login = $pickpoint_info['login'];
        $password = $pickpoint_info['password'];
        $url = self::url($pickpoint_info) . 'login';

        $data = array(
            'Login' => $login,
            'Password' => $password
        );

        $response = Http::post($url, json_encode($data), self::$extra_data);
        $data_session = (array) json_decode($response);
        if (!empty($data_session)) {
            if (!empty($data_session['ErrorMassage'])) {
                self::$last_error = $data_session['ErrorMassage'];
                $return = false;
            } else {
                self::$sid = $data_session['SessionId'];
                $return = true;
            }
        }

        return $return;
    }

    /**
     * Performs logout operation from Pickpoint remote service.
     *
     * @return bool|string
     */
    public static function logout()
    {
        $sid = self::$sid;
        $url = self::$url;
        $data_url = self::$extra_data;

        $data = array(
            'SessionId' => $sid
        );

        $return = false;
        $response = Http::post($url . 'logout', json_encode($data), $data_url);
        $result = (array) json_decode($response);

        if (!empty($result['Success'])) {
            $return = $result['Success'];
        }

        return $return;
    }

    /**
     * Gets the delivery zone.
     *
     * @param string $url           The request URL
     * @param array  $data_zone     The request data
     * @param array  $data_url      The extra parameters
     * @param string $delivery_mode The delivery mode
     *
     * @return array The delivery zone data.
     */
    public static function zonesPickpoint($url_zone, $data_zone, $data_url, $delivery_mode = '')
    {
        $_result = array();
        $response = Http::post($url_zone, json_encode($data_zone), $data_url);

        $result = json_decode($response);
        $data_result = json_decode(json_encode($result), true);
        if (isset($data_result['Error']) && ($data_result['Error'] == 1) && !empty($data_result['ErrorMessage'])){
           self::$last_error = $data_result['ErrorMessage'];

        }  elseif (isset($data_result['Error']) && !empty($data_result['Error'])) {
            self::$last_error = $data_result['Error'];

        } elseif (isset($data_result['Zones'])) {
            foreach ($data_result['Zones'] as $zone) {
                if (!empty($zone['DeliveryMode']) && $delivery_mode == $zone['DeliveryMode']) {
                    $_result = array(
                        'delivery_min' => $zone['DeliveryMin'],
                        'delivery_max' => $zone['DeliveryMax'],
                        'koefficient'  => $zone['Koeff'],
                        'zone'         => $zone['Zone'],
                        'to_pt'        => $zone['ToPT']
                    );
                }
            }

            if (!$_result) {
                $zone = reset($data_result['Zones']);
                $_result = array(
                    'delivery_min' => $zone['DeliveryMin'],
                    'delivery_max' => $zone['DeliveryMax'],
                    'koefficient'  => $zone['Koeff'],
                    'zone'         => $zone['Zone'],
                    'to_pt'        => $zone['ToPT']
                );
            }
        }

        return $_result;
    }

    public static function findPostamatPickpoint(&$pickpoint_id, $city = '')
    {
        $_result = array();
        if (!empty($city)) {
            $data_pickpoint = db_get_row("SELECT * FROM ?:rus_pickpoint_postamat WHERE city_name = ?s", $city);

        } elseif (!empty($pickpoint_id)) {
            $data_pickpoint = static::getPickpointPostamatById($pickpoint_id);
        }

        if (!empty($data_pickpoint)) {
            $pickpoint_id = $data_pickpoint['number'];
            $_result = $data_pickpoint['name'] . ' ' . $data_pickpoint['number'] . ' ' . $data_pickpoint['post_code'] . ' ' . $data_pickpoint['region_name'] . ' ' . $data_pickpoint['city_name'] . ' ' . $data_pickpoint['address'];
        }

        return $_result;
    }

    /**
     * Gets Pickpoint postamat data from the database.
     *
     * @param string $postamat_id Unique postamat identifier
     *
     * @return array Postamat data
     */
    public static function getPickpointPostamatById($postamat_id)
    {
        return db_get_row("SELECT * FROM ?:rus_pickpoint_postamat WHERE number = ?s", $postamat_id);
    }

    public static function postamatPickpoint($url_postamat)
    {
        $data_result = Http::get($url_postamat, self::$extra_data);

        $data_result = json_decode($data_result);
        $data_result = json_decode(json_encode($data_result), true);
        if (isset($data_result['Error']) && ($data_result['Error'] == 1) && !empty($data_result['ErrorMessage'])){
           self::$last_error = $data_result['ErrorMessage'];

        }  elseif (isset($data_result['Error']) && !empty($data_result['Error'])) {
            self::$last_error = $data_result['Error'];

        } elseif (isset($data_result)) {
            db_query('TRUNCATE TABLE ?:rus_pickpoint_postamat');

            foreach ($data_result as $postamat) {
                $pickpoint_office = array(
                    'city_name' => $postamat['CitiName'],
                    'country_name' => $postamat['CountryName'],
                    'region_name' => $postamat['Region'],
                    'number' => $postamat['Number'],
                    'name' => $postamat['Name'],
                    'work_time' => $postamat['WorkTime'],
                    'post_code' => $postamat['PostCode'],
                    'address' => $postamat['Address']
                );

                db_replace_into('rus_pickpoint_postamat', $pickpoint_office);
            }
        }
    }
}
