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

use Tygh\Addons\YandexDelivery\Enum\DeliveryType;
use Tygh\Enum\YesNo;
use Tygh\Shippings\IService;
use Tygh\Shippings\Services\YandexDelivery;
use Tygh\Tygh;

class RealtimeShippingsHookHandler
{
    /**
     * The "realtime_services_process_response_post" hook handler.
     *
     * Actions performed:
     *     - Process and saves information about Yandex.Delivery shipping method.
     *
     * @param array<string, string>|string                                                      $result       The result returned by the shipping service
     * @param int                                                                               $shipping_key Shipping service array position
     * @param IService                                                                          $service      The object of the shipping method, the rates of which have just been calculated
     * @param array<string, array<string, array<string, array<string, array<string, string>>>>> $rate         The result of the shipping rate calculation
     *
     * @see \Tygh\Shippings\RealtimeServices::multithreadingCallback()
     */
    public function onRealtimeServicesProcessResponsePost($result, $shipping_key, IService $service, array $rate)
    {
        /** @var \Tygh\Shippings\Services\YandexDelivery $service */
        if (!($service instanceof YandexDelivery && !empty($rate['data']))) {
            return;
        }
        static $yandex_delivery = [];
        /** @var array<string, array<string, string>> $shipping_info */
        $shipping_info = $service->shipping_info;
        $company_id = $service->company_id;
        $group_key = isset($shipping_info['keys']['group_key']) ? $shipping_info['keys']['group_key'] : 0;
        $shipping_id = isset($shipping_info['keys']['shipping_id']) ? $shipping_info['keys']['shipping_id'] : 0;
        $selected_point = $rate['data']['selected_point'];

        switch ($shipping_info['service_params']['type_delivery']) {
            case DeliveryType::PICKUP:
                $pickup_points = $rate['data']['pickup_points'];
                $yandex_delivery[$group_key][$shipping_id]['pickup_points'] = $pickup_points;
                $yandex_delivery[$group_key][$shipping_id]['courier_delivery'] = YesNo::NO;
                break;
            case DeliveryType::COURIER:
                $courier_points = $rate['data']['deliveries'];
                $yandex_delivery[$group_key][$shipping_id]['courier_points'] = $courier_points;
                $yandex_delivery[$group_key][$shipping_id]['courier_delivery'] = YesNo::YES;
                break;
            case DeliveryType::POST:
            default:
                break;
        }

        if (!empty(Tygh::$app['session']['cart']['chosen_shipping'][$group_key])) {
            $chosen_shipping = (int) Tygh::$app['session']['cart']['chosen_shipping'][$group_key];
        } else {
            $chosen_shipping = (int) $service->shipping_info['shipping_id'];
        }

        $yandex_delivery[$group_key][$shipping_id]['selected_point'] = $selected_point;
        $yandex_delivery[$group_key][$shipping_id]['deliveries'] = $rate['data']['deliveries'];
        $yandex_delivery[$group_key][$shipping_id]['deliveries_info'] = $rate['data']['deliveries_info'];

        if (Tygh::$app->has('view')) {
            Tygh::$app['view']->assign('yandex_delivery_v3', $yandex_delivery);
        }
        Tygh::$app['session']['cart']['yandex_delivery_v3'] = $yandex_delivery;

        if (isset($pickup_points[$selected_point])) {
            Tygh::$app['session']['cart']['selected_yad_office'][$company_id][$shipping_id]['pickup_point_id'] = $selected_point;

            if ((int) $service->shipping_info['shipping_id'] === $chosen_shipping) {
                Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key]['selected_shipping']['pickup_point_id'] = $selected_point;
                Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key]['selected_shipping']['pickup_data'] = $pickup_points[$selected_point];
                Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key]['selected_shipping']['delivery'] = $rate['data']['selected_service'];
            }
        } elseif (isset($courier_points[$selected_point])) {
            Tygh::$app['session']['cart']['selected_yad_courier'][$company_id][$shipping_id]['courier_point_id'] = $selected_point;
            $delivery = isset($rate['data']['deliveries'][$selected_point]) ? $rate['data']['deliveries'][$selected_point] : reset($rate['data']['deliveries']);

            if ((int) $service->shipping_info['shipping_id'] === $chosen_shipping) {
                Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key]['selected_shipping']['courier_point_id'] = $selected_point;
                Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key]['selected_shipping']['courier_data'] = $courier_points[$selected_point];
                Tygh::$app['session']['cart']['shippings_extra']['data'][$group_key]['selected_shipping']['delivery'] = $delivery;
            }
        }
    }
}
