{capture name="mainbox"}

    {capture name="tabsbox"}

    <form action="{""|fn_url}" method="post" name="print_form" class="form-horizontal form-edit ">
        <input type="hidden" class="cm-no-hide-input" name="fake" value="1" />
        <input type="hidden" class="cm-no-hide-input" name="order_id" value="{$smarty.request.order_id}" />

        <div id="content_recipient">
            {include file="addons/rus_russianpost/views/rus_post_blank/tabs/recipient.tpl"}
        </div>
        <div id="content_sender">
            {include file="addons/rus_russianpost/views/rus_post_blank/tabs/sender.tpl"}
        </div>

        <div id="content_settings">
            {include file="addons/rus_russianpost/views/rus_post_blank/tabs/settings.tpl"}
        </div>

        {capture name="buttons"}
            {capture name="tools_list"}
                <li>{btn class="cm-new-window" type="list" text=__("addons.rus_russianpost.blank_7a") dispatch="dispatch[rus_post_blank.print.blank_7a]" form="print_form"}</li>
                <li>{btn class="cm-new-window" type="list" text=__("addons.rus_russianpost.blank_7p") dispatch="dispatch[rus_post_blank.print.blank_7p]" form="print_form"}</li>
                <li>{btn class="cm-new-window" type="list" text=__("addons.rus_russianpost.blank_112ep") dispatch="dispatch[rus_post_blank.print.blank_112ep]" form="print_form"}</li>
                <li>{btn class="cm-new-window" type="list" text=__("addons.rus_russianpost.blank_116") dispatch="dispatch[rus_post_blank.print.blank_116]" form="print_form"}</li>
                <li>{btn class="cm-new-window" type="list" text=__("addons.rus_russianpost.blank_107") dispatch="dispatch[rus_post_blank.print.blank_107]" form="print_form"}</li>
            {/capture}
            {dropdown content=$smarty.capture.tools_list}
        {/capture}
    </form>

    {/capture}

    {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox track=true}

{/capture}

{*{assign var="title" value="{__("rus_post_blank.li.print")}: `$pre_data`"}*}
{assign var="title" value="{__("rus_post_blank.li.print")}:"}


{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons}
