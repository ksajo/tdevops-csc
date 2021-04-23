<div class="control-group">
    <label for="categories_{$rnd}_ids" class="control-label">{__("yml_export.param_exclude_categories")}:</label>
    <div class="controls">
        {include file="pickers/categories/picker.tpl"
            input_name="pricelist_data[exclude_categories]"
            item_ids=$price.param_data.exclude_categories
            multiple=true
        }
        <p class="muted description">{__("tt_yml_export.param_exclude_categories")}</p>
    </div>
</div>
