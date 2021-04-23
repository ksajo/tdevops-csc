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

use Tygh\Addons\RusTaxes\Receipt\Item as ReceiptItem;
use Tygh\Addons\RusTaxes\Receipt\Receipt;
use Tygh\Addons\RusTaxes\TaxType;
use Tygh\Enum\YandexCheckpointVatTypes;
use Tygh\Http;
use Tygh\Payments\Processors\YandexMoneyMWS\Client as MWSClient;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/* HOOKS */

function fn_rus_payments_change_order_status(&$status_to, &$status_from, &$order_info, &$force_notification, &$order_statuses, &$place_order)
{
    $processor_data = fn_get_processor_data($order_info['payment_id']);
    $payment_info = $order_info['payment_info'];

    if (empty($processor_data['processor_script'])) {
        return null;
    }

    if ($processor_data['processor_script'] == 'yandex_money.php' && !empty($payment_info['yandex_postponed_payment'])) {

        try {

            $cert = $processor_data['processor_params']['certificate_filename'];

            $mws_client = new MWSClient();
            $mws_client->authenticate(array(
                'pkcs12_file' => Registry::get('config.dir.certificates') . $cert,
                'pass' => $processor_data['processor_params']['p12_password'],
                'is_test_mode' => $processor_data['processor_params']['mode'] == 'test',
            ));

            if ($status_to == $processor_data['processor_params']['confirmed_order_status']) {

                $mws_client->confirmPayment($payment_info['yandex_invoice_id'], $order_info['total']);

                $payment_info['yandex_confirmed_time'] = date('c');
                $payment_info['yandex_postponed_payment'] = false;

            } elseif ($status_to == $processor_data['processor_params']['canceled_order_status']) {

                $mws_client->cancelPayment($payment_info['yandex_invoice_id']);

                $payment_info['yandex_canceled_time'] = date('c');
                $payment_info['yandex_postponed_payment'] = false;
            }

            $payment_info['order_status'] = $status_to;

            fn_update_order_payment_info($order_info['order_id'], $payment_info);

            $order_info['payment_info'] = $payment_info;

        } catch (\Exception $e) {
            fn_set_notification('E', __('error'), __('addons.rus_payments.yandex_money_mws_operation_error'));
            return $status_to = $status_from;
        }
    } elseif ($processor_data['processor_script'] === 'robokassa.php'
        && $status_to === $processor_data['processor_params']['statuses']['final']
        && isset($order_info['payment_info']['rus_payments.robokassa.prepayment_receipt_created'])
        && !isset($order_info['payment_info']['rus_payments.robokassa.payment_receipt_created'])
    ) {
        $url = 'https://ws.roboxchange.com/RoboFiscal/Receipt/Attach';

        $receipt = fn_rus_payments_robokassa_get_full_payment_receipt($processor_data, $order_info);
        if ($receipt) {
            $data = fn_rus_payments_robokassa_encode_receipt($receipt, $processor_data);

            $request = Http::post($url, $data);
            $request_answer = json_decode($request, true);
            if (isset($request_answer['ResultCode']) && $request_answer['ResultCode'] == 0) {
                fn_update_order_payment_info($order_info['order_id'], ['rus_payments.robokassa.payment_receipt_created' => __('yes')]);
            }
        }
    }
}

/**
 * The "get_payment_processors_post" hook handler.
 *
 * Actions performed:
 *     - Adds specific 'russian' attribute to some payment processors for categorization.
 *
 * @see \fn_get_payment_processors()
 */
function fn_rus_payments_get_payment_processors_post($lang_code, &$processors)
{
    foreach ($processors as &$processor) {
        if ($processor['addon'] === 'rus_payments') {
            $processor['russian'] = true;
        }
    }
    unset($processor);

}

/* \HOOKS */

