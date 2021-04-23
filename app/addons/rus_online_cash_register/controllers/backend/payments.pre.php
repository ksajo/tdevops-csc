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

use Tygh\Settings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/** @var string $mode */

if ($mode == 'update') {
    $payment_id = isset($_REQUEST['payment_id']) ? $_REQUEST['payment_id'] : 0;

    Tygh::$app['view']->assign('cash_register_payments', fn_rus_online_cash_register_get_external_payments());
    Tygh::$app['view']->assign('cash_register_payment_id', fn_rus_online_cash_register_get_payment_external_id($payment_id));
    Tygh::$app['view']->assign('cash_register_sno', Settings::instance()->getVariants('rus_online_cash_register', 'sno'));
} elseif ($mode == 'manage') {
    Tygh::$app['view']->assign('cash_register_payments', fn_rus_online_cash_register_get_external_payments());
    Tygh::$app['view']->assign('cash_register_sno', Settings::instance()->getVariants('rus_online_cash_register', 'sno'));
}
