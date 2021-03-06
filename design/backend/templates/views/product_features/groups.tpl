{script src="js/tygh/tabs.js"}

{capture name="mainbox"}

    {include file="common/pagination.tpl"}

    {assign var="r_url" value=$config.current_url|escape:url}
    {assign var="show_in_popup" value=false}

    <form action="{""|fn_url}" method="post" name="manage_product_features_form" id="manage_product_features_form">
    <input type="hidden" name="return_url" value="{$config.current_url}">
    <div class="items-container{if ""|fn_check_form_permissions} cm-hide-inputs{/if}" id="update_features_list">
        {if $features}
            <div class="table-responsive-wrapper">
                <table width="100%" class="table table-middle table--relative table-responsive">
                    <thead>
                    <tr>
                        <th class="left">
                            {include file="common/check_items.tpl" check_statuses=""|fn_get_default_status_filters:true}
                        </th>
                        <th width="20%">{__("name")}{include file="common/tooltip.tpl" tooltip=__("internal_feature_group_name_tooltip")} / {__("storefront_name")}</th>
                        <th width="30%">{__("features")}</th>
                        <th width="30%">{__("categories")}</th>
                        <th width="5%">&nbsp;</th>
                        <th width="10%" class="right">{__("status")}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$features item="p_feature"}
                        {$non_editable = !$p_feature|fn_allow_save_object:"product_features"}

                        {$href_edit="product_features.update?feature_id=`$p_feature.feature_id`&return_url=`$r_url`"}
                        {$href_delete="product_features.delete?feature_id=`$p_feature.feature_id`&return_url=$r_url"}
                        {$included_features_href = "product_features.manage?parent_id=`$p_feature.feature_id`"|fn_url}

                        {$top_features_names = []}
                        {foreach $p_feature.top_features as $top_feature_id => $top_feature}
                            {$top_features_names[] = $top_feature.internal_name}
                        {/foreach}

                        <tr class="cm-row-item cm-row-status-{$p_feature.status|lower}" data-ct-product_features="{$p_feature.feature_id}">
                            <td class="left" data-th="&nbsp;">
                                <input type="checkbox" name="feature_ids[]" value="{$p_feature.feature_id}" class="cm-item cm-item-status-{$p_feature.status|lower}" />
                            </td>
                            <td data-th="{__("name")}">
                                <div class="object-group-link-wrap">
                                    {if !$non_editable}
                                        <a class="row-status cm-external-click {if $non_editable} no-underline{/if}"{if !$non_editable} data-ca-external-click-id="opener_group{$p_feature.feature_id}"{/if}>{$p_feature.internal_name}</a>
                                    {else}
                                        <span class="unedited-element block">{$p_feature.internal_name|default:__("view")}</span>
                                    {/if}
                                    <span class="muted"><small> #{$p_feature.feature_id}</small></span>
                                    <div><small>{$p_feature.description}</small></div>
                                    {include file="views/companies/components/company_name.tpl" object=$p_feature}
                                </div>
                            </td>
                            <td data-th="{__("features")}">
                                <div class="row-status object-group-details">
                                    {if $top_features_names|count > 0}
                                        <span>
                                            {", "|implode:$top_features_names}{if $p_feature.top_features|count < $p_feature.features_count},...{/if}
                                        </span>
                                        <a href="{$included_features_href}">({$p_feature.features_count nofilter})</a>
                                    {/if}
                                </div>
                            </td>
                            <td data-th="{__("categories")}">
                                <div class="row-status object-group-details">
                                    {$p_feature.feature_description nofilter}
                                </div>
                            </td>
                            <td class="nowrap" data-th="&nbsp;">
                                <div class="hidden-tools">
                                    {capture name="tools_list"}
                                        {if !$non_editable}
                                            <li>{include file="common/popupbox.tpl" id="group`$p_feature.feature_id`" text=$p_feature.description act="edit" href=$href_edit no_icon_link=true}</li>
                                            <li>{btn type="text" text=__("delete") href=$href_delete class="cm-confirm cm-ajax cm-ajax-force cm-ajax-full-render cm-delete-row" data=["data-ca-target-id" => "pagination_contents"] method="POST"}</li>
                                        {else}
                                            <li>{include file="common/popupbox.tpl" id="group`$p_feature.feature_id`" text=$p_feature.description act="edit" link_text=__("view") href=$href_edit no_icon_link=true}</li>
                                        {/if}
                                    {/capture}
                                    {dropdown content=$smarty.capture.tools_list}
                                </div>
                            </td>
                            <td class="right nowrap" data-th="{__("status")}">
                                {include file="common/select_popup.tpl" popup_additional_class="dropleft" id=$p_feature.feature_id status=$p_feature.status hidden=true object_id_name="feature_id" table="product_features" update_controller="product_features"}
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        {else}
            <p class="no-items">{__("no_data")}</p>
        {/if}
    <!--update_features_list--></div>
    </form>

    {include file="common/pagination.tpl"}
    {capture name="adv_buttons"}
        {capture name="add_new_picker"}
            {include file="views/product_features/update.tpl" feature=[] in_popup=true is_group=true return_url=$config.current_url}
        {/capture}
        {include file="common/popupbox.tpl" id="add_new_feature" text=__("new_group") title=__("new_group") content=$smarty.capture.add_new_picker act="general" icon="icon-plus"}
    {/capture}

    {capture name="sidebar"}
        {include file="views/product_features/components/product_feature_groups_search_form.tpl" dispatch="product_features.groups"}
    {/capture}

    {capture name="buttons"}
        {capture name="tools_list"}
            {if $features}
                <li>{btn type="delete_selected" dispatch="dispatch[product_features.m_delete]" form="manage_product_features_form"}</li>
            {/if}
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
    {/capture}

{/capture}
{include file="common/mainbox.tpl" title=__("feature_groups") content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar}
