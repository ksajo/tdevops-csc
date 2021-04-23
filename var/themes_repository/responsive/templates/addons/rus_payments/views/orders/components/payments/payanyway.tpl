{if $payment_method.processor_params.payment_system == "post"}
	<div class="litecheckout__fields-row">
		<div class="control-group litecheckout__field litecheckout__field--full">
			<input class="input-text cm-autocomplete-off litecheckout__input" type="text" id="mailofrussia_sender_address" name="payment_info[mailofrussiaSenderAddress]" value="" size="35" placeholder=" " />
			<label class="cm-required litecheckout__label" for="mailofrussia_sender_address">{__("mailofrussiasenderaddress")}</label>
		</div>
		<div class="control-group litecheckout__field">
			<input class="input-text cm-autocomplete-off litecheckout__input" type="text" id="mailofrussia_sender_index" name="payment_info[mailofrussiaSenderIndex]" value="" size="35" placeholder=" " />
			<label class="cm-required litecheckout__label" for="mailofrussia_sender_index">{__("mailofrussiasenderindex")}</label>
		</div>
	</div>
	<div class="litecheckout__fields-row">
		<div class="control-group litecheckout__field litecheckout__field--full">
			<input class="input-text cm-autocomplete-off litecheckout__input" type="text" id="mailofrussia_sender_name" name="payment_info[mailofrussiaSenderName]" value="" size="35" placeholder=" " />
			<label class="cm-required litecheckout__label" for="mailofrussia_sender_name">{__("mailofrussiasendername")}</label>
		</div>
	</div>
{elseif $payment_method.processor_params.payment_system == "moneymail"}
	<div class="litecheckout__fields-row">
		<div class="control-group litecheckout__field litecheckout__field--full">
			<input class="input-text litecheckout__input" type="email" id="buyer_email" name="payment_info[buyerEmail]" value="" size="35" placeholder=" " />
			<label class="cm-required cm-email litecheckout__label" for="buyer_email">{__("buyeremail")}</label>
		</div>
	</div>
{elseif $payment_method.processor_params.payment_system == "euroset"}
	<div class="litecheckout__fields-row">
		<div class="control-group litecheckout__field litecheckout__field--full">
			<input class="input-text cm-autocomplete-off litecheckout__input cm-mask-phone" type="tel" id="rapida_phone" name="payment_info[rapidaPhone]" value="" size="35" placeholder=" " />
			<label class="cm-required cm-regexp litecheckout__label cm-mask-phone-label" for="rapida_phone">{__("rapidaphone")}</label>
		</div>
	</div>
	<script type="text/javascript">
	//<![CDATA[
	(function(_, $) {
		$(document).ready(function() {
			$.ceFormValidator('setRegexp', {
				rapida_phone: {
					regexp: {literal}"^(\\+[0-9]{10,20})$"{/literal},
					message: "{__("error_rapida_phone")|escape:javascript}"
				}
			});
		});
	}(Tygh, Tygh.$));
	//]]>
	</script>
{elseif $payment_method.processor_params.payment_system == "webmoney"}
	<div class="litecheckout__field">
		<label class="cm-required litecheckout__select-label" for="account_id">{__("webmoneyaccountid")}</label>
		<select id="account_id" name="payment_info[accountId]" class="litecheckout__select">
			<option value="2">WMR</option>
			<option value="3">WMZ</option>
			<option value="4">WME</option>
		</select>
	</div>
{/if}