{$requisites = $receipt->getRequisites()}

<div id="receipt_info_container_{$receipt->getId()}">
    <table width="650px" cellspacing="0" cellpadding="0" style="min-width: 650px; border: 2px solid #000; font-family: Helvetica, Arial, sans-serif;">
        <tbody>
        <tr>
            <td>
                <table width="100%" cellspacing="0" cellpadding="0" border="0"  style="padding: 0 3%; px; font-size: 14px; font-family: Helvetica, Arial, sans-serif;">
                    <tbody>
                    <tr>
                        <td width="50%" style="padding: 6px 0 6px 14px; text-align: left; font-family: Helvetica, Arial, sans-serif;">{$receipt->getTimestamp()->getTimestamp()|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`" nofilter}</td>
                        <td width="50%" style="padding: 6px 14px 6px 0; text-align: right; vertical-align: bottom; text-transform: uppercase; font-family: Helvetica, Arial, sans-serif;"></td>
                    </tr>
                    <tr>
                        <td colspan="2" width="100%" style="padding: 6px 0; border-bottom: 2px groove #fff; text-align: center; text-transform: uppercase; font-family: Helvetica, Arial, sans-serif;">{__("rus_online_cash_register.receipts_list.type.`$receipt->getTypeCode()`")}</td>
                    </tr>
                    <tr>
                        <td colspan="2" width="100%" style="padding: 6px 0; border-bottom: 2px groove #fff; text-align: center; text-transform: uppercase; font-family: Helvetica, Arial, sans-serif;">
                            {__("rus_online_cash_register.status")}: {__("rus_online_cash_register.receipts_list.status.`$receipt->getStatusCode()`")}
                            {if $receipt->getStatusCode() == "fail"}
                                <span class="text-error">{$receipt->getStatusMessage()}</span>
                            {/if}
                        </td>
                    </tr>
                    </tbody>
                </table>

                <table width="100%" cellspacing="0" cellpadding="0" border="0" style="padding: 0 3%; font-size: 14px; font-family: Helvetica, Arial, sans-serif;">
                    <tbody>
                    <tr>
                        <th width="30%" style="padding: 6px 0 6px 4px; border-bottom: 2px groove #fff; text-align: left; word-break: break-all; font-family: Helvetica, Arial, sans-serif;">{__("rus_online_cash_register.receipt.item.name")}</th>
                        <th width="15%" style="padding: 6px 0 6px 0; border-bottom: 2px groove #fff; text-align: right; font-family: Helvetica, Arial, sans-serif;">{__("rus_online_cash_register.receipt.item.price")}</th>
                        <th width="12%" style="padding: 6px 0 6px 0; border-bottom: 2px groove #fff; text-align: right; font-family: Helvetica, Arial, sans-serif;">{__("rus_online_cash_register.receipt.item.quantity")}</th>
                        <th width="12%" style="padding: 6px 0 6px 0; border-bottom: 2px groove #fff; text-align: right; font-family: Helvetica, Arial, sans-serif;">{__("rus_online_cash_register.receipt.item.discount")}</th>
                        <th width="20%" style="padding: 6px 0 6px 0; border-bottom: 2px groove #fff; text-align: right; font-family: Helvetica, Arial, sans-serif;">{__("rus_online_cash_register.receipt.item.tax")}</th>
                        <th width="12%" style="padding: 6px 0 6px 0; border-bottom: 2px groove #fff; text-align: right; font-family: Helvetica, Arial, sans-serif;">{__("rus_online_cash_register.receipt.item.total")}</th>
                    </tr>
                    {foreach from=$receipt->getItems() item="item"}
                        <tr>
                            <td style="padding: 14px 0 14px 4px; vertical-align: top; text-align: left; font-family: Helvetica, Arial, sans-serif;">
                                {$item->getName()}
                            </td>
                            <td style="padding: 14px 0 14px 4px; vertical-align: top; text-align: right; font-family: Helvetica, Arial, sans-serif;">
                                {$curency_code = $receipt->getCurrency()}
                                {$currencies.$curency_code.symbol nofilter}
                                {$item->getPrice()}
                            </td>
                            <td style="padding: 14px 0 14px 4px; vertical-align: top; text-align: right; font-family: Helvetica, Arial, sans-serif;">
                                {$item->getQuantity()}
                            </td>
                            <td style="padding: 14px 0 14px 4px; vertical-align: top; text-align: right; font-family: Helvetica, Arial, sans-serif;">
                                {$currencies.$curency_code.symbol nofilter}
                                {$item->getDiscount()}
                            </td>
                            <td style="padding: 14px 0 14px 4px; vertical-align: top; text-align: right; font-family: Helvetica, Arial, sans-serif;">
                                {$currencies.$curency_code.symbol nofilter}
                                {$item->getTaxSum()}
                            </td>
                            <td style="padding: 14px 0 14px 4px; vertical-align: top; text-align: right; font-family: Helvetica, Arial, sans-serif;">
                                {$currencies.$curency_code.symbol nofilter}
                                {$item->getSum()}
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="5" style="padding: 6px 0; border-top: 2px groove #808080; text-align: right; text-transform: uppercase; font-weight: bold; font-family: Helvetica, Arial, sans-serif;">{__("total")}:</td>
                        <td style="padding: 6px 4px 6px 4px; border-top: 2px groove #808080; text-align: right; font-weight: bold; font-family: Helvetica, Arial, sans-serif;">
                            {$curency_code = $receipt->getCurrency()}
                            {$currencies.$curency_code.symbol nofilter}
                            {$receipt->getTotal()}
                        </td>
                    </tr>
                    </tfoot>
                </table>

                {if $requisites->getFiscalReceiptNumber()}
                    <table width="100%" cellspacing="0" cellpadding="0" border="0" style="padding: 0 3%; font-size: 14px; font-family: Helvetica, Arial, sans-serif;">
                        <tbody>
                        <tr>

                            <td width="50%" style="padding: 12px 0 0 14px; text-align: left; text-transform: uppercase; font-family: Helvetica, Arial, sans-serif;">{__("rus_online_cash_register.fiscal_receipt_number")}: <strong>{$requisites->getFiscalReceiptNumber()}</strong></td>
                            <td width="50%" style="padding: 12px 14px 0 0; text-align: right; text-transform: uppercase; font-family: Helvetica, Arial, sans-serif;">{__("rus_online_cash_register.shift_number")}: <strong>{$requisites->getShiftNumber()}</strong></td>
                        </tr>

                        <tr>
                            <td width="50%" style="padding: 12px 0 0 14px; text-align: left; text-transform: uppercase; font-family: Helvetica, Arial, sans-serif;">{__("rus_online_cash_register.fn_number")}: <strong>{$requisites->getFnNumber()}</strong></td>
                            <td width="50%" style="padding: 12px 14px 0 0; text-align: right; text-transform: uppercase; font-family: Helvetica, Arial, sans-serif;">{__("rus_online_cash_register.fiscal_document_number")}: <strong>{$requisites->getFiscalDocumentNumber()}</strong></td>
                        </tr>
                        <tr>
                            <td width="50%" style="padding: 12px 0 0 14px; text-align: left; text-transform: uppercase; font-family: Helvetica, Arial, sans-serif;">{__("rus_online_cash_register.ecr_registration_number")}: <strong>{$requisites->getEcrRegistrationNumber()}</strong></td>
                            <td width="50%" style="padding: 12px 14px 0 0; text-align: right; text-transform: uppercase; font-family: Helvetica, Arial, sans-serif;">{__("rus_online_cash_register.fiscal_document_attribute")}: <strong>{$requisites->getFiscalDocumentAttribute()}</strong></td>
                        </tr>
                        </tbody>
                    </table>
                {/if}
            </td>
        </tr>
        </tbody>
    </table>
<!--receipt_info_container_{$receipt->getId()}--></div>

<div class="buttons-container">

    {assign var="r_url" value=$config.current_url|escape:url}
    {capture name="tools_list"}
        <li>{btn type="text" href="online_cash_register.refresh?uuid=`$receipt->getUUID()`&return_url=`$r_url`" class="cm-ajax" data=["data-ca-target-id" => "receipt_info_container_{$receipt->getId()}"] text=__("rus_online_cash_register.refresh_receipt") method="POST"}</li>
    {/capture}
    {dropdown content=$smarty.capture.tools_list class="cm-tab-tools droptop" id="tools_general"}

    <a class="cm-dialog-closer cm-cancel tool-link btn">{__("close")}</a>
</div>



