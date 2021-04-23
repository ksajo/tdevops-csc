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

namespace Tygh\Addons\YandexDelivery\Services;

use DateTime;
use Tygh\Addons\YandexDelivery\Enum\DeliveryType;
use Tygh\Addons\YandexDelivery\ServiceProvider;
use Tygh\Registry;

class ShippingService
{
    /** @var YandexDeliveryService $client */
    protected $client;

    /**
     * ShippingService constructor.
     *
     * @param YandexDeliveryService $client Object with methods for Yandex.Delivery API.
     */
    public function __construct(YandexDeliveryService $client)
    {
        $this->client = $client;
    }

    /**
     * Calculates dimensions of delivery.
     *
     * @param array<string, array<string, array<string, string>>> $package_info    Information about package dimensions.
     * @param array<string, float>                                $shipping_params Information about shipping method specified params.
     *
     * @return array{height: float|int, length: float|int, width: float|int}
     */
    public function calculateDimensions(array $package_info, array $shipping_params)
    {
        $package_size = [
            'length' => 0,
            'width' => 0,
            'height' => 0,
        ];

        if (empty($package_info['packages'])) {
            return $package_size;
        }

        $length = !empty($shipping_params['length']) ? $shipping_params['length'] : 0;
        $width = !empty($shipping_params['width']) ? $shipping_params['width'] : 0;
        $height = !empty($shipping_params['height']) ? $shipping_params['height'] : 0;

        $box_data = [];
        foreach ($package_info['packages'] as $package) {
            $box_data[] = [
                empty($package['shipping_params']['box_length']) ? $length : $package['shipping_params']['box_length'],
                empty($package['shipping_params']['box_width']) ? $width : $package['shipping_params']['box_width'],
                empty($package['shipping_params']['box_height']) ? $height : $package['shipping_params']['box_height']
            ];
        }

        foreach ($box_data as $box) {
            $package_size['length'] = $box[0] > $package_size['length'] ? $box[0] : $package_size['length'];
            $package_size['width'] = $box[1] > $package_size['width'] ? $box[1] : $package_size['width'];
            $package_size['height'] += $box[2];
        }

        return $package_size;
    }

    /**
     * Formats packages dimensions into proper form for API requests.
     *
     * @param array<string, array<string, array<string, array<string, string>>>> $package_info Information about product packages.
     *
     * @return array<array<string, array<string, string>>>
     *
     * @psalm-return list<array{dimensions: array{height: int, length: int, weight: float, width: int}}>
     */
    public function formatPackagesDimensions(array $package_info)
    {
        $places = [];
        if (empty($package_info['packages'])) {
            return $places;
        }
        foreach ($package_info['packages'] as $package) {
            if (!isset($package['shipping_params'])) {
                return $places;
            }
            $places[] = [
                'dimensions' => [
                    'length' => (int) ceil((float) $package['shipping_params']['box_length']),
                    'width'  => (int) ceil((float) $package['shipping_params']['box_width']),
                    'height' => (int) ceil((float) $package['shipping_params']['box_height']),
                    'weight' => (float) $package['weight'],
                ]
            ];
        }
        return $places;
    }

    /**
     * Checks product dimensions for empty values.
     *
     * @param array<float> $product_dimensions Product dimensions.
     *
     * @return bool
     */
    public function isProductDimensionsExist(array $product_dimensions)
    {
        foreach ($product_dimensions as $product_dimension) {
            if ($product_dimension === 0.0) {
                return false;
            }
        }
        return true;
    }

    /**
     * Filters delivery services based on specified ids and type of delivery.
     *
     * @param array<string, string> $delivery_info Information about delivery services.
     * @param array<int>            $delivery_ids  List of required delivery service ids.
     *
     * @return array<string, string>
     */
    public function filterDeliveryServices(array $delivery_info, array $delivery_ids)
    {
        $delivery_info = array_filter($delivery_info, static function ($delivery) use ($delivery_ids) {
            return isset($delivery['delivery']['partner']['id'])
                && in_array($delivery['delivery']['partner']['id'], $delivery_ids);
        });

        return $delivery_info;
    }

