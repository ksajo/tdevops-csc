{script src="js/addons/yml_export/ym_options.js"}
<div class="control-group">
    <label class="control-label" for="elm_option_yml2_type_options_{$id}">{__("yml2_type_options")}</label>
    <div class="controls">
        <select id="elm_option_yml2_type_options_{$id}" name="option_data[yml2_type_options]" class="cm-ym-option-select" data-option-id="{$id}">
            <option value="" {if empty($option_data.yml2_type_options)}selected="selected"{/if}>--</option>
            {foreach from=$yml2_options item="option" key="code"}
                <option value="{$code}" {if $option_data.yml2_type_options == $code}selected="selected"{/if}>{$option['name']}</option>
            {/foreach}
        </select>
    </div>
</div>

{foreach from=$yml2_options item="option" key="option_key"}

    {if !empty($option['types'])}
    <div class="control-group ym-option" id="ym_option_{$id}_{$option_key}" {if $option_data.yml2_type_options != $option_key} style="display: none"{/if}>
        <label class="control-label" for="elm_option_yml2_option_param_{$id}">{__("yml2_type_option_param")}</label>
        <div class="controls">
            <select id="elm_option_yml2_option_param_{$id}_{$option_key}" name="option_data[yml2_option_param]" class="cm-ym-option-type-select" data-option-id="{$id}_{$option_key}" {if $option_data.yml2_type_options != $option_key}disabled="disabled"{/if}>
                {foreach from=$option['types'] item="name_type" key="type"}
                    <option value="{$type}" {if $option_data.yml2_option_param == $type}selected="selected"{/if}>{$name_type}</option>
                {/foreach}
                {if $option.customer_type}
                    <option value="customer" {if !empty($option_data.yml2_option_param) && !array_key_exists($option_data.yml2_option_param, $option['types'])}selected="selected"{/if}>{__('yml2_option_type_customer')}</option>
                {/if}
            </select>
            {if $option.customer_type}
                <input type="text" name="option_data[yml2_option_param]" id="elm_yml2_option_param_input_{$id}_{$option_key}" size="10" value="{$option_data.yml2_option_param}" class="input-long option_param_input"
                        {if empty($option_data.yml2_option_param) || array_key_exists($option_data.yml2_option_param, $option['types'])} disabled="disabled" style="display: none;"{/if}/>
            {/if}
        </div>
    </div>
    {/if}
{/foreach}