function fn_rus_payments_install()
{
    $processors = fn_get_schema('rus_payments', 'processors', 'php', true);

    if (fn_allowed_for('ULTIMATE')) {
        $company_id = fn_get_default_company_id();
    } else {
        $company_id = 0;
    }

    if (!empty($processors)) {
        foreach ($processors as $processor_name => $processor_data) {
            $processor_id = db_get_field(
                'SELECT processor_id FROM ?:payment_processors WHERE admin_template = ?s',
                $processor_data['admin_template']
            );

            if (empty($processor_id)) {
                $processor_id = db_query('INSERT INTO ?:payment_processors ?e', $processor_data);
            } else {
                db_query('UPDATE ?:payment_processors SET ?u WHERE processor_id = ?i', $processor_data, $processor_id);
            }
            
            if ($processor_name === 'account') {
                $payment_data = array(
                    'company_id' => $company_id,
                    'position' => 30,
                    'processor_id' => $processor_id,
                    'tax_ids' => '',
                    'localization' => '',
                    'payment_category' => 'tab3',
                    'payment' => $processor_data['processor'],
                    'description' => '',
                    'surcharge_title' => ''
                );

                $payment_id = fn_update_payment($payment_data, 0);

                if (fn_allowed_for('ULTIMATE')) {
                    fn_ult_update_share_object($payment_id, 'payments', $company_id);
                }
            }
        }
    }

    $statuses = fn_get_schema('rus_payments', 'statuses', 'php', true);

    if (!empty($statuses)) {
        foreach ($statuses as $status_name => $status_data) {
            $status = fn_update_status('', $status_data, $status_data['type']);
            fn_set_storage_data($status_name, $status);
        }
    }
}

function fn_rus_payments_uninstall()
{
    $processors = fn_get_schema('rus_payments', 'processors');

    foreach ($processors as $processor_name => $processor_data) {
        if ($processor_name === 'account') {
            $payment_ids = db_get_fields(
                'SELECT a.payment_id FROM ?:payments AS a'
                .' LEFT JOIN ?:payment_processors AS b ON a.processor_id = b.processor_id'
                .' WHERE b.admin_template = ?s',
                $processor_data['admin_template']
            );
            foreach ($payment_ids as $payment_id) {
                fn_delete_payment($payment_id);
            }
        }
        db_query('DELETE FROM ?:payment_processors WHERE admin_template = ?s', $processor_data['admin_template']);
    }

    fn_rus_payments_disable_payments($processors, true);

    $statuses = fn_get_schema('rus_payments', 'statuses', 'php', true);
    if (!empty($statuses)) {
        foreach ($statuses as $status_name => $status_data) {
            fn_delete_status(fn_get_storage_data($status_name), 'O');
        }
    }
}

function fn_rus_payments_disable_payments($payments, $drop_processor_id = false)
{
    $fields = '';
    if ($drop_processor_id) {
        $fields = 'processor_id = 0,';
    }

    foreach ($payments as $payment) {
        $processor_id = db_get_field("SELECT processor_id FROM ?:payment_processors WHERE admin_template = ?s", $payment['admin_template']);

        if (!empty($processor_id)) {
            db_query("UPDATE ?:payments SET $fields status = 'D' WHERE processor_id = ?i", $processor_id);
        }
    }
}

function fn_rus_pay_format_price($price, $payment_currency)
{
    $currencies = Registry::get('currencies');

    if (array_key_exists($payment_currency, $currencies)) {
        if ($currencies[$payment_currency]['is_primary'] != 'Y') {
            $price = fn_format_price($price / $currencies[$payment_currency]['coefficient']);
        }
    } else {
        return false;
    }

    return $price;
}

function fn_rus_pay_format_price_down($price, $payment_currency)
{
    $currencies = Registry::get('currencies');

    if (array_key_exists($payment_currency, $currencies)) {
          $price = fn_format_price($price * $currencies[$payment_currency]['coefficient']);
    } else {
        return false;
    }

    return $price;
}

function fn_rus_payments_normalize_phone($phone)
{
    $phone_normalize = '';

    if (!empty($phone)) {
        if (strpos('+', $phone) === false && $phone[0] == '8') {
            $phone[0] = '7';
        }

        $phone_normalize = str_replace(array(' ', '(', ')', '-'), '', $phone);
    }

    return $phone_normalize;
}

