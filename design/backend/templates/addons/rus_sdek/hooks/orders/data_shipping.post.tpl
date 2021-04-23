
{if $shipment.carrier == "sdek"}
    {assign var="shipment_id" value=$shipment.shipment_id}

    {if $data_shipments.$shipment_id}
        <strong>{__("addons.rus_sdek.shipping_status")}</strong>
        <div class="control-group">
            <div>
                <a href="{"shipments.details?shipment_id=`$shipment.shipment_id`"|fn_url}"><span>#{$shipment.shipment_id}</span></a> - {if $data_shipments.$shipment_id.register_id && $data_status}{$shipment.tracking_number} ({$data_status.status}) {include file="common/popupbox.tpl" id="add_new_shipment_sdek_`$shipment.shipment_id`" content="" act="link" link_text="<i class='icon icon-edit'></i>"}{else}{__("addons.rus_sdek.not_complete")}{/if}
            </div>
        </div>

        {if !$data_shipments.$shipment_id.register_id}
            <div class="clearfix">
                {include file="common/popupbox.tpl" id="add_new_shipment_sdek_`$shipment.shipment_id`" content="" act="create" but_text="{__("addons.rus_sdek.shipping_form")}" but_meta="btn"}
            </div>
        {/if}
    {/if}
{/if}
<hr />