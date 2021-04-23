<div class="control-group">
    <label for="elm_ym_{$field_name}" class="control-label{if $field.required} cm-required{/if}">{__("yml_export.param_$field_name")}:</label>
    <div class="controls">
        <input type="text" readonly="readonly" value="{$access_key}" onclick="this.select()" name="pricelist_data[access_key]">
    </div>
</div>

