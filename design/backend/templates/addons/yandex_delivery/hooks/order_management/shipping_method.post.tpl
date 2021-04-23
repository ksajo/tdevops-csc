{if $product_groups}

    {script src="js/addons/yandex_delivery/yandex.js"}

    {foreach from=$product_groups key=group_key item=group}
        {if $group.shippings && !$group.shipping_no_required}
            {assign var="shipping_data" value=$group.chosen_shippings.$group_key.service_params}

            {foreach from=$group.shippings item=shipping}
                {if $cart.chosen_shipping.$group_key == $shipping.shipping_id}

                    {$yd = $yandex_delivery.$group_key[$shipping.shipping_id]}

                    {if $yd.pickup_points}
                        {$shipping_id = $shipping.shipping_id}
                        {$select_id = $group.chosen_shippings.$group_key.point_id}
                        {$stores_count = $yd.pickup_points|count}

                        {if $stores_count == 1}
                            {foreach from=$yd.pickup_points item=store}
                                <div class="sidebar-row ty-yd-store">
                                    <input type="hidden" name="select_yd_store[{$group_key}][{$shipping_id}]" value="{$store.id}" id="store_{$group_key}_{$shipping_id}_{$store.id}">
                                    {$store.name}
                                    <p class="muted">
                                        {if $store.full_address}{$store.full_address}{/if}
                                    </p>
                                </div>
                            {/foreach}
                        {else}

                            {foreach from=$yd.pickup_points item=store name=st}

                                <div class="sidebar-row ty-yd-store" {if !empty($shipping_data.count_points) && $smarty.foreach.st.iteration > $shipping_data.count_points} style="display: none;"{/if}>
                                    <div class="control-group">
                                        <div id="pickup_stores" class="controls">
                                            <label for="store_{$group_key}_{$shipping_id}_{$store.id}" class="radio">
                                                <input type="radio" name="select_yd_store[{$group_key}][{$shipping_id}]" value="{$store.id}" {if $select_id == $store.id}checked="checked"{/if} id="store_{$group_key}_{$shipping_id}_{$store.id}" class="cm-submit cm-ajax cm-skip-validation" data-ca-dispatch="dispatch[order_management.update_shipping]">
                                                {$store.name} ({$yd.deliveries[$store.delivery_id].delivery_name})
                                            </label>
                                            <p class="muted">
                                                {if $store.full_address} {$store.full_address}{/if}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        {/if}

                        {if !empty($shipping_data.count_points) && $stores_count > $shipping_data.count_points}
                            <div class="ty-yd-show-all">
                                <a class="cm-combination ty-cart-content__detailed-link detailed-link ty-yd-show_all__link cm-show-all-point">{__("yandex_delivery.all_point")}</a>
                            </div>
                        {/if}

                    {elseif $yd.courier_points}
                        {$shipping_id = $shipping.shipping_id}
                        {$select_id = $group.chosen_shippings.$group_key.point_id}
                        {$stores_count = $yd.courier_points|count}

                        {if $stores_count == 1}
                            {foreach from=$yd.courier_points item=store key=courier_id}
                                <div class="sidebar-row ty-yd-store">
                                    <input type="hidden" name="select_yd_courier[{$group_key}][{$shipping_id}]" value="{$courier_id}" id="store_{$group_key}_{$shipping_id}_{$courier_id}">
                                    {$store.name}
                                </div>
                            {/foreach}
                        {else}

                            {foreach from=$yd.courier_points item=store name=st key=courier_id}

                                <div class="sidebar-row ty-yd-store" {if !empty($shipping_data.count_points) && $smarty.foreach.st.iteration > $shipping_data.count_points} style="display: none;"{/if}>
                                    <div class="control-group">
                                        <div id="courier_stores" class="controls">
                                            <label for="store_{$group_key}_{$shipping_id}_{$courier_id}" class="radio">
                                                <input type="radio" name="select_yd_courier[{$group_key}][{$shipping_id}]" value="{$courier_id}" {if $select_id == $courier_id}checked="checked"{/if} id="store_{$group_key}_{$shipping_id}_{$courier_id}" class="cm-submit cm-ajax cm-skip-validation" data-ca-dispatch="dispatch[order_management.update_shipping]">
                                                {$store.name} - {include file="common/price.tpl" value=$store.costWithRules}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        {/if}

                        {if !empty($shipping_data.count_points) && $stores_count > $shipping_data.count_points}
                            <div class="ty-yd-show-all">
                                <a class="cm-combination ty-cart-content__detailed-link detailed-link ty-yd-show_all__link cm-show-all-point">{__("yandex_delivery.all_point")}</a>
                            </div>
                        {/if}
                    {/if}
                {/if}
            {/foreach}
        {/if}
    {/foreach}
{/if}