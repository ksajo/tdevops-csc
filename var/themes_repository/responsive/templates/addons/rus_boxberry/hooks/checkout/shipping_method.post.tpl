{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.module == 'rus_boxberry'}
    {$shipping_id = $shipping.shipping_id}

    {if $shipping.service_code == 'boxberry_self' || $shipping.service_code == 'boxberry_self_prepaid'}
	<div class="litecheckout__group litecheckout__group--center">
        <div class="litecheckout__item litecheckout__item--fill">
            <label for="selected_point_id" class="cm-required">{__('rus_boxberry.pickup_point')}:</label>
            <input id="selected_point_id" type="hidden" name="boxberry_selected_point[{$group_key}][{$shipping_id}]"
                   value="{$cart.shippings_extra.boxberry.$group_key.$shipping_id.point_id}"
            >
            {$cart.shippings_extra.boxberry.$group_key.$shipping_id.pickup_data.address}
        </div>
        <div class="litecheckout__item">
            <a class="ty-btn__secondary ty-btn select_pvz_link" href="#"
                data-boxberry-open="true"
                data-boxberry-token="{$cart.shippings_extra.boxberry.$group_key.$shipping_id.apiKeyWidget}"
                data-boxberry-city="{$group.package_info.location.city}, {$group.package_info.location.state_descr}"
                data-boxberry-weight="{$cart.shippings_extra.boxberry.$group_key.$shipping_id.boxberry_weight}"
                data-boxberry-target-start="{$cart.shippings_extra.boxberry.$group_key.$shipping_id.boxberry_target_start}"
                data-paymentsum="{$cart.shippings_extra.boxberry.$group_key.$shipping_id.boxberry_paymentsum}"
                data-ordersum="{$cart.shippings_extra.boxberry.$group_key.$shipping_id.boxberry_ordersum}"
                data-boxberry-point-input="boxberry_selected_point[{$group_key}][{$shipping_id}]"
                data-boxberry-point-full-name-input="boxberry_selected_point_full_name[{$group_key}][{$shipping_id}]"
            >{__("rus_boxberry.select_pickup_point")}</a>
        </div>
    </div>
    {script src="js/addons/rus_boxberry/boxberry.js"}
    {/if}
{/if}
