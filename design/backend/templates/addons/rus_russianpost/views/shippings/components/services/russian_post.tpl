<fieldset>
    {if $code == 'russian_pochta'}

        <div class="control-group">
            <label for="ship_russian_post_object_type" class="control-label">{__("shipping.russianpost.russian_post_sending_type")}:</label>
            <div class="controls">
                <select id="ship_russian_post_object_type" name="shipping_data[service_params][object_type]">
                    {foreach from=$sending_objects item="object_group"}
                        <optgroup label="{$object_group.title}">
                            {foreach from=$object_group.variants item="object_type" key="object_code"}
                                <option value={$object_code} {if $shipping.service_params.object_type == $object_code}selected="selected"{/if}>{$object_type}</option>
                            {/foreach}
                        </optgroup>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="control-group">
            <label for="ship_russian_post_sending_packages" class="control-label">{__("shipping.russianpost.russian_post_sending_packages")}:</label>
            <div class="controls">
                <select id="ship_russian_post_sending_package" name="shipping_data[service_params][sending_package]">
                    {foreach from=$sending_packages item="s_package" key="k_package"}
                        <option value={$k_package} {if $shipping.service_params.sending_package == $k_package}selected="selected"{/if}>{$s_package}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="control-group">
            <label for="ship_russian_post_shipping_option" class="control-label">{__("shipping.russianpost.russian_post_shipping_option")}:</label>
            <div class="controls">
                <select id="ship_russian_post_shipping_option" name="shipping_data[service_params][isavia]">
                    <option value="0" {if $shipping.service_params.isavia == "0"}selected="selected"{/if}>{__("addons.rus_russianpost.ground")}</option>
                    <option value="1" {if $shipping.service_params.isavia == "1"}selected="selected"{/if}>{__("addons.rus_russianpost.avia_possible")}</option>
                    <option value="2" {if $shipping.service_params.isavia == "2"}selected="selected"{/if}>{__("addons.rus_russianpost.avia")}</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_delivery">{__("shipping.russianpost.russian_post_cash_on_delivery")}:</label>
            <div class="controls">
                <input id="ship_russian_post_delivery" type="text" name="shipping_data[service_params][cash_on_delivery]" size="30" value="{$shipping.service_params.cash_on_delivery}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_average_quantity_in_packet">{__("shipping.russianpost.average_quantity_in_packet")}:</label>
            <div class="controls">
                <input id="ship_russian_post_average_quantity_in_packet" type="text" name="shipping_data[service_params][average_quantity_in_packet]" size="30" value="{$shipping.service_params.average_quantity_in_packet}" />
            </div>
            <div class="controls">
                <p class="muted description">{__("rus_russian_post.delivery_types_that_require_countinpack")}</p>
            </div>
        </div>

        {include file="addons/rus_russianpost/views/shippings/components/services/russian_post_services.tpl" sending_services=$sending_services}

        {include file="common/subheader.tpl" title=__("shippings.russianpost.data_tracking")}

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_login">{__("shipping.russianpost.russian_post_login")}:</label>
            <div class="controls">
                <input id="ship_russian_post_login" type="text" name="shipping_data[service_params][api_login]" size="30" value="{$shipping.service_params.api_login}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_password">{__("shipping.russianpost.russian_post_password")}:</label>
            <div class="controls">
                <input id="ship_russian_post_password" type="text" name="shipping_data[service_params][api_password]" size="30" value="{$shipping.service_params.api_password}" />
            </div>
        </div>

    {elseif $code == 'russian_post_calc'}

        <div class="control-group">
            <label class="control-label" for="user_key">{__("authentication_key")}</label>
            <div class="controls">
                <input id="user_key" type="text" name="shipping_data[service_params][user_key]" size="30" value="{$shipping.service_params.user_key}"/>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="user_key_password">{__("authentication_password")}</label>
            <div class="controls">
                <input id="user_key_password" type="password" name="shipping_data[service_params][user_key_password]" size="30" value="{$shipping.service_params.user_key_password}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="package_type">{__("russianpost_shipping_type")}</label>
            <div class="controls">
                <select id="package_type" name="shipping_data[service_params][shipping_type]">
                    <option value="rp_main" {if $shipping.service_params.shipping_type == "rp_main"}selected="selected"{/if}>{__("ship_russianpost_shipping_type_rp_main")}</option>
                    <option value="rp_1class" {if $shipping.service_params.shipping_type == "rp_1class"}selected="selected"{/if}>{__("ship_russianpost_shipping_type_rp_1class")}</option>
                </select>
            </div>
        </div>

        <span>{__("ship_russianpost_register_text")}</span>
    {/if}

    {include file="common/subheader.tpl" title=__("rus_russianpost.post_blank_settings") target="#post_blank_settings" meta="collapsed"}
    <fieldset id="post_blank_settings" class="collapse">

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_company">{__("rus_russianpost.company")}:</label>
            <div class="controls">
                <input id="ship_russian_post_company" type="text" name="shipping_data[service_params][post_blank_info][company]" size="30" value="{$shipping.service_params.post_blank_info.company}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_company2">{__("rus_russianpost.company2")}:</label>
            <div class="controls">
                <input id="ship_russian_post_company2" type="text" name="shipping_data[service_params][post_blank_info][company2]" size="30" value="{$shipping.service_params.post_blank_info.company2}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_address">{__("rus_russianpost.address")}:</label>
            <div class="controls">
                <input id="ship_russian_post_address" type="text" name="shipping_data[service_params][post_blank_info][address]" size="30" value="{$shipping.service_params.post_blank_info.address}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_address2">{__("rus_russianpost.address2")}:</label>
            <div class="controls">
                <input id="ship_russian_post_address2" type="text" name="shipping_data[service_params][post_blank_info][address2]" size="30" value="{$shipping.service_params.post_blank_info.address2}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_company_phone">{__("rus_russianpost.company_phone")}:</label>
            <div class="controls">
                <input id="ship_russian_post_company_phone" type="text" name="shipping_data[service_params][post_blank_info][company_phone]" size="30" value="{$shipping.service_params.post_blank_info.company_phone}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_inn">{__("rus_russianpost.inn")}:</label>
            <div class="controls">
                <input id="ship_russian_post_inn" type="text" name="shipping_data[service_params][post_blank_info][inn]" size="30" value="{$shipping.service_params.post_blank_info.inn}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_bank">{__("rus_russianpost.bank")}:</label>
            <div class="controls">
                <input id="ship_russian_post_bank" type="text" name="shipping_data[service_params][post_blank_info][bank]" size="30" value="{$shipping.service_params.post_blank_info.bank}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_ras">{__("rus_russianpost.ras")}:</label>
            <div class="controls">
                <input id="ship_russian_post_ras" type="text" name="shipping_data[service_params][post_blank_info][ras]" size="30" value="{$shipping.service_params.post_blank_info.ras}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_kor">{__("rus_russianpost.kor")}:</label>
            <div class="controls">
                <input id="ship_russian_post_kor" type="text" name="shipping_data[service_params][post_blank_info][kor]" size="30" value="{$shipping.service_params.post_blank_info.kor}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_bik">{__("rus_russianpost.bik")}:</label>
            <div class="controls">
                <input id="ship_russian_post_bik" type="text" name="shipping_data[service_params][post_blank_info][bik]" size="30" value="{$shipping.service_params.post_blank_info.bik}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_index">{__("rus_russianpost.index")}:</label>
            <div class="controls">
                <input id="ship_russian_post_index" type="text" name="shipping_data[service_params][post_blank_info][index]" size="30" value="{$shipping.service_params.post_blank_info.index}" />
            </div>
        </div>
    </fieldset>

    {include file="common/subheader.tpl" title=__("rus_russianpost.post_blank_sender") target="#post_blank_sender" meta="collapsed"}
    <fieldset id="post_blank_sender" class="collapse">
        <div class="control-group">
            <label class="control-label" for="ship_russian_post_fiz_fio">{__("rus_russianpost.fiz_fio")}:</label>
            <div class="controls">
                <input id="ship_russian_post_fiz_fio" type="text" name="shipping_data[service_params][post_blank_info][fiz_fio]" size="30" value="{$shipping.service_params.post_blank_info.fiz_fio}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_fiz_address">{__("rus_russianpost.fiz_address")}:</label>
            <div class="controls">
                <input id="ship_russian_post_fiz_address" type="text" name="shipping_data[service_params][post_blank_info][fiz_address]" size="30" value="{$shipping.service_params.post_blank_info.fiz_address}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_fiz_address2">{__("rus_russianpost.fiz_address2")}:</label>
            <div class="controls">
                <input id="ship_russian_post_fiz_address2" type="text" name="shipping_data[service_params][post_blank_info][fiz_address2]" size="30" value="{$shipping.service_params.post_blank_info.fiz_address2}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_fiz_index">{__("rus_russianpost.fiz_index")}:</label>
            <div class="controls">
                <input id="ship_russian_post_fiz_index" type="text" name="shipping_data[service_params][post_blank_info][fiz_index]" size="30" value="{$shipping.service_params.post_blank_info.fiz_index}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_fiz_doc">{__("rus_russianpost.fiz_doc")}:</label>
            <div class="controls">
                <input id="ship_russian_post_fiz_doc" type="text" name="shipping_data[service_params][post_blank_info][fiz_doc]" size="30" value="{$shipping.service_params.post_blank_info.fiz_doc|default: "паспорт"}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_fiz_doc_serial">{__("rus_russianpost.fiz_doc_serial")}:</label>
            <div class="controls">
                <input id="ship_russian_post_fiz_doc_serial" type="text" name="shipping_data[service_params][post_blank_info][fiz_doc_serial]" size="30" value="{$shipping.service_params.post_blank_info.fiz_doc_serial}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_fiz_doc_number">{__("rus_russianpost.fiz_doc_number")}:</label>
            <div class="controls">
                <input id="ship_russian_post_fiz_doc_number" type="text" name="shipping_data[service_params][post_blank_info][fiz_doc_number]" size="30" value="{$shipping.service_params.post_blank_info.fiz_doc_number}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_fiz_doc_date">{__("rus_russianpost.fiz_doc_date")}:</label>
            <div class="controls">
                <input id="ship_russian_post_fiz_doc_date" type="text" name="shipping_data[service_params][post_blank_info][fiz_doc_date]" size="30" value="{$shipping.service_params.post_blank_info.fiz_doc_date}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_fiz_doc_date2">{__("rus_russianpost.fiz_doc_date2")}:</label>
            <div class="controls">
                <input id="ship_russian_post_fiz_doc_date2" type="text" name="shipping_data[service_params][post_blank_info][fiz_doc_date2]" size="30" value="{$shipping.service_params.post_blank_info.fiz_doc_date2}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ship_russian_post_fiz_doc_creator">{__("rus_russianpost.fiz_doc_creator")}:</label>
            <div class="controls">
                <input id="ship_russian_post_fiz_doc_creator" type="text" name="shipping_data[service_params][post_blank_info][fiz_doc_creator]" size="30" value="{$shipping.service_params.post_blank_info.fiz_doc_creator}" />
            </div>
        </div>
    </fieldset>

    {include file="common/subheader.tpl" title=__("rus_russianpost.size_spacing_forms") target="#size_spacing_forms" meta="collapsed"}
    <fieldset id="size_spacing_forms" class="collapse">
        {include file="common/subheader.tpl" title=__("rus_russianpost.107_list") target="#107_list" meta="collapsed"}
        <fieldset id="107_list" class="collapse">
            <div class="control-group">
                <label class="control-label" for="ship_russian_post_107_list_width">{__("rus_russianpost.fiz_doc_creator")}:</label>
                <div class="controls">
                    <input id="ship_russian_post_107_list_width" type="text" name="shipping_data[service_params][post_blank_info][107_list_width]" size="30" value="{$shipping.service_params.post_blank_info.107_list_width|default: "293"}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="ship_russian_post_107_list_height">{__("rus_russianpost.107_list_height")}:</label>
                <div class="controls">
                    <input id="ship_russian_post_107_list_height" type="text" name="shipping_data[service_params][post_blank_info][107_list_height]" size="30" value="{$shipping.service_params.post_blank_info.107_list_height|default: "205"}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="ship_russian_post_107_top">{__("rus_russianpost.107_top")}:</label>
                <div class="controls">
                    <input id="ship_russian_post_107_top" type="text" name="shipping_data[service_params][post_blank_info][107_top]" size="30" value="{$shipping.service_params.post_blank_info.107_top|default: "0"}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="ship_russian_post_107_left">{__("rus_russianpost.107_left")}:</label>
                <div class="controls">
                    <input id="ship_russian_post_107_left" type="text" name="shipping_data[service_params][post_blank_info][107_left]" size="30" value="{$shipping.service_params.post_blank_info.107_left|default: "0"}" />
                </div>
            </div>
        </fieldset>

        {include file="common/subheader.tpl" title=__("rus_russianpost.116_list") target="#116_list" meta="collapsed"}
        <fieldset id="116_list" class="collapse">
            <div class="control-group">
                <label class="control-label" for="ship_russian_post_116_list_width">{__("rus_russianpost.116_list_width")}:</label>
                <div class="controls">
                    <input id="ship_russian_post_116_list_width" type="text" name="shipping_data[service_params][post_blank_info][116_list_width]" size="30" value="{$shipping.service_params.post_blank_info.116_list_width|default: "293"}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="ship_russian_post_116_list_height">{__("rus_russianpost.116_list_height")}:</label>
                <div class="controls">
                    <input id="ship_russian_post_116_list_height" type="text" name="shipping_data[service_params][post_blank_info][116_list_height]" size="30" value="{$shipping.service_params.post_blank_info.116_list_height|default: "205"}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="ship_russian_post_116_top">{__("rus_russianpost.116_top")}:</label>
                <div class="controls">
                    <input id="ship_russian_post_116_top" type="text" name="shipping_data[service_params][post_blank_info][116_top]" size="30" value="{$shipping.service_params.post_blank_info.116_top|default: "0"}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="ship_russian_post_116_left">{__("rus_russianpost.116_left")}:</label>
                <div class="controls">
                    <input id="ship_russian_post_116_left" type="text" name="shipping_data[service_params][post_blank_info][116_left]" size="30" value="{$shipping.service_params.post_blank_info.116_left|default: "0"}" />
                </div>
            </div>
        </fieldset>

        {include file="common/subheader.tpl" title=__("rus_russianpost.7p_list") target="#7p_list" meta="collapsed"}
        <fieldset id="7p_list" class="collapse">
            <div class="control-group">
                <label class="control-label" for="ship_russian_post_7p_top">{__("rus_russianpost.7p_top")}:</label>
                <div class="controls">
                    <input id="ship_russian_post_7p_top" type="text" name="shipping_data[service_params][post_blank_info][7p_top]" size="30" value="{$shipping.service_params.post_blank_info.7p_top|default: "0"}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="ship_russian_post_7p_left">{__("rus_russianpost.7p_left")}:</label>
                <div class="controls">
                    <input id="ship_russian_post_7p_left" type="text" name="shipping_data[service_params][post_blank_info][7p_left]" size="30" value="{$shipping.service_params.post_blank_info.7p_left|default: "0"}" />
                </div>
            </div>
        </fieldset>

        {include file="common/subheader.tpl" title=__("rus_russianpost.7a_list") target="#7a_list" meta="collapsed"}
        <fieldset id="7a_list" class="collapse">
            <div class="control-group">
                <label class="control-label" for="ship_russian_post_7a_top">{__("rus_russianpost.7a_top")}:</label>
                <div class="controls">
                    <input id="ship_russian_post_7a_top" type="text" name="shipping_data[service_params][post_blank_info][7a_top]" size="30" value="{$shipping.service_params.post_blank_info.7a_top|default: "0"}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="ship_russian_post_7a_left">{__("rus_russianpost.7a_left")}:</label>
                <div class="controls">
                    <input id="ship_russian_post_7a_left" type="text" name="shipping_data[service_params][post_blank_info][7a_left]" size="30" value="{$shipping.service_params.post_blank_info.7a_left|default: "0"}" />
                </div>
            </div>
        </fieldset>

        {include file="common/subheader.tpl" title=__("rus_russianpost.112ep_list") target="#112ep_list" meta="collapsed"}
        <fieldset id="112ep_list" class="collapse">
            <div class="control-group">
                <label class="control-label" for="ship_russian_post_112_list_width">{__("rus_russianpost.112_list_width")}:</label>
                <div class="controls">
                    <input id="ship_russian_post_112_list_width" type="text" name="shipping_data[service_params][post_blank_info][112_list_width]" size="30" value="{$shipping.service_params.post_blank_info.112_list_width|default: "210"}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="ship_russian_post_112_list_height">{__("rus_russianpost.112_list_height")}:</label>
                <div class="controls">
                    <input id="ship_russian_post_112_list_height" type="text" name="shipping_data[service_params][post_blank_info][112_list_height]" size="30" value="{$shipping.service_params.post_blank_info.112_list_height|default: "293"}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="ship_russian_post_112_top">{__("rus_russianpost.112_top")}:</label>
                <div class="controls">
                    <input id="ship_russian_post_112_top" type="text" name="shipping_data[service_params][post_blank_info][112_top]" size="30" value="{$shipping.service_params.post_blank_info.112_top|default: "0"}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="ship_russian_post_112_left">{__("rus_russianpost.112_left")}:</label>
                <div class="controls">
                    <input id="ship_russian_post_112_left" type="text" name="shipping_data[service_params][post_blank_info][112_left]" size="30" value="{$shipping.service_params.post_blank_info.112_left|default: "0"}" />
                </div>
            </div>
        </fieldset>
    </fieldset>

</fieldset>

{if $code == 'russian_pochta'}
    {script src="js/addons/rus_russianpost/russian_pochta.js"}
{/if}
