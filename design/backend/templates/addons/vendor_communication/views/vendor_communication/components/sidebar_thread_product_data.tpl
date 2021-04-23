{if $object.object_type == $smarty.const.VC_OBJECT_TYPE_PRODUCT}
{$sidebar_content_width = "192"}

<div class="sidebar-row">
    <h6>{__("vendor_communication.product_details")}</h6>
    <ul class="unstyled">
        <li>
            <p>
                {include file="common/image.tpl"
                    image=$object.main_pair.icon|default:$object.main_pair.detailed
                    image_id=$object.main_pair.image_id
                    image_width=$sidebar_content_width
                    image_height=$sidebar_content_width
                    href="products.update?product_id=`$object.product_id`"|fn_url
                    show_detailed_link=true
                }
            </p>
        </li>
        <li>
            {if fn_check_permissions("products", "update", "admin")}
                <a href={"products.update?product_id=`$object.product_id`"|fn_url} title="{$object.product}">
                    {$object.product}
                </a>
            {else}
                {$object.product}
            {/if}
        </li>
        <li>
            {hook name="vendor_communication:product_info"}
                <span class="muted">
                    {$object.product_code}
                </span>
            {/hook}
        </li>
        <li>
            {include file="common/price.tpl" value=$object.price}
        </li>
    </ul>
</div>
{/if}