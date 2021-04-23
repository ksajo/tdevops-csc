{hook name="block_manager:bulk_edit_actions"}
    <li>
        {btn type="delete_selected"
             dispatch="dispatch[block_manager.block.m_delete]"
             form="manage_blocks_form"
        }
    </li>
{/hook}