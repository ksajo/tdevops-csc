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

use Tygh\Enum\OrderDataTypes;
use Tygh\Http;

defined('BOOTSTRAP') or die('Access denied');

if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode === 'rbx_get_currencies') {
        if (!empty($_REQUEST['merchantid']) && !empty($_REQUEST['payment_id'])) {

            $payment_method_data = fn_get_payment_method_data($_REQUEST['payment_id']);
            if (!empty($payment_method_data)) {
                $url = 'https://auth.robokassa.ru/Merchant/WebService/Service.asmx/GetCurrencies?MerchantLogin=' . $_REQUEST['merchantid'] . '&Language=' . CART_LANGUAGE;
                $data_currencies = Http::get($url);

                $xml = @simplexml_load_string($data_currencies);
                $result = [];

                if (isset($xml->Groups->Group)) {
                    foreach ($xml->Groups->Group as $group) {
                        $key = (string) $group->attributes()->Description;
                        foreach ($group->Items->Currency as $currency) {
                            $sub_key = (string) $currency->attributes()->Label;
                            $cur_name = (string) $currency->attributes()->Name;
                            $result[$key][$sub_key] = $cur_name;
                        }
                    }
                }

                /** @var \Tygh\SmartyEngine\Core $view */
                $view = Tygh::$app['view'];
                $view->assign([
                    'rbx_currencies'   => $result,
                    'processor_params' => $payment_method_data['processor_params'],
                ]);
                $view->display('addons/rus_payments/views/payments/components/cc_processors/robokassa_cur_selectbox.tpl');
            }
        }

        return [CONTROLLER_STATUS_NO_CONTENT];
    }

    if (empty($_REQUEST['InvId']) || empty($_REQUEST['OutSum']) || (empty($_REQUEST['SignatureValue']) && $mode != 'cancel')) {
        die('Access denied');
    }

    $order_id = (int) $_REQUEST['InvId'];

    if ($mode === 'result') {
        $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
        $processor_data = fn_get_processor_data($payment_id);

        $crc = strtoupper(md5($_REQUEST['OutSum'] . ':' . $_REQUEST['InvId'] . ':' . $processor_data['processor_params']['password2']));
        if (strtoupper($_REQUEST['SignatureValue']) == $crc) {
            $pp_response['order_status'] = $processor_data['processor_params']['statuses']['paid'];
            $pp_response['reason_text'] = __('approved');
        } else {
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text'] = __('control_summ_wrong');
        }
        fn_finish_payment($order_id, $pp_response);

        die('OK' . $order_id);
    }

    if ($mode === 'return') {
        $times = 0;
        while ($times <= RK_MAX_AWAITING_TIME) {
            $is_payment_started = (bool) db_get_field('SELECT 1 FROM ?:order_data WHERE order_id = ?i AND type = ?s', $order_id, OrderDataTypes::PAYMENT_STARTED);
            if (!$is_payment_started) {
                break;
            }

            sleep(1);
            $times++;
        }

        $order_status = db_get_field('SELECT status FROM ?:orders WHERE order_id = ?i', $order_id);
        if ($order_status === STATUS_INCOMPLETED_ORDER) {
            $pp_response = [
                'order_status'   => 'F',
                'reason_text'    => __('merchant_response_was_not_received'),
                'transaction_id' => '',
            ];
            fn_finish_payment($order_id, $pp_response);
        }

        fn_order_placement_routines('route', $order_id, false);
    }

    if ($mode === 'cancel') {
        $pp_response = [
            'order_status' => STATUS_INCOMPLETED_ORDER,
            'reason_text'  => __('text_transaction_cancelled'),
        ];
        fn_finish_payment($order_id, $pp_response, false);

        fn_order_placement_routines('route', $order_id);
    }

} else {
    /** @var int $order_id **/
    /** @var array $order_info **/
    /** @var array $processor_data **/

    $total = fn_format_price_by_currency($order_info['total'], CART_PRIMARY_CURRENCY, $processor_data['processor_params']['currency']);
    $total = number_format($total, 2, '.', '');

    if ($processor_data['processor_params']['commission'] == 'admin') {
        $url = 'https://auth.robokassa.ru/Merchant/WebService/Service.asmx/CalcOutSumm';
        $data = [
            'MerchantLogin' => $processor_data['processor_params']['merchantid'],
            'IncCurrLabel' => $processor_data['processor_params']['payment_method'],
            'IncSum' => $total
        ];
        $total_xml = Http::post($url, $data);

        $xml = @simplexml_load_string($total_xml);
        if (isset($xml->Result->Code) && $xml->Result->Code == 0) {
            $total = $xml->OutSum;
        }
    }

    $receipt = json_encode(fn_rus_payments_robokassa_get_receipt($order_info));

    $crc = md5(
        $processor_data['processor_params']['merchantid']
        . ':' . $total
        . ':' . $order_id
        . ':' . $receipt
        . ':' . $processor_data['processor_params']['password1']
    );

    $url = 'https://auth.robokassa.ru/Merchant/Index.aspx';

    $post_data = [
        'MrchLogin'      => $processor_data['processor_params']['merchantid'],
        'OutSum'         => $total,
        'InvId'          => $order_id,
        'Receipt'        => $receipt,
        'Desc'           => $processor_data['processor_params']['details'],
        'SignatureValue' => $crc,
        'Culture'        => CART_LANGUAGE,
        'IncCurrLabel'   => $processor_data['processor_params']['payment_method']
    ];

    if ($processor_data['processor_params']['mode'] != 'live') {
        $post_data['isTest'] = 1;
    }

    fn_create_payment_form($url, $post_data, 'Robokassa server');
}
