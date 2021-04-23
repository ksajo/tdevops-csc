{if fn_check_view_permissions("orders.manage", "GET")}
    <li>
        {btn type="list" 
            text=__("view_orders") 
            dispatch="dispatch[orders.manage]" 
            form="userlist_form"
            data=["data-ca-pass-selected-object-ids-as" => "user_ids"]
        }
    </li>
{/if}
{hook name="profiles:view_tools_list_for_selected"}
{/hook}

{if "ULTIMATE"|fn_allowed_for || !$runtime.company_id}
     {if fn_check_view_permissions("profiles.export_range", "POST")}
        <li class="divider"></li>

        <li>
            {btn type="list" 
                text=__("export_selected") 
                dispatch="dispatch[profiles.export_range]" 
                form="userlist_form"
            }
        </li>
    {/if}

    {hook name="profiles:export_tools_list_for_selected"}
    {/hook}
{/if}

{if fn_check_permissions("profiles", "m_delete", "admin", "POST", ["user_type" => $smarty.request.user_type])}
    <li class="divider"></li>

    <li>
        {btn type="delete_selected"
            dispatch="dispatch[profiles.m_delete]"
            form="userlist_form"
        }
    </li>
{/if}

{hook name="profiles:list_tools_for_selected"}
{/hook}
