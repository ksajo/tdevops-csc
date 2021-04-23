{include file="common/subheader.tpl" title=__("retailcrm.settings.instructions.title") target="#collapsable_instructions"}
<div id="collapsable_instructions" class="in collapse">
    <ol>
        <li>{__("retailcrm.settings.instructions.step_credentials")}</li>
        <li>{__("retailcrm.settings.instructions.step_tab_mapping")}</li>
        <li>{__("retailcrm.settings.instructions.step_connection")}</li>
        <li>{__("retailcrm.settings.instructions.step_mapping_settings")}</li>
        <li>{__("retailcrm.settings.instructions.step_price_list", ["[href_price_list]" => "yml.manage"|fn_url])}</li>
        <li>{__("retailcrm.settings.instructions.step_cron_command", ["[cron_command]" => $retailcrm_order_sync_console_cmd])}</li>
    </ol>
</div>