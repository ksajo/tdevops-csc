{if $product_groups}
    {foreach $product_groups as $group_key => $group}
        {if !$group.shippings || $group.shipping_no_required}
            {continue}
        {/if}
        {$shipping_data = $group.chosen_shippings.$group_key.service_params}
        {foreach $group.shippings as $shipping}
            {if $cart.chosen_shipping.$group_key != $shipping.shipping_id}
                {continue}
            {/if}
            {$yd = $yandex_delivery_v3.$group_key[$shipping.shipping_id]}
            {if $yd.pickup_points}
                {$shipping_id = $shipping.shipping_id}
                {$select_id = $group.chosen_shippings.$group_key.point_id}
                {$stores_count = $yd.pickup_points|count}
                {if $stores_count == 1}
                    {foreach $yd.pickup_points as $store}
                        <div class="sidebar-row ty-yad-store">
                            <input
                                type="hidden"
                                name="select_yad_office[{$group_key}][{$shipping_id}]"
                                value="{$store.id}"
                                id="store_{$group_key}_{$shipping_id}_{$store.id}"
                            >
                            {$store.name}
                            <p class="muted">
                                {if $store.address}{$store.address.shortAddressString}{/if}
                            </p>
                        </div>
                    {/foreach}
                {else}
                    {foreach $yd.pickup_points as $st => $store}
                        <div class="sidebar-row ty-yad-store
                            {if !empty($shipping_data.count_points) && $smarty.foreach.st.iteration > $shipping_data.count_points}hidden{/if}">
                            <div class="control-group">
                                <div id="pickup_stores" class="controls">
                                    <label for="store_{$group_key}_{$shipping_id}_{$store.id}" class="radio">
                                        <input type="radio" name="select_yad_office[{$group_key}][{$shipping_id}]" value="{$store.id}" {if $select_id == $store.id}checked="checked"{/if} id="store_{$group_key}_{$shipping_id}_{$store.id}" class="cm-submit cm-ajax cm-skip-validation" data-ca-dispatch="dispatch[order_management.update_shipping]">
                                        {$store.name} ({$yd.deliveries_info[$store.partnerId].name})
                                    </label>
                                    <p class="muted">
                                        {if $store.address} {$store.address.shortAddressString}{/if}
                                    </p>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                {/if}
            {elseif $yd.courier_points}
                {$shipping_id = $shipping.shipping_id}
                {$select_id = $group.chosen_shippings.$group_key.point_id}
                {$stores_count = $yd.courier_points|count}
                {if $stores_count == 1}
                    {foreach $yd.courier_points as $courier_id => $store}
                        <div class="sidebar-row ty-yd-store">
                            <input
                                type="hidden"
                                name="select_yad_courier[{$group_key}][{$shipping_id}]"
                                value="{$courier_id}"
                                id="store_{$group_key}_{$shipping_id}_{$courier_id}"
                            >
                            {$store.name}
                        </div>
                    {/foreach}
                {else}
                    {foreach $yd.courier_points as $courier_id => $store}
                        <div class="sidebar-row ty-yd-store" {if !empty($shipping_data.count_points) && $smarty.foreach.st.iteration > $shipping_data.count_points} style="display: none;"{/if}>
                            <div class="control-group">
                                <div id="courier_stores" class="controls">
                                    <label for="store_{$group_key}_{$shipping_id}_{$courier_id}" class="radio">
                                        <input type="radio" name="select_yad_courier[{$group_key}][{$shipping_id}]" value="{$courier_id}" {if $select_id == $courier_id}checked="checked"{/if} id="store_{$group_key}_{$shipping_id}_{$courier_id}" class="cm-submit cm-ajax cm-skip-validation" data-ca-dispatch="dispatch[order_management.update_shipping]">
                                        {$store.delivery.partner.name} - {include file="common/price.tpl" value=$store.cost.deliveryForCustomer}
                                    </label>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                {/if}
            {/if}
        {/foreach}
    {/foreach}
{/if}