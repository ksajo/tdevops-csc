{if ($settings.Checkout.disable_anonymous_checkout == "Y" && !$auth.user_id) || ($settings.Checkout.disable_anonymous_checkout != "Y" && !$auth.user_id && !$contact_info_population) || $smarty.session.failed_registration == true}
    <div class="controls">
        {$checkbox_uniq_id = "accept_subscribe_policy_"|uniqid}

        <input type="checkbox" id="elm_personal_data" value="Y" data-ca-error-message-target-node="#{$checkbox_uniq_id}_error_message_target" />
        <label class="cm-required" for="elm_personal_data">{__("addons.rus_personal_data_processing.confidentiality")}</label>
        <span id="{$checkbox_uniq_id}_error_message_target"></span>
        <br />
        <span class="ty-policy-description">{$policy_description nofilter}</span>
    </div>
{/if}