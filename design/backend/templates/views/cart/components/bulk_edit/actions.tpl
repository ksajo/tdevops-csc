{hook name="cart:bulk_edit_actions"}
    <li>
        {btn type="delete_selected"
             dispatch="dispatch[cart.m_delete]"
             form="carts_list_form"
        }
    </li>
{/hook}