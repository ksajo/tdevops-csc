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

$sending_object = array(
    'parcel' => array(
        'title' => __('addons.rus_russianpost.parcel'),
        'variants' => array(
            '4030' => __('addons.rus_russianpost.non_standard_parcel'),
            '27030' => __('addons.rus_russianpost.simple_parcel'),
            '27020' => __('addons.rus_russianpost.parcel_with_declared_value'),
            '27040' => __('addons.rus_russianpost.parcel_with_declared_value_and_cash_on_delivery'),
            '29030' => __('addons.rus_russianpost.express_parcel'),
            '29020' => __('addons.rus_russianpost.express_parcel_with_declared_value'),
            '29040' => __('addons.rus_russianpost.express_parcel_with_declared_value_and_cash_on_delivery'),
            '28030' => __('addons.rus_russianpost.courier_ems_parcel'),
            '28020' => __('addons.rus_russianpost.courier_ems_parcel_with_declared_value'),
            '28040' => __('addons.rus_russianpost.courier_ems_parcel_with_declared_value_and_cash_on_delivery'),
            '4020' => __('addons.rus_russianpost.non_standard_parcel_with_declared_value'),
            '4040' => __('addons.rus_russianpost.non_standard_parcel_with_declared_value_and_cash_on_delivery'),
            '47030' => __('addons.rus_russianpost.1st_class_parcel'),
            '47020' => __('addons.rus_russianpost.1st_class_parcel_with_declared_value'),
            '47040' => __('addons.rus_russianpost.1st_class_parcel_with_declared_value_and_cash_on_delivery'),
            '23030' => __('addons.rus_russianpost.simple_online_parcel'),
            '23020' => __('addons.rus_russianpost.simple_online_parcel_with_declared_value'),
            '23040' => __('addons.rus_russianpost.simple_online_parcel_with_declared_value_and_cash_on_delivery'),
            '24030' => __('addons.rus_russianpost.online_courier_simple'),
            '24020' => __('addons.rus_russianpost.online_courier_simple_with_declared_value'),
            '24040' => __('addons.rus_russianpost.online_courier_simple_with_declared_value_and_cash_on_delivery'),
            '30030' => __('addons.rus_russianpost.business_courier'),
            '30020' => __('addons.rus_russianpost.business_courier_with_declared_value'),
            '31030' => __('addons.rus_russianpost.business_courier_express'),
            '31020' => __('addons.rus_russianpost.business_courier_express_with_declared_value'),
        ),
    ),
    'wrapper' => array(
        'title' => __('addons.rus_russianpost.wrapper'),
        'variants' => array(
            '3000' => __('addons.rus_russianpost.simple_wrapper'),
            '3010' => __('addons.rus_russianpost.registered_wrapper'),
            '3020' => __('addons.rus_russianpost.wrapper_with_declared_value'),
            '3040' => __('addons.rus_russianpost.wrapper_with_declared_value_and_cash_on_delivery'),
            '16010' => __('addons.rus_russianpost.registered_1st_class_wrapper'),
            '16020' => __('addons.rus_russianpost.wrapper_1st_class_parcel_with_declared_value'),
            '16040' => __('addons.rus_russianpost.wrapper_1st_class_with_declared_value_and_cash_on_delivery'),
        ),
    ),
    'ems' => array(
        'title' => __('addons.rus_russianpost.send_ems'),
        'variants' => array(
            '7030' => __('addons.rus_russianpost.ems'),
            '7020' => __('addons.rus_russianpost.ems_with_declared_value'),
            '7040' => __('addons.rus_russianpost.ems_with_declared_value_and_cash_on_delivery'),
            '34030' => __('addons.rus_russianpost.ems_optimal'),
            '34020' => __('addons.rus_russianpost.ems_optimal_with_declared_value'),
            '34040' => __('addons.rus_russianpost.ems_optimal_with_declared_value_and_cash_on_delivery'),
            '41030' => __('addons.rus_russianpost.ems_рт'),
            '41020' => __('addons.rus_russianpost.ems_рт_with_declared_value'),
            '41040' => __('addons.rus_russianpost.ems_рт_with_declared_value_and_cash_on_delivery'),
        ),
    ),
    'international' => array(
        'title' => __('addons.rus_russianpost.send_international'),
        'variants' => array(
            '3001' => __('addons.rus_russianpost.simple_wrapper'),
            '3011' => __('addons.rus_russianpost.registered_wrapper'),
            '4031' => __('addons.rus_russianpost.simple_parcel'),
            '4021' => __('addons.rus_russianpost.parcel_with_declared_value'),
            '4041' => __('addons.rus_russianpost.parcel_with_declared_value_and_cash_on_delivery'),
            '7031' => __('addons.rus_russianpost.ems_simple'),
            '5001' => __('addons.rus_russianpost.small_package_simple'),
            '5011' => __('addons.rus_russianpost.small_package_registered'),
            '9001' => __('addons.rus_russianpost.simple_small_bag'),
            '9011' => __('addons.rus_russianpost.registered_small_bag'),
        ),
    )
);

return $sending_object;

