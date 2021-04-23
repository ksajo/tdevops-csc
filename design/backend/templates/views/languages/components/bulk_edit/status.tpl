{foreach $language_statuses as $status => $status_name}
    <li>
        <a class="cm-ajax cm-post cm-ajax-send-form"
            href="{"languages.update_status?status={$status}"|fn_url}"
            data-ca-target-id="content_languages"
            data-ca-target-form="#languages_form"
        >
            {__("change_to_status", ["[status]" => $status_name])}
        </a>
    </li>
{/foreach}
