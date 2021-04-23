<div class="sidebar-row">
<form action="{""|fn_url}" method="post" name="courier_form">
<h6>{__("search")}</h6>
    <div class="sidebar-field">
        <label for="sdek_elm_order_id">{__("addons.rus_sdek.sdek_delivery_order")}:</label>
        <input type="text" name="sdek_order_id" id="sdek_elm_order_id" value="{$search.sdek_order_id}" size="15"/>
    </div>
    {capture name="simple_search"}
        {include file="common/period_selector.tpl" period=$period display="form"}
    {/capture}
    {include file="common/advanced_search.tpl" no_adv_link=true simple_search=$smarty.capture.simple_search not_saved=true dispatch="shipments.sdek_delivery"}
</form>
</div>