{foreach from=$order_info.shipping item="shipping" name="f_shipp"}

    {if $shipping.module == 'yandex' && $shipping.courier_data && $shipping.service_params.type_delivery == 'courier'}
        <div class="well orders-right-pane form-horizontal">
            <div class="control-group">
                <div class="control-label">
                    {include file="common/subheader.tpl" title=__("yandex_delivery_courierpoint")}
                </div>
            </div>

            <p class="strong">
                {$shipping.courier_data.delivery_name}
            </p>
            <p class="muted">
                {include file="addons/yandex_delivery/views/yandex_delivery/components/schedules.tpl" schedules=$shipping.courier_data.work_time}
            </p>

            {if !empty($yd_order_statuses)}
                <div class="control-group shift-top">
                    <div class="control-label">
                        {include file="common/subheader.tpl" title=__("yandex_delivery.status_delivery")}
                    </div>
                </div>
                <p>
                    {foreach $yd_order_statuses as $yd_order}
                        <a class="underlined" href="{"shipments.details?shipment_id=`$yd_order.shipment_id`"|fn_url}"><span>#{$yd_order.shipment_id}</span></a>
                        <span> - {$yd_order.yd_status_name} ({$yd_order.time})</span><br />
                    {/foreach}
                </p>
            {/if}
        </div>

    {elseif $shipping.module == 'yandex' && $shipping.pickup_data}
        <div class="well orders-right-pane form-horizontal">
            <div class="control-group">
                <div class="control-label">
                    {include file="common/subheader.tpl" title=__("yandex_delivery_pickuppoint")}
                </div>
            </div>

            <p class="strong">
                {$shipping.pickup_data.name}
            </p>
            <p class="muted">
                {$shipping.pickup_data.full_address}<br />
                {foreach from=$shipping.pickup_data.phones item="phone"}
                    <bdi>{$phone.number}</bdi>
                {/foreach}
                <br />

                {include file="addons/yandex_delivery/views/yandex_delivery/components/schedules.tpl" schedules=$shipping.pickup_data.work_time}
            </p>

            {if !empty($yd_order_statuses)}
            <div class="control-group shift-top">
                <div class="control-label">
                    {include file="common/subheader.tpl" title=__("yandex_delivery.status_delivery")}
                </div>
            </div>
            <p>
                {foreach $yd_order_statuses as $yd_order}
                    <a class="underlined" href="{"shipments.details?shipment_id=`$yd_order.shipment_id`"|fn_url}"><span>#{$yd_order.shipment_id}</span></a>
                    <span> - {$yd_order.yd_status_name} ({$yd_order.time})</span><br />
                {/foreach}
            </p>
            {/if}
        </div>
    {/if}
{/foreach}
