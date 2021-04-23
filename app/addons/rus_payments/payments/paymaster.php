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

use Tygh\Addons\RusTaxes\TaxType;

if (defined('PAYMENT_NOTIFICATION')) {

    $order_id = !empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : 0;

    if ($mode == 'notify') {

        $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
        $processor_data = fn_get_payment_method_data($payment_id);

        $secure_string = $_REQUEST['LMI_MERCHANT_ID'].';'.$_REQUEST['order_id'].';'.$_REQUEST['LMI_SYS_PAYMENT_ID'].';'.$_REQUEST['LMI_SYS_PAYMENT_DATE'].';'.$_REQUEST['LMI_PAYMENT_AMOUNT'].';'.$_REQUEST['LMI_CURRENCY'].';'.$_REQUEST['LMI_PAID_AMOUNT'].';'.$_REQUEST['LMI_PAID_CURRENCY'].';'.$_REQUEST['LMI_PAYMENT_SYSTEM'].';'.$_REQUEST['LMI_SIM_MODE'].';'.$processor_data['processor_params']['paymaster_key'];

        $secret_hash = '';
        if (empty($processor_data['processor_params']['sing_algo']) || $processor_data['processor_params']['sing_algo'] == 'md5') {
            $secret_hash = base64_encode(md5($secure_string, true));
        } elseif ($processor_data['processor_params']['sing_algo'] == 'sha256') {
            $secret_hash = base64_encode(hash('sha256', $secure_string, true));
        }

        if ($_REQUEST['LMI_HASH'] == $secret_hash) {
            $pp_response = array(
                'order_status' => 'P'
            );

            $pp_response["transaction_id"] = $_REQUEST['LMI_SYS_PAYMENT_ID'];

            if (fn_check_payment_script('paymaster.php', $order_id)) {
                fn_finish_payment($order_id, $pp_response);
            }

        } else {
            $order_id = $_REQUEST['order_id'];

            $pp_response['order_status'] = 'N';
            $pp_response["reason_text"] = __('text_transaction_cancelled');

            if (fn_check_payment_script('paymaster.php', $order_id)) {
                fn_finish_payment($order_id, $pp_response, false);
            }
        }

    } elseif ($mode == 'return') {

        if (fn_check_payment_script('paymaster.php', $order_id)) {

            $times = 0;
            while ($times <= PAYMASTER_MAX_AWAITING_TIME) {

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

    } elseif ($mode == 'invoice') {

        echo "YES";

    } elseif ($mode == 'error') {

        $pp_response['order_status'] = 'N';
        $pp_response["reason_text"] = __('text_transaction_cancelled');

        if (fn_check_payment_script('paymaster.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
        }

        fn_order_placement_routines('route', $order_id);
    }

} else {

    if (!defined('BOOTSTRAP')) { die('Access denied'); }

    $post_address = "https://paymaster.ru/Payment/Init";
    $paymaster_products = array();
    $payment_desc = '';

    if (is_array($order_info['products'])) {
        foreach ($order_info['products'] as $k => $v) {
            $payment_desc .= $order_info['products'][$k]['product'] . ' / ';
        }
    }

    if (!empty($order_info['gift_certificates'])) {
        $payment_desc .= __('gift_certificate') . ' / ';
    }

    if (empty($processor_data['processor_params']['currency'])) {
        $processor_data['processor_params']['currency'] = 'RUB';
    }

    if (isset($processor_data['processor_params']['send_receipt'])
        && $processor_data['processor_params']['send_receipt'] == 'Y'
    ) {
        /** @var \Tygh\Addons\RusTaxes\ReceiptFactory $receipt_factory */
        $receipt_factory = Tygh::$app['addons.rus_taxes.receipt_factory'];

        $receipt = $receipt_factory->createReceiptFromOrder($order_info, $processor_data['processor_params']['currency']);

        if ($receipt) {
            $product_count = 0;

            foreach ($receipt->getItems() as $item) {
                $tax_type = $item->getTaxType();

                if ($tax_type === TaxType::NONE) {
                    $tax_type = 'no_vat';
                }

                $paymaster_products['LMI_SHOPPINGCART.ITEMS[' . $product_count . '].NAME'] = $item->getName();
                $paymaster_products['LMI_SHOPPINGCART.ITEMS[' . $product_count . '].QTY'] = $item->getQuantity();
                $paymaster_products['LMI_SHOPPINGCART.ITEMS[' . $product_count . '].PRICE'] = $item->getPrice();
                $paymaster_products['LMI_SHOPPINGCART.ITEMS[' . $product_count . '].TAX'] = strtoupper($tax_type);

                $product_count++;
            }
        }
    }

    $payment_desc = base64_encode($payment_desc);

    $customer_phone = '';
    if (!empty($order_info['phone'])) {
        $customer_phone = $order_info['phone'];

    } elseif (!empty($order_info['b_phone'])) {
        $customer_phone = $order_info['b_phone'];

    } elseif (!empty($order_info['s_phone'])) {
        $customer_phone = $order_info['s_phone'];
    }

    $post_data = array(
        'LMI_MERCHANT_ID' => $processor_data['processor_params']['merchant_id'],
        'LMI_PAYMENT_AMOUNT' => fn_format_price_by_currency($order_info['total'], CART_PRIMARY_CURRENCY, $processor_data['processor_params']['currency']),

        'LMI_CURRENCY' => $processor_data['processor_params']['currency'],

        'LMI_PAYMENT_NO' => $order_info['order_id'],
        'LMI_PAYMENT_DESC_BASE64' => $payment_desc,

        'LMI_INVOICE_CONFIRMATION_URL' => fn_url("payment_notification.invoice?payment=paymaster&order_id=$order_id", AREA),
        'LMI_PAYMENT_NOTIFICATION_URL' => fn_url("payment_notification.notify?payment=paymaster&order_id=$order_id", AREA),

        'LMI_SUCCESS_URL' => fn_url("payment_notification.return?payment=paymaster&order_id=$order_id", AREA),
        'LMI_FAILURE_URL' => fn_url("payment_notification.error?payment=paymaster&order_id=$order_id", AREA),

        'LMI_PAYER_PHONE_NUMBER' => $customer_phone,
        'LMI_PAYER_EMAIL' => $order_info['email'],
    );

    if (!empty($processor_data['processor_params']['payment_method'])) {
        $post_data['LMI_PAYMENT_METHOD'] = $processor_data['processor_params']['payment_method'];
    }

    if (!empty($paymaster_products)) {
        $post_data = array_merge($post_data, $paymaster_products);
    }

    fn_create_payment_form($post_address, $post_data, 'PayMaster', false);
}

exit;
