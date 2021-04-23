{if $product_groups.$group_key.chosen_shippings.$group_key.module == 'yandex_delivery'}

    {if $product_groups.$group_key.chosen_shippings.$group_key.pickup_data}
        {$pickup_point = $product_groups.$group_key.chosen_shippings.$group_key.pickup_data}
        {$delivery = $product_groups.$group_key.chosen_shippings.$group_key.delivery}
        <p>{$pickup_point.name}</p>
        <p>{$delivery.delivery.partner.name}, {$pickup_point.address.addressString}</p>
    {/if}

    {if $product_groups.$group_key.chosen_shippings.$group_key.courier_data}
        {$courier_point = $product_groups.$group_key.chosen_shippings.$group_key.courier_data}
        {$delivery = $product_groups.$group_key.chosen_shippings.$group_key.delivery}
        <p>{$courier_point.delivery.partner.name}</p>
    {/if}
{/if}