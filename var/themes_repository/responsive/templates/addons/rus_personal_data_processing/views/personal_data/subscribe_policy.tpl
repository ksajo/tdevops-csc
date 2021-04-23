{if $subscribe_text_policy}
    <div id="subscribe_policy" class="cm-subscribe-policy ty-footer-form-block-policy__input" data-ca-processing-personal-data-allow-autoclick="{if $request_active_consent}true{else}false{/if}">
        <p class="ty-lable-subscribe-policy__wrapper">
        {if $request_active_consent}
            {$checkbox_uniq_id = "accept_subscribe_policy_"|uniqid}

            <input id="{$checkbox_uniq_id}" type="checkbox" data-ca-error-message-target-node="#{$checkbox_uniq_id}_error_message_target" />
            <label for="{$checkbox_uniq_id}" class="cm-required control-label ty-lable-subscribe-policy">
                {$subscribe_text_policy nofilter}
            </label>
            <span id="{$checkbox_uniq_id}_error_message_target"></span>
        {else}
            {if $autoclicked}
                <label for="elm_personal_data" class="control-label ty-lable-subscribe-policy">{$subscribe_text_policy nofilter}</label>
            {/if}
        {/if}
        </p>
    </div>
{else}
    <input type="hidden" id="check_personal_data" value="Y" class="cm-subscribe-personal-data" />
{/if}