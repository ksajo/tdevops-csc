{script src="js/addons/rus_boxberry/boxberry.js"}

{foreach from=$product_groups key=group_key item=group}
    {if $group.shippings && !$group.shipping_no_required}

        {foreach from=$group.shippings item=shipping}
            {if $cart.chosen_shipping.$group_key == $shipping.shipping_id}

                {$shipping_id = $shipping.shipping_id}
                {$pickup_data = $cart.shippings_extra.boxberry.$group_key.$shipping_id.pickup_data}

                {if $pickup_data}
                    <div class="control-group">
                        <div class="control-label">
                            {include file="common/subheader.tpl" title=__("rus_boxberry.pickuppoint")}
                        </div>
                    </div>

                    <div class="strong">
                        <input type="hidden" class="cm-submit cm-ajax cm-skip-validation"
                               name="boxberry_selected_point[{$group_key}][{$shipping_id}]"
                               value="{$cart.shippings_extra.boxberry.$group_key.$shipping_id.point_id}"
                               data-ca-dispatch="dispatch[order_management.update_shipping]">
                        <a class="select_pvz_link" href="#"
                           data-boxberry-open="true"
                           data-boxberry-token="{$cart.shippings_extra.boxberry.$group_key.$shipping_id.apiKeyWidget}"
                           data-boxberry-city="{$group.package_info.location.city}"
                           data-boxberry-weight="{$cart.shippings_extra.boxberry.$group_key.$shipping_id.boxberry_weight}"
                           data-boxberry-target-start="{$cart.shippings_extra.boxberry.$group_key.$shipping_id.boxberry_target_start}"
                           data-paymentsum="{$cart.shippings_extra.boxberry.$group_key.$shipping_id.boxberry_paymentsum}"
                           data-ordersum="{$cart.shippings_extra.boxberry.$group_key.$shipping_id.boxberry_ordersum}"
                           data-boxberry-point-input="boxberry_selected_point[{$group_key}][{$shipping_id}]"
                        >{$pickup_data.full_address|default:__('rus_boxberry.select_pickup_point')}</a>
                    </div>
                {/if}

            {/if}
        {/foreach}
    {/if}
{/foreach}






