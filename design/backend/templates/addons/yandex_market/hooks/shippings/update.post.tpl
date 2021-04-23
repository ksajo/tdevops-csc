
<div class="control-group">
    <label class="control-label" for="elm_ym_shipping_type">{__("yandex_market.shipping_type")}</label>
    <div class="controls">
        <select name="shipping_data[ym_shipping_type]" id="elm_ym_shipping_type" >
            <option value=""> -- </option>
            {foreach from=$ym_shipping_types key=key item=name}
                <option value="{$key}" {if $shipping.ym_shipping_type == $key}selected="selected"{/if}>{$name}</option>
            {/foreach}
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_ym_outlet_ids">{__("yandex_market.shipping_outlets")}</label>
    <div class="controls">
        <input type="text" name="shipping_data[ym_outlet_ids]" id="elm_ym_outlet_ids" size="30" value="{$shipping.ym_outlet_ids}" class="input-large" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_ym_from_date">{__("yandex_market.shipping_from_date")}</label>
    <div class="controls">
        <input type="text" name="shipping_data[ym_from_date]" id="elm_ym_from_date" size="30" value="{$shipping.ym_from_date}" class="input-medium" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_ym_to_date">{__("yandex_market.shipping_to_date")}</label>
    <div class="controls">
        <input type="text" name="shipping_data[ym_to_date]" id="elm_ym_to_date" size="30" value="{$shipping.ym_to_date}" class="input-medium" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_ym_order_before">{__("yandex_market.order_before")}</label>
    <div class="controls">
        <input type="text" name="shipping_data[ym_order_before]" id="elm_ym_order_before" size="30" value="{$shipping.ym_order_before}" class="input-medium" />
    </div>
</div>
