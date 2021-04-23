{hook name="products:bulk_edit_actions"}
    <li>
        {btn type="list"
             text=__("clone_selected")
             dispatch="dispatch[products.m_clone]"
             form="manage_products_form"
        }
    </li>

    <li>
        {btn type="list"
             text=__("export_selected")
             dispatch="dispatch[products.export_range]"
             form="manage_products_form"
        }
    </li>

    <li>
        {btn type="delete_selected"
             dispatch="dispatch[products.m_delete]"
             form="manage_products_form"
        }
    </li>
{/hook}