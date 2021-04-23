<div class="sidebar-row">
    <h6>{__("search")}</h6>

    <form action="{""|fn_url}" name="logs_search_form" method="get">
        <div class="sidebar-field">
            <label for="elm_addon">{__("rus_online_cash_register.logs.status")}</label>
            <select name="search[status]">
                <option value="">{__("any")}</option>
                {foreach from=$statuses item="status_name" key="status_id"}
                    <option value="{$status_id}" {if $search.status == $status_id} selected="selected"{/if}>{$status_name}</option>
                {/foreach}
            </select>
        </div>
        <div class="sidebar-field">
            {include file="common/period_selector.tpl" period=$search.logs_period form_name="logs_search_form" display="form" prefix="logs_"}
        </div>

        <div class="sidebar-field">
            <input class="btn" type="submit" name="dispatch[online_cash_register.logs]" value="{__("search")}">
        </div>
    </form>
</div>