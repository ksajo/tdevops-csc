{if $user_data.yandex_market}
    <br />
    <p><a class="cm-combination" id="sw_ym_addr">{__("yandex_market.address")}</a></p>
    <div class="hidden" id="ym_addr">
        {$_skip = ['country_code', 'state_code', 'address']}
        {foreach from=$user_data.yandex_market.address key=key item=value}
            {if $value && !in_array($key, $_skip)}
                <p>{__("yandex_market.address_{$key}")}: {$value}</p>
            {/if}
        {/foreach}
    </div>

{/if}