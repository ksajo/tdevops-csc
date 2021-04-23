{if ($category_data)}
    <div id="content_yml">
        <div id="yml2_addon" class="in collapse">

            {script src="js/addons/yml_export/yml_tab_categories.js"}

            <div class="control-group">
                <label for="yml2_offer_type" class="control-label">{__("yml2_offer_type")}:</label>
                <input id="yml2_parent_offer_val" type="hidden" value="{$offer_type_parent_category}"/>
                <input id="yml2_offer_type_val" type="hidden" value="{$category_data.yml2_offer_type}"/>
                <div class="controls">
                    <select name="category_data[yml2_offer_type]" id="yml2_offer_type">
                            {if !empty($offer_type_parent_category)}
                                <option value="" {if empty($category_data.yml2_offer_type)}selected="selected"{/if}>{__('yml_export.parent_category', ['[default]' => __($offer_type_parent_name)])}</option>
                            {/if}

                            {foreach from=$yml2_offer_types item="offer_name" key="offer_type"}
                                <option value="{$offer_type}" {if $category_data.yml2_offer_type == $offer_type}selected="selected"{/if}>
                                    {__($offer_name)}
                                </option>
                            {/foreach}
                    </select>
                </div>
            </div>

            <div id="yml2_model_select_div" class="control-group yml_export">
                <label for="yml2_model_select" class="control-label ">{__("yml2_offer_feature_common_model")}:</label>
                {if !empty($yml2_parent_model_select)}
                    <input id="yml2_parent_model_category" type="hidden" value="{$yml2_parent_model_select[0]}.{$yml2_parent_model_select[1]}"/>
                {else}
                    <input id="yml2_parent_model_category" type="hidden" value=""/>
                {/if}
                <input id="yml2_model_category" type="hidden" value="{$yml2_model_category}"/>

                <div class="controls">
                    <select id="yml2_mode_select" name="category_data[yml2_model_select]">
                        {if !empty($yml2_parent_model_select)}

                            {if $yml2_parent_model_select[0] == 'product'}
                                <option value="" {if empty($category_data.yml2_model_select)}selected="selected"{/if}>{__('yml_export.parent_category', ['[default]' => __("yml2_product_field_`$yml2_parent_model_select[1]`")])}</option>
                            {else}
                                <option value="" {if empty($category_data.yml2_model_select)}selected="selected"{/if}>{__('yml_export.parent_category', ['[default]' => $features[$yml2_parent_model_select[1]]['description']])}</option>
                            {/if}
                        {elseif !empty($category_data.parent_id)}
                            <option value="" {if empty($category_data.yml2_model_select)}selected="selected"{/if}>{__('yml_export.use_parent_category_value')}</option>
                        {/if}
                        {if isset($yml2_model_select.product_fields)}
                            {foreach from=$yml2_model_select.product_fields item="field"}
                                <option value="product.{$field}" {if $yml2_model_select.type == 'product' && $yml2_model_select.value == $field} selected="selected"{/if}>{__("yml2_product_field_$field")}</option>
                            {/foreach}
                            <option value="" disabled>---</option>
                        {/if}
                        {foreach from=$features item="feature"}
                            {if isset($yml2_model_select.feature_types) && !in_array($feature.feature_type, $yml2_model_select.feature_types)}
                            {else}
                                <option value="feature.{$feature.feature_id}"{if $yml2_model_select.type == 'feature' && $yml2_model_select.value == $feature.feature_id } selected="selected"{/if}>{$feature['description']}</option>
                            {/if}
                        {/foreach}
                    </select>
                </div>
            </div>

            <div id="yml2_model" class="control-group">
                <label for="yml2_model" class="control-label">{__("yml2_model")}:</label>
                <div class="controls">
                    <input type="text" name="category_data[yml2_model]" size="55" value="{$category_data.yml2_model}" class="input-text-large" {if (!empty($yml2_model_category))}placeholder="{$yml2_model_category}"{/if}/>
                    <p class="muted description">{__("tt_addons_yml_export_hooks_products_detailed_content_post_yml2_model")}</p>
                </div>
            </div>

            <div id="yml2_type_prefix_select_div" class="control-group yml_export">
                <label for="yml2_type_prefix_select" class="control-label ">{__("yml2_offer_feature_common_typeprefix")}:</label>
                {if !empty($yml2_parent_type_prefix_select)}
                    <input id="yml2_parent_type_prefix_select" type="hidden" value="{$yml2_parent_type_prefix_select[0]}.{$yml2_parent_type_prefix_select[1]}"/>
                {else}
                    <input id="yml2_parent_type_prefix_select" type="hidden" value=""/>
                {/if}

                <div class="controls">
                    <select id="yml2_type_prefix_select" name="category_data[yml2_type_prefix_select]">
                        {if !empty($yml2_parent_type_prefix_select)}

                            {if $yml2_parent_type_prefix_select[0] == 'product'}
                                <option value="" {if empty($category_data.yml2_type_prefix_select)}selected="selected"{/if}>{__('yml_export.parent_category', ['[default]' => __("yml2_product_field_`$yml2_parent_type_prefix_select[1]`")])}</option>
                            {else}
                                <option value="" {if empty($category_data.yml2_type_prefix_select)}selected="selected"{/if}>{__('yml_export.parent_category', ['[default]' => $features[$yml2_parent_type_prefix_select[1]]['description']])}</option>
                            {/if}
                        {elseif !empty($category_data.parent_id)}
                            <option value="" {if empty($category_data.yml2_type_prefix_select)}selected="selected"{/if}>{__('yml_export.use_parent_category_value')}</option>
                        {/if}
                        {if isset($yml2_type_prefix_select.product_fields)}
                            {foreach from=$yml2_type_prefix_select.product_fields item="field"}
                                <option value="product.{$field}" {if $yml2_type_prefix_select.type == 'product' && $yml2_type_prefix_select.value == $field} selected="selected"{/if}>{__("yml2_product_field_$field")}</option>
                            {/foreach}
                            <option value="" disabled>---</option>
                        {/if}
                        {foreach from=$features item="feature"}
                            {if isset($yml2_type_prefix_select.feature_types) && !in_array($feature.feature_type, $yml2_type_prefix_select.feature_types)}
                            {else}
                                <option value="feature.{$feature.feature_id}"{if $yml2_type_prefix_select.type == 'feature' && $yml2_type_prefix_select.value == $feature.feature_id } selected="selected"{/if}>{$feature['description']}</option>
                            {/if}
                        {/foreach}
                    </select>
                </div>
            </div>

            <div id="yml2_type_prefix"  class="control-group">
                <label for="yml2_type_prefix" class="control-label">{__("yml2_type_prefix")}:</label>
                <div class="controls">
                    <input type="text" name="category_data[yml2_type_prefix]" size="55" value="{$category_data.yml2_type_prefix}" class="input-text-large" {if (!empty($yml2_type_prefix_category))}placeholder="{$yml2_type_prefix_category}"{/if} />
                    <p class="muted description">{__("tt_addons_yml_export_hooks_products_detailed_content_post_yml2_type_prefix")}</p>
                </div>
            </div>

            {include file="addons/yml_export/common/yml_categories_selector.tpl" name="category_data[yml2_market_category]" value=$category_data.yml2_market_category}
        </div>
    </div>
{/if}