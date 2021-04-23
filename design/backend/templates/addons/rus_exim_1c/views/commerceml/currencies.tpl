
{if ""|fn_allow_save_object:"":true}
    {assign var="act" value="edit"}
{else}
    {assign var="act" value="view"}
{/if}

{capture name="mainbox"}
    <div class="items-container cm-sortable {if !""|fn_allow_save_object:"":true} cm-hide-inputs{/if}"
         data-ca-sortable-table="currencies" data-ca-sortable-id-name="currency_id" id="currencies_list">
            {if $commerceml_currencies}
            <table class="table table-middle table--relative table-objects table-striped">
                <thead class="cm-first-sibling">
                    <tr>
                        <th width="1%">&nbsp;</th>
                        <th width="28%">{__("addons.commerceml.cart_currencies")}</th>
                        <th width="50%">{__("addons.commerceml.commerceml_currencies")}</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                {foreach from=$commerceml_currencies item="commerceml_currency"}
                    {assign var="_href_delete" value="commerceml.currency_delete?id=`$commerceml_currency.id`"}

                    {include file="common/object_group.tpl"
                        id=$commerceml_currency.currency_id
                        text=$commerceml_currency.currency_description
                        details=$commerceml_currency.commerceml_currency
                        href="commerceml.currency_update?id=`$commerceml_currency.id`"
                        href_delete=$_href_delete
                        delete_data=$commerceml_currency.id
                        delete_target_id="currencies_list"
                        table="currencies"
                        object_id_name="currency_id"
                        additional_class="cm-sortable-row cm-sortable-id-`$commerceml_currency.id`"
                        no_table=true
                        non_editable=$runtime.company_id
                        is_view_link=true
                        hidden=true
                        update_controller="currency_update"
                        tool_items=$smarty.capture.tool_items
                        extra_data=$smarty.capture.extra_data
                        nostatus=true
                        act=$act
                        header_text=$commerceml_currency.commerceml_currency
                    }
                {/foreach}
                </tbody>
            </table>
            {else}
                {__("addons.rus_exim_1c.message_currencies")}
            {/if}
        </form>
    <!--currencies_list--></div>

    {if ""|fn_allow_save_object:"":true}
        {capture name="adv_buttons"}
            {capture name="add_new_picker"}
                {include file="addons/rus_exim_1c/views/commerceml/currency_update.tpl" data_currencies=$data_currencies commerceml_currency=[]}
            {/capture}

            {include file="common/popupbox.tpl" id="add_new_currency" text=__("addons.rus_exim_1c.new_currency") content=$smarty.capture.add_new_picker title=__("addons.rus_exim_1c.add_currency") act="general" icon="icon-plus"}
        {/capture}
    {/if}
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}

{include file="common/mainbox.tpl" title=__("addons.rus_exim_1c.setting_currencies") content=$smarty.capture.mainbox sidebar=$smarty.capture.sidebar buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}
