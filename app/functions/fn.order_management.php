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
use Tygh\Enum\ProductOptionsApplyOrder;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * Prepares the array of data of the product that is being changed in cart or in the order,
 * and returns the template of the product with the product data.
 * The function is used only in controllers.
 *
 * @param array  $params An array of parameters.
 * @param array  $auth   Authentication data.
 * @param string $mode   The directory mode.
 *
 * @return array|bool An array with the product data.
 */
function fn_get_data_of_changed_product(&$params, $auth, $mode)
{
    $cart_products = array();
    $_auth = $auth;

    if (empty($params['product_data']) && empty($params['cart_products'])) {
        return false;
    }

    if (!empty($params['product_data'])) {
        unset($params['product_data']['custom_files']);

        $product = fn_get_additional_product_data($params, $auth);
        $product_data = $params;

        fn_update_product_image_in_template($params);

        if (AREA == 'C') {
            if (!empty($params['appearance']['quick_view'])) {
                $display_tpl = 'views/products/quick_view.tpl';

            } elseif (!empty($params['appearance']['details_page'])) {
                $display_tpl = 'views/products/view.tpl';

            } else {
                $display_tpl = 'common/product_data.tpl';
            }
        } else {
            $display_tpl = 'views/products/components/select_product_options.tpl';

            Tygh::$app['view']->assign('product_options', $product['product_options']);
        }

    } else {
        fn_enable_checkout_mode();

        unset($params['cart_products']['custom_files']);

        $cart_products = $params['cart_products'];
        if (!empty($cart_products)) {
            foreach ($cart_products as $cart_id => $product) {
                if (!empty($product['object_id'])) {
                    unset($cart_products[$cart_id]);
                    $cart_products[$product['object_id']] = $product;
                }
            }
        }

        if (AREA == 'A') {
            $_auth = Tygh::$app['session']['customer_auth'];
            if (empty($_auth)) {
                $_auth = fn_fill_auth(array(), array(), false, 'C');
            }
        }

        $_cart = Tygh::$app['session']['cart'];

        $product_data = fn_get_product_options_data($cart_products, $_cart, $params);

        fn_set_hook('calculate_options', $cart_products, $_cart, $auth);

        $exclude_products = array();
        foreach ($_cart['products'] as $cart_id => $product) {
            if (!empty($product['extra']['exclude_from_calculate'])) {
                $exclude_products[$cart_id] = true;
            }
        }

        list($cart_products) = fn_calculate_cart_content($_cart, $_auth, 'S', true, 'F', true);

        fn_gather_additional_products_data($cart_products, array('get_icon' => true, 'get_detailed' => true, 'get_options' => true, 'get_discounts' => false));

        $changed_options = false;
        foreach ($cart_products as $item_id => $product) {
            if ($_cart['products'][$item_id]['product_options'] != $product['selected_options']) {
                $_cart['products'][$item_id]['product_options'] = $product['selected_options'];
                $changed_options = true;
            }
        }

        if ($changed_options) {
            list($cart_products) = fn_calculate_cart_content($_cart, $_auth, 'S', true, 'F', true);
            fn_gather_additional_products_data($cart_products, array('get_icon' => true, 'get_detailed' => true, 'get_options' => true, 'get_discounts' => false));
        }

        if (count(Tygh::$app['session']['cart']['products']) != count($_cart['products'])) {
            $_recalculate = false;

            foreach (Tygh::$app['session']['cart']['products'] as $cart_id => $product) {
                if (!isset($_cart['products'][$cart_id]) && !isset($exclude_products[$cart_id])) {
                    $_recalculate = true;
                    break;
                }
            }

            if ($_recalculate) {
                $_cart = Tygh::$app['session']['cart'];
                list($cart_products) = fn_calculate_cart_content($_cart, $_auth, 'S', true, 'F', true);
            }
        }

        fn_change_product_data_in_cart($cart_products, $_cart, $params);

        Registry::set('navigation', array());
        Tygh::$app['view']->assign('cart_products', $cart_products);
        Tygh::$app['view']->assign('cart', $_cart);

        $params['cart'] = $_cart;

        if (AREA == 'C') {
            $display_tpl = 'views/checkout/components/cart_items.tpl';
        } else {
            $display_tpl = 'views/order_management/components/products.tpl';
        }
    }

    $data = isset($product_data) ? $product_data : $cart_products;

    fn_set_hook('after_options_calculation', $mode, $data, $auth);

    Tygh::$app['view']->display($display_tpl);

    return true;
}

