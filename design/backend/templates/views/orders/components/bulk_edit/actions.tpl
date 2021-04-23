<li>
    {btn type="list"
         text={__("view_purchased_products")}
         dispatch="dispatch[orders.products_range]"
         form="orders_list_form"}
</li>
{hook name="orders:view_tools_list_for_selected"}
{/hook}

<li class="divider"></li>

<li class="mobile-hide">
    {btn type="list"
         text={__("export_selected")}
         dispatch="dispatch[orders.export_range]"
         form="orders_list_form"}
</li>
{hook name="orders:export_tools_list_for_selected"}
{/hook}

<li class="divider"></li>

<li class="mobile-hide">
    {btn type="delete_selected"
        dispatch="dispatch[orders.m_delete]"
        form="orders_list_form"
    }

</li>

{hook name="orders:list_tools_for_selected"}
{/hook}
