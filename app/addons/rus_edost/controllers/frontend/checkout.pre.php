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

$cart = &Tygh::$app['session']['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode === 'update_steps' || $mode === 'checkout' || $mode === 'update_shipping') {

        if (!empty($_REQUEST['pickpointmap'])) {
            $cart['pickpointmap'] = $_REQUEST['pickpointmap'];
        }

        if (!empty($_REQUEST['select_office'])) {
            $cart['select_office'] = $_REQUEST['select_office'];
        }
    }

    return [CONTROLLER_STATUS_OK];
}

if ($mode === 'checkout') {
    if (!empty($_REQUEST['select_office'])) {
        $cart['select_office'] = $_REQUEST['select_office'];
    }
    if (!empty($cart['select_office'])) {
        Tygh::$app['view']->assign('select_office', $cart['select_office']);
    }
    if (!empty($_REQUEST['pickpointmap'])) {
        $cart['pickpointmap'] = $_REQUEST['pickpointmap'];
    }

}