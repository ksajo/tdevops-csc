<li>
    {btn type="list" 
         text="{__("bulk_edit.make_active")}"
         dispatch="dispatch[products.m_activate]" 
         form="manage_products_form"
    }
</li>

<li>
    {btn type="list" 
         text="{__("bulk_edit.make_disabled")}"
         dispatch="dispatch[products.m_disable]" 
         form="manage_products_form"
    }
</li>

<li>
    {btn type="list" 
         text="{__("bulk_edit.make_hidden")}"
         dispatch="dispatch[products.m_hide]" 
         form="manage_products_form"
    }
</li>
