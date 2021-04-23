{foreach from=$cart.shipping item=dellin_shipping}
    {if $dellin_shipping.module == 'dellin'}
        {if $product_groups}
            {foreach from=$product_groups key=group_key item=group}
                {if $group.shippings && !$group.shipping_no_required}
                    {foreach from=$group.shippings item=shipping}
                        {if $cart.chosen_shipping.$group_key == $shipping.shipping_id}

                            {assign var="old_terminal_id" value=$old_ship_data.$group_key.terminal_id}
                            {assign var="terminal_id" value=$arrival_terminals.$group_key.$shipping_id}
                            {assign var="shipping_id" value=$shipping.shipping_id}

                            {if $shipping.data.arrival_terminals}
                                {assign var="arrival_count" value=$shipping.data.arrival_terminals|count}

                                {if $arrival_count == 1}
                                    {foreach from=$shipping.data.arrival_terminals item=arrival_terminal}
                                    <div class="sidebar-row">
                                        <input type="hidden" name="arrival_terminal[{$group_key}][{$shipping_id}]" value="{$arrival_terminal.code}" id="terminal_{$group_key}_{$shipping_id}_{$arrival_terminal.code}" checked="checked" /> 
                                        {$arrival_terminal.name} 
                                        <p class="muted">
                                            {$arrival_terminal.address}
                                        </p>
                                    </div>
                                    {/foreach}
                                {else}
                                    {foreach from=$shipping.data.arrival_terminals item=arrival_terminal}
                                    <div class="sidebar-row">
                                        <div class="control-group">
                                            <div class="controls">
                                                <input type="radio" name="arrival_terminal[{$group_key}][{$shipping_id}]" value="{$arrival_terminal.code}" {if $terminal_id == $arrival_terminal.code || empty($terminal_id) || ($old_terminal_id == $arrival_terminal.code)}checked="checked"{/if} id="terminal_{$group_key}_{$shipping_id}_{$arrival_terminal.code}" />
                                                {$arrival_terminal.name}
                                                <p class="muted">
                                                    {$arrival_terminal.address}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    {/foreach}
                                {/if}
                            {/if}
                        {/if}
                    {/foreach}
                {/if}
            {/foreach}
        {/if}
    {/if}
{/foreach}
