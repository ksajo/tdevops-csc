{if $cash_register_receipts}
    <div class="control-group shift-top">
        <div class="control-label">
            {include file="common/subheader.tpl" title=__("rus_online_cash_register.information")}
        </div>
    </div>

    {foreach from=$cash_register_receipts item="receipt" name="f_receipt"}
        {$receipt_requisites = $receipt->getRequisites()}

        <div id="receipt_{$receipt->getUUID()}">
            <div class="control-group">
                {__("rus_online_cash_register.receipts_list.type.`$receipt->getTypeCode()`")}:
                {if $receipt->getUUID()}
                    {btn type="text" title=$receipt->getUUID() data=["data-ca-view-id" => $receipt->getUUID(), "data-ca-dialog-title" => $receipt->getUUID()] class="cm-dialog-opener cm-dialog-auto-size" text=$receipt->getUUID() href="online_cash_register.receipt?uuid=`$receipt->getUUID()`"}
                {else}
                    -
                {/if}
                {if $receipt->isStatusFail()}
                    <p class="text-error">{$receipt->getStatusMessage()}</p>
                {/if}
            </div>
            {if !$smarty.foreach.f_receipt.last}
                <hr>
            {/if}
        <!--receipt_{$receipt->getUUID()}--></div>
    {/foreach}
{/if}
