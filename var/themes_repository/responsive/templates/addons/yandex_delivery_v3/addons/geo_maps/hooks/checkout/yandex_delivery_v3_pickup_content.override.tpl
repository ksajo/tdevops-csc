{include file="addons/yandex_delivery_v3/views/checkout/components/shippings/map_and_list.tpl"
    shipping = $yandex_delivery_v3.$group_key[$shipping.shipping_id]
    old_office_id = $old_office.$group_key[$shipping.shipping_id]
    shipping_id = $shipping.shipping_id
}