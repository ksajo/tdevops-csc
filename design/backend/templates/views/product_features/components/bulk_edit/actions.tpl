<li>
    {btn type="delete_selected"
        text=__("delete_selected")
        dispatch="dispatch[product_features.m_delete]"
        form="manage_product_features_form"
    }
</li>

<li>
    {btn type="list"
        text=__("create_filters")
        dispatch="dispatch[product_filters.m_create_by_features]"
        form="manage_product_features_form"
    }
</li>
