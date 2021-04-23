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
use Tygh\Shippings\RusSdek;

if ($mode == 'delete' || $mode == 'm_delete') {
    if (!empty($_REQUEST['shipment_ids'])) {
        foreach ($_REQUEST['shipment_ids'] as $shipment_id) {
            list($shipments, $params) = fn_get_shipments_info(array('advanced_info' => true, 'shipment_id' => $shipment_id));
            $shipment = reset($shipments);

            if ($shipment['carrier'] == 'sdek') {
                $params_shipping = array(
                    'shipping_id' => $shipment['shipping_id'],
                    'Date' => date("Y-m-d", $shipment['shipment_timestamp']),
                );

                $data_auth = RusSdek::dataAuth($params_shipping);
                if (!empty($data_auth)) {
                    $data_auth['Number'] = $shipment['order_id'] . '_' . $shipment_id;
                    $data_auth['OrderCount'] = "1";
                    $xml = '            ' . RusSdek::arraySimpleXml('DeleteRequest', $data_auth, 'open');
                    $order_sdek = array (
                        'Number' => $shipment['order_id'] . '_' . $shipment_id
                    );
                    $xml .= '            ' . RusSdek::arraySimpleXml('Order', $order_sdek);
                    $xml .= '            ' . '</DeleteRequest>';

                    $response = RusSdek::xmlRequest(SDEK_URL_INTEGRATION . 'delete_orders.php', $xml, $data_auth);
                    $result = RusSdek::resultXml($response);

                    if (empty($result['error'])) {
                        $param_search = db_quote(' WHERE order_id = ?i AND shipment_id = ?i ', $shipment['order_id'], $shipment_id);
                        db_query('DELETE FROM ?:rus_sdek_products ?p ', $param_search);
                        db_query('DELETE FROM ?:rus_sdek_register ?p ', $param_search);
                        db_query('DELETE FROM ?:rus_sdek_status ?p ', $param_search);
                        db_query('DELETE FROM ?:rus_sdek_history_status ?p ', $param_search);
                        db_query('DELETE FROM ?:rus_sdek_call_recipient ?p ', $param_search);
                        db_query('DELETE FROM ?:rus_sdek_call_courier ?p ', $param_search);
                    }
                }
            }
        }
    }

    $sdek_history = db_get_array("SELECT COUNT(*) FROM ?:rus_sdek_history_status");
    if (empty($sdek_history)) {
        return array(CONTROLLER_STATUS_OK, "shipments.manage");
    }
}

if ($mode == 'details') {
    if (!empty($_REQUEST['shipment_id'])) {
        $shipment_id = $_REQUEST['shipment_id'];
        list($shipments, $params) = fn_get_shipments_info(array('advanced_info' => true, 'shipment_id' => $shipment_id));

        $shipment = reset($shipments);

        if ($shipment['carrier'] != 'sdek') {
            return;
        }

        $data_call_recipients = db_get_array(
            'SELECT * FROM ?:rus_sdek_call_recipient WHERE order_id = ?i and shipment_id = ?i',
            $shipment['order_id'],
            $shipment['shipment_id']
        );

        $data_call_couriers = db_get_array(
            'SELECT * FROM ?:rus_sdek_call_courier WHERE order_id = ?i and shipment_id = ?i',
            $shipment['order_id'],
            $shipment['shipment_id']
        );

        $data_call_recipient= array();
        if (!empty($data_call_recipients)) {
            $data_call_recipient = reset($data_call_recipients);

            if (!empty($data_call_recipient['timebag']) && !empty($data_call_recipient['timeend'])) {
                $data_call_recipient['period'] = __('addons.rus_sdek.time_work_period', array('[timebag]' => $data_call_recipient['timebag'], '[timeend]' => $data_call_recipient['timeend']));
            }
        }

        $data_call_courier= array();
        if (!empty($data_call_couriers)) {
            $data_call_courier = reset($data_call_couriers);

            if (!empty($data_call_courier['timebag']) && !empty($data_call_courier['timeend'])) {
                $data_call_courier['period'] = __('addons.rus_sdek.time_work_period', array('[timebag]' => $data_call_courier['timebag'], '[timeend]' => $data_call_courier['timeend']));
            }

            if (!empty($data_call_courier['lunch_timebag']) && !empty($data_call_courier['lunch_timeend'])) {
                $data_call_courier['period_lunch'] = __('addons.rus_sdek.time_lunch_period', array('[lunch_timebag]' => $data_call_courier['lunch_timebag'], '[lunch_timeend]' => $data_call_courier['lunch_timeend']));
            }
        }

        $status = array();
        $data_status = db_get_row(
            'SELECT status, city_code, timestamp FROM ?:rus_sdek_history_status'
            . ' WHERE order_id = ?i AND shipment_id = ?i'
            . ' ORDER BY timestamp DESC',
            $shipment['order_id'],
            $shipment['shipment_id']
        );

        if (!empty($data_status)) {
            $status = $data_status;
            $status['city'] = db_get_field(
                'SELECT city'
                . ' FROM ?:rus_city_descriptions as a'
                . ' LEFT JOIN ?:rus_sdek_cities_link as b'
                    . ' ON a.city_id=b.city_id'
                . ' WHERE b.sdek_city_code = ?s',
                $data_status['city_code']
            );
        }

        $data_shipment = db_get_row(
            'SELECT * FROM ?:rus_sdek_register WHERE order_id = ?i AND shipment_id = ?i',
            $shipment['order_id'],
            $shipment['shipment_id']
        );

        if (!empty($data_shipment)) {
            Tygh::$app['view']->assign('sdek_shipment_created', true);
        }

        Tygh::$app['view']->assign('data_call_recipient', $data_call_recipient);
        Tygh::$app['view']->assign('data_call_courier', $data_call_courier);
        Tygh::$app['view']->assign('order_id', $shipment['order_id']);
        Tygh::$app['view']->assign('status', $status);
    }
}
