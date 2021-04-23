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

use Tygh\Addons\RusOnlineCashRegister\Receipt\Receipt;
use Tygh\Registry;
use Tygh\Addons\RusOnlineCashRegister\Receipt\Item;
use Tygh\Addons\RusOnlineCashRegister\Receipt\Payment;
use Tygh\Addons\RusOnlineCashRegister\RequestLogger;
use Tygh\Addons\RusTaxes\TaxType;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/** @var string $mode */

if ($mode === 'receipts') {
    /** @var Tygh\Addons\RusOnlineCashRegister\ReceiptRepository $receipt_repository */
    $receipt_repository = Tygh::$app['addons.rus_online_cash_register.receipt_repository'];
    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];

    $search = $conditions = array();
    $limit = null;
    $page = isset($_REQUEST['page']) ? (int) $_REQUEST['page'] : 1;

    if (isset($_REQUEST['items_per_page'])) {
        $limit = (int) $_REQUEST['items_per_page'];
    }

    if (!$limit) {
        $limit = (int) Registry::ifGet('settings.Appearance.admin_elements_per_page', 10);
    }

    if (isset($_REQUEST['search']['type']) && !empty($_REQUEST['search']['type'])) {
        $search['type'] = $conditions['type'] = $_REQUEST['search']['type'];
    }

    if (isset($_REQUEST['search']['status']) && !empty($_REQUEST['search']['status'])) {
        $search['status'] = $conditions['status'] = $_REQUEST['search']['status'];
    }

    if (isset($_REQUEST['receipts_period'])) {
        $search['receipts_period'] = $_REQUEST['receipts_period'];
        $search['receipts_time_from'] = $_REQUEST['receipts_time_from'];
        $search['receipts_time_to'] = $_REQUEST['receipts_time_to'];

        list($time_from, $time_to) = fn_create_periods(array(
            'period' => $_REQUEST['receipts_period'],
            'time_from' => $_REQUEST['receipts_time_from'],
            'time_to' => $_REQUEST['receipts_time_to']
        ));

        $conditions[] = array('timestamp', '>=', $time_from);
        $conditions[] = array('timestamp', '<=', $time_to);
    }

    $total_items = $receipt_repository->count($conditions);

    $page = db_get_valid_page($page, $limit, $total_items);
    $offset = ($page - 1) * $limit;

    $receipts = $receipt_repository->search($conditions, $limit, $offset);

    $search = array_merge($search, array(
        'items_per_page' => $limit,
        'total_items' => $total_items,
        'page' => $page
    ));
    $view->assign('receipts', $receipts);
    $view->assign('search', $search);
    $view->assign('statuses', array(
        Receipt::STATUS_WAIT => __('rus_online_cash_register.receipts_list.status.wait'),
        Receipt::STATUS_DONE => __('rus_online_cash_register.receipts_list.status.done'),
        Receipt::STATUS_FAIL => __('rus_online_cash_register.receipts_list.status.fail')
    ));
    $view->assign('types', array(
        Receipt::TYPE_SELL => __('rus_online_cash_register.receipts_list.type.sell'),
        Receipt::TYPE_SELL_REFUND => __('rus_online_cash_register.receipts_list.type.sell_refund'),
    ));
} elseif ($mode === 'receipt') {
    $uuid = isset($_REQUEST['uuid']) ? (string) $_REQUEST['uuid'] : null;

    if (!$uuid) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    /** @var Tygh\Addons\RusOnlineCashRegister\ReceiptRepository $receipt_repository */
    $receipt_repository = Tygh::$app['addons.rus_online_cash_register.receipt_repository'];
    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];

    $receipt = $receipt_repository->findByUUID($uuid);

    if (!$receipt) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    $view->assign('receipt', $receipt);
} elseif ($mode === 'logs') {
    /** @var Tygh\Addons\RusOnlineCashRegister\RequestLogger $request_logger */
    $request_logger = Tygh::$app['addons.rus_online_cash_register.request_logger'];
    /** @var \Tygh\SmartyEngine\Core $view */
    $view = Tygh::$app['view'];

    $search = $conditions = array();
    $limit = null;
    $page = isset($_REQUEST['page']) ? (int) $_REQUEST['page'] : 1;

    if (isset($_REQUEST['items_per_page'])) {
        $limit = (int) $_REQUEST['items_per_page'];
    }

    if (!$limit) {
        $limit = (int) Registry::ifGet('settings.Appearance.admin_elements_per_page', 10);
    }

    if (isset($_REQUEST['search']['status']) && !empty($_REQUEST['search']['status'])) {
        $search['status'] = $conditions['status'] = $_REQUEST['search']['status'];
    }

    if (isset($_REQUEST['logs_period'])) {
        $search['logs_period'] = $_REQUEST['logs_period'];
        $search['logs_time_from'] = $_REQUEST['logs_time_from'];
        $search['logs_time_to'] = $_REQUEST['logs_time_to'];

        list($time_from, $time_to) = fn_create_periods(array(
            'period' => $_REQUEST['logs_period'],
            'time_from' => $_REQUEST['logs_time_from'],
            'time_to' => $_REQUEST['logs_time_to']
        ));

        $conditions[] = array('timestamp', '>=', $time_from);
        $conditions[] = array('timestamp', '<=', $time_to);
    }

    $total_items = $request_logger->count($conditions);

    $page = db_get_valid_page($page, $limit, $total_items);
    $offset = ($page - 1) * $limit;

    $logs = $request_logger->search($conditions, $limit, $offset);

    $search = array_merge($search, array(
        'items_per_page' => $limit,
        'total_items' => $total_items,
        'page' => $page
    ));

    $view->assign('logs', $logs);
    $view->assign('search', $search);
    $view->assign('statuses', array(
        RequestLogger::STATUS_SEND => __('rus_online_cash_register.logs.status.send'),
        RequestLogger::STATUS_SUCCESS => __('rus_online_cash_register.logs.status.success'),
        RequestLogger::STATUS_FAIL => __('rus_online_cash_register.logs.status.fail')
    ));

} elseif ($mode === 'refresh') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $uuid = isset($_REQUEST['uuid']) ? $_REQUEST['uuid'] : null;

        if ($uuid) {
            /** @var Tygh\Addons\RusOnlineCashRegister\ReceiptRepository $receipt_repository */
            $receipt_repository = Tygh::$app['addons.rus_online_cash_register.receipt_repository'];

            /** @var Tygh\Addons\RusOnlineCashRegister\Service $service */
            $service = Tygh::$app['addons.rus_online_cash_register.service'];

            $receipt = $receipt_repository->findByUUID($uuid);

            if ($receipt) {
                $response = $service->refreshReceipt($receipt);

                if ($response->hasErrors()) {
                    foreach ($response->getErrors() as $error) {
                        fn_set_notification('E', __('error'), $error);
                    }
                }

                if ($_REQUEST['return_url']) {
                    return array(CONTROLLER_STATUS_REDIRECT, $_REQUEST['return_url']);
                }

                exit;
            }
        }
    }

    return array(CONTROLLER_STATUS_NO_PAGE);
} elseif ($mode === 'check_connection') {
    /** @var \Tygh\Addons\RusOnlineCashRegister\Factory $factory */
    $factory = Tygh::$app['addons.rus_online_cash_register.factory'];

    $settings = $_REQUEST;
    if (isset($_REQUEST['payment_data']['processor_params']['atol'])) {
        $settings = $_REQUEST['payment_data']['processor_params']['atol'];
    }

    /** @var \Tygh\Addons\RusOnlineCashRegister\CashRegister\Atol\CashRegister $cash_register */
    $cash_register = $factory->createCashRegisterByArray($settings);

    $response = $cash_register->auth();

    if ($response->hasErrors()) {
        $errors = $response->getErrors();

        fn_set_notification('E', __('error'), __('rus_online_cash_register.connection_refused', array('[error]' => reset($errors))));
    } else {
        fn_set_notification('N', __('notice'), __('rus_online_cash_register.connection_successful'));
    }

    if (!defined('AJAX_REQUEST')) {
        if (isset($_REQUEST['payment_data']['processor_params']['atol'])) {
            return array(CONTROLLER_STATUS_OK, 'payments.manage');
        }
        return array(CONTROLLER_STATUS_REDIRECT, '');
    }

    exit;
} elseif ($mode === 'create_test_receipt') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        /** @var \Tygh\Addons\RusOnlineCashRegister\Service $service */
        $service = Tygh::$app['addons.rus_online_cash_register.service'];

        $currency = Registry::get('addons.rus_online_cash_register.currency');

        $user_data = fn_get_user_info(Tygh::$app['session']['auth']['user_id']);

        $receipt = new Receipt();
        $receipt->setStatus(Receipt::STATUS_WAIT);
        $receipt->setType(Receipt::TYPE_SELL);
        $receipt->setTimestamp(new DateTime());
        $receipt->setObjectId(0);
        $receipt->setObjectType('test');
        $receipt->setEmail($user_data['email']);
        $receipt->setPhone($user_data['phone']);
        $receipt->setItem(new Item(__('rus_online_cash_register.test_product_name'), fn_format_price_by_currency(1.11, CART_PRIMARY_CURRENCY, $currency), 1, TaxType::NONE, 0));
        $receipt->setPayment(new Payment(1, $receipt->getTotal()));
        $receipt->setPaymentMethod(Receipt::PAYMENT_METHOD_FULL_PAYMENT);

        $response = $service->sendReceipt($receipt);

        if ($response->hasErrors()) {
            foreach ($response->getErrors() as $error) {
                fn_set_notification('E', __('error'), $error);
            }
        } else {
            fn_set_notification('N', __('notice'), __('rus_online_cash_register.test_receipt_created'));
        }
    }

    return array(CONTROLLER_STATUS_REDIRECT, 'online_cash_register.receipts');
}
