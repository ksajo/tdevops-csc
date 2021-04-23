{include file="views/profiles/components/profiles_scripts.tpl" states=1|fn_get_all_states}

{if $runtime.company_id}
    {assign var="hide_for_vendor" value=true}
{/if}

{script src="js/tygh/tabs.js"}
{script src="js/tygh/filter_table.js"}
{script src="js/tygh/fileuploader_scripts.js"}
{script src="js/tygh/backend/addons_manage.js"}

{$c_url = $config.current_url}

{capture name="mainbox"}

{capture name="sidebar"}
    {hook name="addons:manage_sidebar"}
    {include file="views/addons/components/addons_developers.tpl"}
    {include file="views/addons/components/addons_search_form.tpl" dispatch="addons.manage"}
    {hook name="addons:manage_sidebar_marketplace"}
        <div class="sidebar-row marketplace">
            <h6>{__("marketplace")}</h6>
            <p class="marketplace-link">{__("marketplace_find_more", ["[href]" => $config.resources.marketplace_url])}</p>
        </div>
    {/hook}
    {if $snapshot_exist && !$hide_for_vendor}
        <div class="sidebar-row">
            <h6>{__("change_addons_initialization")}</h6>
            <ul class="unstyled sidebar-stat" id="addons_counter">
                <li>{__("tools_addons_installed_count")} <span><a href="{"addons.manage&type=installed"|fn_url}">{$addons_counter.installed}</a></span></li>
                <li>{__("tools_addons_activated_count")} <span><a href="{"addons.manage&type=active"|fn_url}">{$addons_counter.activated}</a></span></li>
                <li>{__("tools_addons_other_addons_count")} <span><a href="{"addons.manage&source=third_party"|fn_url}">{$addons_counter.other}</a></span></li>
            <!--addons_counter--></ul>
        </div>
        <hr>
        <div class="sidebar-row">
            <p>{__("change_addons_initialization_description")}</p>
            <div>
                <form action="{""|fn_url}" method="post">
                    <input type="hidden" name="dispatch" value="addons.tools">
                    <button type="submit" class="btn btn-block {if $settings.init_addons == "none"}disabled{/if}" {if $settings.init_addons == "none"}disabled="disabled" {/if}name="init_addons" value="none">{__("tools_addons_disable_all")}</button>
                    <button type="submit" class="btn btn-block {if $settings.init_addons == "core"}disabled{/if}" {if $settings.init_addons == "core"}disabled="disabled" {/if}name="init_addons" value="core">{__("tools_addons_disable_third_party")}</button>
                </form>
            </div>
        </div>
    {/if}

    {/hook}
{/capture}

{capture name="upload_addon"}
    {include file="views/addons/components/upload_addon.tpl"}
{/capture}

<div class="items-container" id="addons_list">
{hook name="addons:manage"}

<div class="cm-tabs-content">
    {if $settings.init_addons}
        <div class="alert alert-block addon-info-msg">
            <span>{__("tools_addons_disabled_msg")}</span>
            <form action="{""|fn_url}" method="post">
                <input type="hidden" name="dispatch" value="addons.tools">
                <button type="submit" class="btn btn-warning" name="init_addons" value="restore">{__("tools_re_enable_add_ons")}</button>
            </form>
        </div>
    {/if}
    {include file="views/addons/components/addons_list.tpl"}
</div>

{/hook}
<!--addons_list--></div>

{capture name="adv_buttons"}
    {hook name="addons:adv_buttons"}
    {if !$runtime.company_id && !"RESTRICTED_ADMIN"|defined}
        {include file="common/popupbox.tpl" id="upload_addon" text=__("upload_addon") title=__("upload_addon") content=$smarty.capture.upload_addon act="general" link_class="cm-dialog-auto-size" icon="icon-plus" link_text=""}
    {/if}
    {/hook}
{/capture}

{/capture}
{include file="common/mainbox.tpl"
    title=__("addons")
    content=$smarty.capture.mainbox
    sidebar=$smarty.capture.sidebar
    adv_buttons=$smarty.capture.adv_buttons
    select_storefront=true
    show_all_storefront=true
    storefront_switcher_param_name="storefront_id"
}
