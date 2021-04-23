{if $user_data.yml_export}
    <br />
    <p><a class="cm-combination" id="sw_ym_addr">{__("yml2_address")}</a></p>
    <div class="hidden" id="ym_addr">
        {$_skip = ['country_code', 'state_code', 'address']}
        {foreach from=$user_data.yml_export.address key=key item=value}
            {if $value && !in_array($key, $_skip)}
                <p>{__("yml2_address_{$key}")}: {$value}</p>
            {/if}
        {/foreach}
    </div>

{/if}