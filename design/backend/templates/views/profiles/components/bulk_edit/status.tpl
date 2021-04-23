<li>
    {btn type="list" 
         text="{__("change_to_status", ["[status]" => __("active")])}"
         dispatch="dispatch[profiles.m_activate]" 
         form="userlist_form"
    }
</li>

<li>
    {btn type="list" 
         text="{__("change_to_status", ["[status]" => __("disabled")])}"
         dispatch="dispatch[profiles.m_disable]" 
         form="userlist_form"
    }
</li>

{include file="common/notify_checkboxes.tpl" 
    prefix="multiple" 
    id="select" 
    notify=true
    notify_customer_status=true
    notify_text=__("notify_user")
    name_prefix="notify"
}
