{if $product_groups}
    {foreach from=$product_groups key=group_key item=group}
        {if $group.shippings && !$group.shipping_no_required}
            {foreach from=$group.shippings item=shipping}
                {if $cart.chosen_shipping.$group_key == $shipping.shipping_id}
                    {$shipping.data.delivery_time nofilter}
                {/if}
            {/foreach}
        {/if}
    {/foreach}
{/if}