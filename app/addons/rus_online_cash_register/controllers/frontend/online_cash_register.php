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


use Tygh\Addons\RusOnlineCashRegister\CashRegister\Atol\InfoResponse;
use Tygh\Addons\RusOnlineCashRegister\RequestLogger;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/** @var string $mode */

if ($mode === 'callback_atol') {
    $data = file_get_contents('php://input');

    if (!$data) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    /** @var Tygh\Addons\RusOnlineCashRegister\RequestLogger $request_logger */
    $request_logger = Tygh::$app['addons.rus_online_cash_register.request_logger'];

    /** @var Tygh\Addons\RusOnlineCashRegister\ReceiptRepository $receipt_repository */
    $receipt_repository = Tygh::$app['addons.rus_online_cash_register.receipt_repository'];

    /** @var Tygh\Addons\RusOnlineCashRegister\Service $service */
    $service = Tygh::$app['addons.rus_online_cash_register.service'];

    $request_logger->log('callback', null, $data, RequestLogger::STATUS_SUCCESS);

    $response = new InfoResponse($data);
    $uuid = $response->getUUID();

    if ($uuid) {
        $receipt = $receipt_repository->findByUUID($uuid);
        $service->updateReceiptByInfoResponse($receipt, $response);

        exit();
    }

    return array(CONTROLLER_STATUS_NO_PAGE);
}
