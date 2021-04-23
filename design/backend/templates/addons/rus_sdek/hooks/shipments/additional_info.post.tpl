
{if $shipment.carrier == "sdek"}
<div>
<input type="hidden" name="shipment_id" value="{$shipment.shipment_id}" />

    {capture name="call_customer"}
        <h4>{__("addons.rus_sdek.call_customer")}</h4>

        {if $data_call_recipient.recipient_name}
            <p class="strong">{$data_call_recipient.recipient_name}</p>
        {/if}
        {if $data_call_recipient.phone}
            <p>{$data_call_recipient.phone}</p>
        {/if}
        {if $data_call_recipient.shipment_date}
            <p>{$data_call_recipient.shipment_date}</p>
        {/if}
        {if $data_call_recipient.period}
            <p>{$data_call_recipient.period}</p>
        {/if}
        {if $data_call_recipient.call_comment}
            <p class="strong">{__("comment")}:</p>
            <p>{$data_call_recipient.call_comment}</p>
        {/if}
    {/capture}

    {capture name="call_courier"}
        <h4>{__("addons.rus_sdek.call_courier")}</h4>

        {if $data_call_courier.call_courier_date}
            <p>{$data_call_courier.call_courier_date}</p>
        {/if}
        {if $data_call_courier.period}
            <p>{$data_call_courier.period}</p>
        {/if}
        {if $data_call_courier.period_lunch}
            <p>{$data_call_courier.period_lunch}</p>
        {/if}
        {if $data_call_courier.comment_courier}
            <p class="strong">{__("comment")}:</p>
            <p>{$data_call_courier.comment_courier}</p>
        {/if}
    {/capture}
</div>

<table width="100%" class="profile-info">
<tr valign="top">
    {if $data_call_recipient}
    <td width="50%">
        {$smarty.capture.call_customer nofilter}
    </td>
    {/if}
    {if $data_call_courier}
    <td width="50%">
        {$smarty.capture.call_courier nofilter}
    </td>
    {/if}
</tr>
</table>

{/if}