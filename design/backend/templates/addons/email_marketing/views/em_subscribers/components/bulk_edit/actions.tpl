<li>
    {btn type="list" 
        text=__("export_selected")
        dispatch="dispatch[em_subscribers.export_range]"
        form="subscribers_form"
    }
</li>

<li>
    {btn type="delete_selected" 
        dispatch="dispatch[em_subscribers.m_delete]" 
        form="subscribers_form"
    }
</li>
