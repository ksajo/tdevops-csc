{foreach from=$order_info.shipping item="shipping_method"}

    {if $shipping_method.service_params.type_delivery == 'pickup' && $shipping_method.module == 'yandex'}
        {$shipment_id = $shipments[$shipping.group_key].shipment_id}
        {if $yandex_delivery_status[$shipment_id]}
    	    <p>{__('yandex_delivery.status_delivery')}: {$yandex_delivery_status[$shipment_id].yd_status_info} ({$yandex_delivery_status[$shipment_id].time})</p>
        {/if}
        <p class="ty-strong">
            {__("shipping")}: {$shipping_method.delivery.delivery_name}
        </p>
        <p class="ty-muted">
            {if $shipping_method.pickup_data.full_address} {$shipping_method.pickup_data.full_address}{/if}</br>
            {if $shipping_method.pickup_data.phone}
                {__("phone")}: {$shipping_method.pickup_data.phone.number}</br>
            {/if}
            {if $shipping_method.pickup_data.work_time}
                {include file="addons/yandex_delivery/views/yandex_delivery/components/schedules.tpl" schedules= $shipping_method.pickup_data.work_time}
            {/if}
            {if $shipping_method.pickup_data.address.comment}
                {$shipping_method.pickup_data.address.comment nofilter}
            {/if}
        </p>

        {assign var="store_count" value=1}
        {assign var="shipping_id" value=$order_info.shipping.shipping_id}

        {assign var="store_location" value=$shipping_method.pickup_data}
        {assign var="map_container" value="yd_map"}
        {include file="addons/yandex_delivery/views/yandex_delivery/components/yandex_details.tpl"}
        <div class="clearfix ty-yd-select-store__map-full-div">
            <div class="ty-yd-select-store__map-details" id="{$map_container}"></div>
        </div>

    {elseif $shipping_method.service_params.type_delivery == 'courier' && $shipping_method.module == 'yandex'}
        {$shipment_id = $shipments[$shipping.group_key].shipment_id}
        {if $yandex_delivery_status[$shipment_id]}
            <p>{__('yandex_delivery.status_delivery')}: {$yandex_delivery_status[$shipment_id].yd_status_info} ({$yandex_delivery_status[$shipment_id].time})</p>
        {/if}

        <p class="ty-strong">
            {__('yandex_courier_delivery')}: {$shipping_method.courier_data.delivery_name}
        </p>

        <p class="ty-muted">
            {if $shipping_method.courier_data.work_time}
                {include file="addons/yandex_delivery/views/yandex_delivery/components/schedules.tpl" schedules= $shipping_method.courier_data.work_time}
            {/if}
        </p>
    {/if}
{/foreach}