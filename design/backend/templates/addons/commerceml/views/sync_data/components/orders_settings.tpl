{include file="common/subheader.tpl" title=__("commerceml.orders_export")}
<fieldset>
    {hook name="commerceml:orders_settings_export"}
    <div class="control-group setting-wide">
        <label class="control-label">{__("commerceml.cml_order_export_strategy")}:</label>

        <div class="controls">
            {foreach $settings_schema["orders_exporter.strategy"].variants as $variant}
                <label class="radio">
                    <input type="radio" value="{$variant}" name="sync_data_settings[{$sync_provider_id}][orders_exporter.strategy]" {if $import_settings["orders_exporter.strategy"] === $variant}checked="checked"{/if}>
                    {__("commerceml.cml_orders_exporter_strategy.{$variant}")}
                </label>
            {/foreach}
        </div>
    </div>

    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_order_statuses_filter">{__("commerceml.cml_order_statuses_filter")}:</label>
        <div class="controls cm-switch-availability-container cm-switch-inverse" id="sw_block_variant_cml_order_statuses_filter_all">
            <div id="block_variant_cml_order_statuses_filter_all">
                <label for="variant_cml_order_statuses_filter_{$status_code}" class="checkbox">
                    <input
                        type="checkbox"
                        name="sync_data_settings[{$sync_provider_id}][orders_exporter.statuses_filter][]"
                        id="variant_cml_order_statuses_filter_all"
                        value="all"
                        class="user-success cm-check-items cm-off cm-skip-unselect-all cm-item cm-item-status-commerceml-order-status-all"
                        data-ca-status="commerceml-order-status"
                        {if $import_settings["orders_exporter.statuses_filter"]|count === 0}
                            checked="checked"
                            disabled="disabled"
                        {/if}
                    >
                    {__("all")}
                </label>
            </div>
            {foreach $settings_schema["orders_exporter.statuses_filter"].variants as $variant}
                <label for="variant_cml_order_statuses_filter_{$status_code}" class="checkbox">
                    <input
                        type="checkbox"
                        name="sync_data_settings[{$sync_provider_id}][orders_exporter.statuses_filter][]"
                        id="variant_cml_order_statuses_filter_{$variant}"
                        class="user-success cm-switch-availability cm-check-disabled cm-check-items cm-off cm-skip-unselect-all cm-item cm-item-status-commerceml-order-status"
                        data-ca-status="commerceml-order-status-all"
                        value="{$variant}"
                        {if $variant|in_array:$import_settings["orders_exporter.statuses_filter"]}checked="checked"{/if}
                    >
                    {$settings_schema["orders_exporter.statuses_filter"].variants_labels[$variant]}
                </label>
            {/foreach}
        </div>
    </div>

    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_order_export_from_order_id">
            {__("commerceml.cml_order_export_from_order_id")}:
            <div class="muted description">{__("commerceml.cml_order_export_from_order_id.tooltip")}</div>
        </label>

        <div class="controls">
            <input id="elm_sync_data_commerceml_cml_order_export_from_order_id" type="text" name="sync_data_settings[{$sync_provider_id}][orders_exporter.export_from_order_id]" size="30" value="{$import_settings["orders_exporter.export_from_order_id"]}" class="user-success" aria-invalid="false">
        </div>
    </div>

    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_order_export_statuses">{__("commerceml.cml_order_export_statuses")}:</label>
        <div class="controls">
            <input type="hidden" name="sync_data_settings[{$sync_provider_id}][orders_exporter.export_order_statuses]" value="N">
            <input id="elm_sync_data_commerceml_cml_order_export_statuses" type="checkbox" name="sync_data_settings[{$sync_provider_id}][orders_exporter.export_order_statuses]" value="Y" {if $import_settings["orders_exporter.export_order_statuses"] == "YesNo::YES"|enum}checked="checked"{/if}>
        </div>
    </div>

    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_order_export_product_options">{__("commerceml.cml_order_export_product_options")}:</label>
        <div class="controls">
            <input type="hidden" name="sync_data_settings[{$sync_provider_id}][orders_exporter.export_product_options]" value="N">
            <input id="elm_sync_data_commerceml_cml_order_export_product_options" type="checkbox" name="sync_data_settings[{$sync_provider_id}][orders_exporter.export_product_options]" value="Y" {if $import_settings["orders_exporter.export_product_options"] == "YesNo::YES"|enum}checked="checked"{/if}>
        </div>
    </div>

    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_order_export_shipping_fee">
            {__("commerceml.cml_order_export_shipping_fee")}:
            <div class="muted description">{__("commerceml.cml_order_export_shipping_fee.tooltip")}</div>
        </label>
        <div class="controls">
            <input type="hidden" name="sync_data_settings[{$sync_provider_id}][orders_exporter.export_shipping_fee]" value="N">
            <input id="elm_sync_data_commerceml_cml_order_export_shipping_fee" type="checkbox" name="sync_data_settings[{$sync_provider_id}][orders_exporter.export_shipping_fee]" value="Y" {if $import_settings["orders_exporter.export_shipping_fee"]}checked="checked"{/if}>
        </div>
    </div>
    {/hook}
</fieldset>

{include file="common/subheader.tpl" title=__("commerceml.orders_import")}

<fieldset>
    {hook name="commerceml:orders_settings_import"}
    <div class="control-group setting-wide">
        <label class="control-label" for="elm_sync_data_commerceml_cml_order_import_changes">{__("commerceml.cml_orders_import_changes")}:</label>
        <div class="controls">
            <input type="hidden" name="sync_data_settings[{$sync_provider_id}][orders_importer.import_changes]" value="N">
            <input id="elm_sync_data_commerceml_cml_order_import_changes" type="checkbox" name="sync_data_settings[{$sync_provider_id}][orders_importer.import_changes]" value="Y" {if $import_settings["orders_importer.import_changes"]}checked="checked"{/if}>
        </div>
    </div>
    {/hook}
</fieldset>