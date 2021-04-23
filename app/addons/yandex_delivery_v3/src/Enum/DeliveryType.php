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

namespace Tygh\Addons\YandexDelivery\Enum;

/**
 * Class DeliveryType contains all possible value of delivery type into Yandex.Delivery.
 *
 * @package Tygh\Addons\YandexDelivery\Enum
 */
class DeliveryType
{
    const PICKUP = 'pickup';
    const COURIER = 'courier';
    const POST = 'post';
}
