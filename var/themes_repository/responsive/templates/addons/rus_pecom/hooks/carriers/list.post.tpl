{if $carrier == "pecom"}
    {$url = "https://kabinet.pecom.ru/status/?codes=`$tracking_number`" scope=parent}
    {$carrier_name = __("carrier_pecom") scope=parent}
{/if}

