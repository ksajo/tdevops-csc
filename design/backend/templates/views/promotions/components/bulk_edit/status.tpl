{foreach $promotion_statuses as $status => $status_name}
    <li>
        <a class="cm-ajax cm-post cm-ajax-send-form"
            href="{"promotions.update_status?status={$status}"|fn_url}"
            data-ca-target-id="pagination_contents"
            data-ca-target-form="#promotion_form"
        >
            {__("change_to_status", ["[status]" => $status_name])}
        </a>
    </li>
{/foreach}