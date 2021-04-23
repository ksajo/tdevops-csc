{foreach $order_info.shipping as $shipping}
    {if $shipping.module !== "Tygh\Addons\YandexDelivery\Services\YandexDeliveryService::MODULE"|constant}
        {continue}
    {/if}
    {if $shipping.service_params.type_delivery === "\Tygh\Addons\YandexDelivery\Enum\DeliveryType::PICKUP"|constant}
        <div class="well orders-right-pane form-horizontal">
            <div class="control-group">
                <div class="control-label">
                    {include file="common/subheader.tpl" title=__("yandex_delivery_v3.pickuppoint")}
                </div>
            </div>

            <p class="strong">
                {$shipping.pickup_data.name}
            </p>
            <p class="muted">
                {$shipping.delivery.delivery.partner.name}
            </p>
            <p class="muted">
                {$shipping.pickup_data.address.addressString}<br />
                {foreach $shipping.pickup_data.phones as $phone}
                    <bdi>{$phone.number}</bdi>
                {/foreach}
                <br />

                {include file="addons/yandex_delivery_v3/views/yandex_delivery/components/schedules.tpl" schedules=$shipping.pickup_data.work_time}
            </p>
            {if !empty($yad_order_statuses)}
                <div class="control-group shift-top">
                    <div class="control-label">
                        {include file="common/subheader.tpl" title=__("yandex_delivery_v3.status_delivery")}
                    </div>
                </div>
                <p>
                    {foreach $yad_order_statuses as $yad_order}
                        <a class="underlined" href="{"shipment.details?shipment_id=`$yad_order.shipment_id`"|fn_url}">
                            <span>#{$yad_order.shipment_id}</span>
                        </a>
                    {/foreach}
                </p>
            {/if}
        </div>
    {elseif $shipping.service_params.type_delivery === "\Tygh\Addons\YandexDelivery\Enum\DeliveryType::COURIER"|constant}
        <div class="well orders-right-pane form-horizontal">
            <div class="control-group">
                <div class="control-label">
                    {include file="common/subheader.tpl" title=__("yandex_delivery_v3.courier")}
                </div>
            </div>

            <p class="strong">
                {$shipping.courier_data.delivery.partner.name}
            </p>
            <p class="muted">
                {include file="addons/yandex_delivery_v3/views/yandex_delivery/components/schedules.tpl" schedules=$shipping.courier_data.work_time}
            </p>

            {if !empty($yad_order_statuses)}
                <div class="control-group shift-top">
                    <div class="control-label">
                        {include file="common/subheader.tpl" title=__("yandex_delivery_v3.status_delivery")}
                    </div>
                </div>
                <p>
                    {foreach $yad_order_statuses as $yad_order}
                        <a class="underlined" href="{"shipments.details?shipment_id=`$yad_order.shipment_id`"|fn_url}"><span>#{$yad_order.shipment_id}</span></a>
                        <span> - {$yad_order.yad_status_name} ({$yad_order.time})</span><br />
                    {/foreach}
                </p>
            {/if}
        </div>
    {/if}
{/foreach}
