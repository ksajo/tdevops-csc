{script src="js/tygh/tabs.js"}
{$id = $shipment.shipment_id|default: 0}
{$is_courier_delivery = $shipping.service_params.type_delivery === "\Tygh\Addons\YandexDelivery\Enum\DeliveryType::COURIER"|constant}
{if $settings.Appearance.calendar_date_format == "month_first"}
    {$date_format = "%m/%d/%Y"}
{else}
    {$date_format = "%d/%m/%Y"}
{/if}
{$today = $smarty.const.TIME|fn_parse_date|date_format:$date_format}
{$tomorrow = ($smarty.const.TIME + $smarty.const.SECONDS_IN_DAY)|fn_parse_date|date_format:$date_format}
<form action="{""|fn_url}" method="post" id="yandex_form_{$id}" name="yandex_form_{$id}" class="form-horizontal form-edit">
    <input type="hidden" class="cm-no-hide-input" name="redirect_url" value="{$config.current_url}" />

    <input type="hidden" name="yandex_order[order_id]" value="{$shipment.order_id|default: $smarty.request.order_id}">
    <input type="hidden" name="yandex_order[shipping_id]" value="{$shipping.shipping_id}">
    <input type="hidden" name="yandex_order[shipment_id]" value="{$id}">

    <div class="tabs cm-j-tabs">
        <ul class="nav nav-tabs">
            <li id="tab_shipment_{$id}" class="cm-js active"><a>{__("yandex_delivery_v3.shipment_information")}</a></li>
            <li id="tab_user_info_{$id}" class="cm-js"><a>{__("yandex_delivery_v3.customer_information")}</a></li>
            <li id="tab_courier_info_{$id}" class="cm-js"><a>{__("yandex_delivery_v3.courier_information")}</a></li>
            <li id="tab_general_{$id}" class="cm-js"><a>{__("yandex_delivery_v3.other_info")}</a></li>
        </ul>
    </div>

    <div class="cm-tabs-content">
        <div id="content_tab_shipment_{$id}">
            <fieldset>
                <div class="control-group">
                    <label class="control-label" for="shipment_type_{$id}">{__("yandex_delivery_v3.shipment_type")}:</label>
                    <div class="controls">
                        <select id="shipment_type_{$id}" name="yandex_order[type]" class="input-slarge form-control">
                            <option value="import">{__("yandex_delivery_v3.shipment_import")}</option>
                            <option value="withdraw">{__("yandex_delivery_v3.shipment_withdraw")}</option>
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="elm_yandex_creation_date_{$id}">{__("yandex_delivery_v3.shipment_date")}:</label>
                    <div class="controls">
                        {include file="common/calendar.tpl" date_id="elm_yandex_creation_date_`$id`" date_name="yandex_order[date]" date_val=$smarty.const.TIME min_date=0 start_year=$settings.Company.company_start_year}
                    </div>
                    <div class="controls">
                        <p class="muted description">{__("yandex_delivery_v3.shipment_date_limitation", ["[today]" => $today, "[tomorrow]" => $tomorrow])}</p>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="assessed_value_{$id}">{__("yandex_delivery_v3.assessed_value")}:</label>
                    <div class="controls">
                        <input id="assessed_value_{$id}" class="input-small" type="text" name="yandex_order[assessed_value]" size="45" value="{$yandex_delivery_data.assessed_value}" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="amount_prepaid_{$id}">{__("yandex_delivery_v3.amount_prepaid")}:</label>
                    <div class="controls">
                        <input id="amount_prepaid_{$id}" class="input-small" type="text" name="yandex_order[amount_prepaid]" size="45" value="{$yandex_delivery_data.amount_prepaid}" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="comment_{$id}">{__("comment")}:</label>
                    <div class="controls">
                        <textarea class="span9" id="comment_{$id}" name="yandex_order[comment]" cols="55" rows="5"></textarea>
                    </div>
                </div>

                <div class="cm-toggle-button">
                    <div class="control-group select-field notify-customer">
                        <div class="controls">
                            <label for="shipment_notify_user_{$id}" class="checkbox">
                                <input type="checkbox" name="yandex_order[notify_user]" id="shipment_notify_user_{$id}" value="{"YesNo::YES"|enum}" />
                                {__("send_shipment_notification_to_customer")}
                            </label>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
        <div id="content_tab_user_info_{$id}">
            <fieldset>
                <div class="control-group">
                    <label class="control-label cm-required" for="first_name_{$id}">{__("first_name")}:</label>
                    <div class="controls">
                        <input id="first_name_{$id}" class="input-medium" type="text" name="yandex_order[first_name]" size="45" value="{$yandex_delivery_data.first_name}" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label cm-required" for="last_name_{$id}">{__("last_name")}:</label>
                    <div class="controls">
                        <input id="last_name_{$id}" class="input-medium" type="text" name="yandex_order[last_name]" size="45" value="{$yandex_delivery_data.last_name}" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label cm-required" for="phone_{$id}">{__("phone")}:</label>
                    <div class="controls">
                        <input id="phone_{$id}" class="input-medium cm-mask-phone js-mask-phone-inited" type="tel" name="yandex_order[phone]" size="45" value="{$yandex_delivery_data.phone}" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label{if $is_courier_delivery} cm-required{/if}" for="street_{$id}">{__("yandex_delivery_v3.street")}:</label>
                    <div class="controls">
                        <input id="street_{$id}" class="input-medium" type="text" name="yandex_order[street]" size="45" value="{$yandex_delivery_data.address}" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label{if $is_courier_delivery} cm-required{/if}" for="house_{$id}">{__("yandex_delivery_v3.house")}:</label>
                    <div class="controls">
                        <input id="house_{$id}" class="input-medium" type="text" name="yandex_order[house]" size="30" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="apartment_{$id}">{__("yandex_delivery_v3.apartment")}:</label>
                    <div class="controls">
                        <input id="apartment_{$id}" class="input-medium" type="text" name="yandex_order[apartment]" size="30" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="email_{$id}">{__("email")}:</label>
                    <div class="controls">
                        <input id="email_{$id}" class="input-medium" type="text" name="yandex_order[email]" size="30" value="{$yandex_delivery_data.email}" />
                    </div>
                </div>
            </fieldset>
        </div>
        <div id="content_tab_courier_info_{$id}">
            <fieldset>
                <div class="control-group">
                    <label class="control-label" for="courier_type_{$id}">{__("yandex_delivery_v3.courier_type")}:</label>
                    <div class="controls">
                        <select id="courier_type_{$id}" name="yandex_order[courier][type]" class="input-medium form-control">
                            <option value="courier">{__("yandex_delivery_v3.courier_type.courier")}</option>
                            <option value="car">{__("yandex_delivery_v3.courier_type.car")}</option>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label cm-required" for="courier_first_name_{$id}" id="courier_first_name_{$id}_label">{__("first_name")}:</label>
                    <div class="controls">
                        <input id="courier_first_name_{$id}" class="input-medium" type="text" name="yandex_order[courier][first_name]" size="45" value="" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label cm-required" for="courier_last_name_{$id}" id="courier_last_name_{$id}_label">{__("last_name")}:</label>
                    <div class="controls">
                        <input id="courier_last_name_{$id}" class="input-medium" type="text" name="yandex_order[courier][last_name]" size="45" value="" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label cm-required" for="courier_phone_{$id}" id="courier_phone_{$id}_label">{__("phone")}:</label>
                    <div class="controls">
                        <input id="courier_phone_{$id}" class="input-medium cm-mask-phone js-mask-phone-inited" type="tel" name="yandex_order[courier][phone]" size="45" value="" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label hidden" for="courier_car_brand_{$id}" id="courier_car_{$id}">{__("yandex_delivery_v3.car_brand")}:</label>
                    <div class="controls">
                        <input id="courier_car_brand_{$id}" class="input-medium hidden" type="text" name="yandex_order[courier][car_brand]" size="45" value="" />
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label hidden" for="courier_car_number_{$id}" id="courier_car_license_{$id}">{__("yandex_delivery_v3.car_number")}:</label>
                    <div class="controls">
                        <input id="courier_car_number_{$id}" class="input-medium hidden" type="text" name="yandex_order[courier][car_number]" size="45" value="" />
                    </div>
                </div>
            </fieldset>
        </div>
        <div id="content_tab_general_{$id}">
            <fieldset>
                <div class="control-group">
                    <label class="control-label cm-required" for="yandex_sender_{$id}">{__("yandex_delivery_v3.yandex_store")}:</label>
                    <div class="controls">
                        <select id="yandex_sender_{$id}" name="yandex_order[sender_id]" class="input-slarge form-control">
                            {foreach $yandex_delivery_data.senders as $sender}
                                <option value="{$sender.id}" {if $shipping.service_params.sender_id == $sender.id}selected="selected"{/if}>{$sender.name}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label cm-required" for="yandex_warehouse_{$id}">{__("yandex_delivery_v3.yandex_warehouse")}:</label>
                    <div class="controls">
                        <select id="yandex_warehouse_{$id}" name="yandex_order[warehouse_id]" class="input-slarge form-control">
                            {foreach $yandex_delivery_data.warehouses as $warehouse}
                                <option value="{$warehouse.id}" {if $shipping.service_params.warehouse_id == $warehouse.id}selected="selected"{/if}>{$warehouse.name}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>

    <div class="buttons-container">
        {include file="buttons/save_cancel.tpl" but_target_form="yandex_form_`$id`" but_name="dispatch[shipments.create_yandex_delivery_order]" cancel_action="close"}
    </div>
