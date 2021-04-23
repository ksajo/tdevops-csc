{if $show_send_sms_order || $show_send_sms_shipment}
<li><a><label for="{$prefix}_{$id}_notify_unisender_users">
    <input type="checkbox" name="__notify_unisender_users" id="{$prefix}_{$id}_notify_unisender_users" value="Y" checked="checked" onclick="Tygh.$('input[name=__notify_unisender_users]').prop('checked', this.checked);" />
    {__("addons.rus_unisender.notify_unisender_users")}</label></a>
</li>
{/if}