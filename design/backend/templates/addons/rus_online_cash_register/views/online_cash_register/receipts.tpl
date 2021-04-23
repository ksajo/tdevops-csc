{capture name="mainbox"}

    {capture name="sidebar"}
        {include file="addons/rus_online_cash_register/views/online_cash_register/components/receipts_search_form.tpl"}
    {/capture}

    {include file="common/pagination.tpl" save_current_page=true save_current_url=true}

    {assign var="return_current_url" value=$config.current_url|escape:url}

    {if $receipts}
        <table width="100%" class="table table-middle table--relative">
            <thead>
                <tr>
                    <th>{__("rus_online_cash_register.receipts_list.uuid")}</th>
                    <th>{__("rus_online_cash_register.receipts_list.source")}</th>
                    <th>{__("rus_online_cash_register.receipts_list.type")}</th>
                    <th>{__("rus_online_cash_register.receipts_list.status")}</th>
                    <th>{__("rus_online_cash_register.receipts_list.total")}</th>
                    <th></th>
                </tr>
            </thead>
            {foreach from=$receipts item="receipt"}
                <tr>
                    <td><a href="#" data-ca-external-click-id="{"recetip_info_link_{$receipt->getId()}"}" class="cm-external-click">{$receipt->getUUID()}</a></td>
                    <td>
                        {if $receipt->getObjectType() === "order"}
                            <a href="{"orders.details?order_id=`$receipt->getObjectId()`"|fn_url}">{__("rus_online_cash_register.receipts_list.source.order", ["[order_id]" => $receipt->getObjectId()])}</a>
                        {else}
                            {__("rus_online_cash_register.receipts_list.source.`$receipt->getObjectType()`")}
                        {/if}
                        <br />
                        <small class="nowrap muted">
                            {$receipt->getTimestamp()->getTimestamp()|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}
                        </small>
                    </td>
                    <td>{__("rus_online_cash_register.receipts_list.type.`$receipt->getTypeCode()`")}</td>
                    <td width="10%">
                        {if $receipt->getStatusCode() == "fail"}
                            <strong class="text-error">{__("rus_online_cash_register.receipts_list.status.`$receipt->getStatusCode()`")}</strong>
                        {else}
                            <strong class="text-info">{__("rus_online_cash_register.receipts_list.status.`$receipt->getStatusCode()`")}</strong>
                        {/if}
                    </td>
                    <td class="right">
                        {$curency_code = $receipt->getCurrency()}
                        {$currencies.$curency_code.symbol nofilter}
                        {$receipt->getTotal()}
                    </td>
                    <td width="10%" class="right nowrap">
                        <div class="pull-right">
                            {capture name="tools_list"}
                                {if $receipt->getUUID()}
                                    <li>{btn type="list" id="recetip_info_link_{$receipt->getId()}" title=$receipt->getUUID() data=["data-ca-view-id" => $receipt->getUUID(), "data-ca-dialog-title" => $receipt->getUUID(), "data-ca-target-id" => "container_receipt_{$receipt->getId()}"] class="cm-dialog-opener cm-dialog-auto-size" text=__("view") href="online_cash_register.receipt?uuid=`$receipt->getUUID()`"}</li>
                                    <li>{btn type="list" text=__("rus_online_cash_register.refresh_receipt") href="online_cash_register.refresh?uuid=`$receipt->getUUID()`&return_url=`$return_current_url`" method="POST"}</li>
                                {/if}
                            {/capture}
                            {dropdown content=$smarty.capture.tools_list}
                        </div>
                    </td>
                </tr>
            {/foreach}
        </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}

    {include file="common/pagination.tpl"}
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        <li>{btn type="list" text=__("rus_online_cash_register.receipts_list.create_test_receipt") href="online_cash_register.create_test_receipt" class="cm-confirm" method="POST"}</li>
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}

{include file="common/mainbox.tpl" title=__("rus_online_cash_register.receipts_list.title") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}
