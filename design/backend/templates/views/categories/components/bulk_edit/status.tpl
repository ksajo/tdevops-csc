<li>
    {btn type="list" 
         text="{__("change_to_status", ["[status]" => __("active")])}"
         dispatch="dispatch[categories.m_activate]" 
         form="category_tree_form"
    }
</li>

<li>
    {btn type="list" 
         text="{__("change_to_status", ["[status]" => __("disabled")])}"
         dispatch="dispatch[categories.m_disable]" 
         form="category_tree_form"
    }
</li>

<li>
    {btn type="list" 
         text="{__("change_to_status", ["[status]" => __("hidden")])}"
         dispatch="dispatch[categories.m_hide]" 
         form="category_tree_form"
    }
</li>
