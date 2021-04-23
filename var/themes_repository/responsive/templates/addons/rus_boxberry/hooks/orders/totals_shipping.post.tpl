{foreach from=$order_info.shipping item="shipping_method"}

	{if $shipping_method.module == 'rus_boxberry'}
        {$shipment_id = $shipments[$shipping.group_key].shipment_id}
        <p class="ty-strong">
            {$shipping_method.pickup_data.type} {$shipping_method.pickup_data.address}
        </p>
        <p class="ty-muted">
            {if $shipping_method.pickup_data.full_address} {$shipping_method.pickup_data.full_address}{/if}</br>
            {if $shipping_method.pickup_data.phone}
                {__("phone")}: {$shipping_method.pickup_data.phone}</br>
            {/if}
            {if $shipping_method.pickup_data.trip_description}
                {$shipping_method.pickup_data.trip_description}
            {/if}
        </p>
    {/if}
{/foreach}
