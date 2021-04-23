{if $show_refund}
    <div class="btn-group">
        <a class="btn cm-dialog-opener cm-dialog-auto-size" data-ca-target-id="rus_payments_refund_dialog">{__("addons.rus_payments.refund")}</a>
    </div>
{/if}

{if $processor_script == 'sbrf.php'}
    <div class="btn-group">
        <a class="btn-small cm-ajax" href="{"orders.send_sbrf_receipt?order_id=`$order_info.order_id`"|fn_url}">{__("send")}</a>
        <a class="btn-small cm-new-window" href="{"orders.print_sbrf_receipt?order_id=`$order_info.order_id`"|fn_url}">{__("rus_payments.print_invoice.sbrf")}</a>
    </div>
{/if}

{if $processor_script == 'account.php'}
    <div class="btn-group">
        <a class="btn-small cm-ajax" href="{"orders.send_account_payment?order_id=`$order_info.order_id`"|fn_url}">{__("send")}</a>
        <a class="btn-small cm-new-window" href="{"orders.print_invoice_payment?order_id=`$order_info.order_id`"|fn_url}">{__("rus_payments.print_invoice.account")}</a>
    </div>
{/if}