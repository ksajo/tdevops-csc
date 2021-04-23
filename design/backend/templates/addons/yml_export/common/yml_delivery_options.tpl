<div class="control-group">
    <label for="yml2_pickup" class="control-label">{__("yml_export.delivery_options")}:</label>
    <div class="controls">

        <table width="100%" class="table table-middle table--relative">
            <thead>
            <tr>
                <th width="15%">{__("yml_export.delivery_cost")}</th>
                <th width="15%">{__("yml_export.delivery_days")}</th>
                <th>{__("yml_export.order_before")}</th>
                <th class="cm-non-cb">&nbsp;</th>
            </tr>
            </thead>
            {foreach from=$data item="option" name="option_index"}
                {assign var="num" value=$smarty.foreach.option_index.iteration}
                <tbody class="hover cm-row-item" id="delivery_options_{$id}_{$num}">
                <tr>
                    <td>
                        <input type="text" name="{$name_data}[{$num}][cost]" size="20" value="{$option['cost']}" class="input-mini" />
                    </td>
                    <td>
                        <input type="text" name="{$name_data}[{$num}][days]" size="20" value="{$option['days']}" class="input-mini" />
                    </td>
                    <td>
                        <input type="text" name="{$name_data}[{$num}][order_before]" size="20" value="{$option['order_before']}" class="input-mini" />
                    </td>
                    <td class="right cm-non-cb">
                        {include file="buttons/multiple_buttons.tpl" item_id="option_variants_`$id`_`$num`" tag_level="3" only_delete="Y"}
                    </td>
                </tbody>
            {/foreach}

            {math equation="x + 1" assign="num" x=$num|default:0}

            <tbody class="hover cm-row-item" id="box_add_delivery_option_{$id}">
            <tr>
                <td>
                    <input type="text" name="{$name_data}[{$num}][cost]" size="20" value="" class="input-mini" />
                </td>
                <td>
                    <input type="text" name="{$name_data}[{$num}][days]" size="20" value="" class="input-mini" />
                </td>
                <td>
                    <input type="text" name="{$name_data}[{$num}][order_before]" size="20" value="" class="input-mini" />
                </td>
                <td class="right cm-non-cb">
                    {include file="buttons/multiple_buttons.tpl" item_id="add_delivery_option_`$id`" tag_level="2"}
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>