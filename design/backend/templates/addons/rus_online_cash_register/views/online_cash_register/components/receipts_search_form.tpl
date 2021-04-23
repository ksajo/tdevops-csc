<div class="sidebar-row">
    <h6>{__("search")}</h6>

    <form action="{""|fn_url}" name="receipts_search_form" method="get">
        <div class="sidebar-field">
            <label for="elm_addon">{__("rus_online_cash_register.receipts_list.type")}</label>
            <select name="search[type]">
                <option value="">{__("any")}</option>
                {foreach from=$types item="type_name" key="type_id"}
                    <option value="{$type_id}" {if $search.type == $type_id} selected="selected"{/if}>{$type_name}</option>
                {/foreach}
            </select>
        </div>
        <div class="sidebar-field">
            <label for="elm_addon">{__("rus_online_cash_register.receipts_list.status")}</label>
            <select name="search[status]">
                <option value="">{__("any")}</option>
                {foreach from=$statuses item="status_name" key="status_id"}
                    <option value="{$status_id}" {if $search.status == $status_id} selected="selected"{/if}>{$status_name}</option>
                {/foreach}
            </select>
        </div>
        <div class="sidebar-field">
            {include file="common/period_selector.tpl" period=$search.receipts_period form_name="receipts_search_form" display="form" prefix="receipts_"}
        </div>

        <div class="sidebar-field">
            <input class="btn" type="submit" name="dispatch[online_cash_register.receipts]" value="{__("search")}">
        </div>
    </form>
</div>