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
use Tygh\RestClient;
use Tygh\Settings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * Handlers
 */

function fn_yandex_metrika_oauth_info()
{
    if (
        !fn_string_not_empty(Registry::get('addons.rus_yandex_metrika.application_id'))
        || !fn_string_not_empty(Registry::get('addons.rus_yandex_metrika.application_password'))
    ) {
        /** @var \Tygh\SmartyEngine\Core $view */
        $view = Tygh::$app['view'];
        $view->assign([
            'widget_copy_title' => __('rus_yandex_metrika.oauth_info.register_an_app'),
            'widget_copy_text' => __('rus_yandex_metrika.oauth_info.callback_uri_description'),
            'widget_copy_code_text' => fn_url('yandex_metrika_tools.oauth'),
        ]);
        return $view->fetch('common/widget_copy.tpl');
    } else {
        $client_id = Registry::get('addons.rus_yandex_metrika.application_id');

        return __('yandex_metrika_oauth_info_part2', [
            '[auth_uri]' => 'https://oauth.yandex.ru/authorize?response_type=code&client_id=' . $client_id,
            '[edit_app_uri]' => 'https://oauth.yandex.ru/client/edit/' . $client_id,
        ]);
    }
}

/**
 * \Handlers
 */

/**
 * Common functions
 */

function fn_yandex_metrika_sync_goals()
{
    $counter_number = Settings::instance()->getValue('counter_number', 'rus_yandex_metrika');

    if (!$counter_number) {
        return false;
    }

    $goals_scheme = fn_get_schema('rus_yandex_metrika', 'goals');
    $selected_goals = Settings::instance()->getValue('collect_stats_for_goals', 'rus_yandex_metrika');

    $ext_goals = array();
    $res = fn_yandex_metrika_rest_client('get', "/management/v1/counter/{$counter_number}/goals.json");

    if (!empty($res['goals'])) {
        foreach ($res['goals'] as $goal) {
            $ext_goals[$goal['name']] = $goal;
        }

    } elseif ($res === false) {
        return false;
    }

    foreach ($goals_scheme as $goal_name => $goal) {
        $ext_goal_name = '[auto] ' . $goal['name'];
        if (!empty($ext_goals[$ext_goal_name])) {
            if (empty($selected_goals[$goal_name]) || $selected_goals[$goal_name] == 'N') {
                fn_yandex_metrika_rest_client('delete', "/management/v1/counter/{$counter_number}/goal/" . $ext_goals[$ext_goal_name]['id']);
            }
        } else {
            if (!empty($selected_goals[$goal_name]) && $selected_goals[$goal_name] == 'Y') {
                $goal['name'] = $ext_goal_name;
                fn_yandex_metrika_rest_client('post', "/management/v1/counter/{$counter_number}/goals", array('goal' => $goal));
            }
        }
    }

    return true;
}

/**
 * Executes Yandex.Metrika API request using the REST client.
 *
 * @param string $type    Request method.
 *                        Allowed values: get, post, delete
 * @param string $url     API url to send request to
 * @param array  $data    Data to send within the request
 * @param array  $headers Request headers
 *
 * @return array|false API response of false on error
 */
function fn_yandex_metrika_rest_client($type, $url, array $data = [], array $headers = [])
{
    static $oauth_token = null;
    static $client = null;

    if (!isset($headers['Authorization'])) {
        if ($oauth_token === null) {
            $oauth_token = Settings::instance()->getValue('auth_token', 'rus_yandex_metrika');
        }
        $headers['Authorization'] = 'OAuth ' . $oauth_token;
    }

    if ($client === null) {
        $client = new RestClient('https://api-metrika.yandex.ru/', null, null, 'basic', $headers);
    }

    if (!$oauth_token) {
        return false;
    }

    $res = false;

    try {
        if ($type === 'get') {
            $res = $client->get($url, $data);

        } elseif ($type === 'post') {
            $res = $client->post($url, $data);

        } elseif ($type === 'delete') {
            $res = $client->delete($url);
        }
    } catch (\Pest_Unauthorized $e) {
        fn_set_notification('E', __('error'), '401 Unauthorized. '. __('yandex_metrika_pest_unauthorized'));

    }  catch (\Pest_NotFound $e) {
        fn_set_notification('E', __('error'), '404 Not Found. ' . __('yandex_metrika_pest_not_found'));

    }  catch (\Pest_Forbidden $e) {
        fn_set_notification('E', __('error'), '403 Forbidden. ' . __('yandex_metrika_pest_forbidden'));

    } catch (\Exception $e){
        fn_set_notification('E', __('error'), strip_tags($e->getMessage()));
    }

    return $res;
}

/**
 * \Common functions
 */

