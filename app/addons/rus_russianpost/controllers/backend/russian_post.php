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
    return;
}

if ($mode === 'get_services_list') {

    if (!empty($_REQUEST['object_id'])
        && defined('AJAX_REQUEST')
    ) {
        $shipping = !empty($_REQUEST['shipping_data']) ? $_REQUEST['shipping_data'] : array();
        $sending_services = fn_rus_russianpost_get_shipping_services_by_sending_object($_REQUEST['object_id']);

        /** @var Tygh\SmartyEngine\Core $view */
        $view = Tygh::$app['view'];

        $view->assign(array(
            'sending_services' => $sending_services,
            'shipping' => $shipping,
        ));

        $view->display('addons/rus_russianpost/views/shippings/components/services/russian_post_services.tpl');

        exit;
    }
}
