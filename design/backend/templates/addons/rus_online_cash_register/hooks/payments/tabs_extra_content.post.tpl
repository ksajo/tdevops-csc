<div id="content_tab_atol_{$id}" class="hidden">

    <div class="control-group">
        <label class="control-label" for="atol_currency_{$id}">{__("currency")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][atol][currency]" id="atol_currency_{$id}">
                {if $currencies}
                    {foreach $currencies as $code => $currency}
                        <option value="{$code}"
                                {if isset($payment.processor_params.atol.currency) && $payment.processor_params.atol.currency === $code}selected="selected"{/if}
                        >{$currency.description}</option>
                    {/foreach}
                {/if}
            </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="atol_sno_{$id}">{__("rus_online_cash_register.sno")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][atol][sno]" id="atol_sno_{$id}">
                {if $cash_register_sno}
                    {foreach $cash_register_sno as $code => $sno_item}
                        <option value="{$code}"
                                {if isset($payment.processor_params.atol.sno) && $payment.processor_params.atol.sno == $code}selected="selected"{/if}
                        >{$sno_item}</option>
                    {/foreach}
                {/if}
            </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="atol_api_version_{$id}">{__("rus_online_cash_register.api_version")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][atol][api_version]" id="atol_api_version_{$id}">
                <option value="4" {if isset($payment.processor_params.atol.api_version) && $payment.processor_params.atol.api_version == __("rus_online_cash_register.api_version_4")}selected="selected"{/if}>{__("rus_online_cash_register.api_version_4")}</option>
                <option value="3" {if isset($payment.processor_params.atol.api_version) && $payment.processor_params.atol.api_version == __("rus_online_cash_register.api_version_3")}selected="selected"{/if}>{__("rus_online_cash_register.api_version_3")}</option>
            </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="atol_mode_{$id}">{__("rus_online_cash_register.mode")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][atol][mode]" id="atol_mode_{$id}">
                <option value="test" {if isset($payment.processor_params.atol.mode) && $payment.processor_params.atol.mode == __("rus_online_cash_register.mode_test")}selected="selected"{/if}>{__("rus_online_cash_register.mode_test")}</option>
                <option value="live" {if isset($payment.processor_params.atol.mode) && $payment.processor_params.atol.mode == __("rus_online_cash_register.mode_live")}selected="selected"{/if}>{__("rus_online_cash_register.mode_live")}</option>
            </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="atol_inn_{$id}">{__("rus_online_cash_register.atol_inn")}:</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][atol][atol_inn]" id="atol_inn_{$id}" {if isset($payment.processor_params.atol.atol_inn)}value="{$payment.processor_params.atol.atol_inn}"{else}value=""{/if} class="input-text" size="60" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="atol_group_code_{$id}">{__("rus_online_cash_register.atol_group_code")}:</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][atol][atol_group_code]" id="atol_group_code_{$id}" {if isset($payment.processor_params.atol.atol_group_code)}value="{$payment.processor_params.atol.atol_group_code}"{else}value=""{/if} class="input-text" size="60" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="atol_payment_addess_{$id}">{__("rus_online_cash_register.atol_payment_address")}:</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][atol][atol_payment_address]" id="atol_payment_addess_{$id}" {if isset($payment.processor_params.atol.atol_payment_address)}value="{$payment.processor_params.atol.atol_payment_address}"{else}value=""{/if} class="input-text" size="60" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="atol_login_{$id}">{__("rus_online_cash_register.atol_login")}:</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][atol][atol_login]" id="atol_login_{$id}" {if isset($payment.processor_params.atol.atol_login)}value="{$payment.processor_params.atol.atol_login}"{else}value=""{/if} class="input-text" size="60" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="atol_password_{$id}">{__("rus_online_cash_register.atol_password")}:</label>
        <div class="controls">
            <input type="password" name="payment_data[processor_params][atol][atol_password]" id="atol_password_{$id}" {if isset($payment.processor_params.atol.atol_password)}value="{$payment.processor_params.atol.atol_password}"{else}value=""{/if}   size="60">
        </div>
    </div>

    <div class="control-group setting-wide">
        <div class="controls">
            {include file="buttons/button.tpl" but_id="vendor_connect_link" but_role="submit" but_meta="btn-primary" but_name="dispatch[online_cash_register.check_connection]" but_text=__("rus_online_cash_register.settings.test_connect")}
        </div>
    </div>

</div>