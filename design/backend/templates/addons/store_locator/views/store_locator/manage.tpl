{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="store_locator_form">
<input type="hidden" name="fake" value="1" />

{include file="common/pagination.tpl" save_current_page=true}

<div class="items-container" id="store_locations">
    {if $store_locations}
    <div class="table-responsive-wrapper">
        <table class="table table-middle table--relative table-responsive">

        <thead>
        <tr>
            {hook name="store_locator:stores_list_header"}
            <th width="1%" class="mobile-hide">
                {include file="common/check_items.tpl" class="cm-no-hide-input"}
            </th>
            <th width="1%" class="shift-left">{__("position_short")}</th>
            <th width="20%" class="shift-left">{__("store_locator")}</th>
            <th width="20%" class="shift-left">{__("city")}</th>
            <th width="20%" class="shift-left">{__("rate_area")}</th>
            {/hook}
            <th width="5%">&nbsp;</th>
            <th class="right" width="10%">{__("status")}</th>
        </tr>
        </thead>

            {foreach from=$store_locations item=loc}
            <tbody>
            <tr class="cm-row-status-{$loc.status|lower}" valign="top" >

                {hook name="store_locator:stores_list"}
                {assign var="allow_save" value=$loc|fn_allow_save_object:"store_locations"}
                {if $allow_save}
                    {assign var="no_hide_input" value="cm-no-hide-input"}
                    {assign var="display" value=""}
                {else}
                    {assign var="no_hide_input" value=""}
                    {assign var="display" value="text"}
                {/if}

                <td class="left {$no_hide_input} mobile-hide">
                    <input type="checkbox" name="store_locator_ids[]" value="{$loc.store_location_id}" class="cm-item" /></td>
                <td data-th="{__("position_short")}">
                    <input type="text" name="store_locators[{$loc.store_location_id}][position]" size="3" value="{$loc.position}" class="input-micro input-hidden" />
                </td>
                <td data-th="{__("store_locator")}">
                    <a class="row-status" href="{"store_locator.update?store_location_id=`$loc.store_location_id`"|fn_url}">{$loc.name}</a>
                    {include file="views/companies/components/company_name.tpl" object=$loc}
                </td>

                <td data-th="{__("city")}">
                    <span class="row-status">{$loc.city}</span>
                </td>

                <td data-th="{__("rate_area")}">
                    {if $loc.main_destination_id}
                        {if fn_check_view_permissions("destinations.update")}
                            <input type="hidden" name="store_locators[{$loc.store_location_id}][main_destination_id]" value="{$loc.main_destination_id}"/>
                            <a href="{"destinations.update&destination_id={$loc.main_destination_id}"|fn_url}"
                               class="row-status"
                            >{$destinations[$loc.main_destination_id]["destination"]}</a>
                        {else}
                            <span class="row-status">
                                {$destinations[$loc.main_destination_id]["destination"]}
                            </span>
                        {/if}
                    {else}
                        <span class="row-status">
                            {__("store_locator.no_rate_area")}
                        </span>
                    {/if}
                </td>
                {/hook}

                <td class="center nowrap" data-th="{__("tools")}">
                    {capture name="tools_list"}
                        {if $allow_save}
                            <li>{btn type="list" text=__("edit") href="store_locator.update?store_location_id=`$loc.store_location_id`"}</li>
                            <li>{btn type="list" class="cm-confirm" text=__("delete") href="store_locator.delete?store_location_id=`$loc.store_location_id`" method="POST"}</li>
                        {/if}
                    {/capture}
                    <div class="hidden-tools right">
                        {dropdown content=$smarty.capture.tools_list}
                    </div>
                </td>
                <td class="right nowrap" data-th="{__("status")}">
                    {include file="common/select_popup.tpl" id=$loc.store_location_id status=$loc.status hidden="" object_id_name="store_location_id" table="store_locations" popup_additional_class="`$no_hide_input`" display=$display}
                </td>

            </tr>
            </tbody>
            {/foreach}
        </table>
    </div>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}
<!--store_locations--></div>


    {include file="common/pagination.tpl" save_current_page=true}
</form>
    {capture name="adv_buttons"}
        {include file="common/tools.tpl" tool_href="store_locator.add" prefix="top" title=__("add_store_location") hide_tools=true}
    {/capture}


{/capture}
    {capture name="sidebar"}
        {hook name="store_locator:manage_sidebar"}
            {include file="addons/store_locator/components/search_form.tpl"
            dispatch="store_locator.manage"
            search=$search
            }
        {/hook}
    {/capture}

    {capture name="buttons"}
        {capture name="tools_list"}
            {hook name="store_locator:manage_tools_list"}
            {if $store_locations}
                <li>{btn type="delete_selected" dispatch="dispatch[store_locator.m_delete]" form="store_locator_form"}</li>
            {/if}
            {/hook}
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
        {if $store_locations}
            {include file="buttons/save.tpl" but_name="dispatch[store_locator.m_update]" but_role="action" but_target_form="store_locator_form" but_meta="cm-submit"}
        {/if}
    {/capture}
{include file="common/mainbox.tpl" title=__("store_locator") content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons select_languages=true buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}
