{hook name="taxes:bulk_edit_actions"}
    <li>
        {btn type="list" 
            text=__("apply_tax_to_products")
            dispatch="dispatch[taxes.apply_selected_taxes]"
            form="taxes_form"
        }
    </li>

    <li>
        {btn type="list"
            text=__("unset_tax_to_products")
            dispatch="dispatch[taxes.unset_selected_taxes]"
            form="taxes_form"
        }
    </li>
    
    <li class="divider"></li>

    <li>
        {btn type="delete_selected"
            dispatch="dispatch[taxes.m_delete]"
            form="taxes_form"
        }
    </li>
{/hook}