function fn_qr_generate($order_info, $delimenter = '|', $dir = "")
{
    $processor_params = $order_info['payment_method']['processor_params'];

    $format_block = 'ST' . '0001' . '2' . $delimenter;

    $required_block = array(
        'Name' => $processor_params['sbrf_recepient_name'],
        'PersonalAcc' => $processor_params['sbrf_settlement_account'],
        'BankName' => $processor_params['sbrf_bank'],
        'BIC' => $processor_params['sbrf_bik'],
        'CorrespAcc' => $processor_params['sbrf_cor_account'],
    );

    $required_block = fn_qr_array2string($required_block, $delimenter);

    if (Registry::get('currencies.RUB')) {
        $order_info['total'] = fn_format_price_by_currency($order_info['total'], CART_PRIMARY_CURRENCY, 'RUB');
    }

    $additional_block = array(
        'PayeeINN' => $processor_params['sbrf_inn'],
        'KPP' => $processor_params['sbrf_kpp'],
        'LastName' => $order_info['b_lastname'],
        'FirstName' => $order_info['b_firstname'],
        'Contract' => $order_info['order_id'],
        'Purpose' => __('sbrf_order_payment') . ' №' . $order_info['order_id'],
        'PayerAddress' => $order_info['b_city'],
        'Sum' => $order_info['total'] * 100,
        'Phone' => $order_info['b_phone'],
    );

    $additional_block = fn_qr_array2string($additional_block, $delimenter);

    $string = $format_block . $required_block . $additional_block;

    $string = substr($string, 0, -1);

    $resolution = $processor_params['sbrf_qr_resolution'];

    $data = array(
        'cht' => 'qr',
        'choe' => 'UTF-8',
        'chl' => $string,
        'chs' => $resolution . 'x' . $resolution,
        'chld' => 'M|4'
    );

    $url = 'https://chart.googleapis.com/chart';

    $response = Http::get($url, $data);

    if (!strpos($response, 'Error')) {

        fn_put_contents($dir . 'qr_code_' . $order_info['order_id'] . '.png', $response);
        $path = $dir . 'qr_code_' . $order_info['order_id'] . '.png';

    } else {
        $path = fn_get_contents(DIR_ROOT. '/images/no_image.png');
    }

    return $path;
}

function fn_qr_array2string($array, $del = '|', $eq = '=')
{
    if (is_array($array)) {

        $string = '';

        foreach ($array as $key => $value) {
            if (!empty($value)) {
                $string .= $key . $eq . $value . $del ;
            }
        }
    }

    return $string;
}

function fn_yandex_money_log_write($data, $file)
{
    $path = fn_get_files_dir_path();
    fn_mkdir($path);
    $file = fopen($path . $file, 'a');

    if (!empty($file)) {
        fputs($file, 'TIME: ' . date('Y-m-d H:i:s', time()) . "\n");
        fputs($file, fn_array2code_string($data) . "\n\n");
        fclose($file);
    }
}

function fn_rus_payments_get_order_info(&$order, $additional_data)
{
    if (!empty($order['payment_info']) && isset($order['payment_info']['yandex_payment_type'])) {

        if ($order['payment_info']['yandex_payment_type'] == 'pc') {
            $payment_type = 'yandex';

        } elseif ($order['payment_info']['yandex_payment_type'] == 'ac') {
            $payment_type = 'card';

        } elseif ($order['payment_info']['yandex_payment_type'] == 'gp') {
            $payment_type = 'terminal';

        } elseif ($order['payment_info']['yandex_payment_type'] == 'mc') {
            $payment_type = 'phone';

        } elseif ($order['payment_info']['yandex_payment_type'] == 'wm') {
            $payment_type = 'webmoney';

        } elseif ($order['payment_info']['yandex_payment_type'] == 'ab') {
            $payment_type = 'alfabank';

        } elseif ($order['payment_info']['yandex_payment_type'] == 'sb') {
            $payment_type = 'sberbank';

        } elseif ($order['payment_info']['yandex_payment_type'] == 'ma') {
            $payment_type = 'masterpass';

        } elseif ($order['payment_info']['yandex_payment_type'] == 'pb') {
            $payment_type = 'psbank';
        }

        if (isset($payment_type)) {
            $order['payment_info']['yandex_payment_type'] = __('yandex_payment_' . $payment_type);
        }
    }
}

function fn_rus_payments_account_fields($fields_account, $user_data)
{
    $account_params = array();
    $profile_fields = db_get_hash_array(
        'SELECT field_id, field_name, field_type, is_default FROM ?:profile_fields',
        'field_id'
    );

    foreach ($fields_account as $name_account => $field_account) {
        if (!empty($profile_fields[$field_account]['field_name'])
            && $profile_fields[$field_account]['is_default'] === 'Y'
        ) {
            $account_params[$name_account] = !empty($user_data[$profile_fields[$field_account]['field_name']])
                ? $user_data[$profile_fields[$field_account]['field_name']]
                : '';

        } elseif (!empty($user_data['fields'][$field_account])) {
            $account_params[$name_account] = !empty($user_data['fields'][$field_account])
                ? $user_data['fields'][$field_account]
                : '';

        } else {
            $account_params[$name_account] = '';
        }
    }

    return $account_params;
}

