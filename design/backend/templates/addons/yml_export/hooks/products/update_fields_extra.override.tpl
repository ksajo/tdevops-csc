{if $field == "yml2_exclude_price_ids"}
    <input id="field_{$field}__h" type="hidden" name="override_products_data[yml2_exclude_price_ids]" value="" disabled="disabled" />
    {foreach from=$yml2_exclude_prices item="price"}
        <div class="select-field nowrap">
            <label class="checkbox" for="products_yml_price_{$product.product_id}_{$price.param_id}">
                <input type="hidden" name="products_data[{$product.product_id}][yml2_exclude_price_ids][{$price.param_id}]"
                       id="products_yml_price_{$product.product_id}_{$price.param_id}"{if $price.param_id|in_array:$product.yml2_exclude_prices} checked="checked"{/if} value="N" />
                <input type="checkbox" name="products_data[{$product.product_id}][yml2_exclude_price_ids][{$price.param_id}]"
                       id="products_yml_price_{$product.product_id}_{$price.param_id}"{if $price.param_id|in_array:$product.yml2_exclude_prices} checked="checked"{/if} value="{$price.param_id}" />
                {$price.param_data.name_price_list}
            </label>
        </div>
    {/foreach}
{/if}