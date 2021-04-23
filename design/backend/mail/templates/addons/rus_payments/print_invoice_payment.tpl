<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
    <head>
        <title>{__("addons.rus_payments.invoice_payment")}</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="Content-Language" content="en" />
        <style type="text/css">
            {literal}
                @media print {
                    .text-button {
                        display: none;
                    }
                }
                p {font-family:"Times New Roman",Times,serif;font-size:15px;}
                .ty-receipt{font-family:"Times New Roman",Times,serif;font-size:14px; line-height: 17px}
                .ty-receipt strong{font-family:"Times New Roman",Times,serif;font-size:12px;}
                .ty-print-receipt{font-size: 11px !important; text-decoration: none}
                .td-border td{
                    border: #000000 1px solid;
                    padding: 1px;
                }
                .text-button {
                    font-family: Arial;
                    text-decoration: none;
                }
                .text-button:hover {
                    text-decoration: underline
                }
                .ty-text {position: relative;}
                .ty-stamp {position: absolute; left: 130px; top: -20px;}
                .td-border .line-border-bottom {border-bottom: white;}
                .td-border .line-border-top {border-top: white; font-size: 12px;}
                .td-border .line-border-left {border-left: white;}
                .td-border .line-border-right {border-right: white;}
            {/literal}
        </style>
    </head>

    <body>
        {if $order_info.payment_method.processor_params}
            {assign var="account_settings" value=$order_info.payment_method.processor_params}
        {/if}
        <div class="ty-receipt">
            <table width="720" cellpadding="0" cellspacing="0">
                <tr>
                    <td colspan="6" valign="top" align="center"><strong>{__("addons.rus_payments.invoice_notification")}</strong></td>
                </tr>
                <tr>
                    <td colspan="6" height="30"></td>
                </tr>
                <tr class="td-border">
                    <td class="line-border-bottom" colspan="4" width="120">{$account_settings.account_bank}</td>
                    <td width="30">{__("addons.rus_payments.account_bik")}</td>
                    <td width="70">{$account_settings.account_bik}</td>
                </tr>
                <tr class="td-border" height="40">
                    <td class="line-border-top" colspan="4" valign="bottom" align="left">{__("addons.rus_payments.bank_recipient")}</td>
                    <td valign="top" align="left">{__("addons.rus_payments.account_cor")}</td>
                    <td valign="top" align="left">{$account_settings.account_cor}</td>
                </tr>
                <tr class="td-border">
                    <td class="line-border-right" width="15">{__("inn_customer")}</td>
                    <td class="line-border-left" width="45">{$account_settings.account_inn}</td>
                    <td class="line-border-right" width="15">{__("addons.rus_payments.account_kpp")}</td>
                    <td class="line-border-left" width="45">{$account_settings.account_kpp}</td>
                    <td valign="top" align="left" rowspan="3">{__("addons.rus_payments.account_current")}</td>
                    <td valign="top" align="left" rowspan="3">{$account_settings.account_current}</td>
                </tr>
                <tr class="td-border" height="30">
                    <td class="line-border-bottom" colspan="4" valign="top" align="left">{$account_settings.account_recepient_name}</td>
                </tr>
                <tr class="td-border">
                    <td class="line-border-top" colspan="4">{__("recipient")}</td>
                </tr>
            </table></br>

            <table width="720" cellpadding="0" cellspacing="0">
                <tr>
                    <td colspan="6" height="30"><h3>{$order_info.text_invoice_payment}</h3></td>
                </tr>
                <tr>
                    <td width="100" height="30">{__("supplier")}:</td>
                    <td colspan="5">{$order_info.info_supplier}</td>
                </tr>
                <tr>
                    <td colspan="6" height="10"></td>
                </tr>
                <tr>
                    <td width="100" height="30">{__("customer")}:</td>
                    <td colspan="5">{$order_info.info_customer}</td>
                </tr>
            </table></br>

            <table width="720" border="1" style="border:#000000 1px solid;" cellpadding="0" cellspacing="0">
                <thead>
                <tr>
                    <th width="20">{__("number")}</th>
                    <th width="200">{__("product")}</th>
                    <th width="50">{__("quantity")}</th>
                    <th width="50">{__("unit")}</th>
                    <th width="70">{__("price")}</th>
                    <th width="70">{__("subtotal")}</th>
                </tr>
                </thead>
                <tbody>
                {assign var="id" value=0}
                {assign var="count_products" value=0}
                {foreach from=$order_info.products item=product}
                {math equation="id + 1" id=$id assign="id"}
                {math equation="count_products + `$product.amount`" count_products=$count_products assign="count_products"}
                <tr>
                    <td align="center">{$id}</td>
                    <td>
                        {$product.product}
                        {hook name="orders:product_info"}
                        {/hook}
                    </td>
                    <td align="center">{$product.amount}</td>
                    <td align="center">{__("items")}</td>
                    <td align="center">
                        {if $product.extra.exclude_from_calculate}{__("free")}{else}{include file="common/price.tpl" value=$product.original_price}{/if}
                    </td>
                    <td align="center">
                        {if $product.extra.exclude_from_calculate}{__("free")}{else}{include file="common/price.tpl" value=$product.display_subtotal}{/if}
                    </td>
                </tr>
                {/foreach}
                </tbody>
            </table></br>

            <table width="720" cellpadding="0" cellspacing="0">
                <tbody>
                <tr align="right">
                    <td width="600"><b>{__("total")}</b></td>
                    <td><b>{include file="common/price.tpl" value=$order_info.subtotal}</b></td>
                </tr>
                {if $shipping_cost}
                <tr align="right">
                    <td width="600"><b>{__("shipping_cost")}</b></td>
                    <td><b>{include file="common/price.tpl" value=$order_info.shipping_cost}</b></td>
                </tr>
                {/if}
                {if $order_info.subtotal_discount && $order_info.subtotal_discount != 0}
                <tr align="right">
                    <td width="600"><b>{__("order_discount")}</b></td>
                    <td><b>{include file="common/price.tpl" value=$order_info.subtotal_discount}</b></td>
                </tr>
                {/if}
                {foreach from=$order_info.taxes item=tax_data}
                    <tr align="right">
                        <td><b>{$tax_data.description}&nbsp;{include file="common/modifier.tpl" mod_value=$tax_data.rate_value mod_type=$tax_data.rate_type}{if $tax_data.regnumber}{/if}</b></td>
                        <td><b>{include file="common/price.tpl" value=$tax_data.tax_subtotal}</b></td>
                    </tr>
                {/foreach}
                <tr align="right">
                    <td><b>{__("addons.rus_payments.total_pay")}</b></td>
                    <td><b>{include file="common/price.tpl" value=$order_info.total}</b></td>
                </tr>
                <tr>
                    <td colspan="2">
                        {__("addons.rus_payments.total_items")} {$count_products}, {__("addons.rus_payments.total_of")} {if $is_rub_total}{$total_print nofilter}{else}{include file="common/price.tpl" value=$order_info.total}{/if}
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <b>{$order_info.str_total}</b>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" height="30"></td>
                </tr>
                </tbody>
            </table></br>

            <table width="720" cellpadding="0" cellspacing="0">
                <tbody>
                <tr>
                    <td colspan="2" width="360">
                        <div class="ty-text">
                            <b>{__("addons.rus_payments.supervisor")}</b> _________________________________
                            <b>{__("addons.rus_payments.accountant")}</b> _______________________________________
                            {if $order_info.path_stamp}
                                <div class="ty-stamp"><img src="{$url_images}" alt="stamp" width="{$account_settings.account_print_width}" height="{$account_settings.account_print_height}" /></div>
                            {/if}
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
            </br>
        </div>
        </br>
        {if $show_print_button}
        <span><a class="text-button" href="javascript:window.print()">{__("addons.rus_payments.invoice_print")}</a></span>
        {/if}
    </body>
</html>
