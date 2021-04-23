<label for="office_{$group_key}_{$shipping_id}_{$store.id}"
       class="ty-one-store js-pickup-search-block-{$group_key} {if $old_office_id == $store.id || $store_count == 1}ty-yad-office__selected{/if} "
>
    <input
            type="radio"
            name="select_yad_office[{$group_key}][{$shipping_id}]"
            value="{$store.id}"
            {if $old_office_id == $store.id || $store_count == 1}
                checked="checked"
            {/if}
            class="cm-yad-select-store ty-yad-office__radio-{$group_key} ty-valign"
            id="office_{$group_key}_{$shipping_id}_{$store.id}"
            data-ca-pickup-select-office="true"
            data-ca-shipping-id="{$shipping_id}"
            data-ca-group-key="{$group_key}"
            data-ca-location-id="{$store.id}"
    />

    <div class="ty-yad-store__label ty-one-store__label">
        <p class="ty-one-store__name">
            <span class="ty-one-store__name-text">{$store.name}</span>
        </p>

        <div class="ty-one-store__description">
            {if $delivery}
                <span class="ty-one-office__name">{$delivery.name}</span>
                <br />
            {/if}
            {if $store.address}
                <span class="ty-one-office__address">{$store.address.addressString}</span>
                <br />
            {/if}
            {if $store.phones}
                {$phone = $store.phones|reset}
                <span class="ty-one-office__worktime">{$phone.number}</span>
                <br />
            {/if}
        </div>
    </div>
</label>
