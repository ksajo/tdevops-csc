
{if $shipment.carrier == "sdek" && $status}
<p class="strong">{__("status")}</p>
<p>{$status.status}</p>
<p class="strong">{__("addons.rus_sdek.location")}</p>
<p>{$status.city}</p>
{/if}