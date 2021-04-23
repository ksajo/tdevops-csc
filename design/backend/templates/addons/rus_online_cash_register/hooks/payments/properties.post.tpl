<div class="control-group">
    <label class="control-label" for="elm_payment_rus_online_cash_register_{$id}">{__("rus_online_cash_register.payment_use_online_cash_register")}:</label>
    <div class="controls">
        {if $cash_register_payments|count > 1}
            <select name="payment_data[cash_register_payment_id]" id="elm_payment_rus_online_cash_register_{$id}">
                <option value=""> --- </option>
                {foreach from=$cash_register_payments key="item_id" item="item"}
                    <option {if isset($cash_register_payment_id) && $cash_register_payment_id == $item_id}selected="selected"{/if} value="{$item_id}">{$item.name}</option>
                {/foreach}
            </select>
        {else}
            <input type="hidden" name="payment_data[cash_register_payment_id]" value="">
            <input type="checkbox" name="payment_data[cash_register_payment_id]" {if $cash_register_payment_id == $cash_register_payments|key}checked="checked"{/if} id="elm_payment_rus_online_cash_register_{$id}" value="{$cash_register_payments|key}" onclick="Tygh.$('#tab_atol_{$id}').toggleBy();">
        {/if}
    </div>
</div>