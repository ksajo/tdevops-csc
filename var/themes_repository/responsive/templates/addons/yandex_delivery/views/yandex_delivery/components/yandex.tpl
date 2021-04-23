{script src="js/addons/yandex_delivery/yandex.js"}

<script type="text/javascript" class="cm-ajax-force">
    (function(_, $) {
        var yd_options = [];
        yd_options[{$group_key}] = {
            'latitude': {$smarty.const.STORE_LOCATOR_DEFAULT_LATITUDE|doubleval},
            'longitude': {$smarty.const.STORE_LOCATOR_DEFAULT_LONGITUDE|doubleval},
            'map_container': '{$map_container}',
            'group_key': {$group_key},
            'zoom': 16,
            'controls': [ 
                'zoomControl',
                'typeSelector',
                'rulerControl',
            ],
            'language': '{$smarty.const.CART_LANGUAGE}',
            'selectStore': true,
            'storeData': [
                {foreach from=$store_locations item="loc" name="st_loc_foreach" key="key"}
                {
                    'store_location_id' : '{$loc.id}',
                    'group_key' : '{$group_key}',
                    'shipping_id' : '{$shipping.shipping_id}',
                    'latitude' : {$loc.lat|doubleval},
                    'longitude' : {$loc.lng|doubleval},
                    'name' :  '{$loc.location_name|escape:javascript nofilter}',
                    'description' : '{$loc.address.comment|escape:javascript nofilter}',
                    'city' : '{$loc.name|escape:javascript nofilter}',
                    'pickup_surcharge' : {$yd_shippings_extra.cost|doubleval},
                    'currency' : '{$currencies.$secondary_currency.symbol  nofilter}',
                    'pickup_address' : '{$loc.full_address|escape:javascript nofilter}',
                    'pickup_phone' : '{$loc.phone.number|escape:javascript nofilter}',
                }
                {if !$smarty.foreach.st_loc_foreach.last},{/if}
                {/foreach}
            ]
        };

        $.ceEvent('on', 'ce.commoninit', function(context) {
            if (context.find('#' + yd_options[{$group_key}].map_container).length) {
               $.ceYdPickup('show', yd_options[{$group_key}]);
            }
        });

    }(Tygh, Tygh.$));
</script>

