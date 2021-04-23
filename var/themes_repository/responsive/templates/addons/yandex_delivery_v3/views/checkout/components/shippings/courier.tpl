<label for="office_{$group_key}_{$shipping_id}_{$store.tariffId}"
       class="ty-one-store js-pickup-search-block-{$group_key} {if $old_office_id == $store.tariffId || $store_count == 1}ty-yad-office__selected{/if} "
>
    <input
            type="radio"
            name="select_yad_courier[{$group_key}][{$shipping_id}]"
            value="{$store.tariffId}"
            {if $old_office_id == $store.tariffId || $store_count == 1}
                checked="checked"
            {/if}
            class="cm-yad-select-store ty-yad-office__radio-{$group_key} ty-valign"
            id="office_{$group_key}_{$shipping_id}_{$store.tariffId}"
            data-ca-pickup-select-office="true"
            data-ca-shipping-id="{$shipping_id}"
            data-ca-group-key="{$group_key}"
            data-ca-location-id="{$store.tariffId}"
    />

    <div class="ty-yad-store__label ty-one-store__label">
        <p class="ty-one-store__name">
            <span class="ty-one-store__name-text">{$store.delivery.partner.name}</span>
        </p>

        <div class="ty-one-store__description">
            {if $store.cost.deliveryForCustomer}
                {__("shipping_cost")}:{include file="common/price.tpl"
                                        value=$store.cost.deliveryForCustomer
                                        class="ty-nowrap"
                                      }
            {/if}
            <br />
        </div>
    </div>
</label>
