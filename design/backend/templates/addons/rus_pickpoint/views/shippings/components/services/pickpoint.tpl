{include file="common/subheader.tpl" title=__("addons.rus_pickpoint.account_settings") target="#pickpoint_account_settings" meta="in collapse"}
<fieldset id="pickpoint_account_settings" class="in collapse">
    <div class="control-group">
        <label class="control-label" for="pickpoint_login">{__("addons.rus_pickpoint.login")}</label>
        <div class="controls">
            <input id="pickpoint_login" type="text" name="shipping_data[service_params][pickpoint_info][login]" size="30" value="{$shipping.service_params.pickpoint_info.login}" />
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="pickpoint_password">{__("addons.rus_pickpoint.password")}</label>
        <div class="controls">
            <input id="pickpoint_password" type="text" name="shipping_data[service_params][pickpoint_info][password]" size="30" value="{$shipping.service_params.pickpoint_info.password}" />
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="pickpoint_ikn">{__("addons.rus_pickpoint.ikn")}</label>
        <div class="controls">
            <input id="pickpoint_ikn" type="text" name="shipping_data[service_params][pickpoint_info][ikn]" size="30" value="{$shipping.service_params.pickpoint_info.ikn}" />
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="pickpoint_server_type">{__("addons.rus_pickpoint.server_type")}</label>
        <div class="controls">
            <select name="shipping_data[service_params][pickpoint_info][server]" id="pickpoint_server_type">
                <option value="work" {if $shipping.service_params.pickpoint_info.server === "work"}selected="selected"{/if}>{__("addons.rus_pickpoint.server_type.work")}</option>
                <option value="test" {if $shipping.service_params.pickpoint_info.server === "test"}selected="selected"{/if}>{__("addons.rus_pickpoint.server_type.test")}</option>
            </select>
        </div>
    </div>
    <div class="control-group">
        <label for="pickpoint_server_secure" class="control-label">{__("addons.rus_pickpoint.server_secure")}:</label>
        <div class="controls"><input type="hidden" name="shipping_data[service_params][pickpoint_info][secure_protocol]" value="{"YesNo::NO"|enum}" />
            <input type="checkbox"
                   name="shipping_data[service_params][pickpoint_info][secure_protocol]"
                   id="pickpoint_server_secure"
                   value="{"YesNo::YES"|enum}"
                   {if $shipping.service_params.pickpoint_info.secure_protocol == "YesNo::YES"|enum}checked="checked"{/if}
            />
        </div>
    </div>
</fieldset>
{include file="common/subheader.tpl" title=__("addons.rus_pickpoint.package_settings") target="#pickpoint_package_settings" meta="in collapse"}
<fieldset id="pickpoint_package_settings" class="in collapse">
    <div class="control-group">
        <label class="control-label" for="pickpoint_width">{__("ship_width")}</label>
        <div class="controls">
            <input id="pickpoint_width" type="text" name="shipping_data[service_params][pickpoint_width]" size="30" value="{$shipping.service_params.pickpoint_width}" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="pickpoint_height">{__("ship_height")}</label>
        <div class="controls">
            <input id="pickpoint_height" type="text" name="shipping_data[service_params][pickpoint_height]" size="30" value="{$shipping.service_params.pickpoint_height}" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="pickpoint_length">{__("ship_length")}</label>
        <div class="controls">
            <input id="pickpoint_length" type="text" name="shipping_data[service_params][pickpoint_length]" size="30" value="{$shipping.service_params.pickpoint_length}" />
        </div>
    </div>

<div class="control-group">
    <label class="control-label" for="delivery_mode">{__("addons.rus_pickpoint.delivery_mode")}</label>
    <div class="controls">
        <select name="shipping_data[service_params][delivery_mode]" id="delivery_mode">
            <option value="Standard" {if $shipping.service_params.delivery_mode == "Standard"}selected="selected"{/if}>{__("addons.rus_pickpoint.standard")}</option>
            <option value="Priority" {if $shipping.service_params.delivery_mode == "Priority"}selected="selected"{/if}>{__("addons.rus_pickpoint.priority")}</option>
        </select>
        <p class="muted description">{__("ttc_addons.rus_pickpoint.delivery_mode")}</p>
    </div>
</div>
</fieldset>
