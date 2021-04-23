{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="manage_yd_orders_form">

{include file="common/pagination.tpl" save_current_page=true save_current_url=true}

{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{assign var="c_icon" value="<i class=\"icon-`$search.sort_order_rev`\"></i>"}
{assign var="c_dummy" value="<i class=\"icon-dummy\"></i>"}

{if $yd_orders}
<table width="100%" class="table table-middle table--relative">
    <thead>
    <tr>
        <th class="center" width="5%">
            {include file="common/check_items.tpl"}
        </th>
        <th width="15%">
            <a class="cm-ajax" href="{"`$c_url`&sort_by=id&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("shipment_id")}{if $search.sort_by == "id"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
        </th>
        <th width="13%">
            <a class="cm-ajax" href="{"`$c_url`&sort_by=order_id&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("order_id")}{if $search.sort_by == "order_id"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
        </th>
        <th width="15%">
            <a class="cm-ajax" href="{"`$c_url`&sort_by=shipment_date&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("shipment_date")}{if $search.sort_by == "shipment_date"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
        </th>
        <th width="14%">
            {__("yandex_delivery.order")}
        </th>
        <th>
            <a class="cm-ajax" href="{"`$c_url`&sort_by=customer&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("customer")}{if $search.sort_by == "customer"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
        </th>
        <th width="5%">&nbsp;</th>
        <th class="right">
            <a class="cm-ajax" href="{"`$c_url`&sort_by=status&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("status")}{if $search.sort_by == "status"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a>
        </th>
    </tr>
    </thead>

    {foreach from=$yd_orders item=order}
    <tr>
        <td class="center">
            <input type="checkbox" name="shipment_ids[]" value="{$order.shipment_id}" class=" cm-item" />
        </td>
        <td>
            {if $order.shipment_id}
                <a class="underlined" href="{"shipments.details?shipment_id=`$order.shipment_id`"|fn_url}"><span>#{$order.shipment_id}</span></a>
            {/if}
        </td>
        <td>
            <a class="underlined" href="{"orders.details?order_id=`$order.order_id`"|fn_url}"><span>#{$order.order_id}</span></a>
        </td>
        <td>
            {if $order.shipment_timestamp && $order.yandex_id}{$order.shipment_timestamp|date_format:"`$settings.Appearance.date_format`"}{else}--{/if}
        </td>
        <td>
            <span>{$order.yandex_full_num}</span>
        </td>
        <td>
            {if $order.user_id}<a href="{"profiles.update?user_id=`$order.user_id`"|fn_url}">{/if}{$order.s_lastname} {$order.s_firstname}{if $order.user_id}</a>{/if}
            {if $order.company}<p class="muted nowrap">{$order.company}</p>{/if}
        </td>
        <td class="nowrap">

            <div class="hidden-tools">
                {assign var="return_current_url" value=$config.current_url|escape:url}
                {capture name="tools_list"}
                    {if $order.yandex_id}
                        <li>{btn type="list" text=__("update") class="cm-confirm" href="yandex_delivery.update?yandex_ids[]=`$order.yandex_id`&redirect_url=`$return_current_url`" method="POST"}</li>
                    {/if}
                    <li>{btn type="list" text=__("delete") class="cm-confirm" href="shipments.delete?shipment_ids[]=`$order.shipment_id`&redirect_url=`$return_current_url`" method="POST"}</li>
                {/capture}
                {dropdown content=$smarty.capture.tools_list}
            </div>

        </td>
        <td class="right">
            <span>{$order.status_name}</span>
        </td>

    </tr>
    {/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}
{include file="common/pagination.tpl"}
</form>
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {if $yd_orders}
            <li>{btn type="list" text=__("yandex_delivery.update_selected") dispatch="dispatch[yandex_delivery.update]" form="manage_yd_orders_form"}</li>
            <li>{btn type="delete_selected" dispatch="dispatch[shipments.m_delete]" form="manage_yd_orders_form"}</li>
        {/if}
    {/capture}
    {if $smarty.capture.tools_list|trim}
        {dropdown content=$smarty.capture.tools_list}
    {/if}
{/capture}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="shipments.yandex_delivery" view_type="shipments_yandex"}
    {include file="addons/yandex_delivery/views/yandex_delivery/components/yd_search_form.tpl" dispatch="shipments.yandex_delivery"}
{/capture}

{capture name="title"}
    {strip}
    {__("shipments")}
    {if $smarty.request.order_id}
        &nbsp;({__("order")}&nbsp;#{$smarty.request.order_id})
    {/if}
    {/strip}
{/capture}
{include file="common/mainbox.tpl" title=$smarty.capture.title content=$smarty.capture.mainbox sidebar=$smarty.capture.sidebar buttons=$smarty.capture.buttons}