{include file="common/subheader.tpl" title=__("information") target="#yandex_checkout_for_marketplaces_payment_instruction_`$payment_id`"}
{if $settings.Security.secure_storefront === "YesNo::YES"|enum}
    {$storefront_url = fn_url("", "SiteArea::STOREFRONT"|enum, "https")|replace:$config.customer_index:""|rtrim:"/"}
{else}
    {$storefront_url = fn_url("", "SiteArea::STOREFRONT"|enum, "http")|replace:$config.customer_index:""|rtrim:"/"}
{/if}
<div id="yandex_checkout_for_marketplaces_payment_instruction_{$payment_id}" class="in collapse">
    {include file="common/widget_copy.tpl"
        widget_copy_text=__("yandex_checkout.url_for_payment_notifications")
        widget_copy_code_text="{$storefront_url}/yoomoney/check_payment"
    }

    {if fn_get_storefront_protocol() != "https"}
        {__("yandex_checkout.server_https")}
    {/if}
</div>


{include file="common/subheader.tpl" title=__("settings") target="#yandex_checkout_for_marketplaces_payment_settings_`$payment_id`"}
<div id="yandex_checkout_for_marketplaces_payment_settings_{$payment_id}" class="in collapse">
    <div class="control-group">
        <label class="control-label cm-required" for="shop_id_{$payment_id}">{__("yandex_checkout.shop_id")}:</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][shop_id]" id="shop_id_{$payment_id}" value="{$processor_params.shop_id}" class="input-text-large"  size="60" />
        </div>
        <div class="controls">
            <p class="muted description">{__("yandex_checkout.shop_id_notice")}</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label cm-required" for="scid_{$payment_id}">{__("yandex_checkout.secret_key_api")}:</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][scid]" id="scid_{$payment_id}" value="{$processor_params.scid}" class="input-text-large"  size="60" />
        </div>
        <div class="controls">
            <p class="muted description">{__("yandex_checkout.secret_key_api_notice")}</p>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="elm_send_receipt_{$payment_id}">
            {__("yandex_checkout.send_receipt_to_yandex")}
        </label>
        <input type="hidden" name="payment_data[processor_params][send_receipt]" value="{"YesNo::NO"|enum}" />
        <div class="controls">
            <input type="checkbox"
                   name="payment_data[processor_params][send_receipt]"
                   id="elm_send_receipt_{$payment_id}"
                   value="{"YesNo::YES"|enum}"
                   {if $processor_params.send_receipt == "YesNo::YES"|enum && $processor_params.currency|default:"RUB" == "RUB"}checked="checked"{/if}
            />
        </div>
        <div class="controls">
            <p class="muted description">{__("yandex_checkout.available_only_for_rub")}</p>
        </div>
    </div>
    {$statuses=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses}
    <div class="control-group">
        <label class="control-label" for="yandex_checkout_for_marketplaces_confirmed_order_status_{$payment_id}">{__("yandex_checkout.confirmed_order_status")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][final_success_status]" id="yandex_checkout_for_marketplaces_confirmed_order_status_{$payment_id}">
                {foreach $statuses as $key => $item}
                    <option value="{$key}"
                            {if $processor_params.final_success_status|default:"C" === $key}
                                selected="selected"
                            {/if}
                    >{$item}</option>
                {/foreach}
            </select>
        </div>
        <div class="controls">
            <p class="muted description">{__("yandex.checkout.confirmed_order_status.notice")}</p>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="currency_{$payment_id}">{__("currency")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][currency]" id="currency_{$payment_id}">
                <option value="RUB"{if $processor_params.currency == "RUB"} selected="selected"{/if}>{__("currency_code_rur")}</option>
                <option value="USD"{if $processor_params.currency == "USD"} selected="selected"{/if}>{__("currency_code_usd")}</option>
                <option value="EUR"{if $processor_params.currency == "EUR"} selected="selected"{/if}>{__("currency_code_eur")}</option>
                <option value="UAH"{if $processor_params.currency == "UAH"} selected="selected"{/if}>{__("currency_code_uah")}</option>
                <option value="KZT"{if $processor_params.currency == "KZT"} selected="selected"{/if}>{__("currency_code_kzt")}</option>
                <option value="CNY"{if $processor_params.currency == "CNY"} selected="selected"{/if}>{__("currency_code_cny")}</option>
                <option value="BYN"{if $processor_params.currency == "BYN"} selected="selected"{/if}>{__("currency_code_byn")}</option>
            </select>
        </div>
    </div>
</div>

<script>
    (function(_, $) {
        var $sendReceipt = $('#elm_send_receipt_{$payment_id}');

        $.ceEvent('on', 'ce.commoninit', function () {
            if ($('#currency_{$payment_id}').val() !== 'RUB') {
                $sendReceipt.prop('checked', null).prop('readonly', true).prop('disabled', true);
            } else {
                $sendReceipt.prop('readonly', null).prop('disabled', null);
            }
        });

        $sendReceipt.change(function () {
            if ($('#currency_{$payment_id}').val() !== 'RUB') {
                $sendReceipt.prop('checked', null).prop('readonly', true).prop('disabled', true);
            } else {
                $sendReceipt.prop('readonly', null).prop('disabled', null);
            }
        });

        $('#currency_{$payment_id}').change(function(e) {
            if ($(this).val() !== 'RUB') {
                $sendReceipt.prop('checked', null).prop('readonly', true).prop('disabled', true);
            } else {
                $sendReceipt.prop('readonly', null).prop('disabled', null);
            }
        });
    })(Tygh, Tygh.$);
</script>