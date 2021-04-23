<fieldset>
    <div class="control-group">
        <label for="ship_yandex_delivery_warehouse" class="control-label">{__("yandex_delivery_v3.yandex_warehouse")}:</label>
        <div class="controls">
            <select id="ship_yandex_delivery_warehouse" name="shipping_data[service_params][warehouse_id]" {if !$warehouses}disabled{/if}>
                {foreach $warehouses as $warehouse}
                    <option value="{$warehouse.id}" {if $shipping.service_params.warehouse_id == $warehouse.id}selected="selected"{/if}>{$warehouse.name}</option>
                {/foreach}
            </select>
            <p class="muted description">
                {__("yandex_delivery_v3.warehouse_selector_info", ["[href]" => $addon_settings])}
            </p>
        </div>
    </div>

    <div class="control-group">
        <label for="ship_yandex_delivery_store" class="control-label">{__("yandex_delivery_v3.yandex_store")}:</label>
        <div class="controls">
            <select id="ship_yandex_delivery_store" name="shipping_data[service_params][sender_id]" {if !$stores}disabled{/if}>
                {foreach $stores as $store}
                    <option value="{$store.id}" {if $shipping.service_params.sender_id == $store.id}selected="selected"{/if}>{$store.name}</option>
                {/foreach}
            </select>
            <p class="muted description">
                {__("yandex_delivery_v3.store_selector_info", ["[href]" => $addon_settings])}
            </p>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="ship_width">{__("ship_width")}:</label>
        <div class="controls">
            <input id="ship_width" type="text" name="shipping_data[service_params][width]" size="30" value="{$shipping.service_params.width}" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="ship_height">{__("ship_height")}:</label>
        <div class="controls">
            <input id="ship_height" type="text" name="shipping_data[service_params][height]" size="30" value="{$shipping.service_params.height}" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="ship_length">{__("ship_length")}:</label>
        <div class="controls">
            <input id="ship_length" type="text" name="shipping_data[service_params][length]" size="30" value="{$shipping.service_params.length}" />
        </div>
    </div>

    <div class="control-group">
        <label for="type_delivery" class="control-label">{__("yandex_delivery_v3.type_delivery")}:</label>
        <div class="controls">
            <select id="type_delivery" name="shipping_data[service_params][type_delivery]">
                <option value="{"\Tygh\Addons\YandexDelivery\Enum\DeliveryType::COURIER"|constant}"
                        {if $shipping.service_params.type_delivery == "\Tygh\Addons\YandexDelivery\Enum\DeliveryType::COURIER"|constant}selected="selected"{/if}
                >{__("yandex_delivery_v3.courier")}</option>
                <option value="{"\Tygh\Addons\YandexDelivery\Enum\DeliveryType::PICKUP"|constant}"
                        {if empty($shipping.service_params.type_delivery)
                            || $shipping.service_params.type_delivery == "\Tygh\Addons\YandexDelivery\Enum\DeliveryType::PICKUP"|constant}
                            selected="selected"
                        {/if}
                >{__("yandex_delivery_v3.pickup")}</option>
            </select>
        </div>
    </div>

    <div class="control-group">
        <label for="ship_yandex_delivery_delivery" class="control-label cm-required cm-multiple-checkboxes">{__("yandex_delivery_v3.shipping_services")}:</label>
        <div class="controls" id="ship_yandex_delivery_delivery">
            {foreach $deliveries as $delivery}
                <label class="checkbox inline" for="delivery_{$delivery.id}">
                    <input type="checkbox"
                           name="shipping_data[service_params][deliveries][]"
                           id="delivery_{$delivery.id}"
                           {if in_array($delivery.id, $deliveries_select)}checked="checked"{/if}
                           value="{$delivery.id}"
                    />
                    {$delivery.name}
                </label>
            {/foreach}
            <p class="muted description">
                {__("yandex_delivery_v3.shipping_services_info")}
            </p>
        </div>
    </div>
</fieldset>
