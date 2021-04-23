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
use Tygh\Pdf;
use RusPostBlank\RusPostBlank;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'print') {
        if (!empty($_REQUEST['order_id'])) {
            $order_id = $_REQUEST['order_id'];
            $order_info = fn_get_order_info($order_id, false, true, false, true);
            if (empty($order_info)) {
                exit;
            }

            $view = Tygh::$app['view'];
            fn_save_post_data('blank_data');
            $lang_code = 'ru';
            $params = $_REQUEST['blank_data'];

            $total_declared = '';
            if (!empty($params['total_cen'])) {
                $total_declared = $params['total_cen'];
            }
            $params['total_declared'] = $total_declared;
            list($total_declared, $params['declared_rub'], $params['declared_kop']) = fn_rus_postblank_rub_kop_price($total_declared);

            $total_imposed = '';
            if (!empty($params['total_cod'])) { 
                $total_imposed = $params['total_cod'];
            }
            $params['total_imposed'] = $total_imposed;
            list($total_imposed, $params['imposed_rub'], $params['imposed_kop']) = fn_rus_postblank_rub_kop_price($total_imposed);

            if (!empty($params['imposed_total']) && $params['imposed_total'] == 'Y') {
                if ($total_declared >= $total_imposed) {
                    $params['not_total'] = 'Y';

                    if (!empty($total_imposed)) {
                        $rp['total_cod'] = RusPostBlank::doit($total_imposed, false, false);
                        $params['total_imposed'] = $params['imposed_rub'] . ' (' . $rp['total_cod'] . ') руб. ' . $params['imposed_kop'] . ' коп.';
                        $params['t_imposed'] = RusPostBlank::clearDoit($total_imposed);
                    }
                } else {
                    fn_set_notification('E', __('error'), __('addons.rus_russianpost.error_total'));

                    return array(CONTROLLER_STATUS_OK, 'rus_post_blank.edit&order_id=' . $_REQUEST['order_id']);
                }
            }

            if (!empty($params['not_total']) && $params['not_total'] == 'Y') {
                $params['t_declared_kop'] = $total_declared;

                if (!empty($total_declared)) {
                    $rp['total_cen'] = RusPostBlank::doit($total_declared, false, false);
                    $params['t_declared_kop'] = $params['declared_rub'] . ' (' . $rp['total_cen'] . ') руб. ' . $params['declared_kop'] . ' коп.';
                    $params['total_declared'] = $params['declared_rub'] . ' (' . $rp['total_cen'] . ') руб. ' . $params['declared_kop'] . ' коп.';
                }
            }

            $params['text1'] = preg_split('//u', $params['text1'], -1, PREG_SPLIT_NO_EMPTY);
            $params['text2'] = preg_split('//u', $params['text2'], -1, PREG_SPLIT_NO_EMPTY);

            $view->assign('data', $params);
            $view->assign('order_info', $order_info);

            if ($action == 'blank_7a') {
                if ($params['print_pdf'] == 'Y') {
                    $pdf_params = array(
                        'page_width' => '198mm',
                        'page_height' => '141mm',
                        'margin_left' => '0mm',
                        'margin_right' => '0mm',
                        'margin_top' => '0mm',
                        'margin_bottom' => '0mm',
                    );

                    $blanks = array(1);
                    $html[] = $view->displayMail('addons/rus_russianpost/blank_7a_pdf.tpl', false, AREA, $order_info['company_id'], $lang_code);
                } else {
                    $view->displayMail('addons/rus_russianpost/blank_7a.tpl', true, AREA, $order_info['company_id'], $lang_code);
                }
            }

            if ($action == 'blank_7p') {
                if ($params['print_pdf'] == 'Y') {
                    $pdf_params = array(
                        'page_width' => '198mm',
                        'page_height' => '141mm',
                        'margin_left' => '0mm',
                        'margin_right' => '0mm',
                        'margin_top' => '0mm',
                        'margin_bottom' => '0mm',
                    );

                    $blanks = array(1);
                    $html[] = $view->displayMail('addons/rus_russianpost/blank_7p_pdf.tpl', false, AREA, $order_info['company_id'], $lang_code);
                } else {
                    $view->displayMail('addons/rus_russianpost/blank_7p.tpl', true, AREA, $order_info['company_id'], $lang_code);
                }
            }

            if ($action == 'blank_112ep') {
                if ($params['print_pdf'] == 'Y') {
                    $pdf_params = array(
                        'page_width' => '210mm',
                        'page_height' => '293mm',
                        'margin_left' => '0mm',
                        'margin_right' => '0mm',
                        'margin_top' => '0mm',
                        'margin_bottom' => '0mm',
                    );

                    $blanks = array(1);
                    $html[] = $view->displayMail('addons/rus_russianpost/blank_112ep_pdf.tpl', false, AREA, $order_info['company_id'], $lang_code);
                } else {
                    $view->displayMail('addons/rus_russianpost/blank_112ep.tpl', true, AREA, $order_info['company_id'], $lang_code);
                }
            }

            if ($action == 'blank_116') {
                if ($params['print_pdf'] == 'Y') {
                    $pdf_params = array(
                        'page_width' => '297mm',
                        'page_height' => '210mm',
                        'margin_left' => '0mm',
                        'margin_right' => '0mm',
                        'margin_top' => '0mm',
                        'margin_bottom' => '0mm',
                    );

                    $blanks = array(1);
                    $html[] = $view->displayMail('addons/rus_russianpost/blank_116_pdf.tpl', false, AREA, $order_info['company_id'], $lang_code);
                } else {
                    $view->displayMail('addons/rus_russianpost/blank_116.tpl', true, AREA, $order_info['company_id'], $lang_code);
                }
            }

            if ($action == 'blank_107') {
                if ($params['print_pdf'] == 'Y') {
                    $pdf_params = array(
                        'page_width' => '293mm',
                        'page_height' => '210mm',
                        'margin_left' => '0mm',
                        'margin_right' => '0mm',
                        'margin_top' => '0mm',
                        'margin_bottom' => '0mm',
                    );

                    $blanks = array(1);
                    $html[] = $view->displayMail('addons/rus_russianpost/blank_107_pdf.tpl', false, AREA, $order_info['company_id'], $lang_code);
                } else {
                    $view->displayMail('addons/rus_russianpost/blank_107.tpl', true, AREA, $order_info['company_id'], $lang_code);
                }
            }

            if ($params['print_pdf'] == 'Y') {
                Pdf::render($html, __("addons.rus_russianpost.{$action}") . ' #' . $order_info['order_id'] . '-' . implode('-', $blanks), false, $pdf_params);
            }
        }

        exit;
    }
}

