<li>
    {btn type="delete_selected"
         dispatch="dispatch[categories.m_delete]"
         form="category_tree_form"
         class="cm-confirm" 
         data=["data-ca-confirm-text" => __("category_deletion_side_effects")]
    }
</li>

<li>
    {btn type="list"
         text=__("export_products")
         dispatch="dispatch[products.export_range]"
         form="category_tree_form"
    }
</li>
