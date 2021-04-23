<div class="control-group">
    <label class="control-label" for="pecom_tarif">{__("rus_pecom.tarif")}:</label>
    <div class="controls">
        <select name="shipping_data[service_params][tarif]" id="pecom_tarif">
            <option value="auto" {if $shipping.service_params.tarif == "auto"}selected="selected"{/if}>{__("rus_pecom.auto")}</option>
            <option value="avia" {if $shipping.service_params.tarif == "avia"}selected="selected"{/if}>{__("rus_pecom.avia")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="pecom_take">{__("rus_pecom.take")}:</label>
    <div class="controls">
        <label class="checkbox">
            <input type="hidden" name="shipping_data[service_params][take]" value="N" />
            <input type="checkbox" name="shipping_data[service_params][take]" id="pecom_take" value="Y" {if $shipping.service_params.take == "Y"}checked="checked"{/if}/>
        </label>
    </div>
</div>


<div class="control-group">
    <label class="control-label" for="pecom_deliver">{__("rus_pecom.deliver")}:</label>
    <div class="controls">
        <label class="checkbox">
            <input type="hidden" name="shipping_data[service_params][deliver]" value="N" />
            <input type="checkbox" name="shipping_data[service_params][deliver]" id="pecom_deliver" value="Y" {if $shipping.service_params.deliver == "Y"}checked="checked"{/if}/>
        </label>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="pecom_take">{__("rus_pecom.insurance")}:</label>
    <div class="controls">
        <label class="checkbox">
            <input type="hidden" name="shipping_data[service_params][insurance]" value="N" />
            <input type="checkbox" name="shipping_data[service_params][insurance]" id="pecom_insurance" value="Y" {if $shipping.service_params.insurance == "Y"}checked="checked"{/if}/>
        </label>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="pecom_pal">{__("rus_pecom.pal")}:</label>
    <div class="controls">
        <label class="checkbox">
            <input type="hidden" name="shipping_data[service_params][pal]" value="N" />
            <input type="checkbox" name="shipping_data[service_params][pal]" id="pecom_pal" value="Y" {if $shipping.service_params.pal == "Y"}checked="checked"{/if}/>
        </label>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="pecom_package_hard">{__("rus_pecom.package_hard")}:</label>
    <div class="controls">
        <label class="checkbox">
            <input type="hidden" name="shipping_data[service_params][package_hard]" value="N" />
            <input type="checkbox" name="shipping_data[service_params][package_hard]" id="pecom_package_hard" value="Y" {if $shipping.service_params.package_hard == "Y"}checked="checked"{/if}/>
        </label>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="pecom_take_tent">{__("rus_pecom.take.tent")}:</label>
    <div class="controls">
        <label class="checkbox">
            <input type="hidden" name="shipping_data[service_params][take_tent]" value="N" />
            <input type="checkbox" name="shipping_data[service_params][take_tent]" id="pecom_take_tent" value="Y" {if $shipping.service_params.take_tent == "Y"}checked="checked"{/if}/>
        </label>
        <p class="muted description">{__("rus_pecom.ttl_tent")}</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="pecom_take_gidro">{__("rus_pecom.take.gidro")}:</label>
    <div class="controls">
        <label class="checkbox">
            <input type="hidden" name="shipping_data[service_params][take_gidro]" value="N" />
            <input type="checkbox" name="shipping_data[service_params][take_gidro]" id="pecom_take_gidro" value="Y" {if $shipping.service_params.take_gidro == "Y"}checked="checked"{/if}/>
        </label>
        <p class="muted description">{__("rus_pecom.ttl_gidro")}</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="pecom_take_speed">{__("rus_pecom.take.speed")}:</label>
    <div class="controls">
        <label class="checkbox">
            <input type="hidden" name="shipping_data[service_params][take_speed]" value="N" />
            <input type="checkbox" name="shipping_data[service_params][take_speed]" id="pecom_take_speed]" value="Y" {if $shipping.service_params.take_speed == "Y"}checked="checked"{/if}/>
        </label>
        <p class="muted description">{__("rus_pecom.ttl_gidro")}</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="pecom_take_moscow">{__("rus_pecom.take.moscow")}:</label>
    <div class="controls">
        <select name="shipping_data[service_params][take_moscow]" id="pecom_take_moscow">
            <option value="0" {if $shipping.service_params.take_moscow == "0"}selected="selected"{/if}>{__("rus_pecom.moscow.0")}</option>
            <option value="1" {if $shipping.service_params.take_moscow == "1"}selected="selected"{/if}>{__("rus_pecom.moscow.1")}</option>
            <option value="2" {if $shipping.service_params.take_moscow == "2"}selected="selected"{/if}>{__("rus_pecom.moscow.2")}</option>
            <option value="3" {if $shipping.service_params.take_moscow == "3"}selected="selected"{/if}>{__("rus_pecom.moscow.3")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="pecom_deliver_tent">{__("rus_pecom.deliver.tent")}:</label>
    <div class="controls">
        <label class="checkbox">
            <input type="hidden" name="shipping_data[service_params][deliver_tent]" value="N" />
            <input type="checkbox" name="shipping_data[service_params][deliver_tent]" id="pecom_deliver_tent" value="Y" {if $shipping.service_params.deliver_tent == "Y"}checked="checked"{/if}/>
        </label>
        <p class="muted description">{__("rus_pecom.ttl_tent")}</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="pecom_deliver_gidro">{__("rus_pecom.deliver.gidro")}:</label>
    <div class="controls">
        <label class="checkbox">
            <input type="hidden" name="shipping_data[service_params][deliver_gidro]" value="N" />
            <input type="checkbox" name="shipping_data[service_params][deliver_gidro]" id="pecom_deliver_gidro" value="Y" {if $shipping.service_params.deliver_gidro == "Y"}checked="checked"{/if}/>
        </label>
        <p class="muted description">{__("rus_pecom.ttl_gidro")}</p>
    </div>
</div>


<div class="control-group">
    <label class="control-label" for="pecom_deliver_speed">{__("rus_pecom.deliver.speed")}:</label>
    <div class="controls">
        <label class="checkbox">
            <input type="hidden" name="shipping_data[service_params][deliver_speed]" value="N" />
            <input type="checkbox" name="shipping_data[service_params][deliver_speed]" id="pecom_deliver_speed" value="Y" {if $shipping.service_params.deliver_gidro == "Y"}checked="checked"{/if}/>
        </label>
        <p class="muted description">{__("rus_pecom.ttl_gidro")}</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="pecom_deliver_moscow">{__("rus_pecom.deliver.moscow")}:</label>
    <div class="controls">
        <select name="shipping_data[service_params][deliver_moscow]" id="pecom_deliver_moscow">
            <option value="0" {if $shipping.service_params.deliver_moscow == "0"}selected="selected"{/if}>{__("rus_pecom.moscow.0")}</option>
            <option value="1" {if $shipping.service_params.deliver_moscow == "1"}selected="selected"{/if}>{__("rus_pecom.moscow.1")}</option>
            <option value="2" {if $shipping.service_params.deliver_moscow == "2"}selected="selected"{/if}>{__("rus_pecom.moscow.2")}</option>
            <option value="3" {if $shipping.service_params.deliver_moscow == "3"}selected="selected"{/if}>{__("rus_pecom.moscow.3")}</option>
        </select>
    </div>
</div>

<div class="control-group">
	<label class="control-label cm-required" for="pecom_height">{__("ship_height")}:</label>
	<div class="controls">
	<input id="pecom_height" type="text" name="shipping_data[service_params][height]" size="30" value="{$shipping.service_params.height}"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label cm-required" for="pecom_width">{__("ship_width")}:</label>
	<div class="controls">
	<input id="pecom_width" type="text" name="shipping_data[service_params][width]" size="30" value="{$shipping.service_params.width}"/>
	</div>
</div>

<div class="control-group">
	<label class="control-label cm-required" for="pecom_length">{__("ship_length")}:</label>
	<div class="controls">
	<input id="pecom_length" type="text" name="shipping_data[service_params][length]" size="30" value="{$shipping.service_params.length}"/>
	</div>
</div>
