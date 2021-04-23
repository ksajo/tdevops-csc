{hook name="tags:bulk_edit_actions"}
    <li>
        {btn type="delete_selected"
             dispatch="dispatch[tags.m_delete]"
             form="tags_form"
        }
    </li>
{/hook}