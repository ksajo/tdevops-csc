{script src="js/addons/yandex_delivery/yandex.js"}

<script type="text/javascript" class="cm-ajax-force">
    (function(_, $) {
        var yd_options = [];
        yd_options = {
            'latitude': {$smarty.const.STORE_LOCATOR_DEFAULT_LATITUDE|doubleval},
            'longitude': {$smarty.const.STORE_LOCATOR_DEFAULT_LONGITUDE|doubleval},
            'map_container': '{$map_container}',
            'zoom': 16,
            'controls': [
                'zoomControl',
                'typeSelector',
                'rulerControl',
            ],
            'language': '{$smarty.const.CART_LANGUAGE}',
            'selectStore': true,
            'storeData': [
                {
                    'store_location_id' : '{$store_location.id}',
                    'group_key' : '{$group_key}',
                    'shipping_id' : '{$shipping.shipping_id}',
                    'latitude' : {$store_location.lat|doubleval},
                    'longitude' : {$store_location.lng|doubleval},
                    'name' :  '{$store_location.location_name|escape:javascript nofilter}',
                    'description' : '{$store_location.address.comment|escape:javascript nofilter}',
                    'city' : '{$store_location.location_name|escape:javascript nofilter}',
                    'pickup_address' : '{$store_location.full_address|escape:javascript nofilter}',
                    'pickup_phone' : '{$store_location.phone.number|escape:javascript nofilter}',
                    'currency' : '{$currencies.$secondary_currency.symbol  nofilter}'
                }
            ]
        };

        $.ceEvent('on', 'ce.commoninit', function(context) {
            if (context.find('#' + yd_options.map_container).length) {
                $.ceYdPickup('show', yd_options);
            }
        });

    }(Tygh, Tygh.$));
</script>