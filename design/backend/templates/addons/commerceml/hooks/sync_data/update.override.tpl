{$sync_provider_id = $smarty.request.sync_provider_id}

{capture name="mainbox"}
    {capture name="tabsbox"}
        <form class="form-edit form-horizontal cm-processed-form cm-check-changes" action="{""|fn_url}" method="post" id="sync_data_settings_form" enctype="multipart/form-data">
            <input type="hidden" name="sync_provider_id" value="{$sync_provider_id}" />
            <input type="hidden" name="selected_section" value="{$smarty.request.selected_section|default:"general"}" />

            <div id="content_general" class="hidden">
                {include file="addons/commerceml/views/sync_data/components/general_info.tpl"}
            </div>

            <div id="content_catalog" class="hidden">
                {include file="addons/commerceml/views/sync_data/components/catalog_settings.tpl"}
            </div>

            <div id="content_products" class="hidden">
                {include file="addons/commerceml/views/sync_data/components/products_settings.tpl"}
            </div>

            <div id="content_orders" class="hidden">
                {include file="addons/commerceml/views/sync_data/components/orders_settings.tpl"}
            </div>

            {foreach $mappable_schema as $type => $schema}
                {if !$schema.parent}
                    <div id="content_{$type}">
                        <div class="hidden"></div>
                    <!--content_{$type}--></div>
                {/if}
            {/foreach}
        </form>

    {/capture}

    {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox group_name="commerceml" active_tab=$smarty.request.selected_section track=true}

{/capture}

{capture name="buttons"}
    {include file="buttons/button.tpl" but_permission_data="sync_data.update?sync_provider_id={$sync_provider_id}" but_role="submit-link" but_name="dispatch[sync_data.update]" but_target_form="sync_data_settings_form" but_text=__("save") but_meta="btn-primary"}
{/capture}

{include file="common/mainbox.tpl" title=$provider_data.name content=$smarty.capture.mainbox buttons=$smarty.capture.buttons show_all_storefront=false}
