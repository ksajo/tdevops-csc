{hook name="pages:bulk_edit_actions"}
    <li>
        {btn type="list"
             text=__("clone_selected")
             dispatch="dispatch[pages.m_clone]"
             form="pages_tree_form"
        }
    </li>

    <li>
        {btn type="delete_selected"
             dispatch="dispatch[pages.m_delete]"
             form="pages_tree_form"
        }
    </li>
{/hook}