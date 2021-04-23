{foreach from=$order_info.shipping item="shipping" key="shipping_id"}
    {if $shipping.module == 'pickpoint' && $shipping.data.pickpoint_postamat}
        <div class="well orders-right-pane form-horizontal">
            <div class="control-group shift-top">
                <div class="control-label">
                    {include file="common/subheader.tpl" title=__("addons.rus_pickpoint.pickpoint")}
                </div>
            </div>

            <p class="muted">
                {$shipping.data.pickpoint_postamat.address_pickpoint}
            </p>
        </div>
    {/if}
{/foreach}
