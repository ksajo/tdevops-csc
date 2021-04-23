{capture name="mainbox"}
<div id="taxes_form">
    <form id='form' action="{""|fn_url}" method="post" name="taxes_form" class="form-horizontal form-edit" enctype="multipart/form-data">
        {include file="common/subheader.tpl" title=__("taxes") target="#taxes"}
        <div id="taxes" class="collapse in">
            <table class="table table-middle table--relative" width="100%">
                <thead class="cm-first-sibling">
                    <tr>
                        <th width="15%">{__("tax_cscart")}</th>
                        <th width="70%">{__("tax_1c")}</th> 
                        <th width="15%"></th>  
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$taxes_data item="tax_data" key="_key" name="tax_1c"}
                        <tr class="cm-row-item">
                            <td width="15%">
                                <select id="tax_id" name="taxes_1c[{$_key}][tax_id]" class="span3">
                                    {foreach from=$taxes item="tax"}
                                        {if $tax_data.tax_id != $tax.tax_id}
                                            <option value="{$tax.tax_id}">{$tax.tax}</option>
                                        {/if}
                                    {/foreach}
                                    <option value="{$tax_data.tax_id}" selected="selected">{$tax_data.tax_id|fn_get_tax_name}</option>
                                </select>
                            </td>
                            <td width="70%"><input type="text" name="taxes_1c[{$_key}][tax_1c]" value="{$tax_data.tax_1c}" class="span8" /></td>
                            <td width="15%">{include file="buttons/clone_delete.tpl" microformats="cm-delete-row" no_confirm=true}</td>
                        </tr>
                    {/foreach}
                    {math equation="x+1" x=$_key|default:0 assign="new_key"}
                    <tr class="cm-row-item" id="box_add_tax">
                        <td width="15%">
                            <select id="tax_id" name="taxes_1c[{$new_key}][tax_id]" class="span3">
                                {foreach from=$taxes item="tax"}
                                    <option value="{$tax.tax_id}">{$tax.tax}</option>
                                {/foreach}
                            </select>
                        </td>
                        <td width="70%"><input type="text" name="taxes_1c[{$new_key}][tax_1c]" class="span8" /></td>
                        <td width="15%">{include file="buttons/multiple_buttons.tpl" item_id="add_tax"}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>
</div>
{/capture}
{capture name="buttons"}
    {include file="buttons/button.tpl" but_text=__("save") but_name="dispatch[commerceml.save_taxes_data]" but_role="submit-link" but_target_form="taxes_form"}
{/capture}
{include file="common/mainbox.tpl" title=__("addons.rus_exim_1c.settings_taxes") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}