/**
 * Checks whether receipt should be sent for Yandex.Checkpoint.
 *
 * @param array $processor_data Payment processor information
 *
 * @return bool
 */
function fn_is_yandex_checkpoint_receipt_required($processor_data)
{
    return !empty($processor_data['processor_params']['send_receipt'])
        && $processor_data['processor_params']['send_receipt'] == 'Y';
}

/**
 * Provides price for Yandex.Checkpoint receipt.
 *
 * @param float  $price    Price of an item (product, shipping, surcharge)
 * @param string $currency Currency code
 *
 * @return array `price` field value for Yandex.Checkpoint receipt
 */
function fn_get_yandex_checkpoint_price($price = 0.00, $currency = 'RUB')
{
    return array(
        'amount'   => (float) fn_format_rate_value((float) $price, 'F', 2, '.', '', ''),
        'currency' => $currency
    );
}

/**
 * Provides item description for Yandex.Checkpoint receipt.
 *
 * @param string $text Item (product, shipping, surcharge) description
 *
 * @return string `text` field value for Yandex.Checkpoint receipt
 */
function fn_get_yandex_checkpoint_description($text = '')
{
    return fn_truncate_chars($text, 128, '');
}

/**
 * Converts receipt instance to Yandex.Checkpoint format.
 *
 * @param Receipt $receipt  Receipt instance
 * @param string  $currency Currency code
 *
 * @return array
 */
function fn_yandex_checkpoint_convert_receipt(Receipt $receipt, $currency)
{
    $items = array();

    foreach ($receipt->getItems() as $item) {
        $items[] = [
            'quantity'           => (int) $item->getQuantity(),
            'price'              => fn_get_yandex_checkpoint_price($item->getPrice(), $currency),
            'tax'                => YandexCheckpointVatTypes::getTaxTypeByBaseType($item->getTaxType()),
            'text'               => fn_get_yandex_checkpoint_description($item->getName()),
            // FIXME: must be customizable
            'paymentMethodType'  => 'full_payment',
            'paymentSubjectType' => 'payment',
        ];
    }

    return array(
        'customerContact' => $receipt->getEmail(),
        'items'           => $items,
    );
}

/**
 * Provides receipt for Yandex.Checkpoint.
 *
 * @param array  $order    Info of the order to build receipt for
 * @param string $currency Currency code
 *
 * @return array|null Receipt data or null when not needed
 */
function fn_yandex_checkpoint_get_receipt($order, $currency)
{
    /** @var \Tygh\Addons\RusTaxes\ReceiptFactory $receipt_factory */
    $receipt_factory = Tygh::$app['addons.rus_taxes.receipt_factory'];

    $receipt = $receipt_factory->createReceiptFromOrder($order, $currency);

    if ($receipt) {
        return fn_yandex_checkpoint_convert_receipt($receipt, $currency);
    }

    return null;
}

/**
 * Determines whether a Yandex.Checkpoint refund is partial.
 *
 * @param array $refund_data Refund info (returned products, refunded shipping etc.)
 * @param array $order_info  Order info
 *
 * @return bool
 */
function fn_yandex_checkpoint_is_partial_refund($refund_data, $order_info)
{
    $is_partial_refund = isset($refund_data['refund_shipping']) && $refund_data['refund_shipping'] == 'N'
        || isset($refund_data['refund_surcharge']) && $refund_data['refund_surcharge'] == 'N';

    if (!$is_partial_refund) {
        foreach ($refund_data['products'] as $cart_id => $product) {
            if ($product['is_returned'] == 'N' || $product['amount'] != $order_info['products'][$cart_id]['amount']) {
                $is_partial_refund = true;
                break;
            }
        }
    }

    if (Registry::get('addons.gift_certificates.status') == 'A'
        && !$is_partial_refund && !empty($refund_data['certificates'])
    ) {
        foreach ($refund_data['certificates'] as $cart_id => $certificate) {
            if ($certificate['is_returned'] == 'N') {
                $is_partial_refund = true;
                break;
            }
        }
    }

    /**
     * Executes after determining whether a Yandex.Checkpoint refund is partial, allows to modify check results.
     *
     * @param array $refund_data       Refund info (returned products, refunded shipping etc.)
     * @param array $order_info        Order info
     * @param bool  $is_partial_refund Whether refund is partial
     */
    fn_set_hook('yandex_checkpoint_is_partial_refund_post', $refund_data, $order_info, $is_partial_refund);

    return $is_partial_refund;
}

