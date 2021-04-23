{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.module == 'yandex_delivery'}
    {script src="js/addons/yandex_delivery_v3/map.js"}
    {if $yandex_delivery_v3.$group_key[$shipping.shipping_id].pickup_points}
        {hook name="checkout:yandex_delivery_v3_pickup_content"}
            {include file="addons/yandex_delivery_v3/views/checkout/components/shippings/list.tpl"
                shipping = $yandex_delivery_v3.$group_key[$shipping.shipping_id]
                shipping_id = $shipping.shipping_id
            }
        {/hook}
    {elseif $yandex_delivery_v3.$group_key[$shipping.shipping_id].courier_points}
        {include file="addons/yandex_delivery_v3/views/checkout/components/shippings/courier_list.tpl"
            shipping = $yandex_delivery_v3.$group_key[$shipping.shipping_id]
            shipping_id = $shipping.shipping_id
        }
    {/if}
{/if}