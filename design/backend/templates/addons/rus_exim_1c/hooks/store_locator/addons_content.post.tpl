{include file="common/subheader.tpl" title=__("commerceml") target="#store_locator_exim_1c_external_ids"}
<div id="store_locator_exim_1c_external_ids">
    <div class="control-group">
        <label for="exim_1s_external_id" class="control-label">{__("rus_exim_1c.store_locator_external_id")}:</label>
        <div class="controls">
            <div class="table-responsive-wrapper">
                <table width="100%" class="table table-middle table--relative table-responsive table-responsive-w-titles">
                    {foreach $store_location.external_1c_ids as $external_1c_id name="external_1c_id_index"}
                        {$num=$smarty.foreach.external_1c_id_index.iteration}
                        <tbody class="hover cm-row-item" id="external_1c_ids_{$id}_{$num}">
                        <tr>
                            <td data-th="&nbsp;">
                                <input type="text" name="store_location_data[external_1c_ids][{$num}]" size="20" value="{$external_1c_id}" class="input-text-large input-large" />
                            </td>
                            <td class="right cm-non-cb" data-th="&nbsp;">
                                {include file="buttons/multiple_buttons.tpl" item_id="option_variants_`$id`_`$num`" tag_level="3" only_delete="Y"}
                            </td>
                        </tbody>
                    {/foreach}

                    {math equation="x + 1" assign="num" x=$num|default:0}

                    <tbody class="hover cm-row-item" id="box_add_external_1c_id_{$id}">
                    <tr>
                        <td data-th="&nbsp;">
                            <input type="text" name="store_location_data[external_1c_ids][{$num}]" size="20" value="" class="input-text-large input-large" />
                        </td>
                        <td class="right cm-non-cb" data-th="&nbsp;">
                            {include file="buttons/multiple_buttons.tpl" item_id="add_external_1c_id_`$id`" tag_level="2"}
                        </td>
                    </tr>
                    </tbody>
                    <tbody class="hover cm-row-item" id="external_1c_ids_{$id}_{$num}">
                    <tr>
                        <td data-th="&nbsp;">
                            <p class="muted description">{__("rus_exim_1c.store_locator_external_id_tooltip")}</p>
                        </td>
                        <td data-th="&nbsp;"></td>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>