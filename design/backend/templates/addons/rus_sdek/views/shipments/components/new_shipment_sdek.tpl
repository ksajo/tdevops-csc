{script src="js/tygh/tabs.js"}
{script src="js/lib/maskedinput/jquery.maskedinput.min.js"}
{script src="js/lib/inputmask/jquery.inputmask.min.js"}
{script src="js/addons/rus_sdek/sdek.js"}

{assign var="shipment_id" value=$shipment.shipment_id}
{assign var="register_id" value=0}
{assign var="id" value=$shipment.shipment_id}

{if $data_shipments.$shipment_id.register_id}
    {assign var="register_id" value=$data_shipments.$shipment_id.register_id}
{/if}

{if $smarty.request.selected_section}
    {assign var="active_tab" value=$smarty.request.selected_section}
{else}
    {assign var="active_tab" value='tab_general_`$id`'}
{/if}

<div id="content_group{$id}">
{if $data_shipments.$shipment_id}
    {assign var="data_shipment" value=$data_shipments.$shipment_id}

    <form action="{""|fn_url}" method="post" name="sdek_form_{$id}" class="form-horizontal form-edit cm-disable-empty-files">
        {if !$in_popup}
            <input type="hidden" name="selected_section" id="selected_section" value="{$smarty.request.selected_section}" />
        {/if}
        <input type="hidden" class="cm-no-hide-input" name="redirect_url" value="{$return_url|default:$smarty.request.return_url}" />

        <input type="hidden" name="order_id" value="{$shipment.order_id}" />
        <input type="hidden" name="add_sdek_info[{$shipment.shipment_id}][Order][RecCityCode]" value="{$rec_city_code}" />
        <input type="hidden" name="add_sdek_info[{$shipment.shipment_id}][Order][SendCityCode]" value="{$data_shipment.send_city_code}" />
        <input type="hidden" name="add_sdek_info[{$shipment.shipment_id}][Order][TariffTypeCode]" value="{$data_shipment.tariff_id}" />

        <div class="tabs cm-j-tabs cm-track">
            <ul class="nav nav-tabs">
                <li id="tab_general_{$id}" class="cm-js {if $active_tab == "tab_general_`$id`"} active{/if}"><a>{__("general")}</a></li>
                <li id="tab_call_customer_{$id}" class="cm-js {if $active_tab == "tab_call_customer_`$id`"} active{/if}"><a>{__("addons.rus_sdek.call_customer")}</a></li>
                <li id="tab_call_courier_{$id}" class="cm-js {if $active_tab == "tab_call_courier_`$id`"} active{/if}"><a>{__("addons.rus_sdek.call_courier")}</a></li>
            </ul>
        </div>

        <div class="cm-tabs-content" id="tabs_content">
            <div id="content_tab_general_{$id}">
            <fieldset>
                <div class="control-group">
                    <label class="control-label right" for="shipping_address">{__("shipping_address")}</label>
                    <div class="controls">
                        {if (empty($data_shipment.offices))}
                            <input type="text" id="shipping_address" value="{$data_shipment.address}" disabled />
                            <input type="hidden" id="shipping_address" name="add_sdek_info[{$shipment.shipment_id}][Address][Street]" value="{$data_shipment.address}" />
                            <input type="hidden" name="add_sdek_info[{$shipment.shipment_id}][Address][House]" value="-" />
                            <input type="hidden" name="add_sdek_info[{$shipment.shipment_id}][Address][Flat]" value="-" />
                        {else}
                            <select id="shipping_address" name="add_sdek_info[{$shipment.shipment_id}][Address][PvzCode]" class="input-slarge">
                                {foreach from=$data_shipment.offices item=address_shipping}
                                    <option value="{$address_shipping.Code}" {if $address_shipping.Code == $data_shipment.address_pvz}selected="selected"{/if}>{$address_shipping.Address}</option>
                                {/foreach}
                            </select>
                        {/if}
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label right" for="number_package">{__("addons.rus_sdek.number_package")}</label>
                    <div class="controls">
                        <input id="number_package" type="text" name="add_sdek_info[{$shipment.shipment_id}][barcode]" value="{$data_shipment.barcode}" {if $register_id}disabled{/if} />
                        <p class="muted description">{__("ttc_addons.rus_sdek.number_package")}</p>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label right" for="shipping_cost">{__("shipping_cost")} ({$currencies.$primary_currency.symbol nofilter})</label>
                    <div class="controls">
                        <input type="text" id="shipping_cost" name="add_sdek_info[{$shipment.shipment_id}][Order][DeliveryRecipientCost]" value="{$data_shipment.delivery_cost|default:"0.00"|fn_format_price:$primary_currency:null:false}" class="input-long" size="10" {if $register_id}disabled{/if} />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label right" for="comment">{__("comment")}</label>
                    <div class="controls">
                        <textarea class="span9" id="comment" name="add_sdek_info[{$shipment.shipment_id}][Order][Comment]" value="" {if $register_id}disabled{/if} cols="55" rows="4">{$data_shipment.comments}</textarea>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label right" for="use_product">{__("addons.rus_sdek.use_product_price")}</label>
                    <div class="controls">
                        <input type="hidden" name="add_sdek_info[{$shipment.shipment_id}][use_product]" value="N" />
                        <input id="use_product" type="checkbox" name="add_sdek_info[{$shipment.shipment_id}][use_product]" {if $data_shipment.use_product == 'Y' || !$data_shipment.use_product}checked="checked"{/if} {if !$data_shipment.use_product}value="Y"{else}value="{$data_shipment.use_product}"{/if} {if $register_id}disabled{/if} />
                        <p class="muted description">{__("ttc_addons.rus_sdek.use_product_price")}</p>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label right" for="use_imposed">{__("addons.rus_sdek.use_imposed")}</label>
                    <div class="controls">
                        <input type="hidden" name="add_sdek_info[{$shipment.shipment_id}][use_imposed]" value="N" />
                        <input id="use_imposed" type="checkbox" name="add_sdek_info[{$shipment.shipment_id}][use_imposed]" {if $data_shipment.use_imposed == 'Y'}checked="checked"{/if} {if !$data_shipment.use_imposed}value="Y"{else}value="{$data_shipment.use_imposed}"{/if} {if $register_id}disabled{/if} />
                        <p class="muted description">{__("ttc_addons.rus_sdek.use_imposed")}</p>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label right" for="cash_delivery">{__("addons.rus_sdek.cash_delivery")} ({$currencies.$primary_currency.symbol nofilter})</label>
                    <div class="controls">
                        <input id="cash_delivery" type="text" name="add_sdek_info[{$shipment.shipment_id}][CashDelivery]" {if $data_shipment.cash_delivery}value="{$data_shipment.cash_delivery}"{else}value="0.00"{/if} class="input-long" size="10" {if $register_id}disabled{/if} />
                    </div>
                </div>

                {if !$register_id}
                <div class="cm-toggle-button">
                    <div class="control-group select-field notify-customer">
                        <div class="controls">
                            <label for="shipment_notify_user" class="checkbox">
                            <input type="checkbox" name="notify_user" id="shipment_notify_user" value="Y" />
                            {__("send_shipment_notification_to_customer")}</label>
                        </div>
                    </div>
                </div>
                {/if}
            </fieldset>
            </div>

            <div id="content_tab_call_customer_{$id}">
            {include file="common/subheader.tpl" title=__("addons.rus_sdek.call_customer")}

            <fieldset>
                <div class="control-group">
                    <label class="control-label right" for="recipient">{__("recipient")}</label>
                    <div class="controls">
                        <input type="text" id="recipient" name="add_sdek_info[{$shipment.shipment_id}][Schedule][RecipientName]" value="{$data_shipment.new_schedules.recipient_name}" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label right" for="phone">{__("phone")}</label>
                    <div class="controls">
                        <input type="text" id="phone" name="add_sdek_info[{$shipment.shipment_id}][Schedule][Phone]" value="{$data_shipment.new_schedules.phone}" size="10" class="input-long" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label right" for="recipient_cost">{__("addons.rus_sdek.recipient_cost")} ({$currencies.$primary_currency.symbol nofilter})</label>
                    <div class="controls">
                        <input id="recipient_cost" type="text" name="add_sdek_info[{$shipment.shipment_id}][Schedule][DeliveryRecipientCost]" value="{$data_shipment.new_schedules.recipient_cost|default:"0.00"|fn_format_price:$primary_currency:null:false}" size="10" class="input-small" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label right" for="schedule_data">{__("addons.rus_sdek.schedule_data")}</label>
                    <div class="controls">
                        {include file="common/calendar.tpl" date_id="schedule_data_`$id`" date_name="add_sdek_info[`$id`][Schedule][Date]" date_val="$data_shipment.new_schedules.date" start_year=$settings.Company.company_start_year}
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label right" for="schedule_period">{__("addons.rus_sdek.schedule_period")}</label>
                    <div class="controls">
                        <input id="timebeg_{$shipment.shipments_id}" class="input-small cm-mask-time" type="text" name="add_sdek_info[{$shipment.shipment_id}][Schedule][TimeBeg]" value="{$data_shipment.new_schedules.timebag}" size="3" /> - <input id="timeend_{$shipment.shipments_id}" class="input-small cm-mask-time" type="text" name="add_sdek_info[{$shipment.shipment_id}][Schedule][TimeEnd]" value="{$data_shipment.new_schedules.timeend}" size="3" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label right" for="schedule_comment">{__("comment")}</label>
                    <div class="controls">
                        <textarea id="schedule_comment" class="span9" name="add_sdek_info[{$shipment.shipment_id}][Schedule][Comment]" cols="55" rows="4">{$data_shipment.new_schedules.call_comment}</textarea>
                    </div>
                </div>
            </fieldset>
            </div>

            <div id="content_tab_call_courier_{$id}">
            {include file="common/subheader.tpl" title=__("addons.rus_sdek.call_courier")}

            <fieldset>
                <div class="control-group">
                    <label class="control-label right" for="date_courier">{__("addons.rus_sdek.date_courier")}</label>
                    <div class="controls">
                        {include file="common/calendar.tpl" date_id="date_courier_`$id`" date_name="add_sdek_info[`$id`][CallCourier][Date]" date_val="$data_shipment.call_couriers.date" start_year=$settings.Company.company_start_year}
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label right" for="time_courier">{__("addons.rus_sdek.time_courier")}</label>
                    <div class="controls">
                        <input id="timebeg_{$shipment.shipments_id}" class="input-small cm-mask-time" type="text" name="add_sdek_info[{$shipment.shipment_id}][CallCourier][TimeBeg]" value="{$data_shipment.call_couriers.timebag}" size="6" /> - <input id="timeend_{$shipment.shipments_id}" class="input-small cm-mask-time" type="text" name="add_sdek_info[{$shipment.shipment_id}][CallCourier][TimeEnd]" value="{$data_shipment.call_couriers.timeend}" size="6" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label right" for="time_lunch_courier">{__("addons.rus_sdek.time_lunch_courier")}</label>
                    <div class="controls">
                        <input id="timebeg_{$shipment.shipments_id}" class="input-small cm-mask-time" type="text" name="add_sdek_info[{$shipment.shipment_id}][CallCourier][LunchBeg]" value="{$data_shipment.call_couriers.lunch_timebag}" size="3" /> - <input id="timeend_{$shipment.shipments_id}" class="input-small cm-mask-time" type="text" name="add_sdek_info[{$shipment.shipment_id}][CallCourier][LunchEnd]" value="{$data_shipment.call_couriers.lunch_timeend}" size="3" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label right" for="courier_comment">{__("comment")}</label>
                    <div class="controls">
                        <textarea id="courier_comment" class="span9" name="add_sdek_info[{$shipment.shipment_id}][CallCourier][Comment]" cols="55" rows="4">{$data_shipment.call_couriers.comment_courier}</textarea>
                    </div>
                </div>
            </fieldset>
            </div>
        </div>

        <div class="buttons-container">
            {if $data_shipments.$shipment_id.register_id}
                {include file="buttons/save_cancel.tpl" but_name="dispatch[orders.call_sdek]" cancel_action="close" save=$id}
            {else}
                {include file="buttons/save_cancel.tpl" but_name="dispatch[orders.sdek_order_delivery]" cancel_action="close" save=$id}
            {/if}
        </div>
    </form>
{/if}
</div>