/**
 * Builds content of an order that is partially refunded via Yandex.Checkpoint.
 *
 * @param array  $refund_data   Refund info (returned products, refunded shipping etc.)
 * @param array  $order_info    Order info
 * @param string $currency      Currency code
 *
 * @return array|null Receipt data or null when not needed
 */
function fn_yandex_checkpoint_get_refund_receipt($refund_data, $order_info, $currency)
{
    /** @var \Tygh\Addons\RusTaxes\ReceiptFactory $receipt_factory */
    $receipt_factory = Tygh::$app['addons.rus_taxes.receipt_factory'];

    $receipt = $receipt_factory->createReceiptFromOrder($order_info, $currency, false);

    if (!$receipt) {
        return null;
    }

    foreach ($refund_data['products'] as $cart_id => $product) {
        if ($product['is_returned'] == 'N') {
            $receipt->removeItem($cart_id, ReceiptItem::TYPE_PRODUCT);
        } else {
            $receipt->setItemQuantity($cart_id, ReceiptItem::TYPE_PRODUCT, $product['amount']);
        }
    }

    if (isset($refund_data['refund_shipping']) && $refund_data['refund_shipping'] == 'N') {
        $receipt->removeItem(0, ReceiptItem::TYPE_SHIPPING);
    }

    if (isset($refund_data['refund_surcharge']) && $refund_data['refund_surcharge'] == 'N') {
        $receipt->removeItem(0, ReceiptItem::TYPE_SURCHARGE);
    }

    if (Registry::get('addons.gift_certificates.status') == 'A'
        && !empty($order_info['gift_certificates'])
        && isset($refund_data['certificates'])
    ) {
        foreach ($refund_data['certificates'] as $cart_id => $certificate) {
            if ($certificate['is_returned'] == 'N') {
                $receipt->removeItem($cart_id, ReceiptItem::TYPE_GIFT_CERTIFICATE);
            }
        }
    }

    /**
     * Executes after building receipt to refund via Yandex.Checkpoint, allows to modify receipt.
     *
     * @param array     $refund_data        Refund info (returned products, refunded shipping etc.)
     * @param array     $order_info         Order info
     * @param string    $currency           Currency code
     * @param Receipt   $receipt            Receipt instance
     */
    fn_set_hook('yandex_checkpoint_build_refunded_order_post', $refund_data, $order_info, $currency, $receipt);

    $receipt->allocateDiscountByUnit();

    return fn_yandex_checkpoint_convert_receipt($receipt, MWSClient::YANDEX_CHECKPOINT_RUB);
}

/**
 * Gets order info for refund.
 *
 * @param array $order_info   Order info
 *
 * @return array
 */
function fn_yandex_checkpoint_get_refunded_order($order_info)
{
    /** @var \Tygh\Addons\RusTaxes\ReceiptFactory $receipt_factory */
    $receipt_factory = Tygh::$app['addons.rus_taxes.receipt_factory'];

    $receipt = $receipt_factory->createReceiptFromOrder($order_info, CART_PRIMARY_CURRENCY);

    if (!$receipt) {
        return $order_info;
    }

    foreach ($order_info['products'] as $cart_id => &$product) {
        $receipt_item = $receipt->getItem($cart_id, ReceiptItem::TYPE_PRODUCT);

        if ($receipt_item) {
            $product['price'] = $receipt_item->getPrice();
        }
    }
    unset($product);

    if (Registry::get('addons.gift_certificates.status') == 'A' && !empty($order_info['gift_certificates'])) {
        foreach ($order_info['certificates'] as $cart_id => &$certificate) {
            $receipt_item = $receipt->getItem($cart_id, ReceiptItem::TYPE_GIFT_CERTIFICATE);

            if ($receipt_item) {
                $certificate['amount'] = $receipt_item->getPrice();
            }
        }
    }
    unset($certificate);

    $shipping_receipt_item = $receipt->getItem(0, ReceiptItem::TYPE_SHIPPING);
    $surcharge_receipt_item = $receipt->getItem(0, ReceiptItem::TYPE_SURCHARGE);

    if ($shipping_receipt_item) {
        $order_info['shipping_cost'] = $shipping_receipt_item->getPrice();
    }

    if ($surcharge_receipt_item) {
        $order_info['payment_surcharge'] = $surcharge_receipt_item->getPrice();
    }

    /**
     * Executes after prepared order data to refund via Yandex.Checkpoint, allows to modify order content.
     *
     * @param array     $order_info         Order info
     * @param Receipt   $receipt            Receipt instance
     */
    fn_set_hook('yandex_checkpoint_get_refunded_order', $order_info, $receipt);

    return $order_info;
}

