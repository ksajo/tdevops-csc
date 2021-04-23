{if fn_check_view_permissions("products.manage", "GET")}
    <li>
        {btn type="list" 
             text=__("view_vendor_products") 
             dispatch="dispatch[products.manage]" 
             form="companies_form"
             data=["data-ca-pass-selected-object-ids-as" => "company_ids"]
        }
    </li>
{/if}

{if fn_check_view_permissions("profiles.manage", "GET")}
    <li>
        {btn type="list" 
             text=__("view_vendor_admins")
             dispatch="dispatch[profiles.manage]" 
             form="companies_form"
             data=["data-ca-pass-selected-object-ids-as" => "company_ids"]
        }
    </li>
{/if}

{if fn_check_view_permissions("orders.manage", "GET")}
    <li>
        {btn type="list" 
             text=__("view_vendor_orders") 
             dispatch="dispatch[orders.manage]" 
             form="companies_form"
             data=["data-ca-pass-selected-object-ids-as" => "company_ids"]
        }
    </li>
{/if}

{if fn_check_view_permissions("companies.update", "POST") && fn_check_view_permissions("companies.export_range", "POST")}
    <li class="divider"></li>
{/if}

{if fn_check_view_permissions("companies.update", "POST")}
    <li>
        {btn type="delete_selected" 
             dispatch="dispatch[companies.m_delete]" 
             form="companies_form"
        }
    </li>
{/if}

<li>
    {btn type="list" 
         text=__("export_selected") 
         dispatch="dispatch[companies.export_range]" 
         form="companies_form"
    }
</li>
