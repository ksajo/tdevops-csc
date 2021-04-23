{if $object.object_type == $smarty.const.VC_OBJECT_TYPE_PRODUCT}
    {include file="addons/vendor_communication/views/vendor_communication/components/sidebar_thread_product_data.tpl"
        object=$object
        object_id=$object_id
    }
{elseif $object.object_type === $smarty.const.VC_OBJECT_TYPE_ORDER}
    {include file="addons/vendor_communication/views/vendor_communication/components/sidebar_thread_order_data.tpl"
        object=$object
        object_id=$object_id
    }
{/if}