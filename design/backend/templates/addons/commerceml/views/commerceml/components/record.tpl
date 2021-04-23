<tr {if !$record.local_id}class="info"{/if}>
    <td {if $parent_record}class="right"{/if} data-th="{__("commerceml.map.entity_name")}">{$record.entity_name}</td>
    <td data-th="{__("commerceml.map.entity_id")}"><code>{$record.entity_id}</code></td>
    <td data-th="{__("commerceml.map.local_id")}">
        <input type="hidden" name="records[{$record.entity_type}][{$record.entity_id}][entity_id]" value="{$record.entity_id}">
        <input type="hidden" name="records[{$record.entity_type}][{$record.entity_id}][local_id]" value="">

        {hook name="commerceml:map_record"}
        {if $record.entity_type === "\Tygh\Addons\CommerceML\Dto\CategoryDto::REPRESENT_ENTITY_TYPE"|constant}
            {$predefined_variants = []}

            {foreach $settings_schema["mapping.category.default_variant"].variants as $variant}
                {$predefined_variants[$variant] = $settings_schema["mapping.category.default_variant"].variants_labels[$variant]}
            {/foreach}

            {if $record.local_id}
                {$value = $record.local_id}
            {else}
                {$value = $import_settings["mapping.category.default_variant"]}
            {/if}

            {include file="views/categories/components/picker/picker.tpl"
                input_name="records[{$record.entity_type}][{$record.entity_id}][local_id]"
                multiple=false
                show_empty_variant=false
                show_advanced=true
                allow_clear=false
                item_ids=[$value]
                dropdown_css_class="object-picker__dropdown--categories"
                predefined_variants=$predefined_variants
            }
        {elseif $record.entity_type === "\Tygh\Addons\CommerceML\Dto\ProductFeatureDto::REPRESENT_ENTITY_TYPE"|constant}
            {$predefined_variants = []}
            {$search_data = ["get_only_selectable" => true]}

            {foreach $settings_schema["mapping.feature.default_variant"].variants as $variant}
                {$predefined_variants[$variant] = $settings_schema["mapping.feature.default_variant"].variants_labels[$variant]}
            {/foreach}

            {if $record.local_id}
                {$value = $record.local_id}
            {else}
                {$value = $import_settings["mapping.feature.default_variant"]}
            {/if}

            <div class="cm-commerceml-map-product-feature" data-external-id="{$record.entity_id}">
                {include file="views/product_features/components/picker/picker.tpl"
                    input_name="records[{$record.entity_type}][{$record.entity_id}][local_id]"
                    item_ids=[$record.local_id]
                    allow_clear=false
                    item_ids=[$value]
                    search_data=$search_data
                    predefined_variants=$predefined_variants
                }
            </div>
        {elseif $record.entity_type === "\Tygh\Addons\CommerceML\Dto\ProductFeatureVariantDto::REPRESENT_ENTITY_TYPE"|constant}
            {$value = $record.local_id}

            {foreach $settings_schema["mapping.feature_variant.default_variant"].variants as $variant}
                {$predefined_variants[$variant] = $settings_schema["mapping.feature_variant.default_variant"].variants_labels[$variant]}
            {/foreach}

            {if $record.local_id}
                {$value = $record.local_id}
            {elseif $parent_record.local_id}
                {$value = $import_settings["mapping.feature_variant.default_variant"]}
            {/if}

            <div class="cm-commerceml-map-product-feature-variant" data-feature-external-id="{$parent_record.entity_id}">
                {include file="views/product_features/components/variants_picker/picker.tpl"
                    input_name="records[{$record.entity_type}][{$record.entity_id}][local_id]"
                    empty_variant_text=$empty_variant
                    item_ids=[$value]
                    feature_id=$parent_record.local_id
                    allow_clear=false
                    predefined_variants=$predefined_variants
                }
            </div>
        {else}
            <select name="records[{$record.entity_type}][{$record.entity_id}][local_id]">
                <option value=""> --- </option>
                {foreach $items as $local_id => $local_name}
                    <option value="{$local_id}" {if $local_id == $record.local_id}selected{/if}>{$local_name}</option>
                {/foreach}
            </select>
        {/if}
        {/hook}
    </td>
</tr>