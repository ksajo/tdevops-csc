<div class="control-group">
    <label class="control-label" for="elm_rus_tax_type_{$id}">{__("rus_taxes.tax_type")}:</label>
    <div class="controls">
        <select name="tax_data[tax_type]" id="elm_rus_tax_type_{$id}">
            {foreach from=$tax_types key="item_id" item="item"}
                <option {if $tax.tax_type == $item_id}selected="selected"{/if} value="{$item_id}">{$item.name}</option>
            {/foreach}
        </select>
        <p class="muted description">{__("rus_taxes.tax_type.tooltip")}</p>
    </div>
</div>