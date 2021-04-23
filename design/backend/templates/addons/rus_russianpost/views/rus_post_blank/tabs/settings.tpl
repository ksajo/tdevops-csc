<fieldset>

    <div class="control-group">
        <label class="control-label" for="blank_not_total">{__("addons.rus_russianpost.not_total")}:</label>
        <div class="controls">
            <label class="checkbox">
                <input type="checkbox" name="blank_data[not_total]" id="blank_not_total" value="Y"/>
            </label>
        </div>
    </div>

    <div class="control-group">
        <label for="blank_total_cen" class="control-label">{__("addons.rus_russianpost.declared_total")}</label>
        <div class="controls">
            <input type="text" name="blank_data[total_cen]" id="blank_total_cen" value="{$pre_total.price_declared}" size="40" class="input-large" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="blank_imposed_total">{__("addons.rus_russianpost.use_imposed")}:</label>
        <div class="controls">
            <label class="checkbox">
                <input type="checkbox" name="blank_data[imposed_total]" id="blank_imposed_total" value="Y"/>
            </label>
        </div>
    </div>

    <div class="control-group">
        <label for="blank_total_cod" class="control-label">{__("addons.rus_russianpost.imposed_total")}</label>
        <div class="controls">
            <input type="text" name="blank_data[total_cod]" id="blank_total_cod" value="{$pre_total.price}" size="40" class="input-large" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_type_mailing">{__("addons.rus_russianpost.type_mailing")}</label>
        <div class="controls">
            <select class="span3" name="blank_data[type_mailing]" id="elm_type_mailing">
                <option value="parcel" >{__("addons.rus_russianpost.type_parcel")}</option>
                <option value="package" >{__("addons.rus_russianpost.type_package")}</option>
            </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="blank_sender">{__("sender")} <small>({__("rus_post_blank.7p")}, {__("addons.rus_russianpost.blank_7a")})</small></label>
        <div class="controls">
            <select class="span3" name="blank_data[sender]" id="blank_sender">
                <option value="1" >{__("company")}</option>
                <option value="0" >{__("rus_post_blank.fiz")}</option>
            </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="blank_print_bg">{__("rus_post_blank.print_bg")}:</label>
        <div class="controls">
            <label class="checkbox">
                <input type="hidden" name="blank_data[print_bg]" value="N" />
                <input type="checkbox" name="blank_data[print_bg]" id="blank_print_bg" checked="checked" value="Y"/>
            </label>
            <p class="muted description">{__("rus_post_blank.print_bg.tooltip")}</p>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="blank_print_pdf">PDF:</label>
        <div class="controls">
            <label class="checkbox">
                <input type="hidden" name="blank_data[print_pdf]" value="N" />
                <input type="checkbox" name="blank_data[print_pdf]" id="blank_print_pdf" checked="checked" value="Y"/>
            </label>
        </div>
    </div>

    {include file="common/subheader.tpl" title=__("rus_post_blank.sms_settings")}

    <div class="control-group">
        <label class="control-label" for="blank_print_sms_for_sender">{__("rus_post_blank.sms_for_sender")}</label>
        <div class="controls">
            <label class="checkbox">
                <input type="hidden" name="blank_data[sms_for_sender]" value="N" />
                <input type="checkbox" name="blank_data[sms_for_sender]" id="blank_print_sms_for_sender" checked="checked" value="Y"/>
            </label>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="blank_print_sms_for_recepient">{__("rus_post_blank.sms_for_recepient")}</label>
        <div class="controls">
            <label class="checkbox">
                <input type="hidden" name="blank_data[sms_for_recepient]" value="N" />
                <input type="checkbox" name="blank_data[sms_for_recepient]" id="blank_print_sms_for_recepient" checked="checked" value="Y"/>
            </label>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="blank_text">{__("rus_post_blank.text")} <small>({__("rus_post_blank.line_1")})</small></label>
        <div class="controls">
            <input type="text" name="blank_data[text1]" id="blank_text" value="" size="40" maxlength="35" class="input-large" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="blank_text2">{__("rus_post_blank.text")} <small>({__("rus_post_blank.line_2")})</small></label>
        <div class="controls">
            <input type="text" name="blank_data[text2]" id="blank_text2" value="" size="40" maxlength="35" class="input-large" />
        </div>
    </div>
</fieldset>