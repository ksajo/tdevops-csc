{script src="js/addons/retailcrm/settings.js"}

<div id="retailcrm_settings_container">
    {if $retailcrm_connection_status}
        {include file="common/subheader.tpl" title=__("retailcrm.settings.mapping_sites") target="#collapsable_sites"}
        <div id="collapsable_sites" class="in collapse">
            <div class="control-group setting-wide">
                <strong class="control-label">{__("retailcrm.settings.shop_sites")}</strong>
                <div class="controls"><strong class="control-label">{__("retailcrm.settings.retailcrm_sites")}</strong></div>
            </div>

            {foreach from=$storefronts item="storefront"}
                <div class="control-group setting-wide">
                    <label for="retailcrm_mapping_sites_{$storefront.company_id}" class="control-label">{$storefront.company}</label>
                    <div class="controls">
                        <input type="hidden" name="retailcrm_mapping[sites][{$storefront.company_id}]">
                        <select id="retailcrm_mapping_sites_{$storefront.company_id}" name="retailcrm_mapping[sites][{$storefront.company_id}]">
                            <option value="">---</option>

                            {foreach from=$retailcrm_sites item="retailcrm_site"}
                                <option value="{$retailcrm_site.code}" {if $map_sites[$storefront.company_id] == $retailcrm_site.code}selected{/if}>{$retailcrm_site.name}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            {/foreach}
        </div>

        {include file="common/subheader.tpl" title=__("retailcrm.settings.mapping_order_statuses") target="#collapsable_order_statuses"}
        <div id="collapsable_order_statuses" class="in collapse">
            <div class="control-group setting-wide">
                <strong class="control-label">{__("retailcrm.settings.shop_order_statuses")}</strong>
                <div class="controls"><strong class="control-label">{__("retailcrm.settings.retailcrm_order_statuses")}</strong></div>
            </div>

            {foreach from=$order_statuses item="order_status"}
                <div class="control-group setting-wide">
                    <label for="retailcrm_mapping_order_statuses_{$order_status.status}" class="control-label">{$order_status.description}</label>
                    <div class="controls">
                        <input type="hidden" name="retailcrm_mapping[order_statuses][{$order_status.status}]">
                        <select id="retailcrm_mapping_order_statuses_{$order_status.status}" name="retailcrm_mapping[order_statuses][{$order_status.status}]">
                            <option value="">---</option>
                            {foreach from=$retailcrm_order_statuses item="retailcrm_order_status"}
                                <option value="{$retailcrm_order_status.code}" {if $map_order_statuses[$order_status.status] == $retailcrm_order_status.code}selected{/if}>{$retailcrm_order_status.name}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            {/foreach}
        </div>

        {include file="common/subheader.tpl" title=__("retailcrm.settings.mapping_payment_types") target="#collapsable_payment_types"}
        <div id="collapsable_payment_types" class="in collapse">
            <div class="control-group setting-wide">
                <strong class="control-label">{__("retailcrm.settings.shop_payment_types")}</strong>
                <div class="controls"><strong class="control-label">{__("retailcrm.settings.retailcrm_payment_types")}</strong></div>
            </div>

            {foreach from=$payment_types item="payment_type"}
                <div class="control-group setting-wide">
                    <label for="retailcrm_mapping_payment_types_{$payment_type.payment_id}" class="control-label">{$payment_type.payment}</label>
                    <div class="controls">
                        <input type="hidden" name="retailcrm_mapping[payment_types][{$payment_type.payment_id}]">
                        <select id="retailcrm_mapping_payment_types_{$payment_type.payment_id}" name="retailcrm_mapping[payment_types][{$payment_type.payment_id}]">
                            <option value="">---</option>
                            {foreach from=$retailcrm_payment_types item="retailcrm_payment_type"}
                                <option value="{$retailcrm_payment_type.code}" {if $map_payment_types[$payment_type.payment_id] == $retailcrm_payment_type.code}selected{/if}>{$retailcrm_payment_type.name}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            {/foreach}
        </div>


        {include file="common/subheader.tpl" title=__("retailcrm.settings.mapping_shipping_types") target="#collapsable_shipping_types"}
        <div id="collapsable_shipping_types" class="in collapse">
            <div class="control-group setting-wide">
                <strong class="control-label">{__("retailcrm.settings.shop_shipping_types")}</strong>
                <div class="controls"><strong class="control-label">{__("retailcrm.settings.retailcrm_shipping_types")}</strong></div>
            </div>

            {foreach from=$shipping_types item="shipping_type"}
                {if $shipping_type.status == 'A'}
                    <div class="control-group setting-wide">
                        <label for="retailcrm_mapping_shipping_types_{$shipping_type.shipping_id}" class="control-label">{$shipping_type.shipping}</label>
                        <div class="controls">
                            <input type="hidden" name="retailcrm_mapping[shipping_types][{$shipping_type.shipping_id}]">
                            <select id="retailcrm_mapping_shipping_types_{$shipping_type.shipping_id}" name="retailcrm_mapping[shipping_types][{$shipping_type.shipping_id}]">
                                <option value="">---</option>
                                {foreach from=$retailcrm_shipping_types item="retailcrm_shipping_type"}
                                    <option value="{$retailcrm_shipping_type.code}" {if $map_shipping_types[$shipping_type.shipping_id] == $retailcrm_shipping_type.code}selected{/if}>{$retailcrm_shipping_type.name}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                {/if}
            {/foreach}
        </div>

        {include file="common/subheader.tpl" title=__("retailcrm.settings.other") target="#collapsable_other"}
        <div id="collapsable_other" class="in collapse">
            <div class="control-group setting-wide">
                <label class="control-label" for="retailcrm_order_method">{__("retailcrm.settings.retailcrm_order_method")}</label>
                <div class="controls">
                    <select id="retailcrm_order_method" name="retailcrm_settings[order_method]">
                        <option value="">---</option>
                        {foreach from=$retailcrm_order_methods item="retailcrm_order_method"}
                            <option value="{$retailcrm_order_method.code}" {if $order_method == $retailcrm_order_method.code}selected{/if}>{$retailcrm_order_method.name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="control-group setting-wide">
                <label class="control-label" for="retailcrm_order_type">{__("retailcrm.settings.retailcrm_order_type")}</label>
                <div class="controls">
                    <select id="retailcrm_order_type" name="retailcrm_settings[order_type]">
                        <option value="">---</option>
                        {foreach from=$retailcrm_order_types item="retailcrm_order_type"}
                            <option value="{$retailcrm_order_type.code}" {if $order_type == $retailcrm_order_type.code}selected{/if}>{$retailcrm_order_type.name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
    {else}
        <div class="text-error">{__("retailcrm.settings.non_connection")}</div>
        <div class="control-group">
            <div class="controls">
                {include file="buttons/button.tpl" but_id="retailcrm_settings_connect_link" but_role="action" but_meta="btn-primary" but_href="{"addons.update.connect?addon=retailcrm"|fn_url}" but_text=__("retailcrm.settings.connect") but_target_id="retailcrm_settings_container"}
            </div>
        </div>
    {/if}
<!--retailcrm_settings_container--></div>