/**
 * Gets the data about the product's stock and options based on the passed data
 * of the product that is being changed in cart or in the order.
 * The function is used only in controllers.
 *
 * @param array $product_data The data of the chaged product.
 * @param array $auth Authentication data.
 *
 * @return bool|array The array with the product data.
 */
function fn_get_additional_product_data(&$product_data, $auth)
{
    $_auth = $auth;

    $_data = reset($product_data['product_data']);
    $product_id = key($product_data['product_data']);

    $product_id = isset($_data['product_id']) ? $_data['product_id'] : $product_id;
    $selected_options = empty($_data['product_options']) ? array() : $_data['product_options'];

    unset($selected_options['AOC']);

    if (isset($product_data['additional_info']['info_type']) && $product_data['additional_info']['info_type'] == 'D') {
        $product = fn_get_product_data($product_id, $_auth, CART_LANGUAGE, '', true, true, true, true, ($auth['area'] == 'A'));
    } else {
        $specific_settings['pid'] = $product_id;
        list($product) = fn_get_products($specific_settings);
        $product = reset($product);
    }

    if (empty($product)) {
        return false;
    }

    $product['changed_option'] = isset($product_data['changed_option']) ? reset($product_data['changed_option']) : '';
    $product['selected_options'] = $selected_options;

    if (!empty($_data['amount'])) {
        $product['selected_amount'] = $_data['amount'];
    }

    // Get specific settings
    $specific_settings = array(
        'get_icon' => isset($product_data['additional_info']['get_icon']) ? $product_data['additional_info']['get_icon'] : false,
        'get_detailed' => isset($product_data['additional_info']['get_detailed']) ? $product_data['additional_info']['get_detailed'] : false,
        'get_options' => isset($product_data['additional_info']['get_options']) ? $product_data['additional_info']['get_options'] : true,
        'get_discounts' => isset($product_data['additional_info']['get_discounts']) ? $product_data['additional_info']['get_discounts'] : true,
        'get_features' => isset($product_data['additional_info']['get_features']) ? $product_data['additional_info']['get_features'] : false,
    );

    fn_set_hook('get_additional_information', $product, $product_data);

    fn_gather_additional_product_data($product, $specific_settings['get_icon'], $specific_settings['get_detailed'], $specific_settings['get_options'], $specific_settings['get_discounts'], $specific_settings['get_features']);

    if (isset($product['inventory_amount'])) {
        $product['amount'] = $product['inventory_amount'];
    }

    if (!empty($product_data['extra_id'])) {
        $product['product_id'] = $product_data['extra_id'];
    }

    Tygh::$app['view']->assign('product', $product);

    return $product;
}

/**
 * Updates the image of the product in the product list template.
 * The function is used only in controllers.
 *
 * @param array $params An array of parameters.
 *
 * @return void
 */
function fn_update_product_image_in_template($params)
{
    // Update the images in the list/grid templates
    if (!empty($params['image'])) {
        $images_data = array();

        foreach ($params['image'] as $div_id => $value) {
            list($obj_id, $width, $height, $type) = explode(',', $value['data']);
            $images_data[$div_id] = array(
                'obj_id' => $obj_id,
                'width' => $width,
                'height' => $height,
                'type' => $type,
                'link' => isset($value['link']) ? $value['link'] : '',
            );
        }

        Tygh::$app['view']->assign('images', $images_data);
    }
}

/**
 * Gets the product data depending on the newly-selected options,
 * and records the data in the $cart session array.
 * The function is used only in controllers.
 *
 * @param array $cart_products The data of the product.
 * @param array $cart          Array of cart content.
 * @param array $params        An array of parameters.
 *
 * @return array|null The array with the product data.
 */
