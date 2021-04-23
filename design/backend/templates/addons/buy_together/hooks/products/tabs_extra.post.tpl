{if "ULTIMATE"|fn_allowed_for}
    {if $runtime.company_id && !$no_hide_input_if_shared_product}
        {assign var="hide_controls" value=false}
    {else}
        {assign var="hide_controls" value=true}
    {/if}
{else}
    {assign var="hide_controls" value=false}
{/if}


{if "MULTIVENDOR"|fn_allowed_for}
    {$hide_controls=($product_data.company_id == 0 || !$runtime.company_id)}
{/if}

<div id="content_buy_together" class="cm-hide-save-button {if $selected_section !== "buy_together"}hidden{/if} {if $hide_controls}cm-hide-inputs{/if}">
    {if !$hide_controls}
        <div class="clearfix">
            <div class="pull-right">
                    {capture name="add_new_picker"}
                        <div id="add_new_chain">
                            {include file="addons/buy_together/views/buy_together/update.tpl" product_id=$product_data.product_id item=[]}
                        </div>
                    {/capture}
                    {include file="common/popupbox.tpl" id="add_new_chain" text=__("new_combination") content=$smarty.capture.add_new_picker link_text=__("add_combination") act="general"}
            </div>
        </div><br>
    {/if}
    
    <div class="items-container" id="update_chains_list">
        <div class="table-responsive-wrapper">
            <table class="table table-middle table--relative table-objects table-responsive">
            {if $chains}
                {foreach from=$chains item=chain}
                    {if $hide_controls}
                        {$link_text=__("view")}
                    {else}
                        {$link_text=__("edit")}
                    {/if}

                    {include file="common/object_group.tpl" id=$chain.chain_id id_prefix="_bt_" text=$chain.name status=$chain.status hidden=false href="buy_together.update?chain_id=`$chain.chain_id`&product_id=`$chain.product_id`" link_text=$link_text object_id_name="chain_id" table="buy_together" href_delete="buy_together.delete?chain_id=`$chain.chain_id`" delete_target_id="update_chains_list" header_text=$chain.name skip_delete=$hide_controls no_table=true hide_for_vendor=$hide_controls}
                {/foreach}
            {else}
                <tr><td data-th="&nbsp;">{__("no_data")}</td></tr>
            {/if}
            </table>
        </div>
    <!--update_chains_list--></div>
<!--content_buy_together--></div>