/**
 * Sends product tax data to PayAnyWay
 *
 * @param array $params     The data received from PayAnyWay
 * @param array $order_info Order information
 */
function fn_rus_payments_payanyway_send_order_info($params, $order_info)
{
    $result_code = 200;
    $dataintegrity_code = $order_info['payment_method']['processor_params']['mnt_dataintegrity_code'];
    $signature = $signature = md5($result_code . $params['MNT_ID'] . $params['MNT_TRANSACTION_ID'] . $dataintegrity_code);
    $inventory = json_encode(fn_rus_payments_payanyway_get_inventory_positions($order_info));

    header("Content-type: application/xml");

    $data = <<<EOT
<?xml version="1.0" encoding="UTF-8" ?>
<MNT_RESPONSE>
EOT;

    $data .= fn_array_to_xml([
        'MNT_ID'             => $params['MNT_ID'],
        'MNT_TRANSACTION_ID' => $params['MNT_TRANSACTION_ID'],
        'MNT_RESULT_CODE'    => 200,
        'MNT_SIGNATURE'      => $signature,
    ]);

    $data .= <<<EOT
<MNT_ATTRIBUTES>
<ATTRIBUTE>
    <KEY>INVENTORY</KEY>
    <VALUE>{$inventory}</VALUE>
</ATTRIBUTE>
<ATTRIBUTE>
EOT;
    $data .= fn_array_to_xml([
        'KEY'   => 'CUSTOMER',
        'VALUE' => $order_info['email'],
    ]);
    $data .= <<<EOT
</ATTRIBUTE>
</MNT_ATTRIBUTES>
</MNT_RESPONSE>
EOT;

    echo $data;
    exit;
}

/**
 * Formats a string with the name for tax data by deleting error-prone symbols
 *
 * @deprecated since 4.10.1
 *
 * @param string Receipt item name
 *
 * @return string Truncates item name
 */
function fn_rus_payments_payanyway_format_item_name($name)
{
    return fn_rus_payments_truncate_receipt_item_name($name);
}

/**
 * Formats a string with the name for tax data by deleting error-prone symbols
 *
 * @param string $name   Receipt item name
 * @param int    $length Length name
 * @param string $suffix String to append to the end of truncated string
 *
 * @return string Truncates item name
 */
function fn_rus_payments_truncate_receipt_item_name($name, $length = 64, $suffix = '...')
{
    $name = preg_replace('/[^0-9a-zA-Zа-яА-Я-,. ]/ui', '', $name);

    if (function_exists('mb_strlen') && mb_strlen($name, 'UTF-8') > $length) {
        $length -= mb_strlen($suffix);
        return rtrim(mb_substr($name, 0, $length, 'UTF-8')) . $suffix;
    }

    return $name;
}

/**
 * Gets full prepayment receipt by order_info for Robokassa service
 *
 * @param array $order_info Order information
 *
 * @return array|false Returns an array with receipt data or false in case of an error
 */
function fn_rus_payments_robokassa_get_receipt($order_info)
{
    /** @var \Tygh\Addons\RusTaxes\ReceiptFactory $receipt_factory */
    $receipt_factory = Tygh::$app['addons.rus_taxes.receipt_factory'];
    $receipt = $receipt_factory->createReceiptFromOrder($order_info, CART_PRIMARY_CURRENCY);
    $receipt_result = [];

    if ($receipt) {
        foreach ($receipt->getItems() as $item) {
            $receipt_result['items'][] = [
                'name'     => fn_rus_payments_truncate_receipt_item_name($item->getName()),
                'quantity' => $item->getQuantity(),
                'sum'      => $item->getTotal(),
                'payment_method' => 'full_prepayment',
                'payment_object' => 'commodity',
                'tax'      => $item->getTaxType(),
            ];
        }
        fn_update_order_payment_info($order_info['order_id'], ['rus_payments.robokassa.prepayment_receipt_created' => __('yes')]);
        return $receipt_result;
    }

    return false;
}

/**
 * Gets full payment receipt for Robokassa service
 *
 * @param array $processor_data Information about selected payment processor
 * @param array $order_info     Order information
 * @param array $params         Additional parameters
 *
 * @return array Returns full payment receipt according to https://docs.robokassa.ru/#7772
 */
