<div class="litecheckout__group">
    <div class="litecheckout__field litecheckout__field--xlarge">
        <input type="text" class="litecheckout__input" name="payment_info[address]" id="address_customer" {if $account_params.address}value="{$account_params.address}"{else}value=""{/if} size="20" placeholder=" " />
        <label for="address_customer" class="ty-control-group__title litecheckout__label">{__("address")}</label>
    </div>

    <div class="litecheckout__field litecheckout__field--xsmall">
        <input type="text" class="litecheckout__input" name="payment_info[zip_postal_code]" id="zip_postal_code" {if $account_params.zip_postal_code}value="{$account_params.zip_postal_code}"{else}value=""{/if} size="6" placeholder=" " />
        <label for="zip_postal_code" class="ty-control-group__title litecheckout__label">{__("zip_postal_code")}</label>
    </div>

    <div class="litecheckout__field litecheckout__field--small">
        <input type="tel" class="litecheckout__input cm-mask-phone" name="payment_info[phone]" id="phone_customer" {if $account_params.phone}value="{$account_params.phone}"{else}value=""{/if} size="20" placeholder=" " />
        <label for="phone_customer" class="ty-control-group__title litecheckout__label cm-mask-phone-label">{__("phone")}</label>
    </div>

    <div class="litecheckout__field litecheckout__field--small">
        <input type="text" class="litecheckout__input" name="payment_info[organization_customer]" id="organization_customer" {if $account_params.organization_customer}value="{$account_params.organization_customer}"{else}value=""{/if} size="20" placeholder=" " />
        <label for="organization_customer" class="ty-control-group__title litecheckout__label">{__("addons.rus_payments.organization_customer")}</label>
    </div>

    <div class="litecheckout__field litecheckout__field--small">
        <input type="text" class="litecheckout__input" name="payment_info[inn_customer]" id="inn_customer" {if $account_params.inn_customer}value="{$account_params.inn_customer}"{else}value=""{/if} size="20" maxlength="12" placeholder=" " />
        <label for="inn_customer" class="ty-control-group__title litecheckout__label">{__("inn_customer")}</label>
    </div>

    <div class="litecheckout__field">
        <textarea id="bank_details" size="35"  cols="30" rows="5" name="payment_info[bank_details]" value="" class="ty-input-textarea cm-autocomplete-off litecheckout__input litecheckout__input--textarea" placeholder=" ">{if $account_params.bank_details}{$account_params.bank_details}{/if}</textarea>
        <label for="bank_details" class="ty-control-group__title litecheckout__label">{__("addons.rus_payments.bank_details")}</label>
    </div>
</div>
