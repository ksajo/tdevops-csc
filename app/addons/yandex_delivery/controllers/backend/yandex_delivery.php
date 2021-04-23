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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if ($mode == 'update' && !empty($_REQUEST['yandex_ids']) && is_array($_REQUEST['yandex_ids'])) {
        fn_yandex_delivery_update_orders($_REQUEST['yandex_ids']);
    }

    if (!empty($_REQUEST['redirect_url'])) {
        return array(CONTROLLER_STATUS_REDIRECT, $_REQUEST['redirect_url']);
    }

    return array(CONTROLLER_STATUS_OK, 'yandex_delivery.manage');
}
