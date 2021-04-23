{$c_url = $config.current_url|fn_query_remove:"sort_by":"sort_order"}
{if $search.sort_order == "asc"}
    {$sort_sign = "<i class=\"ty-icon-down-dir\"></i>"}
{else}
    {$sort_sign = "<i class=\"ty-icon-up-dir\"></i>"}
{/if}
{if !$config.tweaks.disable_dhtml}
    {$ajax_class = "cm-ajax"}
{/if}

{$rev = $smarty.request.content_id|default:"pagination_contents"}

{if "ULTIMATE"|fn_allowed_for}
    {if $addons.vendor_communication.show_on_messages == "Y"}
        {include file="addons/vendor_communication/views/vendor_communication/components/new_thread_button.tpl" object_id=$company_id show_form=true}

        {include
            file="addons/vendor_communication/views/vendor_communication/components/new_thread_form.tpl"
            object_type=$smarty.const.VC_OBJECT_TYPE_COMPANY
            object_id=$company_id
            company_id=$company_id
            vendor_name=$company_id|fn_get_company_name
        }
    {/if}
{/if}

{include file="common/pagination.tpl"}

<table class="ty-table ty-vendor-communication-search" id="threads_table">
    <thead>
    <tr>
        <th width="" class="ty-vendor-communication-search__label hidden-phone">&nbsp;</th>
        <th width="" class="nowrap"><a class="{$ajax_class}" href="{"`$c_url`&sort_by=thread&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("id")}{if $search.sort_by == "thread"}{$sort_sign nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
        {if "MULTIVENDOR"|fn_allowed_for}
            <th width=""><a class="cm-ajax" href="{"`$c_url`&sort_by=company&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("vendor")}{if $search.sort_by == "company"}{$sort_sign nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
        {/if}
        <th width="">{__("message")}</th>
        <th width=""><a class="cm-ajax" href="{"`$c_url`&sort_by=last_updated&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("date")}{if $search.sort_by == "last_updated"}{$sort_sign nofilter}{else}{$c_dummy nofilter}{/if}</a></th>

        {hook name="vendor_communication:manage_header"}{/hook}
    </tr>
    </thead>
    {foreach from=$threads item="thread"}
        {$has_new_message = $auth.user_id != $thread.last_message_user_id && $thread.user_status == $smarty.const.VC_THREAD_STATUS_HAS_NEW_MESSAGE}

        <tr>
            <td class="ty-vendor-communication-search__item ty-vendor-communication-search__label hidden-phone">
                {if $has_new_message}
                    <span class="ty-new__label"></span>
                {/if}
            </td>
            <td class="ty-vendor-communication-search__item ty-vendor-communication-search__thread-id">
                <a class="cm-vendor-communication-thread-dialog-opener" href="{"vendor_communication.view?thread_id=`$thread.thread_id`"|fn_url}" data-ca-thread-id="{$thread.thread_id}">
                    {if $has_new_message}
                        <span class="ty-new__label hidden-desktop hidden-tablet"></span>
                    {/if}
                    <strong>#{$thread.thread_id}</strong>
                </a>
            </td>
            {if "MULTIVENDOR"|fn_allowed_for}
                <td class="ty-vendor-communication-search__item ty-vendor-communication-search__company">
                    <a href="{"companies.products?company_id=`$thread.company_id`"|fn_url}" {if $thread.new_message}class="ty-new__text"{/if}> {$thread.company}</a>
                </td>
            {/if}
            <td class="ty-vendor-communication-search__item ty-vendor-communication-search__message">
                <a class="cm-vendor-communication-thread-dialog-opener clearfix {if $thread.new_message}ty-new__text{/if}" href="{"vendor_communication.view?thread_id=`$thread.thread_id`"|fn_url}" data-ca-thread-id="{$thread.thread_id}">{$thread.last_message|truncate:50}</a>
            </td>
            <td class="ty-vendor-communication-search__item ty-nowrap">
                <a class="cm-vendor-communication-thread-dialog-opener {if $thread.new_message}ty-new__text{/if}" href="{"vendor_communication.view?thread_id=`$thread.thread_id`"|fn_url}" data-ca-thread-id="{$thread.thread_id}">{$thread.last_updated|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</a>
            </td>

            {hook name="vendor_communication:manage_data"}{/hook}
        </tr>

        <div id="view_thread_{$thread.thread_id}" class="hidden ty-vendor-communication-view-thread" title="{__("vendor_communication.contact_with", ["[thread_id]" => $thread.thread_id, "[thread_company]" => $thread.company])}"></div>
        {foreachelse}
        <tr class="ty-table__no-items">
            <td colspan="7"><p class="ty-no-items">{__("vendor_communication.no_threads_found")}</p></td>
        </tr>
    {/foreach}
    <!--threads_table--></table>


{include file="common/pagination.tpl"}

{if $active_thread}
    <div class="cm-vendor-communication-thread-dialog-auto-open" data-ca-thread-id="{$active_thread}"></div>
    <div id="view_thread_auto_open_{$active_thread}" class="hidden ty-vendor-communication-view-thread" title="{__("vendor_communication.thread", ["[thread_id]" => $active_thread])}"></div>
{/if}

{capture name="mainbox_title"}{__("vendor_communication.messages")}{/capture}

{script src="js/addons/vendor_communication/vendor_communication.js"}


