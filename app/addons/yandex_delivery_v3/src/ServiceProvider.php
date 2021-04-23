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

namespace Tygh\Addons\YandexDelivery;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tygh\Addons\YandexDelivery\HookHandlers\CalculateCartHookHandler;
use Tygh\Addons\YandexDelivery\HookHandlers\CreateShipmentHookHandler;
use Tygh\Addons\YandexDelivery\HookHandlers\PickupPointHookHandler;
use Tygh\Addons\YandexDelivery\HookHandlers\RealtimeShippingsHookHandler;
use Tygh\Addons\YandexDelivery\Services\OrderDetailsBuilder;
use Tygh\Addons\YandexDelivery\Services\OrderService;
use Tygh\Addons\YandexDelivery\Services\ShippingService;
use Tygh\Addons\YandexDelivery\Services\YandexDeliveryService;
use Tygh\Enum\YesNo;
use Tygh\Tygh;
use Tygh\Application;
use Tygh\Registry;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Container $app)
    {
        $app['addons.yandex_delivery.api'] = static function (Application $application) {
            $addon_settings = Registry::get('addons.yandex_delivery_v3');
            return new YandexDeliveryService(
                $addon_settings['oauth_key'],
                $addon_settings['cabinet_id'],
                $addon_settings['sender_ids'],
                $addon_settings['warehouse_ids'],
                YesNo::toBool($addon_settings['autoconfirm'])
            );
        };

        $app['addons.yandex_delivery.service'] = static function (Application $application) {
            return new ShippingService(self::getApiService());
        };

        $app['addons.yandex_delivery.order_request_builder'] = static function (Application $application) {
            return new OrderDetailsBuilder(self::getApiService(), self::getShippingService(), fn_get_settled_order_statuses());
        };

        $app['addons.yandex_delivery.hook_handlers.realtime_services'] = static function (Application $application) {
            return new RealtimeShippingsHookHandler();
        };

        $app['addons.yandex_delivery.hook_handlers.calculate_cart'] = static function (Application $application) {
            return new CalculateCartHookHandler();
        };

        $app['addons.yandex_delivery.hook_handlers.create_shipment'] = static function (Application $application) {
            return new CreateShipmentHookHandler();
        };

        $app['addons.yandex_delivery.hook_handlers.pickup_point'] = static function (Application $application) {
            return new PickupPointHookHandler();
        };

        $app['addons.yandex_delivery.order'] = static function (Application $application) {
            return new OrderService(
                self::getApiService(),
                Tygh::$app['addons.rus_taxes.receipt_factory'],
                Tygh::$app['addons.yandex_delivery.order_request_builder']
            );
        };
    }

    /**
     * @return YandexDeliveryService
     */
    public static function getApiService()
    {
        return Tygh::$app['addons.yandex_delivery.api'];
    }

    /**
     * @return ShippingService
     */
    public static function getShippingService()
    {
        return Tygh::$app['addons.yandex_delivery.service'];
    }

    /**
     * @return OrderService
     */
    public static function getOrderService()
    {
        return Tygh::$app['addons.yandex_delivery.order'];
    }
}
