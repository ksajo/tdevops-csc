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

use Tygh\Core\ApplicationInterface;
use Tygh\Core\BootstrapInterface;
use Tygh\Core\HookHandlerProviderInterface;

class Bootstrap implements BootstrapInterface, HookHandlerProviderInterface
{
    /**
     * @inheridoc
     */
    public function boot(ApplicationInterface $app)
    {
        $app->register(new ServiceProvider());
    }

    /**
     * @inheritDoc
     */
    public function getHookHandlerMap()
    {
        return [
            'realtime_services_process_response_post' => [
                'addons.yandex_delivery.hook_handlers.realtime_services',
                'onRealtimeServicesProcessResponsePost',
            ],
            'calculate_cart_taxes_pre' => [
                'addons.yandex_delivery.hook_handlers.calculate_cart',
                'onCalculateCartTaxesPre'
            ],
            'create_shipment_post' => [
                'addons.yandex_delivery.hook_handlers.create_shipment',
                'onCreateShipmentPost'
            ],
            'get_shipments' => [
                'addons.yandex_delivery.hook_handlers.create_shipment',
                'onGetShipment'
            ],
            'delete_shipments' => [
                'addons.yandex_delivery.hook_handlers.create_shipment',
                'onDeleteShipments'
            ],
            'pickup_point_variable_init' => [
                'addons.yandex_delivery.hook_handlers.pickup_point',
                'onInitVariable'
            ]
        ];
    }
}
