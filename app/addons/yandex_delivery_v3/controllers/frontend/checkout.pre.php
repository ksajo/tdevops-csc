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

use Tygh\Tygh;

defined('BOOTSTRAP') or die('Access denied');

if ($mode !== 'place_order') {
    if (isset($_REQUEST['select_yad_office'])) {
        foreach ($_REQUEST['select_yad_office'] as $group_id => $shipping) {
            $company_id = Tygh::$app['session']['cart']['product_groups'][$group_id]['company_id'];
            foreach ($shipping as $shipping_id => $pickup_point_id) {
                Tygh::$app['session']['cart']['selected_yad_office'][$company_id][$shipping_id]['pickup_point_id'] = $pickup_point_id;
            }
        }
        Tygh::$app['session']['cart']['calculate_shipping'] = true;
    }

    if (isset($_REQUEST['select_yad_courier'])) {
        foreach ($_REQUEST['select_yad_courier'] as $group_id => $shipping) {
            $company_id = Tygh::$app['session']['cart']['product_groups'][$group_id]['company_id'];
            foreach ($shipping as $shipping_id => $courier_id) {
                Tygh::$app['session']['cart']['selected_yad_courier'][$company_id][$shipping_id]['courier_point_id'] = $courier_id;
            }
        }
        Tygh::$app['session']['cart']['calculate_shipping'] = true;
    }
}
