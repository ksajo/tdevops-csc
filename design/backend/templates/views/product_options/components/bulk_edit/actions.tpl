<li>
    {btn type="list" 
        text=__("apply_to_products") 
        dispatch="dispatch[product_options.apply]" 
        class="cm-submit" 
        process=true 
        form="manage_product_options_form"
    }
</li>

<li>
    {btn type="delete_selected"
        dispatch="dispatch[product_options.m_delete]"
        form="manage_product_options_form"
    }
</li>