    /**
     * Gets information about specified pickup points.
     *
     * @param array<string, string> $data Information about deliveries.
     *
     * @return array<int, array<string, string>>|false
     */
    public function getPickupPoints(array $data)
    {
        /** @var array<int> $pickup_points */
        $pickup_points = [];
        foreach ($data as $delivery) {
            if (empty($delivery['pickupPointIds'])) {
                continue;
            }
            $pickup_points += $delivery['pickupPointIds'];
        }

        $client = ServiceProvider::getApiService();
        $all_pickup_ids = [];
        while (count($pickup_points)) {
            $all_pickup_ids[] = ['pickupPointIds' => array_splice($pickup_points, 0, 100)];
        }
        $result = $client->putPickupPoints($all_pickup_ids);
        $result = array_combine(array_column($result, 'id'), $result);
        return $result;
    }

    /**
     * @param array $schedules_list
     * @param string $lang_code
     *
     * @return array
     */
    public function formatOpenHoursForPickupPoint(array $schedules_list, $lang_code)
    {
        if (count($schedules_list) === 1) {
            $schedule_item = reset($schedules_list);

            if (!empty($schedule_item['all_day'])) {
                return [__('yandex_delivery_v3.all_day', [], $lang_code)];
            } elseif (isset($schedule_item['schedule'])) {
                return [__('yandex_delivery_v3.day_every.schedule_single', ['[schedule]' => $schedule_item['schedule']], $lang_code)];
            } elseif (isset($schedule_item['from']) && isset($schedule_item['to'])) {
                return [__('yandex_delivery_v3.day_every.schedule_interval', ['[from]' => $schedule_item['from'], '[to]' => $schedule_item['to']], $lang_code)];
            }

            return [__('yandex_delivery_v3.every_day', [], $lang_code)];
        }

        $open_hours = [];

        foreach ($schedules_list as $schedule_item) {
            if (isset($schedule_item['first_day'], $schedule_item['last_day'])
                && $schedule_item['first_day'] === $schedule_item['last_day']
            ) {
                $days = ['[day]' => __("weekday_{$schedule_item['first_day']}", [], $lang_code)];
            } else {
                $days = [
                    '[first_day]' => __("weekday_{$schedule_item['first_day']}", [], $lang_code),
                    '[last_day]'  => __("weekday_{$schedule_item['last_day']}", [], $lang_code),
                ];
            }

            if (isset($schedule_item['schedule'])) {
                $schedule = ['[schedule]' => $schedule_item['schedule']];
            } elseif (isset($schedule_item['from']) && isset($schedule_item['to'])) {
                $schedule = ['[from]' => $schedule_item['from'], '[to]' => $schedule_item['to']];
            } else {
                $schedule = [];
            }

            $day_type = count($days) === 1
                ? 'single'
                : 'interval';

            switch (count($schedule)) {
                case 2:
                    $schedule_type = 'interval';
                    break;
                case 1:
                    $schedule_type = 'single';
                    break;
                default:
                    $schedule_type = 'closed';
            }

            $open_hours[] = __("yandex_delivery_v3.day_{$day_type}.schedule_{$schedule_type}", array_merge($days, $schedule), $lang_code);
        }

        return $open_hours;
    }

