{if $settings.Security.secure_storefront === "YesNo::YES"|enum}
    {$storefront_url = fn_url('', 'SiteArea::STOREFRONT'|enum, 'https')|replace:$config.customer_index:""|rtrim:"/"}
{else}
    {$storefront_url = fn_url('', 'SiteArea::STOREFRONT'|enum, 'http')|replace:$config.customer_index:""|rtrim:"/"}
{/if}

{$result_url = "{$storefront_url}/payment_notification/result/robokassa"}
{$success_url = "{$storefront_url}/payment_notification/success/robokassa"}
{$fail_url = "{$storefront_url}/payment_notification/fail/robokassa"}

{include file = "common/subheader.tpl"
    title = __("rus_payments.robokassa.technical_preferences")
}

<div class="control-group">
    <label class="control-label">
        Result Url
    </label>
    <div class="controls">
        {include file = "common/widget_copy.tpl"
            widget_copy_code_text = $result_url
            widget_copy_class = "widget-copy--compact"
        }
    </div>
</div>

<div class="control-group">
    <label class="control-label">
        {__("rus_payments.robokassa.notification_url_method", ["[url_type]" => "Result Url"])}
    </label>
    <div class="controls">
        <p class="switch">POST</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label">
        Success Url
    </label>
    <div class="controls">
        {include file = "common/widget_copy.tpl"
            widget_copy_code_text = $success_url
            widget_copy_class = "widget-copy--compact"
        }
    </div>
</div>

<div class="control-group">
    <label class="control-label">
        {__("rus_payments.robokassa.notification_url_method", ["[url_type]" => "Success Url"])}
    </label>
    <div class="controls">
        <p class="switch">GET</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label">
        Fail Url
    </label>
    <div class="controls">
        {include file = "common/widget_copy.tpl"
            widget_copy_code_text = $fail_url
            widget_copy_class = "widget-copy--compact"
        }
    </div>
</div>

<div class="control-group">
    <label class="control-label">
        {__("rus_payments.robokassa.notification_url_method", ["[url_type]" => "Fail Url"])}
    </label>
    <div class="controls">
        <p class="switch">GET</p>
    </div>
</div>

<hr>

<div class="control-group">
    <label class="control-label" for="rbx_merchantid">{__("merchantid")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchantid]" id="rbx_merchantid" value="{$processor_params.merchantid}"  size="60"><a href="#" id="rbx_get_currencies">{__("payments.robokassa.get_currencies")}</a>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="rbx_password1">{__("password1")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][password1]" id="rbx_password1" value="{$processor_params.password1}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="rbx_password2">{__("password2")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][password2]" id="rbx_password2" value="{$processor_params.password2}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="rbx_descr">{__("description")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][details]" id="rbx_descr" value="{$processor_params.details}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="rbx_mode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="rbx_mode">
            <option value="test"{if $processor_params.mode == 'test'} selected="selected"{/if}>{__("test")}</option>
            <option value="live"{if $processor_params.mode == 'live'} selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="rbx_commission">{__("commission")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][commission]" id="rbx_commission">
            <option value="customer"{if $processor_params.commission == 'customer'} selected="selected"{/if}>{__("customer")}</option>
            <option value="admin"{if $processor_params.commission == 'admin'} selected="selected"{/if}>{__("administrator")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency_{$payment_id}">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency_{$payment_id}">
            <option value="RUB"{if $processor_params.currency == "RUB"} selected="selected"{/if}>{__("currency_code_rub")}</option>
            <option value="USD"{if $processor_params.currency == "USD"} selected="selected"{/if}>{__("currency_code_usd")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="rbx_currency">{__("payment_method")}:</label>
    {include file="addons/rus_payments/views/payments/components/cc_processors/robokassa_cur_selectbox.tpl"}
</div>

<div class="control-group">
    <label class="control-label" for="rbx_encoding">{__("rus_payments.encoding_algorithm")}</label>
    <div class="controls">
        <select name="payment_data[processor_params][encoding]" id="rbx_encoding">
            <option value="md5"{if $processor_params.encoding === "md5"} selected="selected"{/if}>{"md5"}</option>
            <option value="sha1"{if $processor_params.encoding === "sha1"} selected="selected"{/if}>{"sha1"}</option>
            <option value="sha256"{if $processor_params.encoding === "sha256"} selected="selected"{/if}>{"sha256"}</option>
            <option value="sha384"{if $processor_params.encoding === "sha384"} selected="selected"{/if}>{"sha384"}</option>
            <option value="sha512"{if $processor_params.encoding === "sha512"} selected="selected"{/if}>{"sha512"}</option>
            <option value="ripemd160"{if $processor_params.encoding === "ripemd160"} selected="selected"{/if}>{"ripemd160"}</option>
        </select>
    </div>
</div>

{include file="common/subheader.tpl" title=__("rus_payments.robokassa.text_status_map") target="#text_status_map"}

<div id="text_status_map" class="in collapse">
    {assign var="statuses" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses}

    <div class="control-group">
        <label class="control-label" for="elm_paid">{__("rus_payments.robokassa.paid")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][paid]" id="elm_paid">
                {foreach $statuses as $k => $s}
                    <option value="{$k}" {if (isset($processor_params.statuses.paid) && $processor_params.statuses.paid == $k) || (!isset($processor_params.statuses.paid) && $k == 'P')}selected="selected"{/if}>{$s}</option>
                {/foreach}
            </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_final">{__("rus_payments.robokassa.final_status")}</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][final]" id="elm_final">
                {foreach $statuses as $key => $status}
                    <option value="{$key}" {if $processor_params.statuses.final|default:"C" === $key} selected="selected"{/if}>
                        {$status}
                    </option>
                {/foreach}
            </select>
        </div>
    </div>
</div>

<script type="text/javascript">
    (function (_, $) {
        $(_.doc).ready(function () {
            fn_get_rbx_currencies();
            $('#rbx_get_currencies').on('click', fn_get_rbx_currencies);
        });

        function fn_get_rbx_currencies() {
            var merchantid = $('#rbx_merchantid').val();
            $.ceAjax('request', '{fn_url("payment_notification.rbx_get_currencies")}', {
                data: {
                    payment: 'robokassa',
                    merchantid: merchantid,
                    result_ids: 'rbx_currency_div',
                    payment_id: {$smarty.request.payment_id},
                }
            });
        }
    })(Tygh, Tygh.$);
</script>