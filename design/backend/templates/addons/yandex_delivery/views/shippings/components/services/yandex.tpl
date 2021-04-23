<fieldset>

    <div class="control-group">
        <label for="ship_yandex_delivery_sender" class="control-label">{__("yandex_delivery.yandex_sender")}:</label>
        <div class="controls">
            <select id="ship_yandex_delivery_sender" name="shipping_data[service_params][sender_id]">
                {foreach from=$senders item="sender" key="id"}
                <option value="{$id}" {if $shipping.service_params.sender_id == $id}selected="selected"{/if}>{$sender}</option>
                {/foreach}
            </select>
        </div>
    </div>

    <div class="control-group">
        <label for="ship_yandex_delivery_warehouse" class="control-label">{__("yandex_delivery.yandex_warehouse")}:</label>
        <div class="controls">
            <select id="ship_yandex_delivery_warehouse" name="shipping_data[service_params][warehouse_id]">
                {foreach from=$warehouses item="sender" key="id"}
                    <option value="{$id}" {if $shipping.service_params.warehouse_id == $id}selected="selected"{/if}>{$sender}</option>
                {/foreach}
            </select>
        </div>
    </div>

    <div class="control-group">
        <label for="ship_yandex_delivery_requisite" class="control-label">{__("yandex_delivery.yandex_requisite")}:</label>
        <div class="controls">
            <select id="ship_yandex_delivery_requisite" name="shipping_data[service_params][requisite_id]">
                {foreach from=$requisites item="requisite" key="id"}
                    <option value="{$id}" {if $shipping.service_params.requisite_id == $id}selected="selected"{/if}>{$requisite}</option>
                {/foreach}
            </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="ship_width">{__("ship_width")}</label>
        <div class="controls">
            <input id="ship_width" type="text" name="shipping_data[service_params][width]" size="30" value="{$shipping.service_params.width}" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="ship_height">{__("ship_height")}</label>
        <div class="controls">
            <input id="ship_height" type="text" name="shipping_data[service_params][height]" size="30" value="{$shipping.service_params.height}" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="ship_length">{__("ship_length")}</label>
        <div class="controls">
            <input id="ship_length" type="text" name="shipping_data[service_params][length]" size="30" value="{$shipping.service_params.length}" />
        </div>
    </div>

    <div class="control-group">
        <label for="type_delivery" class="control-label">{__("yandex_delivery.type_delivery")}:</label>
        <div class="controls">
            <select id="type_delivery" name="shipping_data[service_params][type_delivery]">
                <option value="courier" {if $shipping.service_params.type_delivery == "courier"}selected="selected"{/if}>{__("yandex_delivery.courier")}</option>
                <option value="pickup" {if empty($shipping.service_params.type_delivery) || $shipping.service_params.type_delivery == "pickup"}selected="selected"{/if}>{__("yandex_delivery.pickup")}</option>
            </select>
        </div>
    </div>

    <div class="control-group">
        <label for="sort_type" class="control-label">{__("yandex_delivery.sort_points_list")}:</label>
        <div class="controls">
            <select id="sort_type" name="shipping_data[service_params][sort_type]">
                <option value="no" {if $shipping.service_params.sort_type == "no"}selected="selected"{/if}>{__("yandex_delivery.sort_no")}</option>
                <option value="near" {if empty($shipping.service_params.sort_type) || $shipping.service_params.sort_type == "near"}selected="selected"{/if}>{__("yandex_delivery.sort_near")}</option>
            </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="count_points">{__("yandex_delivery.display_count_pickuppoints")}</label>
        <div class="controls">
            <input id="count_points" type="text" name="shipping_data[service_params][count_points]" size="30" value="{$shipping.service_params.count_points}" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="city_from">{__("yandex_delivery.city_from")}</label>
        <div class="controls">
            <select id="city_from" name="shipping_data[service_params][city_from]">
                {foreach from=$available_city_from item="city"}
                    <option value="{$city}" {if $shipping.service_params.city_from == $city}selected="selected"{/if}>{$city}</option>
                {/foreach}
            </select>
            <p class="muted description">{__("ttc_yandex_delivery.city_from")}</p>
        </div>
    </div>

    <div class="control-group">
        <label for="ship_yandex_delivery_delivery" class="control-label cm-required cm-multiple-checkboxes">{__("yandex_delivery.shipping_services")}:</label>
        <div class="controls" id="ship_yandex_delivery_delivery">
            {foreach from=$deliveries item="delivery" key="id"}
            <label class="checkbox inline" for="delivery_{$id}">
                <input type="checkbox"
                       name="shipping_data[service_params][deliveries][]"
                       id="delivery_{$id}"
                       {if array_key_exists($id, $deliveries_select)}checked="checked"{/if}
                       value="{$id}"
                />
                {$delivery}
            </label>
            {/foreach}
        </div>
    </div>

    <div class="control-group">
        <label for="logging" class="control-label" >{__("yandex_delivery.logging")}:</label>
        <div class="controls">
            <input type="checkbox" name="shipping_data[service_params][logging]" id="logging" value="Y" {if $shipping.service_params.logging == 'Y'} checked="checked"{/if}/>
            <p class="muted description">{__("ttc_yandex_delivery.logging")}</p>
        </div>
    </div>

</fieldset>
