{foreach $destination_statuses as $status => $status_name}
    <li>
        <a class="cm-ajax cm-post cm-ajax-send-form" 
            href="{"destinations.update_status?status={$status}"|fn_url}"
            data-ca-target-id="destination_contents"
            data-ca-target-form="#destinations_form"
        >
            {__("change_to_status", ["[status]" => $status_name])}
        </a>
    </li>
{/foreach}