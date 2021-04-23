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

namespace Tygh\Addons\YandexDelivery\HookHandlers;

use Tygh\Addons\YandexDelivery\ServiceProvider;
use Tygh\Addons\YandexDelivery\Services\YandexDeliveryService;

class CreateShipmentHookHandler
{
    /**
     *  * The "create_shipment_post" hook handler.
     *
     * Actions performed:
     *     - Creates a record about order into Yandex.Delivery service to make this shipment.
     *
     * @param array<string, string>                                                      $shipment_data Array of shipment data.
     * @param array<string, array<int|string, array<string, array<string, int|string>>>> $order_info    Shipment order info.
     * @param int                                                                        $group_key     Group number.
     * @param bool                                                                       $all_products  Flag which indicates all products of order are part of created shipment.
     * @param int                                                                        $shipment_id   Created shipment identifier.
     *
     * @see \fn_update_shipment()
     */
    public function onCreateShipmentPost(array $shipment_data, array $order_info, $group_key, $all_products, $shipment_id)
    {
        $shipping_module = reset($order_info['shipping'])['module'];
        if (
            $shipment_data['carrier'] !== YandexDeliveryService::MODULE
            && $shipping_module !== YandexDeliveryService::MODULE
        ) {
            return;
        }
        db_query('INSERT INTO ?:yad_orders ?e', ['shipment_id' => $shipment_id]);
    }

    /**
     * @param array $params
     * @param array $fields_list
     * @param array $joins
     * @param string $condition
     * @param array $group
     */
    public function onGetShipment(array $params, array &$fields_list, array &$joins, &$condition, array $group)
    {
        if (!isset($params['search_yandex_delivery_order'])) {
            return;
        }
        $fields_list = array_merge($fields_list, [
            '?:yad_orders.shipment_id',
            '?:yad_orders.yandex_id',
            '?:yad_statuses.yad_status_code'
        ]);

        $joins = array_merge($joins, [
            'INNER JOIN ?:yad_orders ON ?:yad_orders.shipment_id = ?:shipments.shipment_id',
            'LEFT JOIN ?:yad_statuses ON ?:yad_orders.status = ?:yad_statuses.yad_status_id',
        ]);

        if (!empty($params['yad_status'])) {
            $condition .= db_quote(' AND yad_status_code = ?s', $params['yad_status']);
        }
    }

    /**
     * @param array<int> $shipment_ids Identifiers of deleted shipments
     * @param int        $result       Number of affected by deletion database rows
     */
    public function onDeleteShipments($shipment_ids, $result)
    {
        $yd_order_ids = db_get_fields('SELECT yandex_id FROM ?:yad_orders WHERE shipment_id IN (?n)', $shipment_ids);
        if (empty($yd_order_ids)) {
            return;
        }
        $yandex_delivery = ServiceProvider::getApiService();

        foreach ($yd_order_ids as $yandex_id) {
            if (empty($yandex_id)) {
                continue;
            }
            $yandex_order_data = $yandex_delivery->getOrdersId($yandex_id);
            $status = isset($yandex_order_data['status']) ? $yandex_order_data['status'] : '';
            if (!empty($status) && $status !== 'CANCELED') {
                $yandex_delivery->deleteOrdersId($yandex_id);
            }
        }
        db_query('DELETE FROM ?:yad_orders WHERE shipment_id IN (?n)', $shipment_ids);
        db_query('DELETE FROM ?:yad_order_statuses WHERE yandex_id IN (?n)', $yd_order_ids);
    }
}
