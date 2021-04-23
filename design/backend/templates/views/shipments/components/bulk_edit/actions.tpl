{hook name="shipments:bulk_edit_actions"}
    <li>
        {btn type="list" 
             text=__("bulk_print_packing_slip")
             dispatch="dispatch[shipments.packing_slip]" 
             form="manage_shipments_form"
             class="cm-new-window"
        }
    </li>
{/hook}

    <li class="divider"></li>

    <li>
        {btn type="delete_selected" 
             dispatch="dispatch[shipments.m_delete]" 
             form="manage_shipments_form"
        }
    </li>
