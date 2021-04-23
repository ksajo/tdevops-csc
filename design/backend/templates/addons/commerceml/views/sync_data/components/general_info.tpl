{$marketplace = "store"}

{if "MULTIVENDOR"|fn_allowed_for}
    {$marketplace = "marketplace"}
{/if}

{if $settings.Security.secure_storefront === "YesNo::YES"|enum}
    {$storefront_url = fn_url("", "SiteArea::STOREFRONT"|enum, "https")|replace:$config.customer_index:""|rtrim:"/"}
{else}
    {$storefront_url = fn_url("", "SiteArea::STOREFRONT"|enum, "http")|replace:$config.customer_index:""|rtrim:"/"}
{/if}

<div>
    <div class="well">
        {__("commerceml.general_information.{$marketplace}", ["[http_location]" => "{$storefront_url}/commerceml"])}
    </div>
</div>

{include file="common/subheader.tpl" title=__("commerceml.step_1_title")}

<div id="step_1_block">
    <div class="float-right nowrap right">
        <input type="checkbox" disabled {if $steps_results.step_1}checked="checked"{/if}>
    </div>
    <div>
        {__("commerceml.step_1_instruction.{$marketplace}")}
        <p>{__("commerceml.step_1_links", ["[docs_url]" => $config.resources.docs_url])}</p>
    </div>
</div>

{include file="common/subheader.tpl" title=__("commerceml.step_2_title")}

<div id="step_2_block">
    {__("commerceml.step_2_instruction.{$marketplace}")}
    <div class="float-right nowrap right">
        <input type="checkbox" disabled {if $steps_results.step_2}checked="checked"{/if}>
    </div>
    <div id="step_2_block_matches">
        <p>{__("commerceml.step_2_specify_matches")}</p>
        <ul>
            {foreach $mappable_schema as $type => $schema}
                {if $schema.parent}
                    {$section = $schema.parent}
                {else}
                    {$section = $type}
                {/if}
                <li>
                    {strip}
                    <a href="{"sync_data.update?sync_provider_id={$sync_provider_id}&selected_section={$section}"|fn_url}">
                        {__("commerceml.map.entity_type.{$type}")}
                    </a>
                    {if $mapping_count_summary.$type.cnt > 0}
                        <span> — </span>
                        {if $mapping_count_summary.$type.unmatched_cnt > 0}
                            {if $schema.is_creatable}
                               <span class="text-warning">{__("commerceml.n_items_will_be_created_automatically", [$mapping_count_summary.$type.unmatched_cnt])}</span>
                            {elseif $mapping_count_summary.$type.matched_cnt > 0}
                                <span class="text-warning">{__("commerceml.n_items_will_be_skipped", [$mapping_count_summary.$type.unmatched_cnt])}</span>
                            {else}
                                <span class="text-error">{__("commerceml.no_match")}</span>
                            {/if}
                        {else}
                            <span class="text-success">{__("commerceml.done")}</span>
                        {/if}
                    {/if}
                    {/strip}
                </li>
            {/foreach}
        </ul>
    </div>
</div>

{include file="common/subheader.tpl" title=__("commerceml.step_3_title")}

<div id="step_3_block">
    <div class="float-right nowrap right">
        <input type="checkbox" disabled {if $steps_results.step_3}checked="checked"{/if}>
    </div>
    <div>
        {__("commerceml.step_3_instruction.{$marketplace}")}
        <p>{__("commerceml.last_successful_sync")}: <span class="{if !empty($steps_results.step_3)}text-success{/if}">{if !empty($steps_results.step_3)}{$steps_results.step_3|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}{else}{__("never")}{/if}</span> | <a href="{"commerceml.get_log?company_id=`$runtime.company_id`"|fn_url}">{__("commerceml.view_log")}</a></p>
    </div>
</div>
