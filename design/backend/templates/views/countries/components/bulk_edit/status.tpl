<li>
    {btn type="list" 
         text={__("change_to_status", ["[status]" => __("active")])}
         dispatch="dispatch[countries.m_activate]" 
         form="countries_form"
    }
</li>

<li>
    {btn type="list" 
         text={__("change_to_status", ["[status]" => __("disabled")])}
         dispatch="dispatch[countries.m_disable]" 
         form="countries_form"
    }
</li>