    /**
     * Translate schedule format from Yandex.Delivery API responses to more helpful view.
     *
     * @param array<string, string> $schedule Schedule from Yandex.Delivery API response.
     * @param string                $type     Type of schedule: courier or pickup.
     *
     * @return array
     */
    public function calculateWorkTime(array $schedule, $type = DeliveryType::PICKUP)
    {
        if (empty($schedule)) {
            return [];
        }

        $ndash = html_entity_decode('&ndash;');

        $work_days = [];
        foreach ($schedule as $key => $day) {
            if ($type === DeliveryType::PICKUP) {
                $day_index = $day['day'];

                $day['from'] = substr($day['from'], 0, strrpos($day['from'], ':', -1));
                $day['to'] = substr($day['to'], 0, strrpos($day['to'], ':', -1));
            } else {
                $day_index = isset($day['day']) ? $day['day'] : 0;
                $day['from'] = substr($day['timeFrom'], 0, strrpos($day['timeFrom'], ':', -1));
                $day['to'] = substr($day['timeTo'], 0, strrpos($day['timeTo'], ':', -1));
            }

            $work_days[$day_index]['intervals'][] = [
                'from' => $day['from'],
                'to'   => $day['to']
            ];
        }

        $last_day = reset($work_days);
        $last_day_num = 1;
        $index_compact = 1;
        $compact_work_days = [
            $index_compact => [
                'first_day' => $last_day_num,
                'last_day'  => $last_day_num,
                'intervals' => $last_day['intervals']
            ]
        ];

        foreach ($work_days as $day_num => $day) {
            $last_day_num = $day_num;
            if ($this->checkSameDays($last_day, $day)) {
                $compact_work_days[$index_compact]['last_day'] = $day_num;
            } else {
                $index_compact++;
                $compact_work_days[$index_compact] = [
                    'first_day' => $last_day_num,
                    'last_day'  => $last_day_num,
                    'intervals' => $day['intervals']
                ];
            }
        }

        if ($last_day_num < 7) {
            $index_compact++;
            $compact_work_days[$index_compact]['first_day'] = $last_day_num + 1;
            $compact_work_days[$index_compact]['last_day'] = 7;
            $compact_work_days[$index_compact]['from'] = false;
        }

        foreach ($compact_work_days as &$day) {
            if (!empty($day['intervals'])) {
                foreach ($day['intervals'] as &$interval) {
                    $interval = $interval['from'] . $ndash . $interval['to']; // &ndash; is used as a time interval separator
                }
                unset($interval);
                $day['schedule'] = implode(', ', $day['intervals']);
            }

            if ($day['last_day'] === 7) {
                $day['last_day'] = 0;
            }

            if ($day['first_day'] !== 7) {
                continue;
            }
            $day['first_day'] = 0;
        }
        unset($day);

        return $compact_work_days;
    }

    /**
     * Checks if working schedule on separate days are identical.
     *
     * @param array<string, string> $day_one Working schedule on day one.
     * @param array<string, string> $day_two Working schedule on day two.
     *
     * @return bool
     */
    protected function checkSameDays(array $day_one, array $day_two)
    {
        foreach ($day_one['intervals'] as $interval_index => $interval) {
            if (
                !isset($day_two['intervals'][$interval_index])
                || $interval['from'] !== $day_two['intervals'][$interval_index]['from']
                || $interval['to'] !== $day_two['intervals'][$interval_index]['to']
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get delivery information for specified pickup point identifier.
     *
     * @param array<string, string> $deliveries   Deliveries information.
     * @param int|string            $pickpoint_id Pickup point identifier.
     *
     * @return array
     */
    public function getDeliveryByPickpointId(array $deliveries, $pickpoint_id)
    {
        foreach ($deliveries as $delivery) {
            if (!in_array($pickpoint_id, $delivery['pickupPointIds'])) {
                continue;
            }
            return $delivery;
        }
    }

    /**
     * Get delivery time formatted to specified type of string.
     *
     * @param array<string, string> $shipping_data Information about shipping.
     *
     * @return string
     *
     * @throws \Exception DateTime related exception.
     */
    public function getDeliveryTime(array $shipping_data)
    {
        if (empty($shipping_data['delivery'])) {
            return null;
        }
        $delivery_time = '';
        $time = new DateTime();

        if ($shipping_data['delivery']['calculatedDeliveryDateMin'] === $shipping_data['delivery']['calculatedDeliveryDateMax']) {
            $time_min = new DateTime($shipping_data['delivery']['calculatedDeliveryDateMin']);
            $diff = $time_min->diff($time);
            if ($diff) {
                $delivery_time = ($diff->d === 0)
                    ? '1 ' . __('days')
                    : $diff->d . ' ' . __('days');
            }
        } else {
            $time_min = new DateTime($shipping_data['delivery']['calculatedDeliveryDateMin']);
            $time_max = new DateTime($shipping_data['delivery']['calculatedDeliveryDateMax']);
            $diff_min = $time_min->diff($time);
            $diff_max = $time_max->diff($time);
            if (!($diff_min || $diff_max)) {
                return $delivery_time;
            }
            if ($diff_min) {
                $delivery_time = ($diff_min->d === 0) ? '1' : $diff_min->d;
            }
            if ($diff_max) {
                if ($diff_max->d === 1 && !empty($delivery_time)) {
                    $delivery_time .= ' ' . __('days');
                } else {
                    $delivery_time = empty($delivery_time)
                        ? $delivery_time . $diff_max->d . ' ' . __('days')
                        : $delivery_time . ' - ' . $diff_max->d . ' ' . __('days');
                }
            } elseif (!empty($delivery_time)) {
                $delivery_time .= ' ' . __('days');
            }
        }
        return $delivery_time;
    }
}
