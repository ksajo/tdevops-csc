{script src="js/addons/yml_export/yml_categories.js"}

{$obj_id = $obj_id|default:"yml_categories"}

<div class="control-group">
    <label for="product_type_prefix" class="control-label">{__("yml2_export_market_category")}:</label>
    <div class="controls" id="{$obj_id}_box">
        <input type="text" name="{$name}" size="200" value="{$value}" class="input-large cm-yml-categories" {if (!empty($yml2_market_category))}placeholder="{$yml2_market_category}"{/if}/></br>
        <p class="muted description">{__("tt_yml2_export_market_category")}</p>
    </div>
</div>

