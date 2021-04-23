{assign var="_addon" value=$smarty.request.addon}
{if $separate}
    {script src="js/tygh/tabs.js"}
    {script src="js/tygh/fileuploader_scripts.js"}
    {include file="views/profiles/components/profiles_scripts.tpl" states=1|fn_get_all_states}
{/if}

{if $separate}{capture name="mainbox"}{/if}
<div id="content_group{$_addon}">
    <div id="content_{$_addon}">
    <div class="tabs cm-j-tabs {if $separate}cm-track{/if} {if $subsections|count == 1}hidden{/if}">
        <ul class="nav nav-tabs">
            {foreach from=$subsections key="section" item="subs"}
                {assign var="tab_id" value="`$_addon`_`$section`"}
                <li class="cm-js {if $smarty.request.selected_section == $tab_id}active{/if}" id="{$tab_id}"><a>{$subs.description}</a></li>
            {/foreach}
        </ul>
    </div>
    <div class="cm-tabs-content" id="tabs_content_{$_addon}">
        <form action="{""|fn_url}" method="post" name="update_addon_{$_addon}_form" class=" form-edit form-horizontal" enctype="multipart/form-data">

        <input type="hidden" name="selected_section" value="{$smarty.request.selected_section}" />
        <input type="hidden" name="addon" value="{$smarty.request.addon}" />
        <input type="hidden" name="storefront_id" value="{$smarty.request.storefront_id}" />
        {if $smarty.request.return_url}
        <input type="hidden" name="redirect_url" value="{$smarty.request.return_url}" />
        {/if}
        
        {foreach from=$options key="section" item="field_item"}
             {capture name="separate_section"}
                <div id="content_{$_addon}_{$section}" class="settings{if $subsections.$section.type == "SEPARATE_TAB"} cm-hide-save-button{/if}">
                    {capture name="header_first"}false{/capture}
                    {component
                        name="settings.settings_section"
                        allow_global_individual_settings=true
                        subsection=$field_item
                        section_name=$_addon
                        html_id_prefix="addon_option"
                        html_name="addon_data[options]"
                        class="setting-wide"
                    }{/component}
                </div>
            {/capture}

            {if $subsections.$section.type == "SEPARATE_TAB"}
                {$sep_sections = "`$sep_sections` `$smarty.capture.separate_section`"}
            {else}
                {$smarty.capture.separate_section nofilter}
            {/if}
        {/foreach}

        {if $separate}
            {capture name="buttons"}
                {hook name="addons:action_buttons"}
                    {include file="buttons/save_cancel.tpl" but_name="dispatch[addons.update]" but_target_form="update_addon_`$_addon`_form" hide_second_button=true breadcrumbs=$breadcrumbs save=true}
                {/hook}
            {/capture}
        {else}
            <div class="buttons-container{if $separate} buttons-bg{/if} cm-toggle-button">
                {hook name="addons:action_buttons"}
                    {include file="views/addons/components/addon_info.tpl"}
                    {include file="buttons/save_cancel.tpl" but_name="dispatch[addons.update]" cancel_action="close" save=true}
                {/hook}
            </div>
        {/if}

        </form> 
        {if $subsections.$section.type == "SEPARATE_TAB"}
            {$sep_sections nofilter}
        {/if}

        {if $separate}
            <div class="buttons-container">
                {include file="views/addons/components/addon_info.tpl"}
            </div>
        {/if}
    </div>
    <!--content_{$_addon}--></div>
<!--content_group{$_addon}--></div>
{if $separate}
    {/capture}
    {include file="common/mainbox.tpl"
        title=$addon_name
        content=$smarty.capture.mainbox
        buttons=$smarty.capture.buttons
        select_storefront=true
        show_all_storefront=$show_all_storefront|default:true
        storefront_switcher_param_name="storefront_id"
    }
{/if}
