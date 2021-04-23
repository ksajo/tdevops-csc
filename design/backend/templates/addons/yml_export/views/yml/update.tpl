{if $price}
    {assign var="id" value=$price.param_id}
{else}
    {assign var="id" value=0}
{/if}

{$allow_save = fn_check_permissions("yml", "update", "admin", "POST")}

{capture name="mainbox"}

    <form action="{""|fn_url}" method="post" name="yml_export_price_lists_form" class="form-horizontal form-edit {if !$allow_save} cm-hide-inputs{/if}" enctype="multipart/form-data">
        <input type="hidden" name="fake" value="1" />
        <input type="hidden" name="price_id" value="{$id}" />


    {capture name="tabsbox"}
        {foreach from=$price_lists item="tab" key="tab_name"}
        <div id="content_{$tab_name}">

            {foreach from=$tab item="field" key="field_name"}

                {if $field.update_only && empty($id) || $field.disabled}
                    {continue}
                {/if}

                {if $field.type == 'input'}
                    <div class="control-group">
                        <label for="elm_ym_{$field_name}" class="control-label{if $field.required} cm-required{/if}">{__("yml_export.param_{$field_name}")}:
                        </label>
                        <div class="controls">
                            <input type="text" name="pricelist_data[{$field_name}]" id="elm_ym_{$field_name}" size="55"
                                   {if $field.class}class="{$field.class}"{/if}
                                   {if $field.placeholder}placeholder="{$field.placeholder}"{/if}
                                   value="{if isset($price.param_data.$field_name)}{$price.param_data.$field_name}{elseif isset($field.default)}{$field.default}{/if}"
                            />
                            {if $field.tooltip}
                                <p class="muted description">{$field.tooltip nofilter}</p>
                            {/if}
                        </div>
                    </div>

                {elseif $field.type == 'checkbox'}
                    <div class="control-group">
                        <label for="elm_ym_{$field_name}" class="control-label{if $field.required} cm-required{/if}">{__("yml_export.param_{$field_name}")}:
                        </label>
                        <div class="controls">
                            <input type="hidden" name="pricelist_data[{$field_name}]" value="N">
                            <input type="checkbox" name="pricelist_data[{$field_name}]" id="elm_ym_{$field_name}"
                                {if (isset($price.param_data.$field_name) && $price.param_data.$field_name == 'Y') ||
                                    (!isset($price.param_data.$field_name) && $field.default == 'Y')} checked{/if} value="Y"/>
                            {if $field.tooltip}
                                <p class="muted description">{$field.tooltip nofilter}</p>
                            {/if}
                        </div>
                    </div>

                {elseif $field.type == 'selectbox'}
                    <div class="control-group">
                        <label for="elm_ym_{$field_name}" class="control-label{if $field.required} cm-required{/if}">{__("yml_export.param_{$field_name}")}:
                        </label>
                        <div class="controls">
                            <select name="pricelist_data[{$field_name}]" id="elm_ym_{$field_name}">
                                {foreach from=$field.variants item="option_name" key="option_code"}
                                    <option value="{$option_code}"
                                        {if (isset($price.param_data.$field_name) && $price.param_data.$field_name == $option_code) ||
                                            (!isset($price.param_data.$field_name) && $field.default == $option_code)} selected="selected"{/if}>{__($option_name)}</option>
                                {/foreach}
                            </select>
                            {if $field.tooltip}
                                <p class="muted description">{$field.tooltip nofilter}</p>
                            {/if}
                        </div>
                    </div>

                {elseif $field.type == 'template'}
                    {include file=$field.template name_data="pricelist_data[{$field.name_data}]" data=$price.param_data[$field.name_data] params=$field.params}
                {/if}

            {/foreach}
        </div>
        {/foreach}
    {/capture}

    {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section track=true}

    {capture name="buttons"}
        {if !$id}
            {include file="buttons/save_cancel.tpl" but_role="submit-link" but_target_form="yml_export_price_lists_form" but_name="dispatch[yml.update]"}
        {else}
            {if "ULTIMATE"|fn_allowed_for && !$allow_save}
                {assign var="hide_first_button" value=true}
                {assign var="hide_second_button" value=true}
            {/if}
            {include file="buttons/save_cancel.tpl" but_name="dispatch[yml.update]" but_role="submit-link" but_target_form="yml_export_price_lists_form" hide_first_button=$hide_first_button hide_second_button=$hide_second_button save=$id}
        {/if}
    {/capture}

    </form>
{/capture}



{include file="common/mainbox.tpl" title=__("yml_export.price_lists") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}