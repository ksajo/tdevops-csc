{script src="js/addons/rus_edost/func.js"}
{script src="//pickpoint.ru/select/postamat.js" charset="utf-8"}

{if $product_groups}
    {foreach from=$product_groups key=group_key item=group}
        {if $group.shippings && !$group.shipping_no_required}

            {foreach from=$group.shippings item=shipping}
                {if $cart.chosen_shipping.$group_key == $shipping.shipping_id}

                    {assign var="old_office_id" value=$old_ship_data.$group_key.office_id}

                    {assign var="shipping_id" value=$shipping.shipping_id}

                    {if $shipping.data.office}
                        {assign var="office_count" value=$shipping.data.office|count}
                        {if !$old_office_id && $office_count > 1}
                            {assign var="old_office_id" value=$shipping.data.office|key}
                        {/if}
                    
                        {if $office_count == 1}
                            {foreach from=$shipping.data.office item=office}
                            <div class="sidebar-row">
                                <input type="hidden"
                                    name="select_office[{$group_key}][{$shipping_id}]"
                                    value="{$office.office_id}"
                                    id="office_{$office.office_id}"
                                    data-ca-pickup-select-office="true"
                                    data-ca-shipping-id="{$shipping_id}"
                                    data-ca-group-key="{$group_key}"
                                    data-ca-location-id="{$office.office_id}">
                                {$office.name}
                                <p class="muted">{$office.address}<br />
                                {$office.tel}<br />
                                {$office.schedule}<br />
                                </p>
                            </div>
                            {/foreach}
                        {else}
                            {foreach from=$shipping.data.office item=office}
                            <div class="sidebar-row">
                                <div class="control-group">
                                    <div class="controls">
                                        <label for="office_{$office.office_id}" class="radio">
                                            <input type="radio"
                                                name="select_office[{$group_key}][{$shipping_id}]"
                                                value="{$office.office_id}"
                                                {if $old_office_id == $office.office_id}checked="checked"{/if}
                                                id="office_{$office.office_id}"
                                                data-ca-pickup-select-office="true"
                                                data-ca-shipping-id="{$shipping_id}"
                                                data-ca-group-key="{$group_key}"
                                                data-ca-location-id="{$office.office_id}">
                                                {$office.name}
                                        </label>
                                        <p class="muted">{$office.address}<br />
                                        {$office.tel}<br />
                                        {$office.schedule}<br />
                                        </p>
                                    </div>
                                </div>
                            </div>
                            {/foreach}
                        {/if}
                    {/if}

                    {if $shipping.module == "edost" && $shipping.service_code == $smarty.const.CODE_SERVICE_PICKPOINT}
                        <input type="hidden" name="pickpointmap[{$group_key}][{$shipping.shipping_id}][pickpoint_id]" id="pickpoint_id" value="{$old_ship_data.$group_key.pickpointmap_data.pickpoint_id}" />
                        <input type="hidden" name="pickpointmap[{$group_key}][{$shipping.shipping_id}][pickpoint_name]" id="pickpoint_name" value="{$old_ship_data.$group_key.pickpointmap_data.pickpoint_name}" />
                        <input type="hidden" name="pickpointmap[{$group_key}][{$shipping.shipping_id}][pickpoint_address]" id="pickpoint_address" value="{$old_ship_data.$group_key.pickpointmap_data.pickpoint_address}" />

                        <div class="sidebar-row">
                            <div id="pickpoint_name_terminal">{$old_ship_data.$group_key.pickpointmap_data.pickpoint_name}</div>
                            <div id="pickpoint_address_terminal">{$old_ship_data.$group_key.pickpointmap_data.pickpoint_address}</div>
                        </div>

                        <a href="#" id="pickpoint_select_terminal" data-pickpoint-select-state="{$group.package_info.location.state_descr}" data-pickpoint-select-city="{$group.package_info.location.city}">{__("select")}</a>
                    {/if}
                {/if}
            {/foreach}
        {/if}
    {/foreach}
{/if}