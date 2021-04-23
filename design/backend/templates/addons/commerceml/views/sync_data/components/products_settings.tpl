{$marketplace = "store"}

{if "MULTIVENDOR"|fn_allowed_for}
    {$marketplace = "marketplace"}
{/if}

{include file="common/subheader.tpl" title=__("commerceml.main_information")}

<table class="table table-middle table--relative table-striped">
    <thead>
        <tr>
            <th width="300px">{__("commerceml.field_in_$marketplace")}</th>
            <th width="300px">{__("commerceml.field_in_crm")}</th>
            <th>{__("commerceml.field_import_only_for_new_products")}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_product_name_source")}</td>
            <td>
                <select id="elm_sync_data_commerceml_cml_catalog_convertor_product_name_source" name="sync_data_settings[{$sync_provider_id}][catalog_convertor.product_name_source]">
                    {foreach $settings_schema["catalog_convertor.product_name_source"].variants as $variant}
                        <option value="{$variant}" {if $import_settings["catalog_convertor.product_name_source"] == $variant}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_product_name_source.{$variant|lower}")}</option>
                    {/foreach}
                </select>
            </td>
            <td>
                <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_update_product_name]" value="Y">
                <input type="checkbox" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_update_product_name]" value="N" {if !$import_settings["catalog_importer.allow_update_product_name"]}checked{/if}>
            </td>
        </tr>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_product_code_source")}</td>
            <td>
                <select id="elm_sync_data_commerceml_cml_catalog_convertor_product_code_source" name="sync_data_settings[{$sync_provider_id}][catalog_convertor.product_code_source]">
                    {foreach $settings_schema["catalog_convertor.product_code_source"].variants as $variant}
                        <option value="{$variant}" {if $import_settings["catalog_convertor.product_code_source"] == $variant}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_product_code_source.{$variant|lower}")}</option>
                    {/foreach}
                </select>
            </td>
            <td>
                <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_update_product_code]" value="Y">
                <input type="checkbox" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_update_product_code]" value="N" {if !$import_settings["catalog_importer.allow_update_product_code"]}checked{/if}>
            </td>
        </tr>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_full_description_source")}</td>
            <td>
                <select id="elm_sync_data_commerceml_cml_catalog_convertor_full_description_source" name="sync_data_settings[{$sync_provider_id}][catalog_convertor.full_description_source]">
                    {foreach $settings_schema["catalog_convertor.full_description_source"].variants as $variant}
                        <option value="{$variant}" {if $import_settings["catalog_convertor.full_description_source"] == $variant}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_full_description_source.{$variant|lower}")}</option>
                    {/foreach}
                </select>
            </td>
            <td>
                <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_update_product_full_description]" value="Y">
                <input type="checkbox" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_update_product_full_description]" value="N" {if !$import_settings["catalog_importer.allow_update_product_full_description"]}checked{/if}>
            </td>
        </tr>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_short_description_source")}</td>
            <td>
                <select id="elm_sync_data_commerceml_cml_catalog_convertor_short_description_source" name="sync_data_settings[{$sync_provider_id}][catalog_convertor.short_description_source]">
                    {foreach $settings_schema["catalog_convertor.short_description_source"].variants as $variant}
                        <option value="{$variant}" {if $import_settings["catalog_convertor.short_description_source"] == $variant}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_short_description_source.{$variant|lower}")}</option>
                    {/foreach}
                </select>
            </td>
            <td>
                <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_update_product_short_description]" value="Y">
                <input type="checkbox" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_update_product_short_description]" value="N" {if !$import_settings["catalog_importer.allow_update_product_short_description"]}checked{/if}>
            </td>
        </tr>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_page_title_source")}</td>
            <td>
                <select id="elm_sync_data_commerceml_cml_catalog_convertor_page_title_source" name="sync_data_settings[{$sync_provider_id}][catalog_convertor.page_title_source]">
                    {foreach $settings_schema["catalog_convertor.page_title_source"].variants as $variant}
                        <option value="{$variant}" {if $import_settings["catalog_convertor.page_title_source"] == $variant}selected="selected"{/if}>{__("commerceml.cml_catalog_convertor_page_title_source.{$variant|lower}")}</option>
                    {/foreach}
                </select>
            </td>
            <td>
                <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_update_product_page_title]" value="Y">
                <input type="checkbox" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_update_product_page_title]" value="N" {if !$import_settings["catalog_importer.allow_update_product_page_title"]}checked{/if}>
            </td>
        </tr>

        <tr>
            <td class="commerceml-sync-data-setting-name">{__("commerceml.cml_catalog_convertor_promo_text_property_source")}</td>
            <td class="commerceml-sync-data-setting-variants">
                <input type="text" name="sync_data_settings[{$sync_provider_id}][catalog_convertor.promo_text_property_source]" id="elm_sync_data_commerceml_cml_catalog_convertor_promo_text_property_source" size="32" value="{$import_settings["catalog_convertor.promo_text_property_source"]}" />
            </td>
            <td>
                <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_update_product_promotext]" value="Y">
                <input type="checkbox" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_update_product_promotext]" value="N" {if !$import_settings["catalog_importer.allow_update_product_promotext"]}checked{/if}>
            </td>
        </tr>
    </tbody>
