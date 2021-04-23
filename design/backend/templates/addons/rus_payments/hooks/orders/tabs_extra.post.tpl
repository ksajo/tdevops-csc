{if $show_refund}
    <div class="hidden orders-right-pane form-horizontal" title="{__("addons.rus_payments.refund")}" id="rus_payments_refund_dialog">
        <form action="{""|fn_url}" method="post" class="rus-payments-refund-form cm-form-dialog-closer" name="refund_form">
            <input type="hidden" name="refund_data[order_id]" value="{$order_info.order_id}" />
            <div class="control-group">
                {if $show_detailed_refund}
                <table class="table" id="yandex_checkpoint_detailed_refund_content">
                    <thead>
                    <tr>
                        <th width="1%">{include file="common/check_items.tpl" class="yc-refund-recalculator"}</th>
                        <th>{__("product")}</th>
                        <th width="1%" class="right">{__("price")}</th>
                        <th width="1%">{__("qty")}</th>
                    </tr>
                    <tbody>
                    {hook name="yandex_checkpoint:return_info"}
                    {foreach from=$returned_order_info.products item="oi" key="key"}
                        <tr>
                            <td width="1%" class="left">
                                <input type="hidden"
                                       name="refund_data[products][{$oi.cart_id}][is_returned]"
                                       value="N"
                                />
                                <input type="checkbox"
                                       name="refund_data[products][{$oi.cart_id}][is_returned]"
                                       value="Y"
                                       class="cm-item yc-refund-recalculator"
                                       data-ca-refund-value="{if $oi.extra.exclude_from_calculate}0{else}{$oi.price|fn_format_price:$primary_currency:null:true}{/if}"
                                       data-ca-cart-id="{$oi.cart_id}"
                                       checked="checked"
                                />
                            </td>
                            <td>
                                <a href="{"products.update?product_id=`$oi.product_id`"|fn_url}">{$oi.product nofilter}</a>
                                {if $oi.product_options}
                                    <div class="options-info">
                                    {include file="common/options_info.tpl" product_options=$oi.product_options}
                                    </div>
                                {/if}
                            </td>
                            <td class="right nowrap">
                                {if $oi.extra.exclude_from_calculate}
                                    {__("free")}
                                {else}
                                    {include file="common/price.tpl" secondary_currency=$primary_currency value=$oi.price}
                                {/if}
                            </td>
                            <td>
                                <input type="hidden" name="returns[{$oi.cart_id}][available_amount]" value="{$oi.amount}" />
                                <select name="refund_data[products][{$oi.cart_id}][amount]"
                                        class="yc-refund-recalculator input-small"
                                        id="elm_refund_amount_{$oi.cart_id}"
                                        data-ca-refund-amount-{$oi.cart_id}
                                >
                                    {for $amount = 1 to $oi.amount}
                                        <option value="{$amount}"
                                                {if $amount == $oi.amount}selected="selected"{/if}
                                        >{$amount}</option>
                                    {/for}
                                </select>
                            </td>
                        </tr>
                    {/foreach}
                    {if $returned_order_info.shipping_cost|floatval}
                        <tr>
                            <td class="left">
                                <input type="hidden" name="refund_data[refund_shipping]" value="N"/>
                                <input type="checkbox"
                                       name="refund_data[refund_shipping]"
                                       value="Y"
                                       class="cm-item yc-refund-recalculator"
                                       data-ca-refund-value="{$returned_order_info.shipping_cost|fn_format_price:$primary_currency:null:true}"
                                       data-ca-cart-id="shipping_cost"
                                       checked="checked"
                                />
                            </td>
                            <td>{__("shipping")}</td>
                            <td class="right nowrap">
                                {include file="common/price.tpl" value=$returned_order_info.shipping_cost secondary_currency=$primary_currency}
                            </td>
                            <td>
                                <input type="hidden"
                                       value="1"
                                       id="elm_refund_amount_shipping_cost"
                                       data-ca-refund-amount-shipping_cost
                                />
                            </td>
                        </tr>
                    {/if}
                    {if $returned_order_info.payment_surcharge|floatval}
                        <tr>
                            <td class="left">
                                <input type="hidden" name="refund_data[refund_surcharge]" value="N"/>
                                <input type="checkbox"
                                       name="refund_data[refund_surcharge]"
                                       value="Y"
                                       class="cm-item yc-refund-recalculator"
                                       data-ca-refund-value="{$returned_order_info.payment_surcharge|fn_format_price:$primary_currency:null:true}"
                                       data-ca-cart-id="payment_surcharge"
                                       checked="checked"
                                />
                            </td>
                            <td>{__("payment_surcharge")}</td>
                            <td class="right nowrap">
                                {include file="common/price.tpl" value=$returned_order_info.payment_surcharge secondary_currency=$primary_currency}
                            </td>
                            <td>
                                <input type="hidden"
                                       value="1"
                                       id="elm_refund_amount_payment_surcharge"
                                       data-ca-refund-amount-payment_surcharge
                                />
                            </td>
                        </tr>
                    {/if}
                    {if $addons.gift_certificates.status == "A" && $returned_order_info.gift_certificates}
                        {foreach $returned_order_info.gift_certificates as $cart_id => $certificate}
                            <tr>
                                <td class="left">
                                    <input type="hidden"
                                           name="refund_data[certificates][{$cart_id}][is_returned]"
                                           value="N"
                                    />
                                    <input type="checkbox"
                                           name="refund_data[certificates][{$cart_id}][is_returned]"
                                           value="Y"
                                           class="cm-item yc-refund-recalculator"
                                           data-ca-refund-value="{$certificate.amount|fn_format_price:$primary_currency:null:true}"
                                           data-ca-cart-id="certificate_{$cart_id}"
                                           checked="checked"
                                    />
                                </td>
                                <td>{__("gift_certificate")}</td>
                                <td class="right nowrap">
                                    {include file="common/price.tpl" value=$certificate.amount secondary_currency=$primary_currency}
                                </td>
                                <td>
                                    <input type="hidden"
                                           value="1"
                                           id="elm_refund_amount_certificate_{$cart_id}"
                                           data-ca-refund-amount-certificate_{$cart_id}
                                    />
                                </td>
                            </tr>
                        {/foreach}
                    {/if}
                    {/hook}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>&nbsp;</td>
                            <td><strong>{__("addons.rus_payments.amount")}</strong></td>
                            <td class="right nowrap">
                                <input
                                        type="text"
                                        name="refund_data[amount]"
                                        id="rus_payments_refund_amount"
                                        class="input-small cm-numeric right"
                                        data-a-sign="{$currencies.$primary_currency.symbol|strip_tags nofilter}"
                                        {if $currencies.$primary_currency.after == "Y"}data-p-sign="s"{/if}
                                        data-a-dec="."
                                        readonly="readonly"
                                />
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                    </tfoot>
                </table>
                {else}
                <label class="control-label" for="rus_payments_refund_amount">{__("addons.rus_payments.amount")} ({$currencies.$primary_currency.symbol nofilter})</label>
                <div class="controls">
                    <input type="text" name="refund_data[amount]" id="rus_payments_refund_amount" class="input-small" value="{$order_info.total|default:"0.00"|fn_format_price:$primary_currency:null:false}" />
                </div>
                {/if}
            </div>


            <div class="control-group">
                <label class="control-label" for="rus_payments_refund_cause">{__("addons.rus_payments.cause")}</label>
                <div class="controls">
                    <textarea name="refund_data[cause]" cols="55" rows="3" id="rus_payments_refund_cause"></textarea>
                </div>
            </div>
            <div class="buttons-container">
                <a class="cm-dialog-closer cm-cancel tool-link btn">{__("cancel")}</a>
                {include file="buttons/button.tpl" but_text=__("refund") but_meta="" but_name="dispatch[orders.rus_payments_refund]" but_role="button_main"}
            </div>
        </form>
    <!--rus_payments_refund_dialog--></div>
{/if}
