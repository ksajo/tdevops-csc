{foreach $user_group_statuses as $status => $status_name}
    <li>
        <a class="cm-ajax cm-post cm-ajax-send-form"
            href="{"usergroups.m_update_statuses?status={$status}"|fn_url}"
            data-ca-target-id="pagination_contents"
            data-ca-target-form="#usergroups_form"
        >
            {__("change_to_status", ["[status]" => $status_name])}
        </a>
    </li>
{/foreach}
