{foreach $order_status_descr as $status => $status_name}
    <li>
        <a class="cm-ajax cm-post cm-ajax-send-form" 
            href="{"orders.m_update?status={$status}"|fn_url}"
            data-ca-target-id="pagination_contents"
            data-ca-target-form="#orders_list_form"
        >
            {__("change_to_status", ["[status]" => $status_name])}
        </a>
    </li>
{/foreach}

{include file="common/notify_checkboxes.tpl" 
    prefix="multiple" 
    id="select" 
    notify_customer_status=true
    notify_department_status = true
    notify_vendor_status = true
    name_prefix="notify"
}
