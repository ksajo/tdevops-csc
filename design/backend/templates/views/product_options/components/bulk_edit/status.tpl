{foreach $product_option_statuses as $status => $status_name}
    <li>
        <a class="cm-ajax cm-post cm-ajax-send-form"
            href="{"product_options.m_update_statuses?status={$status}"|fn_url}"
            data-ca-target-id="pagination_contents"
            data-ca-target-form="#manage_product_options_form"
        >
            {__("change_to_status", ["[status]" => $status_name])}
        </a>
    </li>
{/foreach}
