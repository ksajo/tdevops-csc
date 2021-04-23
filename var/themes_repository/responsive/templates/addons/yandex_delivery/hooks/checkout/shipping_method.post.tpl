{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.module == 'yandex'}
    {if $yandex_delivery.$group_key[$shipping.shipping_id].pickup_points || $yandex_delivery.$group_key[$shipping.shipping_id].courier_points}
        <div class="clearfix">
            {include file="addons/yandex_delivery/views/yandex_delivery/components/cms.tpl"}
        </div>
    {/if}
{/if}