<label for="office_{$group_key}_{$shipping_id}_{$store.Code}"
       class="ty-one-store js-pickup-search-block-{$group_key} {if $old_office_id == $store.Code || $store_count == 1}ty-sdek-office__selected{/if} "
>
    <input
        type="radio"
        name="select_office[{$group_key}][{$shipping_id}]"
        value="{$store.Code}"
        {if $old_office_id == $store.Code || $store_count == 1}
            checked="checked"
        {/if}
        class="cm-sdek-select-store ty-sdek-office__radio-{$group_key} ty-valign"
        id="office_{$group_key}_{$shipping_id}_{$store.Code}"
        data-ca-pickup-select-office="true"
        data-ca-shipping-id="{$shipping_id}"
        data-ca-group-key="{$group_key}"
        data-ca-location-id="{$store.Code}"
    />

    <div class="ty-sdek-store__label ty-one-store__label">
        <p class="ty-one-store__name">
            <span class="ty-one-store__name-text">{$store.Name}</span>
        </p>

        <div class="ty-one-store__description">
            {if $store.Address}
                <span class="ty-one-office__address">{$store.Address}</span>
                <br />
            {/if}
            {if $store.WorkTime}
                <span class="ty-one-office__worktime">{$store.WorkTime}</span>
                <br />
            {/if}
            {if $store.NearestStation}
                <span class="ty-one-office__worktime">{__('lite_checkout.nearest_station')}: {$store.NearestStation}</span>
                <br />
            {/if}
            {if $store.Phone}
                <span class="ty-one-office__worktime">{$store.Phone}</span>
                <br />
            {/if}
        </div>
    </div>
</label>
