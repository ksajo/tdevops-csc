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

/** @var string $mode */

if ($mode === 'details') {
    $order_id = $_REQUEST['order_id'];

    /** @var Tygh\Addons\RusOnlineCashRegister\ReceiptRepository $receipt_repository */
    $receipt_repository = Tygh::$app['addons.rus_online_cash_register.receipt_repository'];
    $receipts = $receipt_repository->findAllByObject('order', $order_id);

    Tygh::$app['view']->assign('cash_register_receipts', $receipts);
}
