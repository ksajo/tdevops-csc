{foreach $shipping_statuses as $status => $status_name}
    <li>
        <a class="cm-ajax cm-post cm-ajax-send-form"
            href="{"shippings.m_update_statuses?status={$status}"|fn_url}"
            data-ca-target-id="shippings_content"
            data-ca-target-form="#shippings_form"
        >
            {__("change_to_status", ["[status]" => $status_name])}
        </a>
    </li>
{/foreach}
