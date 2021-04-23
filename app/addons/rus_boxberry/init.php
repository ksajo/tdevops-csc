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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'pre_place_order',
    'create_shipment',
    'get_shipments',
    'get_shipments_info_post',
    'pickup_point_variable_init',
    'rus_cities_geo_maps_set_customer_location_pre_post',
    'rus_cities_location_manager_detect_zipcode_post_post',
    'checkout_update_steps_before_update_user_data'
);

Registry::get('class_loader')->add('Boxberry', Registry::get('config.dir.addons') . 'rus_boxberry/lib');
