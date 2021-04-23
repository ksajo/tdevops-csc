{script type="text/javascript" src="//insales.boxberry.ru/registration/js/boxberry.reg.js"}
{script src="js/addons/rus_boxberry/boxberry.js"}
<fieldset>
    <div class="control-group">
        <label class="control-label" for="password">{__("rus_boxberry.api_password")}</label>
        <div class="controls">
            <input id="password" type="text" name="shipping_data[service_params][password]" size="30" value="{$shipping.service_params.password}"/>
        </div>
        {if !$shipping.service_params.password}
            <div id="reg_to_boxberry" class="controls">
                <a href="#" onclick="boxberry_registration.open('token_callback');return false;">{__("rus_boxberry.take_api_password")}</a>
            </div>
        {/if}
    </div>
    <div class="control-group">
        <label class="control-label" for="boxberry_target_start">{__("rus_boxberry.target_start")}</label>
        <div class="controls">
            <input id="margin_percent" type="text" name="shipping_data[service_params][boxberry_target_start]" size="30" value="{$shipping.service_params.boxberry_target_start}"/>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="default_weight">{__("rus_boxberry.default_weight")}</label>
        <div class="controls">
            <input id="default_weight" type="text" name="shipping_data[service_params][default_weight]" size="30" value="{$shipping.service_params.default_weight}"/>
        </div>
    </div>
</fieldset>
