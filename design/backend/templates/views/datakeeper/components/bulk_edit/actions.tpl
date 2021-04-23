{hook name="datakeeper:bulk_edit_actions"}
    <li>
        {btn type="delete_selected"
             dispatch="dispatch[datakeeper.m_delete]"
             form="backups_form"
        }
    </li>
{/hook}