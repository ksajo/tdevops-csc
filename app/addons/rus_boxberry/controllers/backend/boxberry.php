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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'save_token') {
    if (isset($_REQUEST['token']) && !empty($_REQUEST['token'])){
        $shipping_data['service_params']['password'] = $_REQUEST['token'];
        $shipping_id = $_REQUEST['shipping_id'];

        fn_update_shipping($shipping_data, $shipping_id, DEFAULT_LANGUAGE);
    }
}