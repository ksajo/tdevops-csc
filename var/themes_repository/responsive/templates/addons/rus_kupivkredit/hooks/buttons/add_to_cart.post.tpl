{if $tinkoff_payments_exist && ($settings.Checkout.allow_anonymous_shopping === "allow_shopping" || $auth.user_id)}
	{$c_url = $config.current_url|escape:url}
	{if $quick_view || $but_role == "action"}
		{$but_meta = "kupivkredit-button-mini"}
	{else}
		{$but_meta = "kupivkredit-button"}
	{/if}
	{include file="buttons/button.tpl"
		but_id="kvk_`$but_id`"
		but_text=__("kupivkredit_button")
		but_name="dispatch[checkout.add.kvk_activate.`$obj_id`]"
		but_onclick=$but_onclick
		but_href=$but_href
		but_target=$but_target
		but_role="text"
		but_meta=$but_meta
		but_icon="kupivkredit-icon"
	}
{/if}
