<div class="control-group">
    <label for="yml2_variants_unit" class="control-label">{__("yml_export.yml2_variants_unit")}</label>
    <div class="controls">
        <div class="checkbox-list">
            <input id="yml2_variants_unit" type="text" name="feature_data[yml2_variants_unit]" value="{$feature.yml2_variants_unit}" />
        </div>
    </div>
</div>

<div class="control-group">
    <label class="control-label">{__("yml_export.yml2_exclude_from_price")}</label>
    <div class="controls">
        <div class="checkbox-list shift-input">
            <input type="hidden" name="feature_data[yml2_exclude_prices]" value="" />
            {foreach from=$yml2_price_lists item="price"}
                <label>
                    <input type="checkbox"
                           name="feature_data[yml2_exclude_prices][{$price.param_id}]"
                           value="Y"
                           {if $feature['yml2_exclude_prices'] && in_array($price.param_id, $feature['yml2_exclude_prices'])}
                           checked="checked"
                           {/if}/>
                    {$price.param_data.name_price_list}
                </label>
            {/foreach}
        </div>
    </div>
</div>
