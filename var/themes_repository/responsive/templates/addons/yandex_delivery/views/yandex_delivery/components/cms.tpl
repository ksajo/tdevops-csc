{$yd_shippings = $yandex_delivery.$group_key[$shipping.shipping_id]}
{$old_store_id = $yd_shippings.selected_point}
{$store_count = $yd_shippings.pickup_points|count}
{$map_container = "yd_map_$group_key"}
{$store_locations = $yd_shippings.pickup_points}
{$courier_count = $yd_shippings.courier_points|count}
{$courier_locations = $yd_shippings.courier_points}

{include file="addons/yandex_delivery/views/yandex_delivery/components/yandex.tpl"}

{if $yd_shippings.courier_delivery == 'Y' && $courier_locations}

    <div class="ty-yd-select-store__map-wrapper">
        <div class="ty-yd-select-store__map hidden" id="{$map_container}">
        </div>
    </div>

    <div class="ty-yd-select-store">
        {foreach from=$courier_locations item=store name=st key=store_id}

            <div class="ty-yd-store {if $courier_count == 1}ty-yd-store__selected{/if}" {if !empty($shipping.service_params.count_points) && $smarty.foreach.st.iteration > $shipping.service_params.count_points}style="display: none;"{/if}>
                <input type="radio"
                    name="select_yd_courier[{$group_key}][{$shipping.shipping_id}]"
                    value="{$store_id}"
                    {if $old_store_id == $store_id || $courier_count == 1}checked="checked"{/if}
                    id="store_{$group_key}_{$shipping.shipping_id}_{$store_id}"
                    class="ty-yd-store__radio-{$group_key} ty-valign cm-yd-select-store"
                    data-ca-pickup-select-store="true"
                    data-ca-shipping-id="{$shipping.shipping_id}"
                    data-ca-group-key="{$group_key}"
                    data-ca-location-id="{$store_id}">

                <div class="ty-yd-store__label">
                    <label for="store_{$group_key}_{$shipping.shipping_id}_{$store_id}" class="ty-valign ty-yd-store__name">
                        {$store.delivery_name}

                        <div>
                            {if $store.costWithRules}{__("shipping_cost")}: {include file="common/price.tpl" value=$store.costWithRules class="ty-nowrap"}{/if}<br />
                        </div>
                    </label>
                    <a data-ca-scroll="yd_map_{$group_key}" data-ca-group-key="{$group_key}" class="cm-yd-view-location"></a>
                </div>
            </div>
        {/foreach}
    </div>
    {if $shipping.service_params.count_points != 0 && $courier_count > $shipping.service_params.count_points}
        <div class="ty-yd-show-all">
            <a class="cm-combination ty-cart-content__detailed-link detailed-link ty-yd-show_all__link cm-show-all-point">{__("yandex_delivery.all_point")}</a>
        </div>
    {/if}

{elseif $store_locations}

    <div class="ty-yd-select-store__map-wrapper">
        {if $store_count > 1}
            <div class="ty-pickup-location__item-all_stores">
                <div class="ty-pickup-location__item-view">{include file="buttons/button.tpl" but_role="text" but_meta="cm-yd-view-locations ty-btn__secondary" but_text=__("view_all") but_extra="data-ca-scroll={$map_container} data-ca-group-key={$group_key} "}</div>
            </div>
        {/if}
        <div class="ty-yd-select-store__map" id="{$map_container}">
        </div>
    </div>

    <div class="ty-yd-select-store">
        {foreach from=$store_locations item=store name=st key=store_id}

            <div class="ty-yd-store{if $store_count == 1} ty-yd-store__selected{/if}"{if !empty($shipping.service_params.count_points) && $smarty.foreach.st.iteration > $shipping.service_params.count_points} style="display: none;"{/if}>
                <input type="radio"
                    name="select_yd_store[{$group_key}][{$shipping.shipping_id}]"
                    value="{$store_id}"
                    {if $old_store_id == $store_id || $store_count == 1}checked="checked"{/if}
                    id="store_{$group_key}_{$shipping.shipping_id}_{$store_id}"
                    class="ty-yd-store__radio-{$group_key} ty-valign cm-yd-select-store"
                    data-ca-pickup-select-store="true"
                    data-ca-shipping-id="{$shipping.shipping_id}"
                    data-ca-group-key="{$group_key}"
                    data-ca-location-id="{$store_id}">

                <div class="ty-yd-store__label">
                    <a data-ca-scroll="yd_map_{$group_key}" data-ca-latitude="{$store.lat}" data-ca-longitude="{$store.lng}" data-ca-group-key="{$group_key}" class="cm-yd-view-location ty-yd-icon-location"></a>
                    <label for="store_{$group_key}_{$shipping.shipping_id}_{$store_id}" class="ty-valign ty-yd-store__name">
                        {$store.name}

                        <div class="ty-yd-store__description">
                            {$yd_shippings.deliveries[$store.delivery_id].delivery_name}, {if $store.short_address}{$store.short_address}{/if}
                            <br/>
                            {if $store.phone}{__("phone")}: {$store.phone.number}</br>{/if}
                            {if $store.address.comment}
                                <a id="sw_store_description_{$store_id}" class="cm-combination ty-cart-content__detailed-link detailed-link ty-yd-store__detailed-link">{__("description")}</a>
                                <div id="store_description_{$store_id}" class="hidden ty-yd-store__comment">
                                    {if $store.full_address}{$store.full_address}. {/if}
                                    {$store.address.comment nofilter}
                                </div>
                            {/if}
                        </div>
                    </label>
                </div>
            </div>
        {/foreach}
    </div>
    {if $shipping.service_params.count_points != 0 && $store_count > $shipping.service_params.count_points}
        <div class="ty-yd-show-all">
            <a class="cm-combination ty-cart-content__detailed-link detailed-link ty-yd-show_all__link cm-show-all-point">{__("yandex_delivery.all_point")}</a>
        </div>
    {/if}

{/if}