function fn_rus_yandex_metrika_place_order($order_id, $action, $order_status, $cart, $auth)
{
    if (Registry::get('addons.rus_yandex_metrika.ecommerce') != 'Y') {
        return;
    }

    $purchased = array(
        'action' => array(
            'id' => $order_id,
            'revenue' => $cart['total'],
        )
    );

    if (!empty($cart['coupons'])) {
        $coupon = array_keys($cart['coupons']);
        $purchased['action']['coupon'] = reset($coupon);
    }

    foreach($cart['products'] as $product_hash => $product_data) {

        $product_id = $product_data['product_id'];

        $purchased['products'][$product_id] = array(
            'id' => $product_id,
            'quantity' => $product_data['amount'],
            'name' => $product_data['product'],
            'price' => $product_data['price'],
        );

        if (!empty($product_data['yml_market_category'])) {
            $purchased['products'][$product_id]['category'] = $product_data['yml_market_category'];
        }
    }

    Tygh::$app['session']['yandex_metrika']['purchased'] = $purchased;
}

function fn_rus_yandex_metrika_sum_statistics(&$statistics, $added, $deleted)
{
    if (!empty($added)) {
        foreach ($added as $product_id => $statistic_added) {

            if (isset($statistics['added'][$product_id])) {
                $statistics['added'][$product_id]['quantity'] += $statistic_added['quantity'];

            } else {
                $statistics['added'][$product_id] = $statistic_added;
            }

            if (isset($statistics['deleted'][$product_id])) {
                fn_rus_yandex_metrika_sum($product_id, $statistics, $statistic_added, $statistics['deleted'][$product_id]);
            }

            if (isset($deleted[$product_id])) {
                fn_rus_yandex_metrika_sum($product_id, $statistics, $statistic_added, $deleted[$product_id]);
            }
        }
    }
    
    if (!empty($deleted)) {
        foreach ($deleted as $product_id => $statistic_deleted) {

            if (isset($statistics['deleted'][$product_id])) {
                $statistics['deleted'][$product_id]['quantity'] += $statistic_deleted['deleted'];
            } else {
                $statistics['deleted'][$product_id] = $statistic_deleted;
            }

            if (isset($statistics['added'][$product_id])) {
                fn_rus_yandex_metrika_sum($product_id, $statistics, $statistics['added'][$product_id], $statistic_deleted);
            }

            if (isset($added[$product_id])) {
                fn_rus_yandex_metrika_sum($product_id, $statistics, $added, $statistic_deleted);
            }
        }
    }
}

function fn_rus_yandex_metrika_sum($product_id, &$statistics, $added, $deleted)
{
    if (empty($added)) {
        $statistics['deleted'][$product_id] = $deleted;

    } elseif (empty($deleted)) {
        $statistics['added'][$product_id] = $added;

    } elseif ($deleted['quantity'] > $added['quantity']) {
        unset($statistics['added'][$product_id]);
        $statistics['deleted'][$product_id]['quantity'] = $deleted['quantity'] - $added['quantity'];

    } elseif ($deleted['quantity'] < $added['quantity']) {
        unset($statistics['deleted'][$product_id]);
        $statistics['added'][$product_id]['quantity'] = $added['quantity'] - $deleted['quantity'];

    } else {
        unset($statistics['added'][$product_id]);
        unset($statistics['deleted'][$product_id]);
    }
}

function fn_rus_yandex_metrika_get_add_data($product_data, $count = 0)
{
    $product_id = $product_data['product_id'];
    $product_add = array(
        'id' => $product_id,
        'quantity' => empty($count) ? $product_data['amount'] : $count,
        'price' => $product_data['price'],
        'name' => $product_data['product']
    );

    $header_features = fn_get_product_features_list($product_data, 'H');
    foreach ($header_features as $feature_id => $feature_data) {
        if ($feature_data['feature_type'] == 'E') {
            $product_add['brand'] = $feature_data['variant'];
            break;
        }
    }

    if (!empty($product['yml_market_category'])) {
        $product_add['category'] = $product['yml_market_category'];
    }

    return $product_add;
}

function fn_rus_yandex_metrika_order_placement_routines()
{
    unset(Tygh::$app['session']['yandex_metrika']['added']);
    unset(Tygh::$app['session']['yandex_metrika']['deleted']);
}

function fn_rus_yandex_metrika_save_cart_content_pre($cart, $user_id, $type, $user_type)
{
    if (Registry::get('addons.rus_yandex_metrika.ecommerce') != 'Y') {
        return;
    }

    if (empty($user_id)) {
        if (fn_get_session_data('cu_id')) {
            $user_id = fn_get_session_data('cu_id');
        } else {
            $user_id = fn_crc32(uniqid(TIME));
            fn_set_session_data('cu_id', $user_id, COOKIE_ALIVE_TIME);
        }
    }

    $_products = fn_get_cart_products($user_id);

    $old_products = array();
    foreach($_products as $product_data) {
        $old_products[$product_data['item_id']] = $product_data;
    }

    Tygh::$app['session']['yandex_metrika']['old'] = $old_products;
}

