<tr {if !$clone}id="{$holder}_{$field_id}" {/if}class="cm-js-item{if $sortable} profile-field-picker__sortable-row{/if}{if $clone} cm-clone hidden{/if}">
    <td width="1%" data-th="&nbsp;">
        {if $sortable}
            <input type="hidden" name="field_id" value="{$field_id}"/>
            <span class="handler"></span>
        {/if}
    </td>
    <td data-th="{__("id")}">
        <a href="{"profile_fields.update?field_id=`$field_id`"|fn_url}">&nbsp;<span>#{$field_id}</span>&nbsp;</a></td>
    <td data-th="{__("description")}">{$description}</td>
    <td {if $adjust_requireability === false}class="hidden"{/if} data-th="{__("required")}">
        <input type="hidden" name="block_data[content][items][required][field_id_{$field_id}]" value="{"YesNo::NO"|enum}" {if $clone}disabled{/if}>
        <input type="checkbox" name="block_data[content][items][required][field_id_{$field_id}]" value="{"YesNo::YES"|enum}" {if $required == "YesNo::YES"|enum}checked{/if}>
    </td>
    {if !$view_only}
    <td class="nowrap" data-th="&nbsp;">
        <div class="hidden-tools">
            {capture name="tools_list"}
                <li>{btn type="list" text=__("edit") href="profile_fields.update?field_id=`$field_id`"}</li>
                <li>{btn type="list" text=__("remove") onclick="Tygh.$.cePicker('delete_js_item', '{$holder}', '{$field_id}', 'pf_'); return false;"}</li>
            {/capture}
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    {/if}
</tr>
