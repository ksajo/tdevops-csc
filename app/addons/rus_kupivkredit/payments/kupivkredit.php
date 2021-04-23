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

use Tygh\Enum\YesNo;

defined('BOOTSTRAP') or die('Access denied');

/** @var int $order_id */
$order_info     = fn_get_order_info($order_id);
$processor_data = $order_info['payment_method'];
$url            = ($processor_data['processor_params']['test'] === YesNo::YES) ? 'https://' . KVK_API_TEST_URL : 'https://' . KVK_API_URL;
$request_url    = $url . '/api/partners/v1/lightweight/create';
$request_data   = [];

$request_data['shopId'] = $processor_data['processor_params']['kvk_shop_id'];
$request_data['customerEmail'] = $order_info['email'] ? $order_info['email'] : '';
$request_data['customerPhone'] = $order_info['b_phone'] ? $order_info['b_phone'] : '';
$request_data['integrationType'] = 'CSCart';

if (isset($processor_data['processor_params']['kvk_show_case_id'])) {
    $request_data['showcaseId'] = $processor_data['processor_params']['kvk_show_case_id'];
}

$order_subtotal = $order_info['subtotal'];
$items = [];

foreach ($order_info['products'] as $item) {
    $price = fn_format_price(($item['subtotal'] - fn_external_discounts($item)) / $item['amount']);
    $category = db_get_field(
        'SELECT ?:category_descriptions.category FROM ?:category_descriptions'
        . ' LEFT JOIN ?:products_categories ON ?:category_descriptions.category_id = ?:products_categories.category_id'
        . ' WHERE ?:products_categories.product_id = ?i AND ?:products_categories.link_type = ?s AND ?:category_descriptions.lang_code = ?s',
        $item['product_id'],
        'M',
        $order_info['lang_code']
    );

    $item_data = [
        'name'     => $item['product'],
        'price'    => $price,
        'amount'   => $item['amount'],
        'category' => $category
    ];

    $items[] = $item_data;
}

if (!empty($order_info['use_gift_certificates']) || !empty($order_info['subtotal_discount'])) {
    $total_discount = 0;

    if (!empty($order_info['use_gift_certificates'])) {
        foreach ($order_info['use_gift_certificates'] as $data) {
            $total_discount += (float) $data['amount'];
        }
    }

    if (!empty($order_info['subtotal_discount'])) {
        $total_discount += $order_info['subtotal_discount'];
    }

    $discount_percentage = $total_discount / $order_subtotal;

    $total = 0;

    foreach ($items as $key => $item) {
        if ($item['price'] <= 0) {
            continue;
        }

        $items[$key]['price'] -= $item['price'] * $discount_percentage;
        $items[$key]['price'] = fn_format_rate_value($items[$key]['price'], 'F', 2, '.', '', '');

        $total += $items[$key]['price'] * $items[$key]['amount'];
    }

    $total_difference = fn_format_rate_value(
        ($order_subtotal - $total_discount) - $total,
        'F',
        2,
        '.',
        '',
        ''
    );

    if ($total_difference !== 0) {
        $first_item = array_shift($items);

        if ($first_item['amount'] > 1) {
            $first_item['amount'] = $first_item['amount'] - 1;

            $new_item = $first_item;
            $new_item['amount'] = 1;
            $new_item['price'] += $total_difference;

            $items[] = $new_item;
        } elseif ($first_item['amount'] === 1) {
            $first_item['price'] += $total_difference;
        }

        array_unshift($items, $first_item);
    }
}

$count = 0;
foreach ($items as $item) {
    $request_data['itemName_' . $count] = $item['name'];
    $request_data['itemPrice_' . $count] = fn_format_rate_value($item['price'], 'F', 2, '.', '', '');
    $request_data['itemQuantity_' . $count] = $item['amount'];
    $request_data['itemCategory_' . $count] = $item['category'];

    $count += 1;
}


if (!empty($order_info['shipping_cost'])) {
    $request_data['itemName_' . $count] = __('shipping_cost');
    $request_data['itemQuantity_' . $count] = 1;
    $request_data['itemPrice_' . $count] = fn_format_rate_value($order_info['shipping_cost'], 'F', 2, '.', '', '');

    $count += 1;
}

if (!empty($order_info['taxes'])) {
    foreach ($order_info['taxes'] as $tax) {
        if ($tax['price_includes_tax'] === YesNo::YES) {
            continue;
        }

        $request_data['itemName_' . $count] = __('tax');
        $request_data['itemQuantity_' . $count] = 1;
        $request_data['itemPrice_' . $count] = fn_format_rate_value($tax['tax_subtotal'], 'F', 2, '.', '', '');

        $count += 1;
    }
}

if (!empty($order_info['gift_certificates'])) {
    foreach ($order_info['gift_certificates'] as $certificate_data) {
        $request_data['itemName_' . $count] = __('gift_certificate');
        $request_data['itemQuantity_' . $count] = 1;
        $request_data['itemPrice_' . $count] = fn_format_rate_value($certificate_data['amount'], 'F', 2, '.', '', '');

        $count += 1;
    }
}

$surcharge = isset($order_info['payment_surcharge']) ? (int) $order_info['payment_surcharge'] : 0;

if ($surcharge !== 0) {
    $request_data['itemName_' . $count] = __('payment_surcharge');
    $request_data['itemQuantity_' . $count] = 1;
    $request_data['itemPrice_' . $count] = fn_format_rate_value($order_info['payment_surcharge'], 'F', 2, '.', '', '');
}

$order_total = fn_format_rate_value($order_info['total'], 'F', 2, '.', '', '');

$request_data['sum'] = $order_total;

fn_change_order_status($order_id, 'O');
fn_clear_cart(Tygh::$app['session']['cart']);
fn_create_payment_form(
    $request_url,
    $request_data,
    'Тинькофф: Кредитование покупателей (КупиВкредит)',
    true,
    $method = 'post',
    $parse_url = false,
    $target = 'form',
    $connection_message = __('rus_kupivkredit.redirect_to_create_order')
);

exit;