function fn_rus_yandex_metrika_save_cart_content_post($cart, $user_id, $type, $user_type) 
{
    if (Registry::get('addons.rus_yandex_metrika.ecommerce') != 'Y') {
        return;
    }

    $added = array();
    $deleted = array();
    $products_old = empty(Tygh::$app['session']['yandex_metrika']['old'])
        ? []
        : Tygh::$app['session']['yandex_metrika']['old'];

    if (empty($user_id)) {
        if (fn_get_session_data('cu_id')) {
            $user_id = fn_get_session_data('cu_id');
        } else {
            $user_id = fn_crc32(uniqid(TIME));
            fn_set_session_data('cu_id', $user_id, COOKIE_ALIVE_TIME);
        }
    }

    $_products = fn_get_cart_products($user_id);
    $products = array();
    foreach($_products as $product_data) {
        $products[$product_data['item_id']] = $product_data;
    }

    $deleted_products = array_diff_key($products_old, $products);
    foreach ($deleted_products as $deleted_product) {
        $product_id = $deleted_product['product_id'];
        $deleted[$product_id] = array(
            'id' => $product_id,
            'quantity' => $deleted_product['amount'],
            'name' => $deleted_product['product']
        );
    }

    $added_products = array_diff_key($products, $products_old);
    foreach ($added_products as $added_product) {
        $added[$added_product['product_id']] = fn_rus_yandex_metrika_get_add_data($added_product);
    }

    foreach ($products as $cart_id => $product_data) {
        $product_id = $product_data['product_id'];

        if (isset($products_old[$cart_id])) {

            if ($product_data['amount'] > $products_old[$cart_id]['amount']) {
                $added_count = $product_data['amount'] - $products_old[$cart_id]['amount'];

                $added[$product_id] = fn_rus_yandex_metrika_get_add_data($product_data, $added_count);

            } elseif ($product_data['amount'] < $products_old[$cart_id]['amount']) {
                $deleted_count = $products_old[$cart_id]['amount'] - $product_data['amount'];

                $deleted[$product_id] = array(
                    'id' => $product_id,
                    'quantity' => $deleted_count,
                    'name' => $product_data['product']
                );
            }
        }
    }

    $yandex_metrika = array();
    fn_rus_yandex_metrika_sum_statistics($yandex_metrika, $added, $deleted);

    unset(Tygh::$app['session']['yandex_metrika']['old']);

    if (defined('AJAX_REQUEST')) {
        if (!empty($yandex_metrika['added'])) {
            $yandex_metrika['added'] = array_values($yandex_metrika['added']);
        }

        if (!empty($yandex_metrika['deleted'])) {
            $yandex_metrika['deleted'] = array_values($yandex_metrika['deleted']);
        }

        Tygh::$app['ajax']->assign('yandex_metrika', $yandex_metrika);

    } else {
        if (!empty($yandex_metrika['added'])) {
            Tygh::$app['session']['yandex_metrika']['added'] = $yandex_metrika['added'];
        }

        if (!empty($yandex_metrika['deleted'])) {
            Tygh::$app['session']['yandex_metrika']['deleted'] = $yandex_metrika['deleted'];
        }
    }
}

function fn_rus_yandex_metrika_get_additional_information($product, $product_data)
{
    if (Registry::get('addons.rus_yandex_metrika.ecommerce') == 'Y' && defined('AJAX_REQUEST')) {

        $detail = array(
            'id' => $product['product_id'],
            'name' => $product['product'],
            'price' => $product['price'],
            'category' => fn_get_category_path($product['main_category']),
        );

        foreach ($product['header_features'] as $feature_id => $feature_data) {
            if ($feature_data['feature_type'] == 'E') {
                $detail['brand'] = $feature_data['variant'];
                break;
            }
        }

        if (!empty($product['selected_options'])) {
            $variants_name = array();
            foreach ($product['selected_options'] as $option_id => $option_variant_id) {
                $option_data = fn_get_product_option_data($option_id, $product['product_id']);

                if (isset($option_data['variants'][$option_variant_id]['variant_name'])) {
                    if (!empty($option_data['variants'][$option_variant_id]['yml2_variant'])) {
                        $variants_name[] = $option_data['variants'][$option_variant_id]['yml2_variant'];
                    } else {
                        $variants_name[] = $option_data['variants'][$option_variant_id]['variant_name'];
                    }
                }
            }

            $detail['variant'] = implode(', ', $variants_name);
        }

        $yandex_metrika['detail'][] = $detail;

        Tygh::$app['ajax']->assign('yandex_metrika', $yandex_metrika);
    }
}
