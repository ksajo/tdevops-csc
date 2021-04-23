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

use Tygh\Registry;
use Tygh\Shippings\YandexDelivery\YandexDelivery;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$params = $_REQUEST;

if (defined('AJAX_REQUEST')) {

    if ($mode == 'autocomplete' && !empty($params['q'])) {

        $yd = YandexDelivery::init();

        $params['city'] = !empty($params['city']) ? $params['city'] : '';
        $result = $yd->autocomplete($params['q'], $params['type'], $params['city']);

        $select = array();
        if (!empty($result)) {
            foreach ($result as $city) {
                $city['value'] = explode(',', $city['value']);

                $select[] = array(
                    'code' => $city['value'],
                    'value' => $city['value'][0],
                    'label' => $city['label'],
                );
            }
        }

        Registry::get('ajax')->assign('autocomplete', $select);
        exit();

    } elseif ($mode == 'get_index' && !empty($params['address'])) {

        $yd = YandexDelivery::init();

        $address[] = $params['address'];

        if (!empty($params['city'])) {
            $address[] = $params['city'];
        }

        $result = $yd->getIndex(implode(',', $address));

        if (!empty($result)) {
            Registry::get('ajax')->assign('get_index', $result);
        }

        exit();
    }
}