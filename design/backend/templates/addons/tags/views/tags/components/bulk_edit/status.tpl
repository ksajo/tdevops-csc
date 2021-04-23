<li>
    {btn type="list" 
         text=__("change_to_status", ["[status]" => __("active")])
         dispatch="dispatch[tags.approve]" 
         form="tags_form"
    }
</li>

<li>
    {btn type="list" 
         text=__("change_to_status", ["[status]" => __("disabled")])
         dispatch="dispatch[tags.disapprove]" 
         form="tags_form"
    }
</li>