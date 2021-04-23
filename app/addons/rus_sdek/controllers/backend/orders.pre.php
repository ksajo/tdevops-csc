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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$params = $_REQUEST;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'delete' || $mode == 'm_delete') {

        if (!empty($_REQUEST['order_id'])) {
            $data_orders = db_get_array('SELECT * FROM ?:rus_sdek_register WHERE order_id =?i', $_REQUEST['order_id']);

            foreach($data_orders as $_order) {
                $shipment_id = $_order['shipment_id'];
                list($shipments, $params) = fn_get_shipments_info(array('advanced_info' => true, 'shipment_id' => $shipment_id));
                $shipment = reset($shipments);

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
                }

                $param_search = db_quote(' WHERE order_id = ?i AND shipment_id = ?i ', $_REQUEST['order_id'], $shipment_id);
                db_query('DELETE FROM ?:rus_sdek_products ?p ', $param_search);
                db_query('DELETE FROM ?:rus_sdek_register ?p ', $param_search);
                db_query('DELETE FROM ?:rus_sdek_status ?p ', $param_search);
                db_query('DELETE FROM ?:rus_sdek_history_status ?p ', $param_search);
                db_query('DELETE FROM ?:rus_sdek_call_recipient ?p ', $param_search);
                db_query('DELETE FROM ?:rus_sdek_call_courier ?p ', $param_search);
            }
        }

        $url = fn_url("orders.manage");
        return array(CONTROLLER_STATUS_OK, $url);
    }
}
