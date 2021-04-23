{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.module == 'pickpoint'}
    {assign var="shipping_id" value=$shipping.shipping_id}
    {assign var="pickpoint_postamat" value=$pickpoint_office.$group_key.$shipping_id}
    {if !$pickpoint_office.$group_key.$shipping_id && $p_office.$shipping_id}
        {assign var="pickpoint_postamat" value=$p_office.$shipping_id}
    {/if}
    {if $addons.rus_pickpoint.secure_protocol == 'Y'}
        {assign var="url" value='https://pickpoint.ru/select/postamat.js'}
    {else}
        {assign var="url" value='http://pickpoint.ru/select/postamat.js'}
    {/if}
    {script src="js/addons/rus_pickpoint/func.js"}
    <script type="text/javascript" src="{$url}"></script>

    <input type="hidden" name="pickpoint_office[{$group_key}][{$shipping.shipping_id}][pickpoint_id]" id="pickpoint_id_{$group_key}" value="{$pickpoint_postamat.pickpoint_id}" />
    <input type="hidden" name="pickpoint_office[{$group_key}][{$shipping.shipping_id}][address_pickpoint]" id="address_pickpoint_{$group_key}" value="{$pickpoint_postamat.address_pickpoint}" />
    <div>{$pickpoint_postamat.address_pickpoint}</div>
    <a class="ty-btn__secondary ty-btn" id="pickpoint_terminal" onclick="fn_select_pickpoint_terminal({$group_key}); PickPoint.open(addressPostamat, { fromcity:'{$fromcity}',city:'{$pickpoint_city}' });return false">{__("addons.rus_pickpoint.select_terminal")}<input type="radio" name="pickpoint_select_{$group_key}" value="{$group_key}" {if $pickpoint_select == $group_key}checked="checked"{/if} id="pickpoint_select_{$group_key}" class="ty-one-pickpoint-terminal ty-valign hidden"></a>
{/if}
