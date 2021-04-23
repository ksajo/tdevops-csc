{capture name="mainbox"}

    {assign var="c_icon" value="<i class=\"icon-`$search.sort_order_rev`\"></i>"}
    {assign var="c_dummy" value="<i class=\"icon-dummy\"></i>"}

    {include file="common/pagination.tpl"
        save_current_page=true
        save_current_url=true
        div_id=$smarty.request.content_id
    }

    {assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
    {assign var="rev" value=$smarty.request.content_id|default:"pagination_contents"}
    {assign var="show_vendor_col" value=$auth.user_type == "A" && !$runtime.company_id}



    {capture name="tabsbox"}
        {if $threads}
            <form action="{""|fn_url}" method="post" name="threads_list_form" id="threads_list_form" class="{if $runtime.company_id}cm-hide-inputs{/if}">
                <input type="hidden" name="communication_type" value="{$smarty.request.communication_type}"/>
                <div class="table-responsive-wrapper">
                    <table width="100%" class="table table-middle table--relative table-responsive">
                        <thead>
                        <tr>
                            {if !$runtime.company_id && $auth.user_type == "A"}
                                <th class="left">
                                    {include file="common/check_items.tpl"}</th>
                            {/if}
                            <th width="1%" class="status-label">&nbsp;</th>
                            <th width="15%">
                                <a class="cm-ajax"
                                   href="{"`$c_url`&sort_by=thread&sort_order=`$search.sort_order_rev`"|fn_url}"
                                   data-ca-target-id={$rev}>
                                    {__("id")}
                                    {if $search.sort_by == "thread"}
                                        {$c_icon nofilter}{else}{$c_dummy nofilter}
                                    {/if}
                                </a>
                            </th>
                            {if $search.communication_type == "Addons\\VendorCommunication\\CommunicationTypes::VENDOR_TO_CUSTOMER"|enum}
                                <th width="15%">
                                    <a class="cm-ajax"
                                       href="{"`$c_url`&sort_by=name&sort_order=`$search.sort_order_rev`"|fn_url}"
                                       data-ca-target-id={$rev}>
                                        {__("customer")}
                                        {if $search.sort_by == "name"}
                                            {$c_icon nofilter}
                                        {else}
                                            {$c_dummy nofilter}
                                        {/if}
                                    </a>
                                </th>
                            {/if}
                            {if $search.communication_type == "Addons\\VendorCommunication\\CommunicationTypes::VENDOR_TO_ADMIN"|enum}
                                <th width="15%">{__("subject")}</th>
                            {/if}
                            {if $show_vendor_col}
                                <th width="15%">
                                    <a class="cm-ajax"
                                       href="{"`$c_url`&sort_by=company&sort_order=`$search.sort_order_rev`"|fn_url}"
                                       data-ca-target-id={$rev}>
                                        {__("vendor")}
                                        {if $search.sort_by == "company"}
                                            {$c_icon nofilter}
                                        {else}
                                            {$c_dummy nofilter}
                                        {/if}
                                    </a>
                                </th>
                            {/if}
                            <th width="45%">{__("message")}</th>
                            {hook name="vendor_communication:manage_header"}{/hook}
                            <th>&nbsp;</th>
                            <th width="9%">
                                <a class="cm-ajax"
                                   href="{"`$c_url`&sort_by=last_updated&sort_order=`$search.sort_order_rev`"|fn_url}"
                                   data-ca-target-id={$rev}>
                                    {__("date")}
                                    {if $search.sort_by == "last_updated"}
                                        {$c_icon nofilter}{else}{$c_dummy nofilter}
                                    {/if}
                                </a>
                            </th>
                        </tr>
                        </thead>
                        {foreach from=$threads item=thread}
                            {$thread_href = "vendor_communication.view?thread_id=`$thread.thread_id`&communication_type=`$search.communication_type`"|fn_url}

                            {$has_new_message = $auth.user_id != $thread.last_message_user_id && $thread.user_status == $smarty.const.VC_THREAD_STATUS_HAS_NEW_MESSAGE}
                            {if !$runtime.compnay_id && $auth.user_type == "A"}
                                <td class="left mobile-hide">
                                    <input type="checkbox" name="thread_ids[]" value="{$thread.thread_id}" class="cm-item" />
                                </td>
                            {/if}
                            <td>
                                {if $has_new_message}
                                    <span class="status-new__label"></span>
                                {/if}
                            </td>
                            <td class="{if $has_new_message}status-new__text{/if}" data-th="{__("id")}">
                                <a href="{$thread_href}">
                                    {__("vendor_communication.ticket")} <bdi>#{$thread.thread_id}</bdi>
                                </a>
                                {include file="views/companies/components/company_name.tpl" object=$thread}
                            </td>
                            {if $search.communication_type == "Addons\\VendorCommunication\\CommunicationTypes::VENDOR_TO_CUSTOMER"|enum}
                                <td class="{if $has_new_message}status-new__text{/if}" data-th="{__("customer")}">
                                    {if $auth.user_type == "A"}
                                        {if $thread.customer_email}<a href="mailto:{$thread.customer_email|escape:url}">@</a>{/if}
                                        <a href="{"profiles.update&user_id={$thread.user_id}"|fn_url}">
                                            {$thread.firstname} {$thread.lastname}
                                        </a>
                                    {else}
                                        {$thread.firstname} {$thread.lastname}
                                    {/if}
                                </td>
                            {/if}
                            {if $search.communication_type == "Addons\\VendorCommunication\\CommunicationTypes::VENDOR_TO_ADMIN"|enum}
                                <td class="{if $has_new_message}status-new__text{/if}" data-th="{__("subject")}">
                                    <a href="{$thread_href}" class="no-link">
                                        {$thread.subject}
                                    </a>
                                </td>
                            {/if}
                            {if $show_vendor_col}
                                <td data-th="{__("vendor")}">
                                    <a href="{$thread_href}" class="no-link">
                                        {$thread.company}
                                    </a>
                                </td>
                            {/if}
                            <td class="message-title {if $has_new_message}status-new__text{/if}" data-th="{__("message")}">
                                <a href="{$thread_href}" class="no-link">
                                    <strong>
                                        {if $thread.last_message_user_id == $auth.user_id}
                                            {__("vendor_communication.you")}
                                        {elseif $thread.last_message_user_type == "A"}
                                            {__("administration")}
                                        {elseif $thread.last_message_user_type == "V"}
                                            {__("vendor")}
                                        {else}
                                            {__("customer")}
                                        {/if}
                                    </strong>
                                </a>
                                <a href="{$thread_href}" class="no-link">
                                    {$thread.last_message}
                                </a>
                            </td>
                            {hook name="vendor_communication:manage_data"}{/hook}
                            <td class="right">
                                {capture name="tools_list"}
                                    {capture name="tools_delete"}
                                        <li>
                                            {btn
                                                type="list"
                                                text=__("delete")
                                                class="cm-confirm"
                                                href="vendor_communication.delete_thread?thread_id=`$thread.thread_id`&communication_type=`$search.communication_type`"
                                                method="POST"
                                            }
                                        </li>
                                    {/capture}
                                    {if $auth.user_type == "A"}
                                        {$smarty.capture.tools_delete nofilter}
                                    {/if}
                                {/capture}
                                <div class="hidden-tools">
                                    {dropdown content=$smarty.capture.tools_list}
                                </div>
                            </td>
                            <td class="nowrap {if $has_new_message}status-new__text{/if}" data-th="{__("date")}">
                                <a href="{$thread_href}"  class="no-link">
                                    {$thread.last_updated|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}
                                </a>
                            </td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
            </form>
        {else}
            <p class="no-items">{__("no_data")}</p>
        {/if}

        {include file="common/pagination.tpl" div_id=$smarty.request.content_id}
    {/capture}
    {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$communication_type track=true}

{/capture}

{capture name="adv_buttons"}
    {assign var="_title" value=__("vendor_communication.message_center")}
{/capture}

{capture name="adv_buttons"}
    {if $search.communication_type == "Addons\\VendorCommunication\\CommunicationTypes::VENDOR_TO_ADMIN"|enum}
        {include
            file="addons/vendor_communication/views/vendor_communication/components/new_thread_button.tpl"
            but_icon="icon-plus"
            but_role="text"
            but_meta="btn btn-primary cm-dialog-opener"
        }
    {/if}
{/capture}

{capture name="buttons"}
    {if $threads}
        {capture name="tools_list"}
            {if $auth.user_type == "A"}
                <li>
                    {btn type="delete_selected" dispatch="dispatch[vendor_communication.m_delete_thread]" form="threads_list_form"}
                </li>
            {/if}
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
    {/if}
{/capture}

{capture name="sidebar"}
    {hook name="vendor_communication:manage_sidebar"}
        {include
            file="addons/vendor_communication/views/vendor_communication/components/thread_search_form.tpl"
            dispatch="vendor_communication.threads"
            period=$search.period
        }
    {/hook}
{/capture}

{include
    file="common/mainbox.tpl"
    title=$_title
    content=$smarty.capture.mainbox
    sidebar=$smarty.capture.sidebar
    adv_buttons=$smarty.capture.adv_buttons
    buttons=$smarty.capture.buttons
    content_id="manage_threads"
}
