{* rus_build_kupivkredit dbazhenov *}
<p>{__("rus_kupivkredit.settings_instructions", ["[url]" => $smarty.const.KVK_INSTRUCTION_URL])}</p>
<hr>
<div class="control-group">
    <label class="control-label" for="kupivkredit_shop_id">{__("kupivkredit_shop_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][kvk_shop_id]" id="kupivkredit_shop_id" value="{$processor_params.kvk_shop_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="kupivkredit_show_case_id">{__("rus_kupivkredit.show_case_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][kvk_show_case_id]" id="kupivkredit_show_case_id" value="{$processor_params.kvk_show_case_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="kupivkredit_test">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][test]" id="kupivkredit_test">
            <option value="Y" {if $processor_params.test == "Y"}selected="selected"{/if}>{__("test")}</option>
            <option value="N" {if $processor_params.test == "N"}selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>