function fn_get_product_options_data($cart_products, &$cart, $params)
{
    foreach ($cart_products as $cart_id => $item) {
        if (isset($cart['products'][$cart_id])) {
            $amount = isset($item['amount']) ? $item['amount'] : 1;
            $product_data = fn_get_product_data($item['product_id'], $auth, CART_LANGUAGE, '', false, false, false, false, false, false, false);

            if (
                isset($product_data['options_type'])
                && $product_data['options_type'] === ProductOptionsApplyOrder::SEQUENTIAL
                && isset($item['product_options'])
                && isset($params['changed_option'][$cart_id])
            ) {
                $item['product_options'] = fn_fill_sequential_options($item, $params['changed_option'][$cart_id]);
                unset($params['changed_option']);
            }

            $product_options = isset($item['product_options']) ? $item['product_options'] : array();
            $amount = fn_check_amount_in_stock($item['product_id'], $amount, $product_options, $cart_id, $cart['products'][$cart_id]['is_edp'], 0, $cart);

            if ($amount === false) {
                continue;
            }

            $cart['products'][$cart_id]['amount'] = $amount;
            $cart['products'][$cart_id]['selected_options'] = isset($item['product_options']) ? $item['product_options'] : array();
            $cart['products'][$cart_id]['product_options'] = fn_get_selected_product_options($item['product_id'], $cart['products'][$cart_id]['selected_options']);
            $cart['products'][$cart_id] = fn_apply_options_rules($cart['products'][$cart_id]);
            $cart['products'][$cart_id]['product_options'] = $cart['products'][$cart_id]['selected_options'];

            if (!empty($cart['products'][$cart_id]['extra']['saved_options_key'])) {
                $cart['saved_product_options'][$cart['products'][$cart_id]['extra']['saved_options_key']] = $cart['products'][$cart_id]['product_options'];
            }

            if (!empty($item['object_id'])) {
                $cart['products'][$cart_id]['object_id'] = $item['object_id'];

                if (!empty($cart['products'][$cart_id]['extra']['saved_options_key'])) {
                    // Product from promotion. Save object_id for this product
                    $cart['saved_object_ids'][$cart['products'][$cart_id]['extra']['saved_options_key']] = $item['object_id'];
                }
            }

            unset($cart['products'][$cart_id]['extra']['exclude_from_calculate']);
        }
    }

    return isset($product_data) ? $product_data : null;
}

/**
 * Changes the product data in the $cart array.
 * The function is used only in controllers.
 *
 * @param array  $cart_products  The data of the product.
 * @param array  $cart           Array of cart content.
 * @param array  $params         An array of parameters.
 *
 * @return void
 */
function fn_change_product_data_in_cart(&$cart_products, &$cart, $params)
{
    if (!empty($cart_products)) {
        foreach ($cart_products as $k => $product) {
            if (!empty($product['object_id'])) {
                $c_product = !empty($cart['products'][$k]) ? $cart['products'][$k] : array();

                unset($cart_products[$k], $cart['products'][$k]);

                $cart['products'][$product['object_id']] = $c_product;
                $cart_products[$product['object_id']] = $product;
                $k = $product['object_id'];
            }

            if (isset($product['object_id'], $params['changed_option'][$product['object_id']])) {
                $cart_products[$k]['changed_option'] = $params['changed_option'][$product['object_id']];
            } elseif (isset($params['changed_option'][$k])) {
                $cart_products[$k]['changed_option'] = $params['changed_option'][$k];
            } else {
                $cart_products[$k]['changed_option'] = '';
            }
        }
    }
}

/**
 * Fills sequential options with default values. Necessary for cart total calculation
 *
 * @param array $item Cart item
 * @param int $changed_option Changed option identifier
 * @return array New options list
 */
function fn_fill_sequential_options($item, $changed_option)
{
    $params['pid'] = $item['product_id'];
    list($product) = fn_get_products($params);
    $product = reset($product);

    $product['changed_option'] = $changed_option;
    $product['selected_options'] = $item['product_options'];

    fn_gather_additional_product_data($product, false, false, true, false, false);

    if (count($item['product_options']) != count($product['selected_options'])) {
        foreach ($item['product_options'] as $option_id => $variant_id) {
            if (isset($product['selected_options'][$option_id]) || (in_array($product['product_options'][$option_id]['option_type'], array('I', 'T', 'F')))) {
                continue;
            }

            if (!empty($product['product_options'][$option_id]['variants'])) {
                reset($product['product_options'][$option_id]['variants']);
                $variant_id = key($product['product_options'][$option_id]['variants']);
            } else {
                $variant_id = '';
            }

            $product['selected_options'][$option_id] = $variant_id;
            $product['changed_option'] = $option_id;

            fn_gather_additional_product_data($product, false, false, true, false, false);
        }
    }

    return $product['selected_options'];
}
