{capture name="mainbox"}
    {$allow_save = fn_check_permissions("yml", "update", "admin", "POST")}
    <form action="{""|fn_url}" method="post" name="yml_export_offers" class="form-horizontal form-edit {if !$allow_save} cm-hide-inputs{/if}">

        {foreach from=$yml_offer_types item="offer_name" key="offer"}
            {if !empty($yml_offer_features[$offer])}

                {include file="common/subheader.tpl" title="{__($offer_name)}" target="#collapsable_addon_option_yml_export_{$offer}"}

                <div id="collapsable_addon_option_yml_export_{$offer}" class="in collapse" style="height: auto;">

                    {foreach from=$yml_offer_features[$offer] item="data" key="offer_feature_key"}
                        <div id="container_addon_option_yml_export_{$offer}_{$offer_feature_key}" class="control-group setting-wide yml_export">
                            <label for="addon_option_yml_export_{$offer_feature_key}" class="control-label ">{__("yml2_offer_feature_{$offer}_{$offer_feature_key}")}:
                            </label>

                            <div class="controls">
                                <select id="addon_option_yml_export_{$offer_feature_key}"
                                        name="data[ym_features][{$offer}][{$offer_feature_key}]"
                                        class="cm-object-selector"
                                        data-ca-page-size="50"
                                        data-ca-enable-search="true"
                                        data-ca-load-via-ajax="true"
                                        data-ca-data-url="{"yml.get_variants_list?offer=`$offer`&offer_key=`$offer_feature_key`"|fn_url nofilter}"
                                        >
                                    {if $data.type == 'product'}
                                        <option value="product.{$data.value}" selected="selected">{__("yml2_product_field_`$data.value`")}</option>
                                    {else}
                                        <option value="feature.{$data.value}" selected="selected">{$data.feature_name}</option>
                                    {/if}
                                </select>
                                <div class="right">
                                </div>
                            </div>
                        </div>
                    {/foreach}

                </div>
            {/if}
        {/foreach}

    </form>
{/capture}

{capture name="buttons"}
    {include file="buttons/save.tpl" but_name="dispatch[yml.update_offers]" but_role="submit-link" but_target_form="yml_export_offers"}
{/capture}


{include file="common/mainbox.tpl" title=__("yml_export.offers_params") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}