{foreach $c_statuses as $status => $status_name}
    <li>
        {btn type="list" 
            text=__("change_to_status", ["[status]" => "{$status_name}"])
            dispatch="dispatch[companies.m_update_statuses]"
            form="companies_form" 
            class="cm-process-items cm-dialog-opener"
            data=["data-ca-target-id" => "content_selected_make_status_{$status}"]
        }
    </li>
{/foreach}