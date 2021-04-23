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

if (defined('PAYMENT_NOTIFICATION')) {

    if (isset($_REQUEST['ordernumber'])) {
        list($order_id) = explode('_', $_REQUEST['ordernumber']);

    } elseif (isset($_REQUEST['orderNumber'])) {
        list($order_id) = explode('_', $_REQUEST['orderNumber']);

    } elseif (isset($_REQUEST['merchant_order_id'])) {
        list($order_id) = explode('_', $_REQUEST['merchant_order_id']);

    } else {
        $order_id = 0;
    }

    $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
    $processor_data = fn_get_processor_data($payment_id);
    $shop_id = $processor_data['processor_params']['shop_id'];

    if (!empty($processor_data['processor_params']['logging']) && $processor_data['processor_params']['logging'] == 'Y') {
        fn_yandex_money_log_write($mode, 'ym_request.log');
        fn_yandex_money_log_write($_REQUEST, 'ym_request.log');
    }

    if ($mode == 'ok') {

        if (fn_check_payment_script('yandex_money.php', $order_id)) {

            $times = 0;
            while ($times <= YM_MAX_AWAITING_TIME) {

                $_order_id = db_get_field("SELECT order_id FROM ?:order_data WHERE order_id = ?i AND type = 'S'", $order_id);
                if (empty($_order_id)) {
                    break;
                }

                sleep(1);
                $times++;
            }

            $order_status = db_get_field("SELECT status FROM ?:orders WHERE order_id = ?i", $order_id);

            if ($order_status == STATUS_INCOMPLETED_ORDER) {
                fn_change_order_status($order_id, 'O');
            }

            fn_order_placement_routines('route', $order_id, false);
        }

    } elseif ($mode == 'error') {

        $pp_response['order_status'] = 'N';
        $pp_response["reason_text"] = __('text_transaction_declined');

        if (fn_check_payment_script('yandex_money.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
        }

        fn_order_placement_routines('route', $order_id);

    } elseif ($mode == 'return') {

        if (!empty($_REQUEST['yandexPaymentId'])) {
            $order_status = db_get_field("SELECT status FROM ?:orders WHERE order_id = ?i", $order_id);
            $pp_response["transaction_id"] = $_REQUEST['yandexPaymentId'];

            if ($order_status == STATUS_INCOMPLETED_ORDER) {
                $pp_response['order_status'] = 'O';
            }

        } else {
            $pp_response['order_status'] = 'N';
            $pp_response["reason_text"] = __('text_transaction_cancelled');
        }

        if (fn_check_payment_script('yandex_money.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
        }

        fn_order_placement_routines('route', $order_id);

    } elseif ($mode == 'check_order') {

        $date_time = date('c');
        $code = YANDEX_MONEY_CODE_SUCCESS;
        $invoiceId = $_REQUEST['invoiceId'];

        header("Content-Type: text/xml; charset=utf-8");

        $dom = new DOMDocument('1.0', 'utf-8');
        $item = $dom->createElement('checkOrderResponse');
        $item->setAttribute('performedDatetime', $date_time);
        $item->setAttribute('shopId', $shop_id);
        $item->setAttribute('invoiceId', $invoiceId);

        $order_info = fn_get_order_info($order_id);
        $order_total = empty($order_info['payment_info']['yandex_total']) ? $order_info['total'] : $order_info['payment_info']['yandex_total'];

        $item->setAttribute('yandex_total', $order_info['payment_info']['yandex_total']);
        $item->setAttribute('order_total', $order_info['total']);
        $item->setAttribute('orderSumAmount1', $_REQUEST['orderSumAmount']);
        if ($_REQUEST['orderSumAmount'] != $order_total) {
            $code = YANDEX_MONEY_CODE_TRANSFER_REFUSED;
            $item->setAttribute('orderSumAmount', $order_total);

        } else {

            $hash = $_REQUEST['action'] . ';' . $_REQUEST['orderSumAmount'] . ';' . $_REQUEST['orderSumCurrencyPaycash'] . ';' . $_REQUEST['orderSumBankPaycash'] . ';' . $_REQUEST['shopId'] . ';' . $_REQUEST['invoiceId'] . ';' . $_REQUEST['customerNumber'] . ';' . $processor_data['processor_params']['md5_shoppassword'];
            $hash = md5($hash);
            $hash = strtoupper($hash);

            if ($_REQUEST['md5'] != $hash) {
                $code = YANDEX_MONEY_CODE_AUTH_ERROR;
            }
        }

        $item->setAttribute('code', $code);
        $dom->appendChild($item);
        echo($dom->saveXML());

        if (!empty($processor_data['processor_params']['logging']) && $processor_data['processor_params']['logging'] == 'Y') {
            fn_yandex_money_log_write($dom->saveXML(), 'ym_check_order.log');
        }

        exit;

    } elseif ($mode == 'payment_aviso') {

        $date_time = date('c');
        $code = YANDEX_MONEY_CODE_SUCCESS;
        $invoiceId = $_REQUEST['invoiceId'];

        $hash = $_REQUEST['action'].';'.$_REQUEST['orderSumAmount'].';'.$_REQUEST['orderSumCurrencyPaycash'].';'.$_REQUEST['orderSumBankPaycash'].';'.$_REQUEST['shopId'].';'.$_REQUEST['invoiceId'].';'.$_REQUEST['customerNumber'].';'.$processor_data['processor_params']['md5_shoppassword'];
        $hash = md5($hash);
        $hash = strtoupper($hash);

        if ($_REQUEST['md5'] == $hash) {

            $order_status = 'P';
            $pp_response = array(
                'order_status' => $order_status,
                'yandex_invoice_id' => $invoiceId,
            );

            if (isset($_REQUEST['merchant_order_id'])) {
                $pp_response['yandex_merchant_order_id'] = $_REQUEST['merchant_order_id'];
            }

            if (isset($_REQUEST['order_number'])) {
                $pp_response['yandex_order_number'] = $_REQUEST['order_number'];
            }

            if (
                !empty($processor_data['processor_params']['postponed_payments_enabled'])
                && $processor_data['processor_params']['postponed_payments_enabled'] == 'Y'
            ) {
                $pp_response['order_status'] = $processor_data['processor_params']['unconfirmed_order_status'];
                $pp_response['yandex_postponed_payment'] = true;
            }

            // the receipt is marked as sent when the payment aviso (payment confirmation) arrives
            if (fn_is_yandex_checkpoint_receipt_required($processor_data)) {
                $pp_response['yandex_checkpoint_receipt_sent'] = __('yes');
            }

            if (fn_check_payment_script('yandex_money.php', $order_id)) {
                fn_finish_payment($order_id, $pp_response);
            }

        } else {
            $code = YANDEX_MONEY_CODE_AUTH_ERROR;
            $pp_response['order_status'] = 'N';
            $pp_response['reason_text'] = __('error');

            if (fn_check_payment_script('yandex_money.php', $order_id)) {
                fn_finish_payment($order_id, $pp_response, false);
            }
        }

        header("Content-Type: text/xml; charset=utf-8");

        $dom = new DOMDocument('1.0', 'utf-8');
        $item = $dom->createElement('paymentAvisoResponse');
        $item->setAttribute('performedDatetime', $date_time);
        $item->setAttribute('code', $code);
        $item->setAttribute('invoiceId', $invoiceId);
        $item->setAttribute('shopId', $shop_id);

        $dom->appendChild($item);
        echo($dom->saveXML());

        if (!empty($processor_data['processor_params']['logging']) && $processor_data['processor_params']['logging'] == 'Y') {
            fn_yandex_money_log_write($dom->saveXML(), 'ym_payment_aviso.log');
        }

        db_query('DELETE FROM ?:user_session_products WHERE order_id = ?i AND type = ?s', $order_id, 'C');
        fn_clear_cart(Tygh::$app['session']['cart']);
        exit;
    }

} else {
    if (!defined('BOOTSTRAP')) { die('Access denied'); }

    /** @var array $order_info */
    /** @var array $processor_data */
    /** @var array $payment_info */

    $payment_url = fn_rus_payments_yandex_checkpoint_get_payment_url($processor_data['processor_params']['mode']);

    $payment_request = fn_rus_payments_yandex_checkpoint_get_payment_request($order_info, $processor_data, $payment_info);

    if (!empty($processor_data['processor_params']['logging'])
        && $processor_data['processor_params']['logging'] == 'Y'
    ) {
        fn_yandex_money_log_write($payment_request, 'ym_post_data.log');
    }

    fn_rus_payments_yandex_checkpoint_set_payment_validation_data($order_info['order_id'], $payment_request);

    fn_create_payment_form($payment_url, $payment_request, $processor_data['processor'], false);
}

exit;
