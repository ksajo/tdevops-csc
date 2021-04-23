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

if (defined('PAYMENT_NOTIFICATION')) {
    if (empty($_REQUEST['orderId'])) {
        die('Access denied');
    }

    list($order_id) = explode('_', $_REQUEST['orderId']);
    $pp_response = array();

    if ($mode == 'placement') {

        if (fn_payment_rbk_is_empty($_REQUEST)) {
            die('Access denied');
        }

        $sign = fn_payment_rbk_get_hash($order_id, $_REQUEST);
        $paymentStatus = $_REQUEST['paymentStatus'];

        $pp_response['transaction_id'] = $_REQUEST['paymentId'];
        $pp_response['reason_text'] = __('rbk_status' . $paymentStatus);

        if ($sign == $_REQUEST['hash']) {

            if ($paymentStatus == 5) {
                $pp_response['order_status'] = 'P';
                fn_finish_payment($order_id, $pp_response);
            }

        } else {
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text'] = __('error');
            fn_finish_payment($order_id, $pp_response);
        }

        exit;

    } elseif ($mode == 'success') {

        $_placed = false;
        $times = 0;

        while (!$_placed) {
            $_order_id = db_get_field("SELECT order_id FROM ?:order_data WHERE order_id = ?i AND type = 'S'", $order_id);

            if (empty($_order_id)) {
                $_placed = true;
            }

            sleep(1);

            $times++;
            if ($times > RBK_MAX_AWAITING_TIME) {
                break;
            }
        }

    } elseif ($mode == 'fail') {

    }

    fn_order_placement_routines('route', $order_id, false);

} else {
    $processor_url = 'https://rbkmoney.ru/acceptpurchase.aspx';

    $rbk_eshopId = $processor_data['processor_params']['rbk_eshopId'];
    $_order_id = $order_info['repaid'] ? ($order_id . '_' . $order_info['repaid']) : $order_info['order_id'];
    $_currency = $processor_data['processor_params']['currency'];

    $post = array();
    $post['eshopId'] = $rbk_eshopId;
    $post['orderId'] = $_order_id;
    $post['serviceName'] = __('rbk_serviceName');
    $post['recipientAmount'] = fn_payment_rbk_get_order_total($order_info['total']);
    $post['recipientCurrency'] = ($_currency == 'RUB') ? 'RUR' : $_currency;
    $post['user_email'] = $order_info['email'];
    $post['version'] = 1;
    $post['preference'] = $processor_data['processor_params']['rbk_paymethod'];
    $post['language'] = $processor_data['processor_params']['rbk_language'];
    $post['successUrl'] = fn_url('payment_notification.success?payment=rbk&orderId=' . $_order_id);
    $post['failUrl'] = fn_url('payment_notification.fail?payment=rbk&orderId=' . $_order_id);
    $post['rbk_secretKey'] = $processor_data['processor_params']['rbk_secretKey'];
    $hash = $post['eshopId'] . '::' . $post['recipientAmount'] . '::' . $post['recipientCurrency'] . '::' . $post['user_email'] . '::' . $post['serviceName'] . '::' . $post['orderId'] . '::::' . $post['rbk_secretKey'];
    $post['hash'] = md5($hash);

    fn_create_payment_form($processor_url, $post, 'RBK', false);
}

function fn_payment_rbk_is_empty($request)
{
    return empty($request['serviceName'])       ||
           empty($request['eshopAccount'])      ||
           empty($request['recipientCurrency']) ||
           empty($request['paymentStatus'])     ||
           empty($request['userName'])          ||
           empty($request['userEmail'])         ||
           empty($request['paymentData']);
}

function fn_payment_rbk_get_hash($order_id, $request)
{
    $order_info = fn_get_order_info($order_id);

    $hash_data = array(
        $order_info['payment_method']['processor_params']['rbk_eshopId'],
        $request['orderId'],
        $request['serviceName'],
        $request['eshopAccount'],
        fn_payment_rbk_get_order_total($order_info['total']),
        $request['recipientCurrency'],
        $request['paymentStatus'],
        $request['userName'],
        $request['userEmail'],
        $request['paymentData']
    );

    if (!empty($_REQUEST['secretKey'])) {
        $hash_data[] = $_REQUEST['secretKey'];
    } elseif (!empty($order_info['payment_method']['processor_params']['rbk_secretKey'])) {
        $hash_data[] = $order_info['payment_method']['processor_params']['rbk_secretKey'];
    }

    $hash = implode('::', $hash_data);

    return md5($hash);
}

function fn_payment_rbk_get_order_total($order_total)
{
    $total = fn_format_price_by_currency($order_total, CART_PRIMARY_CURRENCY, 'RUB');
    $total = number_format($total, 2, '.', '');

    return $total;
}