function fn_rus_payments_robokassa_get_full_payment_receipt(array $processor_data, array $order_info, array $params = [])
{
    /** @var \Tygh\Addons\RusTaxes\ReceiptFactory $receipt_factory */
    $receipt_factory = Tygh::$app['addons.rus_taxes.receipt_factory'];
    $receipt = $receipt_factory->createReceiptFromOrder($order_info, CART_PRIMARY_CURRENCY);
    if ($receipt) {
        $receipt_result = [];
        foreach ($receipt->getItems() as $item) {
            $receipt_result['items'][] = [
                'name'           => fn_rus_payments_truncate_receipt_item_name($item->getName()),
                'quantity'       => $item->getQuantity(),
                'sum'            => $item->getTotal(),
                'payment_method' => 'full_payment',
                'payment_object' => 'commodity',
                'tax'            => $item->getTaxType(),
            ];
            if (isset($receipt_result['vats'])) {
                foreach ($receipt_result['vats'] as &$vat) {
                    if ($vat['type'] === $item->getTaxType()) {
                        $vat['sum'] += $item->getTaxSum();
                        continue 2;
                    }
                }
                unset($vat);
                $receipt_result['vats'][] = [
                    'type' => $item->getTaxType(),
                    'sum'  => $item->getTaxSum(),
                ];
            } else {
                $receipt_result['vats'][] = [
                    'type' => $item->getTaxType(),
                    'sum'  => $item->getTaxSum(),
                ];
            }
        }

        $result = array_merge($receipt_result, [
            'merchantId' => $processor_data['processor_params']['merchantid'],
            'id'         => isset($params['id'])
                ? $params['id']
                : time(),
            'originId'   => $order_info['order_id'],
            'operation'  => 'sell',
            'URL'        => fn_url('', 'C'),
            'total'      => number_format($order_info['total'], 2, '.', ''),
            'client'     => [
                'email' => $receipt->getEmail(),
                'phone' => $receipt->getPhone(),
            ],
            'payments'   => [
                [
                    /**
                     * @var int
                     * @see https://docs.robokassa.ru/#7772
                     */
                    'type' => 2,
                    'sum'  => number_format($order_info['total'], 2, '.', ''),
                ],
            ],
        ]);
    } else {
        $result = [];
    }

    return $result;
}

/**
 * Encodes full payment receipt according to Robokassa API docs https://docs.robokassa.ru/#7782
 *
 * @param array $receipt        Receipt structure
 * @param array $processor_data Array of payment processor params
 *
 * @return string Encoded form of full payment receipt
 */
function fn_rus_payments_robokassa_encode_receipt(array $receipt, array $processor_data)
{
    $json = json_encode($receipt, JSON_UNESCAPED_SLASHES);
    $data = strtr(base64_encode($json), [
        '+' => '-',
        '/' => '_',
    ]);
    $signature = rtrim($data, '=');
    $post_signature = $signature . $processor_data['processor_params']['password1'];
    $encoding_signature = hash($processor_data['processor_params']['encoding'], $post_signature);
    return rtrim($signature . '.' . base64_encode($encoding_signature), '=');
}

/**
 * Gets product tax data for PayAnyWay
 *
 * @param array $order_info Order information
 *
 * @return array An array of products with taxes
 */
function fn_rus_payments_payanyway_get_inventory_positions($order_info)
{
    $map_taxes = fn_get_schema('rus_payments', 'payanyway_map_taxes');
    $inventory_positions = [];

    /** @var \Tygh\Addons\RusTaxes\ReceiptFactory $receipt_factory */
    $receipt_factory = Tygh::$app['addons.rus_taxes.receipt_factory'];
    $receipt = $receipt_factory->createReceiptFromOrder($order_info, CART_PRIMARY_CURRENCY);

    if ($receipt) {
        foreach ($receipt->getItems() as $item) {
            $inventory_positions[] = [
                'name' => fn_rus_payments_truncate_receipt_item_name($item->getName()),
                'price' => $item->getPrice(),
                'quantity' => $item->getQuantity(),
                'type' => $item->getType(),
                'vatTag' => isset($map_taxes[$item->getTaxType()])
                ? $map_taxes[$item->getTaxType()]
                : $map_taxes[TaxType::NONE]
            ];
        }
    }

    return $inventory_positions;
}

