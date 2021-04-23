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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$params = $_REQUEST;

if ($mode == 'details') {
    if(!empty($params['order_id'])) {
        $data_status = array();
        list($_shipments) = fn_get_shipments_info(array('order_id' => $params['order_id'], 'advanced_info' => true));
        if (!empty($_shipments)) {
            foreach ($_shipments as $key => $shipment) {
                if ($shipment['carrier'] == 'russian_post') {
                    $data_status = db_get_array("SELECT * FROM ?:rus_russianpost_status WHERE order_id = ?i AND shipment_id = ?i AND tracking_number = ?s", $params['order_id'], $shipment['shipment_id'], $shipment['tracking_number']);

                    $data_tracking[$shipment['shipment_id']] = array(
                        'shipping_id' => $shipment['shipping_id'],
                        'tracking_number' => $shipment['tracking_number'],
                        'data_history' => $data_status
                    );
                }
            }

            if (!empty($data_tracking)) {
                Tygh::$app['view']->assign('data_tracking', $data_tracking);
                $navigation_tabs = Registry::get('navigation.tabs');
                $navigation_tabs['pochta_information'] = array(
                    'title' => __('shipping_information'),
                    'js' => true,
                    'href' => 'orders.details?order_id=' . $params['order_id'] . '&selected_section=pochta_information'
                );
                Registry::set('navigation.tabs', $navigation_tabs);
            }
        }
    }
}
