{$map_container = "ya_map_container_`$shipping_id`"}
{$store_count = $shipping.pickup_points|count}
{$old_office_id = $shipping.selected_point}

<div class="litecheckout__item ty-checkout-select-store__map-full-div pickup pickup--list">
    {foreach $shipping.pickup_points as $pickup_point}
        {capture name="marker_content"}
            <div class="litecheckout-ya-baloon">
                <strong class="litecheckout-ya-baloon__store-name">{$pickup_point.address.shortAddressString}</strong>
                {if $pickup_point.address.addressString}
                    <p class="litecheckout-ya-baloon__store-address">{$pickup_point.address.addressString nofilter}</p>
                {/if}
                <p class="litecheckout-ya-baloon__select-row">
                    <a data-ca-shipping-id="{$shipping.shipping_id}"
                       data-ca-group-key="{$group_key}"
                       data-ca-location-id="{$pickup_point.id}"
                       data-ca-target-map-id="{$map_container}"
                       class="cm-yad-select-location ty-btn ty-btn__primary text-button ty-width-full"
                    >{__("select")}</a>
                </p>
                {if $pickup_point.phones}
                    {$phone = $pickup_point.phones|reset}
                    <p class="litecheckout-ya-baloon__store-phone"><a href="tel:{$phone.number nofilter}">{$phone.number nofilter}</a></p>
                {/if}
                {if $pickup_point.work_time}<p class="litecheckout-ya-baloon__store-time">{include file="addons/yandex_delivery_v3/views/yandex_delivery/components/schedules.tpl" schedules=$pickup_point.work_time}</p>{/if}
                {if $pickup_point.instruction}<div class="litecheckout-ya-baloon__store-description">{$pickup_point.instruction nofilter}</div>{/if}
            </div>
        {/capture}
        <div class="cm-rus-yad-map-marker-{$shipping.shipping_id} hidden"
             data-ca-geo-map-marker-lat="{$pickup_point.coordY}"
             data-ca-geo-map-marker-lng="{$pickup_point.coordX}"
                {if $old_office_id == $pickup_point.Code || $store_count == 1}
                    data-ca-geo-map-marker-selected="true"
                {/if}
        >{$smarty.capture.marker_content nofilter}</div>

        {if $old_office_id == $pickup_point.id}
        <div class="ty-checkout-select-store pickup__offices-wrapper visible-phone pickup__offices-wrapper--near-map">
            {* List *}
            <div class="litecheckout__fields-row litecheckout__fields-row--wrapped pickup__offices pickup__offices--list pickup__offices--list-no-height">
                {include file="addons/yandex_delivery_v3/views/checkout/components/shippings/pickup_point.tpl"
                    store = $pickup_point
                    delivery = $shipping.deliveries_info.$partner_id
                }
            </div>
            {* End of List *}
        </div>
        {/if}
    {/foreach}
    {* For mobiles; List wrapper with selected pickup item *}

    {* For mobiles; button for popup with pickup points *}
    <button class="ty-btn ty-btn__secondary cm-open-pickups pickup__open-pickupups-btn visible-phone"
        data-ca-title="{__("lite_checkout.choose_from_list")}"
        data-ca-target=".pickup__offices-wrapper-open"
        type="button"
    >{__('lite_checkout.choose_from_list')}</button>
    <span class="visible-phone cm-open-pickups-msg"></span>
    {* For mobiles; button for popup with pickup points *}

    {* List wrapper *}
    <div class="ty-checkout-select-store pickup__offices-wrapper pickup__offices-wrapper-open hidden-phone">

        {* Search *}
        <div class="pickup__search">
            <div class="pickup__search-field litecheckout__field">
                <input type="text"
                       id="pickup-search_{$group_key}"
                       class="litecheckout__input js-pickup-search-input"
                       placeholder=" "
                       value=""
                       data-ca-pickup-group-key="{$group_key}"
                />
                <label class="litecheckout__label" for="pickup-search_{$group_key}">{__("search")}</label>
            </div>
        </div>
        {* End of Search *}

        {* List *}
        <label for="pickup_office_list"
               class="cm-required cm-multiple-radios hidden"
               data-ca-validator-error-message="{__("pickup_point_not_selected")}"></label>
        <div class="litecheckout__fields-row litecheckout__fields-row--wrapped pickup__offices pickup__offices--list"
             id="pickup_office_list"
             data-ca-error-message-target-node-change-on-screen="xs,xs-large,sm"
             data-ca-error-message-target-node-after-mode="true"
             data-ca-error-message-target-node-on-screen=".cm-open-pickups-msg"
             data-ca-error-message-target-node=".pickup__offices--list"
        >
            {foreach $shipping.pickup_points as $pickup_point}
                {$partner_id = $pickup_point.partnerId}
                {include file="addons/yandex_delivery_v3/views/checkout/components/shippings/pickup_point.tpl"
                    store = $pickup_point
                    delivery = $shipping.deliveries_info.$partner_id
                }
            {/foreach}
        </div>
        {* End of List *}

    </div>
    {* End of List wrapper *}

</div>