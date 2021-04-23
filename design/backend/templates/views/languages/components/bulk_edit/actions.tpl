{hook name="languages:bulk_edit_actions"}
    <li>
        {btn type="delete_selected" 
             dispatch="dispatch[languages.m_delete]" 
             form="languages_form"
        }
    </li>
{/hook}