/**
 * Returns the payment URL specified in the payment method settings for the current mode (test/live).
 * If the payment URL for the current mode is not specified or it is incorrect, returns URL for the
 * test work with the ASSIST system
 *
 * @param  array  $processor_data  data on the payment processor
 * @return string payment url
 */
function fn_assist_get_payment_url($processor_data)
{
    return filter_var(
        trim(
            $processor_data['processor_params']['mode'] === 'L'
                ? $processor_data['processor_params']['payment_url_live']
                : $processor_data['processor_params']['payment_url_test'],
            " \t\n\r\0\x0B/"
        ),
        FILTER_VALIDATE_URL,
        array('options' => array('default' => 'https://payments.demo.paysecure.ru'))
    );
}

/**
 * Gets URL to submit Yandex.Checkpoint payment request to.
 *
 * @param string $mode Payment mode (test, live)
 *
 * @return string URL
 */
function fn_rus_payments_yandex_checkpoint_get_payment_url($mode)
{
    if ($mode === 'test') {
        return 'https://demomoney.yandex.ru/eshop.xml';
    }

    return 'https://money.yandex.ru/eshop.xml';
}

/**
 * Gets Yandex.Checkpoint payment request parameters.
 *
 * @param array $order_info     Order info
 * @param array $processor_data Payment method info (obtained from the database)
 * @param array $payment_info   Payment info (submited on checkout page)
 *
 * @return array Request parameters
 */
function fn_rus_payments_yandex_checkpoint_get_payment_request(
    array $order_info,
    array $processor_data,
    array $payment_info
) {
    $phone = '';
    if (!empty($order_info['phone'])) {
        $phone = $order_info['phone'];
    } elseif (!empty($order_info['b_phone'])) {
        $phone = $order_info['b_phone'];
    } elseif (!empty($order_info['s_phone'])) {
        $phone = $order_info['s_phone'];
    }

    $phone = str_replace('+', '', $phone);

    $orderNumber = $order_info['order_id'] . '_' . substr(md5($order_info['order_id'] . TIME), 0, 3);

    $session_id = Tygh::$app['session']->getName() . '=' . Tygh::$app['session']->getID();

    $payment_request = array(
        'shopId'          => $processor_data['processor_params']['shop_id'],
        'Sum'             => fn_format_price_by_currency(
            $order_info['total'],
            CART_PRIMARY_CURRENCY,
            $processor_data['processor_params']['currency']
        ),
        'scid'            => $processor_data['processor_params']['scid'],
        'customerNumber'  => $order_info['email'],
        'orderNumber'     => $orderNumber,
        'shopSuccessURL'  => fn_url(
            'payment_notification.ok?payment=yandex_money&ordernumber=' . $orderNumber,
            AREA,
            'https'
        ),
        'shopFailURL'     => fn_url(
            'payment_notification.error?payment=yandex_money&ordernumber=' . $orderNumber,
            AREA,
            'https'
        ),
        'shopDefaultUrl'  => fn_url(
            'payment_notification.return?payment=yandex_money&ordernumber=' . $orderNumber,
            AREA,
            'https'
        ),
        'cps_email'       => $order_info['email'],
        'cps_phone'       => $phone,
        'paymentAvisoURL' => fn_url(
            'payment_notification.payment_aviso?payment=yandex_money&' . $session_id,
            AREA,
            'https'
        ),
        'checkURL'        => fn_url(
            'payment_notification.check_order?payment=yandex_money',
            AREA,
            'https'
        ),
        'paymentType'     => empty($payment_info['yandex_payment_type'])
            ? ''
            : strtoupper($payment_info['yandex_payment_type']),
        'cms_name'        => 'cscart',
    );

    if (fn_is_yandex_checkpoint_receipt_required($processor_data)) {
        $receipt = fn_yandex_checkpoint_get_receipt($order_info, $processor_data['processor_params']['currency']);
        if ($receipt) {
            $payment_request['ym_merchant_receipt'] = json_encode($receipt);
        }
    }

    return $payment_request;
}

/**
 * Stores some payment data for further Yandex.Checkpoint payment request validation.
 *
 * @param int   $order_id        Order identifier
 * @param array $payment_request Payment request
 *
 * @return bool
 */
function fn_rus_payments_yandex_checkpoint_set_payment_validation_data($order_id, array $payment_request)
{
    $pp_response = array(
        'yandex_total' => $payment_request['Sum']
    );

    return fn_update_order_payment_info($order_id, $pp_response);
}