if ($mode == 'edit') {
    $tabs = array (
        'settings' => array (
            'title' => __('settings'),
            'js' => true
        ),
        'recipient' => array (
            'title' => __('recipient'),
            'js' => true
        ),
        'sender' => array (
            'title' => __('sender'),
            'js' => true
        ),
    );
    Registry::set('navigation.tabs', $tabs);

    $order_id = $_REQUEST['order_id'];
    $order_info = fn_get_order_info($order_id, false, true, false, true);

    if (CART_PRIMARY_CURRENCY != 'RUB') {
        $currencies = Registry::get('currencies');
        if (!empty($currencies['RUB'])) {
            $currency = $currencies['RUB'];
            if (!empty($currency)) {
                $order_info['total'] = fn_format_rate_value($order_info['total'], 'F', $currency['decimals'], $currency['decimals_separator'], '', $currency['coefficient']);
                $order_info['total'] = fn_format_price($order_info['total'], 'RUB', 2);
            }
        }
    }
    $total['price_declared'] = $order_info['total'];
    $total['price'] = $order_info['total'];

    $firstname = '';
    $lastname = '';

    if (!empty($order_info['lastname'])) {
        $lastname = $order_info['lastname'];

    } elseif (!empty($order_info['b_lastname'])) {
        $lastname = $order_info['b_lastname'];

    } elseif (!empty($order_info['s_lastname'])) {
        $lastname = $order_info['s_lastname'];
    }

    if (!empty($order_info['firstname'])) {
        $firstname = $order_info['firstname'];

    } elseif (!empty($order_info['b_firstname'])) {
        $firstname = $order_info['b_firstname'];

    } elseif (!empty($order_info['s_firstname'])) {
        $firstname = $order_info['s_firstname'];
    }

    $order_info['fio'] = $lastname . ' ' . $firstname;

    $order_info['state_name'] = fn_get_state_name($order_info['s_state'], $order_info['s_country'], DESCR_SL);
    $order_info['country_name'] = fn_get_country_name($order_info['s_country'], DESCR_SL);

    $order_info['address_line_2'] = $order_info['country_name'] . ', ' . $order_info['state_name'] . ', ' . $order_info['s_city'];

    if (!empty($order_info['phone'])) {
        $order_info['recipient_phone'] = fn_rus_russianpost_normalize_phone($order_info['phone']);

    } elseif (!empty($order_info['b_phone'])) {
        $order_info['recipient_phone'] = fn_rus_russianpost_normalize_phone($order_info['b_phone']);

    } elseif (!empty($order_info['s_phone'])) {
        $order_info['recipient_phone'] = fn_rus_russianpost_normalize_phone($order_info['s_phone']);
    }

    Tygh::$app['view']->assign('pre_total', $total);
    Tygh::$app['view']->assign('order_info', $order_info);

    $shipping = reset($order_info['shipping']);
    $shipping = fn_get_shipping_info($shipping['shipping_id']);
    if (isset($shipping['service_params']['post_blank_info'])) {
        $pre_data = $shipping['service_params']['post_blank_info'];
        $pre_data['company_phone'] = fn_rus_russianpost_normalize_phone($pre_data['company_phone']);
    } else {
        $pre_data = [
            '107_list_width' => 293,
            '107_list_height' => 205,
            '107_top' => 0,
            '107_left' => 0,
            '116_list_width' => 293,
            '116_list_height' => 205,
            '116_top' => 0,
            '116_left' => 0,
            '7p_top' => 0,
            '7p_left' => 0,
            '7a_top' => 0,
            '7a_left' => 0,
            '112_list_width' => 210,
            '112_list_height' => 293,
            '112_top' => 0,
            '112_left' => 0,
        ];
    }


    Tygh::$app['view']->assign('pre_data', $pre_data);
}

function fn_rus_russianpost_normalize_phone($data_phone)
{
    $array_search = ['+', '7', '8'];

    $data_phone = preg_replace('/[^\d\+]/', '', $data_phone);
    $data_phone = str_replace($array_search, '', substr($data_phone, 0, 2)) . substr($data_phone, 2);

    return $data_phone;
}
