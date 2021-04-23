<fieldset>
    {hook name="commerceml:catalog_settings_main"}
    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_catalog_new_product_status">{__("commerceml.cml_catalog_new_product_status")}:</label>
        <div class="controls">
            <select id="elm_sync_data_commerceml_cml_catalog_new_product_status" name="sync_data_settings[{$sync_provider_id}][catalog_importer.new_product_status]">
                {foreach $settings_schema["catalog_importer.new_product_status"].variants as $variant}
                    <option value="{$variant}" {if $import_settings["catalog_importer.new_product_status"] == $variant}selected="selected"{/if}>{__("commerceml.cml_catalog_new_product_status.{$variant|lower}")}</option>
                {/foreach}
            </select>
        </div>
    </div>

    {if $settings_schema["catalog_importer.allow_import_features"].editable}
        <div class="control-group setting-wide">
            <label class="control-label">{__("commerceml.cml_catalog_product_feature_creation")}:</label>
            <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_import_features]" value="N">

            <div class="controls">
                <label class="radio">
                    <input type="radio" value="Y" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_import_features]" {if $import_settings["catalog_importer.allow_import_features"]}checked="checked"{/if}>
                    {__("commerceml.cml_catalog_product_feature_creation.y")}
                </label>
                <label class="radio">
                    <input type="radio" value="N" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_import_features]" {if !$import_settings["catalog_importer.allow_import_features"]}checked="checked"{/if}>
                    {__("commerceml.cml_catalog_product_feature_creation.n")}
                </label>
            </div>
        </div>
    {/if}

    {if $settings_schema["catalog_importer.allow_import_categories"].editable}
        <div class="control-group setting-wide">
            <label class="control-label">{__("commerceml.cml_catalog_category_creation")}:</label>
            <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_import_categories]" value="N">

            <div class="controls">
                <label class="radio">
                    <input type="radio" value="Y" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_import_categories]" {if $import_settings["catalog_importer.allow_import_categories"]}checked="checked"{/if}>
                    {__("commerceml.cml_catalog_category_creation.y")}
                </label>
                <label class="radio">
                    <input type="radio" value="N" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_import_categories]" {if !$import_settings["catalog_importer.allow_import_categories"]}checked="checked"{/if}>
                    {__("commerceml.cml_catalog_category_creation.n")}
                </label>
            </div>
        </div>
    {/if}

    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_catalog_default_category_id">{__("commerceml.cml_catalog_default_category_id")}:<div class="muted description">{__("commerceml.cml_catalog_default_category_id.tooltip")}</div></label>
        <div class="controls">
            {include file="views/categories/components/picker/picker.tpl"
                input_name="sync_data_settings[{$sync_provider_id}][catalog_importer.default_category_id]"
                multiple=false
                show_advanced=true
                show_empty_variant=true
                allow_clear=true
                item_ids=[$import_settings["catalog_importer.default_category_id"]]
                dropdown_css_class="object-picker__dropdown--categories"
            }
        </div>
    </div>

    <div class="control-group setting-wide">
        <label class="control-label">{__("commerceml.cml_catalog_product_category_update_strategy")}:</label>

        <div class="controls">
            {foreach $settings_schema["catalog_importer.product_category_update_strategy"].variants as $variant}
                <label class="radio">
                    <input type="radio" value="{$variant}" name="sync_data_settings[{$sync_provider_id}][catalog_importer.product_category_update_strategy]" {if $import_settings["catalog_importer.product_category_update_strategy"] === $variant}checked="checked"{/if}>
                    {__("commerceml.cml_catalog_product_category_update_strategy.{$variant}")}
                </label>
            {/foreach}
        </div>
    </div>

    <div class="control-group setting-wide">
        <label class="control-label">{__("commerceml.cml_catalog_product_image_update_strategy")}:</label>

        <div class="controls">
            {foreach $settings_schema["catalog_importer.product_image_update_strategy"].variants as $variant}
                <label class="radio">
                    <input type="radio" value="{$variant}" name="sync_data_settings[{$sync_provider_id}][catalog_importer.product_image_update_strategy]" {if $import_settings["catalog_importer.product_image_update_strategy"] === $variant}checked="checked"{/if}>
                    {__("commerceml.cml_catalog_product_image_update_strategy.{$variant}")}
                </label>
            {/foreach}
        </div>
    </div>
    {/hook}

    {include file="common/subheader.tpl" title=__("commerceml.automatic_matching_title") target="#commerceml_automatic_matching"}
    <div id="commerceml_automatic_matching" class="in collapse">
        {hook name="commerceml:catalog_settings_automatic_matching"}
        {if $settings_schema["catalog_importer.allow_matching_category_by_name"].editable}
            <div class="control-group setting-wide">
                <label class="control-label" for="elm_sync_data_commerceml_cml_catalog_allow_matching_category_by_name">{__("commerceml.cml_catalog_allow_matching_category_by_name")}:</label>
                <div class="controls">
                    <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_matching_category_by_name]" value="N">
                    <input id="cml_catalog_allow_matching_category_by_name" type="checkbox" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_matching_category_by_name]" value="Y" {if $import_settings["catalog_importer.allow_matching_category_by_name"]}checked="checked"{/if}>
                </div>
            </div>
        {/if}

        <div class="control-group setting-wide">
            <label class="control-label" for="elm_sync_data_commerceml_cml_catalog_allow_matching_product_by_product_code">{__("commerceml.cml_catalog_allow_matching_product_by_product_code")}:</label>
            <div class="controls">
                <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_matching_product_by_product_code]" value="N">
                <input id="elm_sync_data_commerceml_cml_catalog_allow_matching_product_by_product_code" type="checkbox" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_matching_product_by_product_code]" value="Y" {if $import_settings["catalog_importer.allow_matching_product_by_product_code"]}checked="checked"{/if}>
            </div>
        </div>
        {/hook}
    </div>

    {include file="common/subheader.tpl" title=__("commerceml.additional_settings_title") target="#commerceml_additional_settings" meta="collapsed"}
    <div id="commerceml_additional_settings" class="out collapse">
        {hook name="commerceml:catalog_settings_additional"}
        <div class="control-group setting-wide">
            <label class="control-label" for="elm_sync_data_commerceml_cml_catalog_import_mode">{__("commerceml.cml_catalog_import_mode")}:</label>
            <div class="controls">
                <select id="elm_sync_data_commerceml_cml_catalog_import_mode" name="sync_data_settings[{$sync_provider_id}][catalog_importer.import_mode]">
                    {foreach $settings_schema["catalog_importer.import_mode"].variants as $variant}
                        <option value="{$variant}" {if $import_settings["catalog_importer.import_mode"] == $variant}selected="selected"{/if}>{__("commerceml.cml_catalog_import_mode.{$variant|lower}")}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="control-group setting-wide">
            <label class="control-label" for="elm_sync_data_commerceml_cml_catalog_allow_import_offers">{__("commerceml.cml_catalog_allow_import_offers")}:</label>
            <div class="controls">
                <input type="hidden" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_import_offers]" value="N">
                <input id="elm_sync_data_commerceml_cml_catalog_allow_import_offers" type="checkbox" name="sync_data_settings[{$sync_provider_id}][catalog_importer.allow_import_offers]" value="Y" {if $import_settings["catalog_importer.allow_import_offers"]}checked="checked"{/if}>
            </div>
        </div>

        <div class="control-group setting-wide">
            <label class="control-label" for="elm_sync_data_commerceml_cml_default_lang">{__("commerceml.cml_default_lang")}:</label>
            <div class="controls">
                <select id="elm_sync_data_commerceml_cml_default_lang" name="sync_data_settings[{$sync_provider_id}][default_lang]">
                    {foreach $settings_schema["default_lang"].variants as $variant}
                        <option value="{$variant}" {if $import_settings["default_lang"] == $variant}selected="selected"{/if}>{$settings_schema["default_lang"].variants_labels[$variant]}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        {/hook}
    </div>
</fieldset>