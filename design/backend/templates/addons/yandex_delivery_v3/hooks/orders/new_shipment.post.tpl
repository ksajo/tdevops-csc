{if $can_create_yandex_draft}
    {if $can_auto_confirm}
        <div class="clearfix">
            {include file="buttons/button.tpl"
                but_text=__("yandex_delivery_v3.yandex_delivery_form")
                but_role="add"
                but_target_id="content_add_new_yandex_order_0"
                but_meta="btn cm-dialog-opener"
            }
        </div>
    {else}
        <div class="clearfix">
            {include file="buttons/button.tpl"
                but_text=__("yandex_delivery_v3.create_draft")
                but_name="dispatch[shipments.create_yandex_delivery_draft]"
                but_meta="btn"
            }
        </div>
    {/if}
{/if}