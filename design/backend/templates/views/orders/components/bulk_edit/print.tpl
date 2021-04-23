<li>
    {btn type="list" 
         text=__("invoice")
         dispatch="dispatch[orders.bulk_print]" 
         form="orders_list_form" 
         class="cm-new-window"}
</li>

<li>
    {btn type="list" 
         text=__("invoice_pdf")
         dispatch="dispatch[orders.bulk_print..pdf]" 
         form="orders_list_form"}
</li>

<li>
    {btn type="list" 
         text=__("packing_slip")
         dispatch="dispatch[orders.packing_slip]" 
         form="orders_list_form" 
         class="cm-new-window"}
</li>
