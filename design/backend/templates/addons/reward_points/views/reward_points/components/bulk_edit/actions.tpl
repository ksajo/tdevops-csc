{hook name="reward_points:bulk_edit_actions"}
    <li>
        {btn type="delete_selected"
             dispatch="dispatch[reward_points.m_delete]"
             form="userlog_form"
        }
    </li>
{/hook}