</table>

{include file="common/subheader.tpl" title=__("commerceml.shipping_information")}

<div>
    <div class="well">
        {__("commerceml.shipping_information_info_block")}
    </div>
</div>

<table class="table table-middle table--relative table-striped">
    <thead>
        <tr>
            <th width="300px">{__("commerceml.field_in_$marketplace")}</th>
            <th>{__("commerceml.fields_in_crm")}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_weight_property_source_list")}</td>
            <td>
                <textarea id="elm_sync_data_commerceml_cml_catalog_convertor_weight_property_source_list" name="sync_data_settings[{$sync_provider_id}][catalog_convertor.weight_property_source_list]" rows="5" cols="19" class="input-large user-success">{"\n"|implode:$import_settings["catalog_convertor.weight_property_source_list"]}</textarea>
            </td>
        </tr>
        <tr>
            <td>
                {__("commerceml.cml_catalog_convertor_free_shipping_property_source_list")}
                <div class="muted description">{__("commerceml.cml_catalog_convertor_free_shipping_property_source_list.tooltip")}</div>
            </td>
            <td>
                <textarea id="elm_sync_data_commerceml_cml_catalog_convertor_free_shipping_property_source_list" name="sync_data_settings[{$sync_provider_id}][catalog_convertor.free_shipping_property_source_list]" rows="5" cols="19" class="input-large user-success">{"\n"|implode:$import_settings["catalog_convertor.free_shipping_property_source_list"]}</textarea>
            </td>
        </tr>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_shipping_cost_property_source_list")}</td>
            <td>
                <textarea id="elm_sync_data_commerceml_cml_catalog_convertor_shipping_cost_property_source_list" name="sync_data_settings[{$sync_provider_id}][catalog_convertor.shipping_cost_property_source_list]" rows="5" cols="19" class="input-large user-success">{"\n"|implode:$import_settings["catalog_convertor.free_shipping_property_source_list"]}</textarea>
            </td>
        </tr>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_number_of_items_property_source_list")}</td>
            <td>
                <textarea id="elm_sync_data_commerceml_cml_catalog_convertor_number_of_items_property_source_list" name="sync_data_settings[{$sync_provider_id}][catalog_convertor.number_of_items_property_source_list]" rows="5" cols="19" class="input-large user-success">{"\n"|implode:$import_settings["catalog_convertor.number_of_items_property_source_list"]}</textarea>
            </td>
        </tr>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_box_length_property_source_list")}</td>
            <td>
                <textarea id="elm_sync_data_commerceml_cml_catalog_convertor_box_length_property_source_list" name="sync_data_settings[{$sync_provider_id}][catalog_convertor.box_length_property_source_list]" rows="5" cols="19" class="input-large user-success">{"\n"|implode:$import_settings["catalog_convertor.box_length_property_source_list"]}</textarea>
            </td>
        </tr>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_box_width_property_source_list")}</td>
            <td>
                <textarea id="elm_sync_data_commerceml_cml_catalog_convertor_box_width_property_source_list" name="sync_data_settings[{$sync_provider_id}][catalog_convertor.box_width_property_source_list]" rows="5" cols="19" class="input-large user-success">{"\n"|implode:$import_settings["catalog_convertor.box_width_property_source_list"]}</textarea>
            </td>
        </tr>
        <tr>
            <td>{__("commerceml.cml_catalog_convertor_box_height_property_source_list")}</td>
            <td>
                <textarea id="elm_sync_data_commerceml_cml_catalog_convertor_box_height_property_source_list" name="sync_data_settings[{$sync_provider_id}][catalog_convertor.box_height_property_source_list]" rows="5" cols="19" class="input-large user-success">{"\n"|implode:$import_settings["catalog_convertor.box_height_property_source_list"]}</textarea>
            </td>
        </tr>
    </tbody>
</table>