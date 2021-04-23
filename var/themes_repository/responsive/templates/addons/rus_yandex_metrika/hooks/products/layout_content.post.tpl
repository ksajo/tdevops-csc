{if $addons.rus_yandex_metrika.ecommerce == 'Y'}
    <script type="text/javascript">
        (function (w, _, $) {
            $(document).ready(function () {
                w.dataLayerYM.push({
                    "ecommerce": {
                        "detail": {
                            "products": [
                                {
                                    "id": {$product.product_id},
                                    "name": {$product.product|strip_tags:false|json_encode nofilter},
                                    "price": "{$product.price}",
                                    "brand": {$ym_brand|strip_tags:false|json_encode nofilter},
                                    {if $ym_variant}
                                    "variant": {$ym_variant|strip_tags:false|json_encode nofilter},
                                    {/if}
                                    {if $category}
                                    "category": {$category|strip_tags:false|json_encode nofilter},
                                    {/if}
                                }
                            ]
                        }
                    }
                });
            });
        }(window, Tygh, Tygh.$));
    </script>
{/if}