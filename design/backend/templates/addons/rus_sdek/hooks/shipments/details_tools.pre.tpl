{if $shipment.carrier == "sdek" && $sdek_shipment_created}
    <li>{btn type="list" text=__("addons.rus_sdek.receipt_order") href="orders.sdek_get_ticket?order_id=`$order_info.order_id`&shipment_id=`$shipment.shipment_id`" class="cm-new-window"}</li>
{/if}
