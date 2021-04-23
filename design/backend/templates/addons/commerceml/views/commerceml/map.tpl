{$marketplace = "store"}

{if "MULTIVENDOR"|fn_allowed_for}
    {$marketplace = "marketplace"}
{/if}

<div id="content_{$type}">
    {script src="js/addons/commerceml/map.js"}
    {include file="common/pagination.tpl" div_id="pagination_content_`$type`" disable_history=true}

    <div class="table-responsive-wrapper">
        {if $records}
            <table width="100%" class="table table-middle table--relative table-responsive cm-commerceml-map-table">
                <thead>
                <tr>
                    <th width="30%">{__("commerceml.map.entity_name")}</th>
                    <th width="30%">{__("commerceml.map.entity_id")}</th>
                    <th width="30%">{__("commerceml.map.local_id")}</th>
                </tr>
                </thead>
                {foreach $records as $key => $record}
                    <tbody>
                        {include file="addons/commerceml/views/commerceml/components/record.tpl"}
                    </tbody>
                    {if $record.sub_records}
                        {foreach $record.sub_records as $sub_type => $sub_records}
                            <tbody>
                                {foreach $sub_records as $sub_record}
                                    {include file="addons/commerceml/views/commerceml/components/record.tpl" record=$sub_record parent_record=$record}
                                {/foreach}
                            </tbody>
                        {/foreach}
                    {/if}
                {/foreach}
            </table>
        {else}
            <p class="no-items">{__("commerceml.map.no_available_data.$marketplace")}</p>
        {/if}
    </div>

    {include file="common/pagination.tpl" div_id="pagination_content_`$type`" disable_history=true}
<!--content_{$type}--></div>