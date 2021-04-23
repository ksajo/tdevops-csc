{script src="js/tygh/tabs.js"}

{$id = $shipment.shipment_id}

<form action="{""|fn_url}" method="post" id="yandex_form_{$id}" name="yandex_form_{$id}" class="form-horizontal form-edit">
    <input type="hidden" class="cm-no-hide-input" name="redirect_url" value="{$config.current_url}" />

    <input type="hidden" name="yandex_order[order_id]" value="{$shipment.order_id}">
    <input type="hidden" name="yandex_order[shipping_id]" value="{$shipping.shipping_id}">
    <input type="hidden" name="yandex_order[shipment_id]" value="{$id}">

    <div class="tabs cm-j-tabs">
        <ul class="nav nav-tabs">
            <li id="tab_shipment_{$id}" class="cm-js active"><a>{__("yandex_delviery.shipment_information")}</a></li>
            <li id="tab_user_info_{$id}" class="cm-js"><a>{__("yandex_delviery.customer_information")}</a></li>
            <li id="tab_general_{$id}" class="cm-js"><a>{__("yandex_delviery.other_info")}</a></li>
        </ul>
    </div>

    <div class="cm-tabs-content">
        <div id="content_tab_shipment_{$id}">
            <fieldset>
                <div class="control-group">
                    <label class="control-label" for="shipment_type_{$id}">{__('yandex_delivery.shipment_type')}</label>
                    <div class="controls">
                        <select id="shipment_type_{$id}" name="yandex_order[type]" class="input-slarge form-control">

                            {if $shipping.delivery.is_ff_withdraw_available}
                                <option value="warehouse_import">{__('yandex_delivery.yourself_warehouse')}</option>{/if}

                            {if $shipping.delivery.is_ff_import_available}
                                <option value="warehouse_withdraw">{__('yandex_delivery.courier_warehouse')}</option>{/if}

                            {if $shipping.delivery.is_ds_withdraw_available}
                                <option value="delivery_import">{__('yandex_delivery.yourself_delivery', ['[delivery]' => $shipping.delivery.name])}</option>{/if}

                            {if $shipping.delivery.is_ds_import_available}
                                <option value="delivery_withdraw">{__('yandex_delivery.courier_delivery', ['[delivery]' => $shipping.delivery.name])}</option>{/if}
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="elm_yandex_creation_date_{$id}">{__('yandex_delivery.shipment_date')}</label>
                    <div class="controls">
                        {include file="common/calendar.tpl" date_id="elm_yandex_creation_date_`$id`" date_name="yandex_order[date]" date_val=$smarty.const.TIME min_date=0 start_year=$settings.Company.company_start_year}
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="assessed_value_{$id}">{__("yandex_delivery.assessed_value")}</label>
                    <div class="controls">
                        <input id="assessed_value_{$id}" class="input-small" type="text" name="yandex_order[assessed_value]" size="45" value="{$yandex_order_data.assessed_value.$id}" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="amount_prepaid_{$id}">{__("yandex_delivery.amount_prepaid")}</label>
                    <div class="controls">
                        <input id="amount_prepaid_{$id}" class="input-small" type="text" name="yandex_order[amount_prepaid]" size="45" value="{$yandex_order_data.amount_prepaid.$id}" />
                        <p class="muted description">{__("ttc_yandex_delivery.amount_prepaid")}</p>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="comment_{$id}">{__("comment")}</label>
                    <div class="controls">
                        <textarea class="span9" id="comment_{$id}" name="yandex_order[comment]" cols="55" rows="5"></textarea>
                    </div>
                </div>

                <div class="cm-toggle-button">
                    <div class="control-group select-field notify-customer">
                        <div class="controls">
                            <label for="shipment_notify_user_{$id}" class="checkbox">
                                <input type="checkbox" name="yandex_order[notify_user]" id="shipment_notify_user_{$id}" value="Y" />
                                {__("send_shipment_notification_to_customer")}</label>
                                <p class="muted description">{__("yandex_delivery.info_about_of_tracking_number")}</p>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
        <div id="content_tab_user_info_{$id}">
            <fieldset>
                <div class="control-group">
                    <label class="control-label cm-required" for="first_name_{$id}">{__("first_name")}</label>
                    <div class="controls">
                        <input id="first_name_{$id}" class="input-medium" type="text" name="yandex_order[first_name]" size="45" value="{$yandex_order_data.firstname}" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label cm-required" for="last_name_{$id}">{__("last_name")}</label>
                    <div class="controls">
                        <input id="last_name_{$id}" class="input-medium" type="text" name="yandex_order[last_name]" size="45" value="{$yandex_order_data.lastname}" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label cm-required" for="phone_{$id}">{__("phone")}</label>
                    <div class="controls">
                        <input id="phone_{$id}" class="input-small" type="text" name="yandex_order[phone]" size="14" value="{$yandex_order_data.phone}" />
                    </div>
                </div>
            </fieldset>
        </div>
        <div id="content_tab_general_{$id}">
            <fieldset>
                <div class="control-group">
                    <label class="control-label cm-required" for="yandex_sender_{$id}">{__("yandex_delivery.yandex_sender")}</label>
                    <div class="controls">
                        <select id="yandex_sender_{$id}" name="yandex_order[sender_id]" class="input-slarge form-control">
                            {foreach from=$yandex_order_data.senders item="sender" key="sender_id"}
                                <option value="{$sender_id}" {if $shipping.service_params.sender_id == $sender_id}selected="selected"{/if}>{$sender}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label cm-required" for="yandex_warehouse_{$id}">{__('yandex_delivery.yandex_warehouse')}</label>
                    <div class="controls">
                        <select id="yandex_warehouse_{$id}" name="yandex_order[warehouse_id]" class="input-slarge form-control">
                            {foreach from=$yandex_order_data.warehouses item="warehous" key="warehous_id"}
                                <option value="{$warehous_id}" {if $shipping.service_params.warehouse_id == $warehous_id}selected="selected"{/if}>{$warehous}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label cm-required" for="yandex_requisite_{$id}">{__('yandex_delivery.yandex_requisite')}</label>
                    <div class="controls">
                        <select id="yandex_requisite_{$id}" name="yandex_order[requisite_id]" class="input-slarge form-control">
                            {foreach from=$yandex_order_data.requisites item="requisite" key="requisite_d"}
                                <option value="{$requisite_d}" {if $shipping.service_params.requisite_id == $requisite_d}selected="selected"{/if}>{$requisite}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>

    <div class="buttons-container">
        {include file="buttons/save_cancel.tpl" but_target_form="yandex_form_`$id`" but_name="dispatch[shipments.create_yandex_order]" cancel_action="close"}
    </div>
</form>
