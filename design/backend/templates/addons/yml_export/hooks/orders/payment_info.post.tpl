{if $order_info.yml_export}

    <div class="control-group">
        <div class="control-label">{__("method")}</div>
        <div id="tygh_payment_info" class="controls">{__("yml_export")}</div>
    </div>

    <div class="control-group">
        <div class="control-label">{__("order_id")}</div>
        <div class="controls">{$order_info.yml_export.order_id}</div>
    </div>

    <div class="control-group">
        <div class="control-label">{__("payment_type")}</div>
        {if $order_info.yml_export.payment_type}
            <div class="controls">{__("yml2_payment_type_{$order_info.yml_export.payment_type|strtolower}")}</div>
        {/if}
    </div>

    <div class="control-group">
        <div class="control-label">{__("payment_method")}</div>
        {if $order_info.yml_export.payment_method}
            <div class="controls">{__("yml2_payment_method_{$order_info.yml_export.payment_method|strtolower}")}</div>
        {/if}
    </div>

    {if $order_info.yml_export.status}
        <div class="control-group">
            <div class="control-label">{__("status")}</div>
            <div class="controls">{$order_info.yml_export.status}</div>
        </div>
    {/if}

    {if $order_info.yml_export.substatus}
        <div class="control-group">
            <div class="control-label">{__("reason")}</div>
            <div class="controls">{__("yml2_substatus_{$order_info.yml_export.substatus|strtolower}")}</div>
        </div>
    {/if}

{/if}