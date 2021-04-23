{foreach from=$order_info.shipping item="shipping" name="f_shipp"}
    {if $shipping.module == 'rus_boxberry' && $shipping.pickup_data}
        <div class="well orders-right-pane form-horizontal">
            <div class="control-group">
                <div class="control-label">
                    {include file="common/subheader.tpl" title=__("rus_boxberry.pickuppoint")}
                </div>
            </div>
            <p class="strong">
                {$shipping.pickup_data.name}
            </p>
            <p class="muted">
                {$shipping.pickup_data.full_address}<br />
                <bdi>{$shipping.pickup_data.phone}</bdi>
            </p>
        </div>
    {/if}
{/foreach}
