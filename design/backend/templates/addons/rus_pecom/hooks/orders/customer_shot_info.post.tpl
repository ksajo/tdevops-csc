{foreach from=$order_info.shipping item="shipping" key="shipping_id" name="f_shipp"}
    {if $shipping.module == 'pecom' && $shipping.data}
        <div class="well orders-right-pane form-horizontal">
            <div class="control-group shift-top">
                <div class="control-label">
                    {include file="common/subheader.tpl" title=__("rus_pecom.order_info")}
                </div>
            </div>
            <p class="strong">
            {__('delivery_time')}: {$shipping.data.delivery_time nofilter}
            </p>
        </div>
    {/if}
{/foreach}