{$old_office_id = $shipping.selected_point}

<div class="litecheckout__item ty-checkout-select-store__map-full-div pickup pickup--list">
    {* List wrapper *}
    <div class="ty-checkout-select-store pickup__offices-wrapper pickup__offices-wrapper-open hidden-phone">
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
            {foreach $shipping.courier_points as $store}
                {include file="addons/yandex_delivery_v3/views/checkout/components/shippings/courier.tpl"
                    store = $store
                }
            {/foreach}
        </div>
        {* End of List *}

    </div>
    {* End of List wrapper *}

</div>