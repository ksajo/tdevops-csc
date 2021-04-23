<div class="{if $selected_section !== "yml"}hidden{/if}" id="content_yml">

    {script src="js/addons/yml_export/yml_tab_products.js"}

    {include file="common/subheader.tpl" title=__("general") target="#acc_general"}

    <div id="acc_general" class="collapse in">
        <div class="control-group">
            <label for="yml2_offer_type" class="control-label">{__("yml2_offer_type")}:</label>
            <input id="yml2_parent_offer_val" type="hidden" value="{$offer_type_parent_category}"/>
            <input id="yml2_offer_type_val" type="hidden" value="{$product_data.yml2_offer_type}"/>
            <div class="controls">
                <select name="product_data[yml2_offer_type]" id="yml2_offer_type">
                    <option value="" {if empty($product_data.yml2_parent_offer_type_name)}selected="selected"{/if}>{if !empty($yml2_parent_offer_type_name)}{__('yml_export.category_value', ['[default]' => __($yml2_parent_offer_type_name)])}{else}{__('yml_export.use_category_value')}{/if}</option>
                    {foreach from=$yml2_offer_types item="offer_name" key="offer_type"}
                        <option value="{$offer_type}" {if $product_data.yml2_offer_type == $offer_type}selected="selected"{/if}>{__($offer_name)}</option>
                    {/foreach}
                </select>
                <p class="muted description">{__("tt_addons_yml_export_hooks_products_tabs_content_post_yml2_offer_type")}</p>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">{__("yml_export.yml2_exclude_export")}:</label>
            <div class="controls">
                <input type="hidden" name="product_data[yml2_exclude_price_ids]" value="" />
                {foreach from=$yml2_prices item="price"}
                    <label class="checkbox inline" for="elm_yml_exclude_product_{$price.param_id}">
                        <input type="checkbox" name="product_data[yml2_exclude_price_ids][{$price.param_id}]" id="elm_yml_exclude_product_{$price.param_id}" {if $price.param_id|in_array:$yml2_exclude_prices}checked="checked"{/if} value="{$price.param_id}" />
                        {$price.param_data.name_price_list}</label>
                    {foreachelse}
                    &ndash;
                {/foreach}
            </div>
        </div>

        <div class="control-group">
            <label for="yml2_cpa" class="control-label">{__("yml_export.yml2_cpa")}:</label>
            <div class="controls">
                <select name="product_data[yml2_cpa]" id="yml2_cpa">
                    <option value="Y" {if $product_data.yml2_cpa == "Y"}selected="selected"{/if}>{__("yml2_true")}</option>
                    <option value="N" {if $product_data.yml2_cpa == "N"}selected="selected"{/if}>{__("yml2_false")}</option>
                </select>
                <p class="muted description">{__("ttc_yml_export.yml2_cpa")}</p>
            </div>
        </div>

        <div class="control-group">
            <label for="yml2_brand" class="control-label">{__("yml2_brand")}:</label>
            <div class="controls">
                <input type="text" name="product_data[yml2_brand]" id="yml2_brand" size="55" value="{$product_data.yml2_brand}" class="input-text-large" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_yml_product_descr">{__("yml_export.yml2_description")}:</label>
            <div class="controls">
                <textarea id="elm_yml_product_descr" name="product_data[yml2_description]" cols="55" rows="4" class="input-large">{$product_data.yml2_description}</textarea>
                <p class="muted description">{__("ttc_yml_export.yml2_description")}</p>
            </div>
        </div>

        <div id="yml2_model_div" class="control-group">
            <label for="yml2_model" class="control-label">{__("yml2_model")}:</label>
            <div class="controls">
                <input type="text" name="product_data[yml2_model]" id="yml2_model" size="55" value="{$product_data.yml2_model}" class="input-text-large" {if (!empty($yml2_model_category))}placeholder="{$yml2_model_category}"{/if}/>
                <p class="muted description">{__("tt_addons_yml_export_hooks_products_detailed_content_post_yml2_model")}</p>
            </div>
        </div>

        <div id="yml2_type_prefix_div" class="control-group">
            <label for="yml2_type_prefix" class="control-label">{__("yml2_type_prefix")}:</label>
            <div class="controls">
                <input type="text" name="product_data[yml2_type_prefix]" id="yml2_type_prefix" size="55" value="{$product_data.yml2_type_prefix}" class="input-text-large"  {if (!empty($yml2_type_prefix_category))}placeholder="{$yml2_type_prefix_category}"{/if}/>
                <p class="muted description">{__("tt_addons_yml_export_hooks_products_detailed_content_post_yml2_type_prefix")}</p>
            </div>
        </div>

        <div class="control-group">
            <label for="yml2_sales_notes" class="control-label">{__("yml2_sales_notes")}:</label>
            <div class="controls">
                <input type="text" name="product_data[yml2_sales_notes]" id="yml2_sales_notes" size="50" value="{$product_data.yml2_sales_notes}" class="input-text-large" />
            </div>
        </div>

        {include file="addons/yml_export/common/yml_categories_selector.tpl" name="product_data[yml2_market_category]" value=$product_data.yml2_market_category}

        <div class="control-group">
            <label for="yml2_country" class="control-label">{__("yml2_country")}:</label>
            <div class="controls">
                <input type="text" name="product_data[yml2_origin_country]" id="yml2_country" size="55" value="{$product_data.yml2_origin_country}" class="input-text-large" />
            </div>
        </div>

        <div class="control-group">
            <label for="yml2_manufacturer_warranty" class="control-label">{__("yml2_manufacturer_warranty")}:</label>
            <div class="controls">
                <select name="product_data[yml2_manufacturer_warranty]" id="yml2_manufacturer_warranty">
                    <option value="" {if $product_data.yml2_manufacturer_warranty == ""}selected="selected"{/if}>{__("yml2_none")}</option>
                    <option value="Y" {if $product_data.yml2_manufacturer_warranty == "Y"}selected="selected"{/if}>{__("yml2_true")}</option>
                    <option value="N" {if $product_data.yml2_manufacturer_warranty == "N"}selected="selected"{/if}>{__("yml2_false")}</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label for="yml2_expiry" class="control-label">{__("yml2_expiry")}:</label>
            <div class="controls">
                <input type="text" name="product_data[yml2_expiry]" id="yml2_expiry" size="55" value="{$product_data.yml2_expiry}" class="input-text-large" />
                <p class="muted description">{__("tt_addons_yml_export_hooks_products_tabs_content_post_yml2_expiry")}</p>
            </div>
        </div>

        <div class="control-group">
            <label for="yml2_bid" class="control-label">{__("yml2_bid")}:</label>
            <div class="controls">
                <input type="text" name="product_data[yml2_bid]" id="yml2_bid" size="10" value="{$product_data.yml2_bid}" class="input-small" />
            </div>
        </div>

        <div class="control-group">
            <label for="yml2_cbid" class="control-label">{__("yml2_cbid")}:</label>
            <div class="controls">
                <input type="text" name="product_data[yml2_cbid]" id="yml2_cbid" size="10" value="{$product_data.yml2_cbid}" class="input-small" />
            </div>
        </div>

        <div class="control-group">
            <label for="yml2_fee" class="control-label">{__("yml_export.fee")}:</label>
            <div class="controls">
                <input type="text" name="product_data[yml2_fee]" id="yml2_fee" size="10" value="{$product_data.yml2_fee}" class="input-small" />
                <p class="muted description">{__("ttc_yml_export.fee")}</p>
            </div>
        </div>

        <div class="control-group">
            <label for="yml2_purchase_price" class="control-label">{__("yml2_purchase_price")}:</label>
            <div class="controls">
                <input type="text" name="product_data[yml2_purchase_price]" id="yml2_purchase_price" size="10" value="{$product_data.yml2_purchase_price}" class="input-small" />
            </div>
        </div>

        <div class="control-group">
            <label for="yml2_adult" class="control-label">{__("yml2_adult")}:</label>
            <div class="controls">
                <select name="product_data[yml2_adult]" id="yml2_adult">
                    <option value="N" {if $product_data.yml2_adult == "N"}selected="selected"{/if}>{__("yml2_false")}</option>
                    <option value="Y" {if $product_data.yml2_adult == "Y"}selected="selected"{/if}>{__("yml2_true")}</option>
                </select>
            </div>
        </div>
    </div>

    <hr>

    {include file="common/subheader.tpl" title=__("shipping") target="#acc_shipping"}

    <div id="acc_shipping" class="collapse in">
        <div class="control-group">
            <label for="yml2_delivery" class="control-label">{__("yml2_delivery")}:</label>
            <div class="controls">
                <select name="product_data[yml2_delivery]" id="yml2_delivery">
                    <option value="" {if $product_data.yml2_delivery == ""}selected="selected"{/if}>---</option>
                    <option value="Y" {if $product_data.yml2_delivery == "Y"}selected="selected"{/if}>{__("yml2_true")}</option>
                    <option value="N" {if $product_data.yml2_delivery == "N"}selected="selected"{/if}>{__("yml2_false")}</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label for="yml2_store" class="control-label">{__("yml2_store")}:</label>
            <div class="controls">
                <select name="product_data[yml2_store]" id="yml2_store">
                    <option value="" {if $product_data.yml2_store == ""}selected="selected"{/if}>---</option>
                    <option value="Y" {if $product_data.yml2_store == "Y"}selected="selected"{/if}>{__("yml2_true")}</option>
                    <option value="N" {if $product_data.yml2_store == "N"}selected="selected"{/if}>{__("yml2_false")}</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label for="yml2_pickup" class="control-label">{__("yml2_pickup")}:</label>
            <div class="controls">
                <select name="product_data[yml2_pickup]" id="yml2_pickup">
                    <option value="" {if $product_data.yml2_pickup == ""}selected="selected"{/if}>---</option>
                    <option value="Y" {if $product_data.yml2_pickup == "Y"}selected="selected"{/if}>{__("yml2_true")}</option>
                    <option value="N" {if $product_data.yml2_pickup == "N"}selected="selected"{/if}>{__("yml2_false")}</option>
                </select>
            </div>
        </div>

        {include file="addons/yml_export/common/yml_delivery_options.tpl" name="product_data[yml2_delivery_options]" data=$product_data['yml2_delivery_options'] name_data="product_data[yml2_delivery_options]"}
    </div>

</div>