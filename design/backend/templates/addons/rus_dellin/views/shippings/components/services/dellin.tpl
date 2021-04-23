<fieldset>

<div class="control-group">
    <label class="control-label" for="appkey">{__("shipping.rus_dellin.appkey")}:</label>
    <div class="controls">
        <input id="appkey" type="text" name="shipping_data[service_params][appkey]" size="30" value="{$shipping.service_params.appkey}"/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="login">{__("shipping.rus_dellin.login")}:</label>
    <div class="controls">
        <input id="login" type="text" name="shipping_data[service_params][login]" size="30" value="{$shipping.service_params.login}"/>
        <p class="muted description">{__("shipping.rus_dellin.login.tooltip")}</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="password">{__("shipping.rus_dellin.password")}:</label>
    <div class="controls">
        <input id="password" type="text" name="shipping_data[service_params][password]" size="30" value="{$shipping.service_params.password}"/>
        <p class="muted description">{__("shipping.rus_dellin.password.tooltip")}</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="individual_calculator">{__("shipping.rus_dellin.individual_calculator")}:</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][individual_calculator]" value="N" />
        <input type="checkbox" name="shipping_data[service_params][individual_calculator]" value="Y" {if $shipping.service_params.individual_calculator == "Y"}checked="checked"{/if} />
        <p class="muted description">{__("shipping.rus_dellin.individual_calculator.tooltip")}</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="derival_door">{__("shipping.rus_dellin.derival_door")}:</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][derival_door]" value="N" />
        <input type="checkbox" name="shipping_data[service_params][derival_door]" value="Y" {if $shipping.service_params.derival_door == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="arrival_door">{__("shipping.rus_dellin.arrival_door")}:</label>
    <div class="controls">
        <input type="hidden" name="shipping_data[service_params][arrival_door]" value="N" />
        <input type="checkbox" name="shipping_data[service_params][arrival_door]" value="Y" {if $shipping.service_params.arrival_door == "Y"}checked="checked"{/if} />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="avia_delivery">{__("shipping.rus_dellin.avia_delivery")}:</label>
    <div class="controls">
        <select name="shipping_data[service_params][avia_delivery]" id="avia_delivery">
            <option value="0" {if $shipping.service_params.avia_delivery == '0'} selected="selected"{/if}>{__("no")}</option>
            <option value="1" {if $shipping.service_params.avia_delivery == '1'} selected="selected"{/if}>{__("yes")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="small_delivery">{__("shipping.rus_dellin.small_delivery")}:</label>
    <div class="controls">
        <select name="shipping_data[service_params][small_delivery]" id="small_delivery">
            <option value="0" {if $shipping.service_params.small_delivery == '0'} selected="selected"{/if}>{__("no")}</option>
            <option value="1" {if $shipping.service_params.small_delivery == '1'} selected="selected"{/if}>{__("yes")}</option>
        </select>
        <p class="muted description">{__("shipping.rus_dellin.small_delivery.tooltip")}</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="express_delivery">{__("shipping.rus_dellin.express_delivery")}:</label>
    <div class="controls">
        <select name="shipping_data[service_params][express_delivery]" id="express_delivery">
            <option value="0" {if $shipping.service_params.express_delivery == '0'} selected="selected"{/if}>{__("no")}</option>
            <option value="1" {if $shipping.service_params.express_delivery == '1'} selected="selected"{/if}>{__("yes")}</option>
        </select>
        <p class="muted description">{__("shipping.rus_dellin.express_delivery.tooltip")}</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="package">{__("shipping.rus_dellin.package")}:</label>
    <div class="controls">
        <select name="shipping_data[service_params][package]" id="package">
            <option value="not" {if $shipping.service_params.package == 'not'} selected="selected"{/if}>{__("no")}</option>
            <option value="tough_packing" {if $shipping.service_params.package == 'tough_packing'} selected="selected"{/if}>{__("shipping.rus_dellin.tough_packing")}</option>
            <option value="tough_box" {if $shipping.service_params.package == 'tough_box'} selected="selected"{/if}>{__("shipping.rus_dellin.tough_box")}</option>
            <option value="cardboard" {if $shipping.service_params.package == 'cardboard'} selected="selected"{/if}>{__("shipping.rus_dellin.cardboard")}</option>
            <option value="additional_package" {if $shipping.service_params.package == 'additional_package'} selected="selected"{/if}>{__("shipping.rus_dellin.additional_package")}</option>
            <option value="pellicle" {if $shipping.service_params.package == 'pellicle'} selected="selected"{/if}>{__("shipping.rus_dellin.pellicle")}</option>
            <option value="bag" {if $shipping.service_params.package == 'bag'} selected="selected"{/if}>{__("shipping.rus_dellin.bag")}</option>
            <option value="pallet" {if $shipping.service_params.package == 'pallet'} selected="selected"{/if}>{__("shipping.rus_dellin.pallet")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="derival_services">{__("shipping.rus_dellin.derival_services")}:</label>
    <div class="controls">
        <ul style="list-style-type:none">
            <li>
                <input type="checkbox" name="shipping_data[service_params][derival_services][lateral_loading]" value="lateral_loading" {if $shipping.service_params.derival_services.lateral_loading == "lateral_loading"}checked="checked"{/if} />
                {__("shipping.rus_dellin.lateral_loading")}
            </li>
            <li>
                <input type="checkbox" name="shipping_data[service_params][derival_services][top_loading]" value="top_loading" {if $shipping.service_params.derival_services.top_loading == "top_loading"}checked="checked"{/if} />
                {__("shipping.rus_dellin.top_loading")}
            </li>
            <li>
                <input type="checkbox" name="shipping_data[service_params][derival_services][loadinglift]" value="loadinglift" {if $shipping.service_params.derival_services.loadinglift == "loadinglift"}checked="checked"{/if} />
                {__("shipping.rus_dellin.loadinglift")}
            </li>
            <li>
                <input type="checkbox" name="shipping_data[service_params][derival_services][manipulator]" value="manipulator" {if $shipping.service_params.derival_services.manipulator == "manipulator"}checked="checked"{/if} />
                {__("shipping.rus_dellin.manipulator")}
            </li>
            <li>
                <input type="checkbox" name="shipping_data[service_params][derival_services][open_car]" value="open_car" {if $shipping.service_params.derival_services.open_car == "open_car"}checked="checked"{/if} />
                {__("shipping.rus_dellin.open_car")}
            </li>
            <li>
                <input type="checkbox" name="shipping_data[service_params][derival_services][movable]" value="movable" {if $shipping.service_params.derival_services.movable == "movable"}checked="checked"{/if} />
                {__("shipping.rus_dellin.movable")}
            </li>
        </ul>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="arrival_services">{__("shipping.rus_dellin.arrival_services")}:</label>
    <div class="controls">
        <ul style="list-style-type:none">
            <li>
                <input type="checkbox" name="shipping_data[service_params][arrival_services][lateral_loading]" value="lateral_loading" {if $shipping.service_params.arrival_services.lateral_loading == "lateral_loading"}checked="checked"{/if} />
                {__("shipping.rus_dellin.lateral_loading")}
            </li>
            <li>
                <input type="checkbox" name="shipping_data[service_params][arrival_services][top_loading]" value="top_loading" {if $shipping.service_params.arrival_services.top_loading == "top_loading"}checked="checked"{/if} />
                {__("shipping.rus_dellin.top_loading")}
            </li>
            <li>
                <input type="checkbox" name="shipping_data[service_params][arrival_services][loadinglift]" value="loadinglift" {if $shipping.service_params.arrival_services.loadinglift == "loadinglift"}checked="checked"{/if} />
                {__("shipping.rus_dellin.loadinglift")}
            </li>
            <li>
                <input type="checkbox" name="shipping_data[service_params][arrival_services][manipulator]" value="manipulator" {if $shipping.service_params.arrival_services.manipulator == "manipulator"}checked="checked"{/if} />
                {__("shipping.rus_dellin.manipulator")}
            </li>
            <li>
                <input type="checkbox" name="shipping_data[service_params][arrival_services][open_car]" value="open_car" {if $shipping.service_params.arrival_services.open_car == "open_car"}checked="checked"{/if} />
                {__("shipping.rus_dellin.open_car")}
            </li>
            <li>
                <input type="checkbox" name="shipping_data[service_params][arrival_services][movable]" value="movable" {if $shipping.service_params.arrival_services.movable == "movable"}checked="checked"{/if} />
                {__("shipping.rus_dellin.movable")}
            </li>
        </ul>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="dellin_height">{__("shipping.rus_dellin.height")}:</label>
    <div class="controls">
        <input id="dellin_height" type="text" name="shipping_data[service_params][height]" size="30" value="{$shipping.service_params.height}"/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="dellin_length">{__("shipping.rus_dellin.length")}:</label>
    <div class="controls">
        <input id="dellin_length" type="text" name="shipping_data[service_params][length]" size="30" value="{$shipping.service_params.length}"/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="dellin_width">{__("shipping.rus_dellin.width")}:</label>
    <div class="controls">
        <input id="dellin_width" type="text" name="shipping_data[service_params][width]" size="30" value="{$shipping.service_params.width}"/>
    </div>
</div>

</fieldset>
