{foreach from=$order_info.shipping item="shipping" key="shipping_id"}
    {if ($shipping.module == 'dellin') && ($shipping.terminal_data)}
        <div class="well orders-right-pane form-horizontal">
            <div class="control-group shift-top">
                <div class="control-label">
                    {include file="common/subheader.tpl" title=__("shipping.rus_dellin.header_shipping_terminal")}
                </div>
            </div>

            <p class="strong">{$shipping.terminal_data.name}</p>

            <p class="muted">
                {$shipping.terminal_data.address}
            </p>
        </div>
    {/if}
{/foreach}
