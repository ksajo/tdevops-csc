{foreach from=$order_info.shipping item="shipping_method"}
    {if $shipping_method.module == 'pickpoint' && !empty($shipping_method.data.pickpoint_postamat)}
        {assign var="pickpoint_shipping" value=true}
    {/if}
{/foreach}

{if $pickpoint_shipping}
{foreach from=$order_info.shipping item="shipping_method"}
    {if $shipping_method.module == 'pickpoint'}
    <li>
        <p class="ty-muted">
            {$shipping_method.data.pickpoint_postamat.address_pickpoint}
        </p>
    </li>
    {/if}
{/foreach}
{/if}
