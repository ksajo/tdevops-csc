{if $order_info}
    <script type="text/javascript">
    var yaParams = {
        currencyCode: "{$order_info.secondary_currency|escape:javascript}",
        purchase: {
            actionField: {
                id: "{$order_info.order_id}",
                revenue: {$order_info.total}
            },
            exchange_rate: 1,
            products: [
                {foreach from=$order_info.products item=products}
                {
                    id: "{$products.product_id|escape:javascript}",
                    name: {$products.product|strip_tags:false|json_encode nofilter},
                    price: {$products.price},
                    quantity: {$products.amount}
                },
                {/foreach}
            ]
        }
    };
    </script>
{/if}