</form>
<script>
    (function(_, $) {
        var $shipmentType = $('#shipment_type_{$id}');
        var $courierInfo = $('#tab_courier_info_{$id}');
        var $courierType = $('#courier_type_{$id}');
        $shipmentType.change(function () {
            pickShipmentDate();
            if ($shipmentType.val() !== 'import') {
                $courierInfo.addClass('hidden');
                $('#courier_car_license_{$id}').removeClass('cm-required');
                $('#courier_car_{$id}').removeClass('cm-required');
                $('#courier_first_name_{$id}_label').removeClass('cm-required');
                $('#courier_last_name_{$id}_label').removeClass('cm-required');
                $('#courier_phone_{$id}_label').removeClass('cm-required');
            } else {
                $courierInfo.removeClass('hidden');
                $('#courier_first_name_{$id}_label').addClass('cm-required');
                $('#courier_last_name_{$id}_label').addClass('cm-required');
                $('#courier_phone_{$id}_label').addClass('cm-required');
            }
            $courierType.trigger('change');
        });
        $courierType.change(function () {
            if ($courierType.val() !== 'courier') {
                $('#courier_car_number_{$id}').removeClass('hidden');
                $('#courier_car_brand_{$id}').removeClass('hidden');

                $('#courier_car_license_{$id}').removeClass('hidden');
                $('#courier_car_{$id}').removeClass('hidden');
                $('#courier_car_license_{$id}').addClass('cm-required');
                $('#courier_car_{$id}').addClass('cm-required');

            } else {
                $('#courier_car_number_{$id}').addClass('hidden');
                $('#courier_car_brand_{$id}').addClass('hidden');

                $('#courier_car_license_{$id}').addClass('hidden');
                $('#courier_car_{$id}').addClass('hidden');
                $('#courier_car_license_{$id}').removeClass('cm-required');
                $('#courier_car_{$id}').removeClass('cm-required');
            }
        });
        $shipmentType.trigger('change');

        function pickShipmentDate() {
            $('#elm_yandex_creation_date_{$id}').datepicker('option', 'minDate', $shipmentType.val() === 'import' ? 0 : 1);
            if ($shipmentType.val() === 'import') {
                $('#elm_yandex_creation_date_{$id}').val('{$today|escape:javascript}');
            }
        }
    })(Tygh, Tygh.$);
</script>
