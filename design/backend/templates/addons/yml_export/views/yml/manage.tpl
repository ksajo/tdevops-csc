{capture name="mainbox"}

    {script src="js/addons/yml_export/manage.js"}

    {$allow_save = fn_check_permissions("yml", "update", "admin", "POST")}

    {if $allow_save}
        {assign var="no_hide_input" value="cm-no-hide-input"}
    {else}
        {assign var="no_hide_input" value=""}
    {/if}

    <form action="{""|fn_url}" method="post" name="yml_export_price_lists_form" class=" cm-hide-inputs" enctype="multipart/form-data">
        <input type="hidden" name="fake" value="1" />

        {if $price_lists}
            <table class="table table-middle table--relative">
                <thead>
                <tr>
                    <th width="1%" class="left">
                        {include file="common/check_items.tpl" class="cm-no-hide-input"}</th>
                    <th>{__("name")}</th>

                    <th width="15%">{__("yml_export.generation_link")}</th>
                    <th width="15%">{__("yml_export.get_link")}</th>
                    <th width="25%">{__("yml_export.generation_status")}</th>

                    <th width="6%">&nbsp;</th>
                    <th width="10%" class="right">{__("status")}</th>
                </tr>
                </thead>
                {foreach from=$price_lists item=price}
                <tr class="cm-row-status-{$price.status|lower}">
                    <td class="left">
                        <input type="checkbox" name="price_ids[]" value="{$price.param_id}" class="cm-item {$no_hide_input}" /></td>
                    <td class="{$no_hide_input}">
                        <a class="row-status" href="{"yml.update?price_id=`$price.param_id`"|fn_url}">{$price.param_data.name_price_list}</a>
                    </td>
                    <td class="{$no_hide_input}">
                        <a class="row-status" href="{$price.generate_link}" target="_blank">{__("yml_export.create")}</a>
                    </td>
                    <td class="{$no_hide_input}">
                        <a class="row-status" href="{$price.get_link}" target="_blank">{__("yml_export.go_to_link")}</a>
                    </td>
                    <td class="{$no_hide_input}" id="generation_status_{$price.param_id}">
                        {if $price.count > 0 && $generation_statuses[$price.param_id] == 'active'}
                            <span>{round(100 / $price.count * $price.offset)}% ({$price.offset}/{$price.count}, {$price.runtime})</span>
                        {elseif !empty($price.time)}
                            <span>{$price.time}{if $generation_statuses[$price.param_id] != 'finish'}, {__("yml_export.stop_generate")}{/if}</span>
                        {else}
                            <span></span>
                        {/if}
                        <!--generation_status_{$price.param_id}--></td>
                    <td id="price_list_tool_{$price.param_id}">
                        {capture name="tools_list"}
                            <li>{btn type="list" text=__("edit") href="yml.update?price_id=`$price.param_id`"}</li>
                            {if $generation_statuses[$price.param_id] == 'active'}
                            <li>{btn type="list" class="cm-confirm cm-post" text=__("yml_export.abort_generate") href="yml.stop_generate?price_id=`$price.param_id`"}</li>
                            {/if}
                            {if $allow_save}
                                <li>{btn type="list" class="cm-confirm cm-post" text=__("delete") href="yml.delete_price_list?price_id=`$price.param_id`"}</li>
                            {/if}
                        {/capture}
                        <div class="hidden-tools">
                            {dropdown content=$smarty.capture.tools_list}
                        </div>
                        <!--price_list_tool_{$price.param_id}--></td>
                    <td class="right">
                        {include file="common/select_popup.tpl" id=$price.param_id status=$price.status non_editable=!$allow_save hidden=true object_id_name="param_id" table="yml_param" popup_additional_class="`$no_hide_input` dropleft" hidden=false}
                    </td>
                </tr>
                {/foreach}
            </table>
        {else}
            <p class="no-items">{__("no_data")}</p>
        {/if}

    </form>
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {if $price_lists}
            <li>{btn type="delete_selected" dispatch="dispatch[yml.m_delete_price_lists]" form="yml_export_price_lists_form"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}

{capture name="adv_buttons"}
    {include file="common/tools.tpl" tool_href="yml.update" prefix="top" hide_tools="true" title=__("yml_export.add_price") icon="icon-plus"}
{/capture}

{include file="common/mainbox.tpl" title=__("yml_export.price_lists") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}