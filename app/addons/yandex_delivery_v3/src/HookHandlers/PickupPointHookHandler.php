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
use Tygh\Addons\YandexDelivery\ServiceProvider;
use Tygh\Addons\YandexDelivery\Services\YandexDeliveryService;
use Tygh\Template\Document\Variables\PickpupPointVariable;

class PickupPointHookHandler
{
    /**
     * @param PickpupPointVariable $instance
     * @param array $order
     * @param string $lang_code
     * @param bool $is_selected
     * @param string $name
     * @param string $phone
     * @param string $full_address
     * @param string $open_hours_raw
     * @param string $open_hours
     * @param string $description_raw
     * @param string $description
     */
    public function onInitVariable
    (
        PickpupPointVariable $instance,
        $order,
        $lang_code,
        &$is_selected,
        &$name,
        &$phone,
        &$full_address,
        &$open_hours_raw,
        &$open_hours,
        &$description_raw,
        &$description
    ) {
        if (empty($order['shipping'])) {
            return;
        }

        if (is_array($order['shipping'])) {
            $shipping = reset($order['shipping']);
        } else {
            $shipping = $order['shipping'];
        }

        if (empty($shipping['module']) || $shipping['module'] !== YandexDeliveryService::MODULE) {
            return;
        }

        if ($shipping['service_params']['type_delivery'] !== DeliveryType::PICKUP) {
            return;
        }
        if (empty($shipping['pickup_data'])) {
            return;
        }
        $pickup_data = $shipping['pickup_data'];
        $is_selected = true;
        $shipping_service = ServiceProvider::getShippingService();
        $name = $pickup_data['name'];
        $phones = $pickup_data['phones'];
        if ($phones) {
            $phone = reset($phones)['number'];
        }
        $full_address = $pickup_data['address']['addressString'];
        $open_hours_raw = $shipping_service->calculateWorkTime($pickup_data['schedule']);
        $open_hours_raw = $shipping_service->formatOpenHoursForPickupPoint($open_hours_raw, $lang_code);
        $open_hours = implode('<br/>', $open_hours_raw);
        $description_raw = $description = $pickup_data['instruction'];
    }
}
