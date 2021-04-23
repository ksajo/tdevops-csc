<div id="russian_post_services_list">
    {foreach from=$sending_services key="service_id" item="service_data"}
        <div class="control-group">
            <label class="control-label" for="ship_russian_post_{$service_data.name}">{$service_data.label}:</label>
            <div class="controls">
                <input type="hidden" name="shipping_data[service_params][services][{$service_data.name}]" value="N" />
                <input
                    type="checkbox"
                    class="russian-post-service-item"
                    id="russian_post_service_item_{$service_id}"
                    data-ca-exclude-ids="{$service_data.exclude_ids|to_json}"
                    name="shipping_data[service_params][services][{$service_data.name}]"
                    value="{$service_id}"
                    {if $shipping.service_params.services[$service_data.name] == $service_id}checked="checked"{/if}
                />
            </div>
        </div>
    {/foreach}
<!--russian_post_services_list--></div>
