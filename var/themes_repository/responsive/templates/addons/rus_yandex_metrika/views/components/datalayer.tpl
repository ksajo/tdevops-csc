<script type="text/javascript">
    (function (window, document, _, $) {
        $(document).ready(function () {
            {if $yandex_metrika.deleted|default:[]}
            window.dataLayerYM.push({
                ecommerce: {
                    remove: {
                        products: [
                            {foreach $yandex_metrika.deleted as $product}
                            {
                                id: {$product.id},
                                name: {$product.name|strip_tags:false|json_encode nofilter},
                                quantity: {$product.quantity},
                                {if $product.category}
                                category: {$product.category|strip_tags:false|json_encode nofilter},
                                {/if}
                            },
                            {/foreach}
                        ]
                    }
                }
            });
            {/if}

            {if $yandex_metrika.added|default:[]}
            window.dataLayerYM.push({
                ecommerce: {
                    add: {
                        products: [
                            {foreach $yandex_metrika.added as $product}
                            {
                                id: {$product.id},
                                name: {$product.name|strip_tags:false|json_encode nofilter},
                                price: {$product.price},
                                quantity: {$product.quantity},
                                {if $product.brand}
                                brand: {$product.brand|strip_tags:false|json_encode nofilter},
                                {/if}
                                {if $product.category}
                                category: {$product.category|strip_tags:false|json_encode nofilter},
                                {/if}
                            },
                            {/foreach}
                        ]
                    }
                }
            });
            {/if}

            {if $yandex_metrika.purchased|default:[]}
            window.dataLayerYM.push({
                ecommerce: {
                    purchase: {
                        actionField: {
                            id: {$yandex_metrika.purchased.action.id},
                            revenue: {$yandex_metrika.purchased.action.revenue},
                            {if $yandex_metrika.purchased.action.coupon}
                            coupon: '{$yandex_metrika.purchased.action.coupon}'
                            {/if}
                        },
                        products: [
                            {foreach $yandex_metrika.purchased.products as $product}
                            {
                                id: {$product.id},
                                name: {$product.name|strip_tags:false|json_encode nofilter},
                                price: {$product.price},
                                {if $product.brand}
                                brand: {$product.brand|strip_tags:false|json_encode nofilter},
                                {/if}
                                {if $product.category}
                                category: {$product.category|strip_tags:false|json_encode nofilter},
                                {/if}
                                quantity: {$product.quantity},
                            },
                            {/foreach}
                        ]
                    }
                }
            });
            {/if}
        });
    })(window, Tygh.doc, Tygh, Tygh.$);
</script>