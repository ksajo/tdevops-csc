{foreach $shipping.shipment_keys as $shipment_key}
    {$shipment = $shipments[$shipment_key]}
    {$shipment_id = $shipment.shipment_id}
    {if $yandex_delivery_data.orders.$shipment_id}
        <div class="clearfix">
            <div class="hidden" title="{__("yandex_delivery_v3.add_yandex_order")}" id="content_add_new_yandex_order_{$shipment_id}">
                {include file="addons/yandex_delivery_v3/views/shipments/components/new_yandex_order.tpl"}
            <!--content_add_new_yandex_order_{$shipment_id}--></div>
        </div>
    {/if}
{/foreach}
{if $can_auto_confirm}
    <div class="clearfix">
        <div class="hidden" title="{__("yandex_delivery_v3.add_yandex_order")}" id="content_add_new_yandex_order_0">
            {include file="addons/yandex_delivery_v3/views/shipments/components/new_yandex_order.tpl"}
            <!--content_add_new_yandex_order_0--></div>
    </div>
{/if}