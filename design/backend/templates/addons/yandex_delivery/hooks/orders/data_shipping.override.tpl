{if $shipment.carrier == "yandex"}
    {assign var="shipment_id" value=$shipment.shipment_id}

    {if $yandex_order_data.orders.$shipment_id}
        <div class="control-group">
            <div class="control-label">
                {if $shipment.carrier_info}
                    {$shipment.carrier_info.name}
                {/if}
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                {__("shipment_id")}
            </div>
            <div class="controls">
                <a href="{"shipments.details?shipment_id=`$shipment.shipment_id`"|fn_url}"><span>#{$shipment.shipment_id}</span></a>
            </div>
        </div>
        <div class="tracking-number-right-pane">
            <div class="control-group">
                <div class="control-label">
                    {__("tracking_number")}
                </div>
                <div class="controls">
                    <a class="hand cm-tooltip icon-edit cm-combination tracking-number-edit-link" title="{__("edit")}" id="sw_tracking_number_{$shipment_key}"></a>
                    {if $shipment.carrier_info.tracking_url}
                        <a href="{$shipment.carrier_info.tracking_url nofilter}" target="_blank" id="on_tracking_number_{$shipment_key}">{if $shipment.tracking_number}{$shipment.tracking_number}{else}&mdash;{/if}</a>
                    {else}
                        <span id="on_tracking_number_{$shipment_key}">{$shipment.tracking_number}</span>
                    {/if}
                    <div class="hidden" id="tracking_number_{$shipment_key}">
                        <input class="input-small" type="text" name="update_shipping[{$shipping.group_key}][{$shipment.shipment_id}][tracking_number]" size="45" value="{$shipment.tracking_number}" />
                        <input type="hidden" name="update_shipping[{$shipping.group_key}][{$shipment.shipment_id}][shipping_id]" value="{$shipping.shipping_id}" />
                        <input type="hidden" name="update_shipping[{$shipping.group_key}][{$shipment.shipment_id}][carrier]" value="{$shipment.carrier}" />
                    </div>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    {__("yandex_delivery.inner_order")}
                </div>
                <div class="controls">
                    {if $yandex_order_data.orders.$shipment_id.yandex_id}
                        {$yandex_order_data.orders.$shipment_id.yandex_full_num}
                    {/if}
                </div>
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
                {__("status")}
            </div>
            <div class="controls">
                {$yandex_order_data.orders.$shipment_id.status_name}
            </div>
        </div>

        {if !$yandex_order_data.orders.$shipment_id.yandex_id}
            <div class="clearfix">
                {include file="buttons/button.tpl" but_text=__("yandex_delivery.yandex_order_form") but_role="add" but_target_id="content_add_new_yandex_order_`$shipment.shipment_id`" but_meta="btn cm-dialog-opener"}
            </div>
        {/if}
    {/if}
    <hr />
{/if}