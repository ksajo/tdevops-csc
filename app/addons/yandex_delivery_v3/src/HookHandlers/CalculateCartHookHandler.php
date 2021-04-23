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

namespace Tygh\Addons\YandexDelivery\HookHandlers;

use Tygh\Addons\YandexDelivery\Services\YandexDeliveryService;

class CalculateCartHookHandler
{
    /**
     * The "calculate_cart_taxes_pre" hook handler.
     *
     * Actions performed:
     *     - Saving information about shipping before calculation cart taxes process.
     *
     * @param array<string, string>                                      $cart            Cart data.
     * @param array<string, string>                                      $cart_products   Cart products.
     * @param array<string, array<string, array<string, array<string>>>> $product_groups  Products grouped by packages, suppliers, vendors.
     * @param bool                                                       $calculate_taxes Whether taxes should be calculated.
     * @param array<string, string>                                      $auth            Auth data.
     *
     * @see \fn_calculate_cart_content()
     */
    public function onCalculateCartTaxesPre(array $cart, array $cart_products, array &$product_groups, $calculate_taxes, array $auth)
    {
        if (empty($cart['shippings_extra']['data'])) {
            return;
        }

        foreach ($product_groups as $group_key => $group) {
            if (empty($group['chosen_shippings'])) {
                continue;
            }
            foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                if ($shipping['module'] !== YandexDeliveryService::MODULE) {
                    continue;
                }

                $shipping_id = $shipping['shipping_id'];
                $company_id = $group['company_id'];

                $point_id = 0;
                if (isset($cart['selected_yad_courier'][$company_id][$shipping_id]['courier_point_id'])) {
                    $point_id = $cart['selected_yad_courier'][$company_id][$shipping_id]['courier_point_id'];
                } elseif (isset($cart['selected_yad_office'][$company_id][$shipping_id]['pickup_point_id'])) {
                    $point_id = $cart['selected_yad_office'][$company_id][$shipping_id]['pickup_point_id'];
                }

                if (!empty($cart['shippings_extra']['data'][$group_key]['selected_shipping']['courier_data'])) {
                    $product_groups[$group_key]['chosen_shippings'][$shipping_key]['courier_data'] =
                        $cart['shippings_extra']['data'][$group_key]['selected_shipping']['courier_data'];
                }

                if (!empty($cart['shippings_extra']['data'][$group_key]['selected_shipping']['pickup_data'])) {
                    $product_groups[$group_key]['chosen_shippings'][$shipping_key]['pickup_data'] =
                        $cart['shippings_extra']['data'][$group_key]['selected_shipping']['pickup_data'];
                }

                $product_groups[$group_key]['chosen_shippings'][$shipping_key]['point_id'] = $point_id;
                $product_groups[$group_key]['chosen_shippings'][$shipping_key]['delivery'] =
                    $cart['shippings_extra']['data'][$group_key]['selected_shipping']['delivery'];
            }
        }
    }
}
