{foreach from=$cart.shipping item=pickpoint_shipping}
    {if $pickpoint_shipping.module == 'pickpoint'}
        {script src="js/addons/rus_pickpoint/func.js"}
        {if $addons.rus_pickpoint.secure_protocol == 'Y'}
            {assign var="url" value='https://pickpoint.ru/select/postamat.js'}
        {else}
            {assign var="url" value='http://pickpoint.ru/select/postamat.js'}
        {/if}
        <script type="text/javascript" src="{$url}"></script>

        <input type="hidden" name="pickpoint_select" value="{$group_key}" id="pickpoint_select" />

        {if $product_groups}
            {foreach from=$product_groups key=group_key item=group}
                {if $group.shippings && !$group.shipping_no_required}
                    {if !empty($pickpoint_postamat)}
                        {foreach from=$group.shippings item=shipping}
                            {if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.module == 'pickpoint'}
                                {assign var="shipping_id" value=$shipping.shipping_id}
                                {assign var="pickpoint_offices" value=$pickpoint_postamat.$group_key.$shipping_id}
                                <div class="control-group">
                                    <input type="hidden" name="pickpoint_office[{$group_key}][{$shipping_id}][pickpoint_id]" id="pickpoint_id" value="{$pickpoint_offices.pickpoint_id}" />
                                    <input type="hidden" name="pickpoint_office[{$group_key}][{$shipping_id}][address_pickpoint]" id="address_pickpoint" value="{$pickpoint_offices.address_pickpoint}" />
                                    <div>
                                        {$pickpoint_offices.address_pickpoint}
                                    </div>
                                </div>
                            {/if}
                        {/foreach}
                    {/if}

                    {if $shipping_id}
                    <div class="control-group">
                        <a href="#" id="pickpoint_terminal_{$group_key}" class="cm-submit cm-ajax cm-skip-validation" onclick="fn_click_pickpoint_terminal({$group_key}); PickPoint.open(addressPostamatOrder, { fromcity:'{$group.package_info.location.state_descr}',city:'{$group.package_info.location.city}' });return false">{__("addons.rus_pickpoint.select_terminal")}</a>
                    </div>
                    {/if}
                {/if}
            {/foreach}
        {/if}
    {/if}
{/foreach}
