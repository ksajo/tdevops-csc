{foreach from=$yml2_offer_types item="offer_name" key="offer"}
    {if !empty($yml2_offer_features[$offer])}
    <h4 class="subheader hand" data-toggle="collapse" data-target="#collapsable_addon_option_yml_export_{$offer}">
        {__($offer_name)}
        <span class="exicon-collapse"></span>
    </h4>

    <div id="collapsable_addon_option_yml_export_{$offer}" class="in collapse" style="height: auto;">
        <fieldset>

            {foreach from=$yml2_offer_features[$offer] item="data" key="offer_feature_key"}
            <div id="container_addon_option_yml_export_{$offer}_{$feature_key}" class="control-group setting-wide yml_export">
                <label for="addon_option_yml_export_export_encoding" class="control-label ">{__("yml2_offer_feature_{$offer}_{$offer_feature_key}")}:
                </label>

                <div class="controls">
                    <select id="addon_option_yml_export_export_encoding" name="addon_data[ym_features][{$offer}][{$offer_feature_key}]">
                        <option value=""{if empty($addon_data['ym_features'][$offer][$offer_feature_key])} selected="selected"{/if}>---</option>

                        {if isset($data.product_fields)}
                            {foreach from=$data.product_fields item="field"}
                                <option value="product.{$field}"
                                        {if $data.type == 'product' && $data.value == $field}
                                    selected="selected"
                                        {/if}>
                                    {__("yml2_product_field_$field")}
                                </option>
                            {/foreach}

                            <option value="">---</option>
                        {/if}

                        {foreach from=$features item="feature"}
                            {if isset($data.feature_types) && !in_array($feature.feature_type, $data.feature_types)}

                            {else}
                                <option value="feature.{$feature.feature_id}"
                                        {if $data.type == 'feature' && $data.value == $feature.feature_id }
                                            selected="selected"
                                        {/if}>
                                    {$feature['description']}
                                </option>
                            {/if}
                        {/foreach}
                    </select>
                    <div class="right">
                    </div>
                </div>
            </div>
            {/foreach}

        </fieldset>
    </div>
    {/if}
{/foreach}
