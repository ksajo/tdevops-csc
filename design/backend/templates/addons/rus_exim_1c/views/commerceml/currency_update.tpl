
<div id="currencies_commerceml">
    <form action="{""|fn_url}" name="currencies_form" method="post" class=" form-horizontal form-edit">
        <fieldset>
            <input type="hidden" name="data_currencies[currency_key]" value="{$commerceml_currency.id}" />

            <div class="control-group">
                <label class="control-label" for="currency_id">{__("addons.commerceml.name_currency")}:</label>
                <div class="controls">
                    {if !""|fn_allow_save_object:"":true}
                        {foreach from=$data_currencies item="currency"}
                            {if $currency.currency_id == $commerceml_currency.currency_id}
                                <span class="shift-input">{$currency.description}</span>
                            {/if}
                        {/foreach}
                    {else}
                        <select id="currency_id" name="data_currencies[currency_id]">
                            {foreach from=$data_currencies item="currency"}
                                <option {if $currency.currency_id == $commerceml_currency.currency_id}selected="selected"{/if} value="{$currency.currency_id}">{$currency.description}</option>
                            {/foreach}
                        </select>
                    {/if}
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="currency_id">{__("addons.commerceml.commerceml_currency")}:</label>
                <div class="controls">
                    {if !""|fn_allow_save_object:"":true}
                        <span class="shift-input">{$commerceml_currency.commerceml_currency}</span>
                    {else}
                        <input type="text" name="data_currencies[commerceml_currency]" size="18" value="{$commerceml_currency.commerceml_currency}" />
                    {/if}
                </div>
            </div>
        </fieldset>

        {if ""|fn_allow_save_object:"":true}
        <div class="buttons-container">
            {include file="buttons/save_cancel.tpl" but_name="dispatch[commerceml.save_currencies_data]" cancel_action="close" save="currencies_commerceml"}
        </div>
        {/if}

    </form>
<!--currencies_commerceml--></div>
