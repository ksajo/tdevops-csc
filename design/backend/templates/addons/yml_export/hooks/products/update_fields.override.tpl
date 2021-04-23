{if $field == "yml2_exclude_price_ids"}
    <input id="field_{$field}__h" type="hidden" name="override_products_data[yml2_exclude_price_ids]" value="" disabled="disabled" />
    {foreach from=$yml2_exclude_prices item="price"}
        <div class="select-field nowrap no-padding">
            <label class="checkbox" for="field_{$field}__{$price.param_id}"><input type="checkbox" name="override_products_data[yml2_exclude_price_ids][{$price.param_id}]" id="field_{$field}__{$price.param_id}"  value="{$price.param_id}" disabled="disabled" />
                {$price.param_data.name_price_list}
            </label>
        </div>
    {/foreach}
{/if}