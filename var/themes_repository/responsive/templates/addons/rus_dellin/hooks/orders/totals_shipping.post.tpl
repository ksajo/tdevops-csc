{foreach from=$order_info.shipping item="shipping_method"}
    {if $shipping_method.module == 'dellin'}
        {assign var="dellin_shipping" value=true}
    {/if}
{/foreach}

{if $dellin_shipping}
{foreach from=$order_info.shipping item="shipping_method"}
    <li>{if $shipping_method.terminal_data}   
            <p class="ty-strong">
                {$shipping_method.terminal_data.name}
            </p>
            <p class="ty-muted">
                {$shipping_method.terminal_data.address}
            </p>
        {/if}
    </li>
{/foreach}
{/if}
