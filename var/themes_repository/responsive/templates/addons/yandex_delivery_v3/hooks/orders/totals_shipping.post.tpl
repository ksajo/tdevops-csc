{foreach $order_info.shipping as $shipping_method}
    {if $shipping_method.module !== "Tygh\Addons\YandexDelivery\Services\YandexDeliveryService::MODULE"|constant}
        {continue}
    {/if}
    {if $shipping_method.service_params.type_delivery == "\Tygh\Addons\YandexDelivery\Enum\DeliveryType::PICKUP"|constant}
        <p class="ty-strong">
            {__("shipping")}: {$shipping_method.delivery.delivery.partner.name}
        </p>
        <p class="ty-muted">
            {$shipping_method.pickup_data.address.addressString}
        </p>
        <p class="ty-muted">
            {if $shipping_method.pickup_data.phones}
                {__("phone")}:
                {foreach $shipping_method.pickup_data.phones as $phone}
                    {$phone.number}<br />
                {/foreach}
            {/if}
            {if $shipping_method.pickup_data.work_time}
                {include file="addons/yandex_delivery_v3/views/yandex_delivery/components/schedules.tpl"
                    schedules = $shipping_method.pickup_data.work_time
                }
            {/if}
            {if $shipping_method.pickup_data.address.comment}
                {$shipping_method.pickup_data.address.comment nofilter}
            {/if}
        </p>
    {elseif $shipping_method.service_params.type_delivery == "\Tygh\Addons\YandexDelivery\Enum\DeliveryType::COURIER"|constant}
        <p class="ty-strong">
            {__("yandex_delivery_v3.courier_delivery", ["[delivery]" => $shipping_method.courier_data.delivery.partner.name])}
        </p>
        <p class="ty-muted">
            {if $shipping_method.courier_data.work_time}
                {include file="addons/yandex_delivery_v3/views/yandex_delivery/components/schedules.tpl" schedules=$shipping_method.courier_data.work_time}
            {/if}
        </p>
    {/if}
{/foreach}