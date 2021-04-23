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

$schema['send_type'] = array(
    '0' => __('addons.rus_russianpost.undefined'),
    '3' => __('addons.rus_russianpost.wrapper'),
    '4' => __('addons.rus_russianpost.parcel'),
    '5' => __('addons.rus_russianpost.small_package'),
    '7' => __('addons.rus_russianpost.send_ems'),
    '9' => __('addons.rus_russianpost.package_m'),
    '16' => __('addons.rus_russianpost.wrapper_first_class'),
    '20' => __('addons.rus_russianpost.multi_envelope'),
    '23' => __('addons.rus_russianpost.parcel_online'),
    '24' => __('addons.rus_russianpost.courier_online'),
    '27' => __('addons.rus_russianpost.parcel_standard'),
    '28' => __('addons.rus_russianpost.parcel_courier'),
    '29' => __('addons.rus_russianpost.parcel_express'),
    '30' => __('addons.rus_russianpost.business_courier'),
    '31' => __('addons.rus_russianpost.business_express')
);

return $schema;
