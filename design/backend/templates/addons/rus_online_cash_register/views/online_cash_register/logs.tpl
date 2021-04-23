{capture name="mainbox"}

    {capture name="sidebar"}
        {include file="addons/rus_online_cash_register/views/online_cash_register/components/logs_search_form.tpl"}
    {/capture}

    {include file="common/pagination.tpl" save_current_page=true save_current_url=true}

    {assign var="return_current_url" value=$config.current_url|escape:url}

    {if $logs}
        <table width="100%" class="table table-middle table--relative">
            <thead>
                <tr>
                    <th>{__("rus_online_cash_register.logs.date")}</th>
                    <th>{__("rus_online_cash_register.logs.url")}</th>
                    <th>{__("rus_online_cash_register.logs.status")}</th>
                    <th>{__("rus_online_cash_register.logs.request")} / {__("rus_online_cash_register.logs.response")}</th>
                </tr>
            </thead>
            {foreach from=$logs item="log_data"}
                <tr>
                    <td>
                        <small class="nowrap muted">
                            {$log_data.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}
                        </small>
                    </td>
                    <td width="20%" class="wrap">{$log_data.url}</td>
                    <td width="10%">
                        {if $log_data.status == "\Tygh\Addons\RusOnlineCashRegister\RequestLogger::STATUS_FAIL"|constant}
                            <strong class="text-error">{__("rus_online_cash_register.logs.status.fail")}</strong>
                        {elseif $log_data.status == "\Tygh\Addons\RusOnlineCashRegister\RequestLogger::STATUS_SUCCESS"|constant}
                            <strong class="text-info">{__("rus_online_cash_register.logs.status.success")}</strong>
                        {elseif $log_data.status == "\Tygh\Addons\RusOnlineCashRegister\RequestLogger::STATUS_SEND"|constant}
                            <strong class="text-info">{__("rus_online_cash_register.logs.status.send")}</strong>
                        {/if}
                    </td>
                    <td class="wrap">
                        <strong>{__("rus_online_cash_register.logs.request")}</strong>
                        <div>{$log_data.request}</div>
                        <strong>{__("rus_online_cash_register.logs.response")}</strong>
                        <div>{$log_data.response}</div>
                    </td>
                </tr>
            {/foreach}
        </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}

    {include file="common/pagination.tpl"}
{/capture}


{include file="common/mainbox.tpl" title=__("rus_online_cash_register.logs.title") content=$smarty.capture.mainbox sidebar=$smarty.capture.sidebar}
