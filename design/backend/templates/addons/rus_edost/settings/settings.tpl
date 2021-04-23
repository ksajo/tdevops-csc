<fieldset>
    <div class="control-group">
        <label class="control-label" for="cities_update">{__('addons.rus_edost.label_cities_update')}:</label>
        <div class="controls" id="cities_update">
            {include file="buttons/button.tpl" but_role="submit" but_name="dispatch[edost.cities_update]" but_text=__("addons.rus_edost.cities_update")}
            <p class="muted description">{__("shipping.rus_edost.cities_update.tooltip")}</p>
        </div>
    </div>
</fieldset>
