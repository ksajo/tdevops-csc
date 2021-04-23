<div class="ty-sdek-checkout-select-office" id="sdek_offices">

{foreach from=$sdek_offices item=office name=item}

    <div class="ty-sdek-office{if $old_office_id == $office.Code || $office_count == 1} ty-sdek-office__selected{/if} ty-sdek-office__apply">
        <input
            type="radio"
            name="select_office[{$group_key}][{$shipping_id}]"
            value="{$office.Code}"
            {if $old_office_id == $office.Code || $office_count == 1}
                checked="checked"
            {/if}
            id="office_{$group_key}_{$shipping_id}_{$office.Code}"
            class="cm-sdek-select-store ty-sdek-office__radio-{$group_key} ty-valign"
            data-ca-pickup-select-office="true"
            data-ca-shipping-id="{$shipping_id}"
            data-ca-group-key="{$group_key}"
            data-ca-location-id="{$office.Code}"
        />
        <div class="ty-sdek-store__label">
            <a data-ca-latitude="{$office.coordY}" data-ca-longitude="{$office.coordX}" class="cm-sdek-view-location ty-sdek-icon-location"></a>
            <label for="office_{$group_key}_{$shipping_id}_{$office.Code}" class="ty-one-office__name">
                {$office.Name}<i class="ty-sdek-name-office-{$group_key}-{$office.Code} ty-icon-ok {if $old_office_id != $office.Code}ty-sdek-office-point-disabled{/if}"></i>
                <div>
                    <span class="ty-one-office__address">{$office.Address}</span><br />
                    {if $office.Phone}<span class="ty-one-office__phone"><br />{__("phone")}: {$office.Phone}</span>{/if}
                    <span class="ty-one-office__worktime">{$office.WorkTime}</span><br />
                    <span class="ty-one-office__note">{$office.Note}</span>
                </div>
            </label>
        </div>
    </div>

{/foreach}


<!--sdek_offices--></div>
