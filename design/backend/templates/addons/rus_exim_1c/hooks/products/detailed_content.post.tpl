{include file="common/subheader.tpl" title=__("1c.data_1c") target="#exim_1c"}
<div id="exim_1c" class="in collapse">
    <div class="control-group">
        <label for="external_id" class="control-label">{__("1c.external_id")}:</label>
        <div class="controls">
        <input type="text" name="product_data[external_id]" id="product_external_id" size="55" value="{$product_data.external_id}" class="input-text-large" />
        </div>
    </div>

    <div class="control-group">
        <label for="product_update_1c" class="control-label">{__("1c.update_1c")}:</label>
        <div class="controls">
		    <input type="hidden" name="product_data[update_1c]" value="N" />
            <input type="checkbox" name="product_data[update_1c]" id="product_update_1c" value="Y" {if $product_data.update_1c == "Y" || $runtime.mode == "add"}checked="checked"{/if} />
        </div>
    </div>
</div>