{if $data_tracking}
    <div id="content_pochta_information">
        <a class="cm-ajax ty-btn" href="{"orders.details&order_id=`$d_order.order_id`"|fn_url}" data-ca-event="ce.update_object_status_callback">{__("shipping.russianpost.search_tracking_number")}</a>
        <hr />

        <input type="hidden" name="order_id" id="order_id" value="{$d_order.order_id}" />
        <input type="hidden" name="result_ids" value="content_pochta_information" />

        {foreach from=$data_tracking item="d_tracking" key="shipment_id"}
            <input type="hidden" name="shipping_id" id="shipping_id" value="{$d_tracking.shipping_id}" />
            <input type="hidden" name="tracking_number" id="tracking_number" value="{$d_tracking.tracking_number}" />
            <input type="hidden" name="shipment_id" id="shipment_id" value="{$shipment_id}" />

            <h3>{__('tracking_number')}: {$d_tracking.tracking_number}</h3>

            {if $d_tracking.data_history}
                <table class="ty-orders-detail__table ty-table">
                <thead>
                    <tr>
                        <th width="25%" class="center">{__("date")}</th>
                        <th width="30%">{__("address")}</th>
                        <th width="20%" class="left">{__("shipping.russianpost.type_operation")}</th>
                        <th width="25%" class="left">{__("status")}</th>
                    </tr>
                </thead>
                {foreach from=$d_tracking.data_history item="status"}
                    <tr>
                        <td>{$status.timestamp|date_format:"`$settings.Appearance.date_format`"}</td>
                        <td>{$status.address}</td>
                        <td>{$status.type_operation}</td>
                        <td class="center">{$status.status}</td>
                    </tr>
                {/foreach}
                </table>
            {else}
                <p class="no-items">{__("no_data")}</p>
            {/if}
            <hr />
            <br />
        {/foreach}
    <!--content_pochta_information--></div>
{/if}

