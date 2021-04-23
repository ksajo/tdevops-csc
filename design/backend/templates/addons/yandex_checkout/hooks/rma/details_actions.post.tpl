{if $is_refund == "YesNo::YES"|enum
    && $order_info.payment_method.processor_params
    && $order_info.payment_method.processor_params.is_yandex_checkout|default:null
}
    <div class="control-group notify-department">
        <label class="control-label"
               for="elm_yandex_checkout_perform_refund"
        >{__("yandex_checkout.rma.perform_refund")}</label>
        <div class="controls">
            {if $order_info.payment_info["yandex_checkout.full_refund_id"]}
                <p class="label label-success">{__("refunded")}</p>
            {else}
                <label class="checkbox">
                    <input type="checkbox"
                           name="change_return_status[yandex_checkout_perform_refund]"
                           id="elm_yandex_checkout_perform_refund"
                           value={"YesNo::YES"|enum}
                    />
                </label>
            {/if}
        </div>
    </div>
{/if}