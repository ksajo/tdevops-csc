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

if ($mode == 'search_tracking') {
    $data_tracking = array();
    if (!empty($params['order_id'])) {
        list($_shipments) = fn_get_shipments_info(array('order_id' => $params['order_id'], 'advanced_info' => true));
        if (!empty($_shipments)) {
            foreach ($_shipments as $key => $shipment) {
                if ($shipment['carrier'] == 'russian_post') {
                    $shipping_data = fn_get_shipping_info($shipment['shipping_id'], DESCR_SL);
                    $data_history = array();
                    if (!empty($shipping_data['service_params']['api_login']) && !empty($shipping_data['service_params']['api_password'])) {
                        $url = 'https://tracking.russianpost.ru/rtm34?wsdl';

                        $data_search = array(
                            'OperationHistoryRequest' => array(
                                'Barcode' => $shipment['tracking_number'],
                                'MessageType' => 0,
                                'Language' => 'RUS'
                            ),
                            'AuthorizationHeader' => array(
                                'login' => $shipping_data['service_params']['api_login'],
                                'password' => $shipping_data['service_params']['api_password']
                            )
                        );

                        $data_soap = new SoapClient($url, array('trace' => 1, 'soap_version' => SOAP_1_2));
                        $result = @$data_soap->getOperationHistory(new SoapParam($data_search, 'OperationHistoryRequest'));
                        if (!empty($result)) {
                            foreach ($result->OperationHistoryData->historyRecord as $d_result) {
                                $data_status = array(
                                    'order_id' => $params['order_id'],
                                    'shipment_id' => $shipment['shipment_id'],
                                    'tracking_number' => $shipment['tracking_number'],
                                    'timestamp' => strtotime($d_result->OperationParameters->OperDate),
                                    'address' => $d_result->AddressParameters->OperationAddress->Description,
                                    'type_operation' => $d_result->OperationParameters->OperType->Name,
                                    'status' => (string) $d_result->OperationParameters->OperAttr->Name
                                );

                                $status_id = db_get_row('SELECT id FROM ?:rus_russianpost_status WHERE order_id = ?i AND shipment_id = ?i AND tracking_number = ?s AND timestamp = ?i AND status = ?s', $data_status['order_id'], $data_status['shipment_id'], $data_status['tracking_number'], $data_status['timestamp'], $data_status['status']);
                                if (empty($status_id)) {
                                    db_query('INSERT INTO ?:rus_russianpost_status ?e', $data_status);
                                }

                                $data_history[] = array(
                                    'timestamp' => $data_status['timestamp'],
                                    'address' => $data_status['address'],
                                    'type_operation' => $data_status['type_operation'],
                                    'status' => $data_status['status']
                                );
                            }
                        }
                    }

                    $data_tracking[$shipment['shipment_id']] = array(
                        'shipping_id' => $shipment['shipping_id'],
                        'tracking_number' => $shipment['tracking_number'],
                        'data_history' => $data_history
                    );
                }
            }

            if (!empty($data_tracking)) {
                Tygh::$app['view']->assign('data_tracking', $data_tracking);
            }
        }

        if (defined('AJAX_REQUEST')) {
            Tygh::$app['view']->display('addons/rus_russianpost/views/orders/components/order_status.tpl');
            exit;
        }
    }

    return array(CONTROLLER_STATUS_OK, 'orders.details&order_id=' . $params['order_id']);
}
