{foreach from=$shipping.shipment_keys item="shipment_key"}
    {$shipment = $shipments[$shipment_key]}
    {$shipment_id = $shipment.shipment_id}

    {if $yandex_order_data.orders.$shipment_id}
        <div class="clearfix">
            <div class="hidden" title="{__("yandex_delivery.add_yandex_order")}" id="content_add_new_yandex_order_{$shipment_id}">
                {include file="addons/yandex_delivery/views/shipments/components/new_yandex_order.tpl"}
            <!--content_add_new_yandex_order_{$shipment_id}--></div>
        </div>
    {/if}
{/foreach}