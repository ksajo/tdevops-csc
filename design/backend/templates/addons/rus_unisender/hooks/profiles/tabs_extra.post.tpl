{if $user_data.user_type == "C" && $show_tab_send_sms}
<div id="content_message" class="cm-hide-save-button">
    <h4 class="subheader ">{__("addons.rus_unisender.add_message_to_unisender")}</h4>
    <div class="control-group">
        <label class="control-label cm-mask-phone-label" for="elm_profile_phone">{__("phone")}: </label>
        <div class="controls">
            <input id="elm_profile_phone" class="cm-mask-phone" type="text"  name="sms_data[phone]" value="{$user_data['phone']}">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="elm_profile_sms">{__("addons.rus_unisender.sms_message")}: </label>
        <div class="controls">
            <textarea id="text_sms" rows="3" cols="32" name="sms_data[text]"></textarea>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="elm_button_send_sms"></label>
        <div class="controls">
            <a data-ca-dispatch="dispatch[unisender.send_sms]" data-ca-target-form="profile_form" data-ca-target-id="content_message" id="button_send_sms" class="btn cm-submit cm-ajax">{__("send")}</a>
        </div>
    </div>
<!--content_message--></div>
{/if}
