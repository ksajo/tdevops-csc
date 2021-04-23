<div class="bulk-edit clearfix hidden"
     data-ca-bulkedit-expanded-object="true"
     data-ca-bulkedit-component="expandedObject"
>

    <ul class="btn-group bulk-edit__wrapper">
        {hook name="profiles:bulk_edit_items"}
        <li class="btn bulk-edit__btn bulk-edit__btn--check-items">
            <input class="bulk-edit__btn-content--checkbox hidden bulkedit-disabler" 
                   type="checkbox" 
                   checked 
                   data-ca-bulkedit-toggler="true"
                   data-ca-bulkedit-enable="[data-ca-bulkedit-default-object=true]" 
                   data-ca-bulkedit-disable="[data-ca-bulkedit-expanded-object=true]"
            />
            <span class="bulk-edit__btn-content dropdown-toggle" data-toggle="dropdown">
                <span data-ca-longtap-selected-counter="true">0</span> <span class="mobile-hide">{__("selected")}</span> <span class="caret mobile-hide"></span>
            </span>
            {include file="common/check_items.tpl"
                     dropdown_menu_class="cm-check-items"
                     wrap_select_actions_into_dropdown=true 
                     check_statuses=""|fn_get_default_status_filters:true
            }
        </li>

        {if !($auth.user_type === "{"UserTypes::VENDOR"|enum}" && $smarty.request.user_type === "{"UserTypes::CUSTOMER"|enum}") 
            && (fn_check_permissions("profiles", "m_activate", "admin", "POST", ["user_type" => $smarty.request.user_type])
            && fn_check_permissions("profiles", "m_disable", "admin", "POST", ["user_type" => $smarty.request.user_type]))}
            <li class="btn bulk-edit__btn bulk-edit__btn--status dropleft-mod cm-no-hide-input">
                <span class="bulk-edit__btn-content dropdown-toggle" data-toggle="dropdown">{__("status")} <span class="caret mobile-hide"></span></span>

                <ul class="dropdown-menu">
                    {include file="views/profiles/components/bulk_edit/status.tpl"}
                </ul>
            </li>
        {/if}

        {capture name="action_tools"}
            {include file="views/profiles/components/bulk_edit/actions.tpl"}
        {/capture}

        {if $smarty.capture.action_tools|trim}
            <li class="btn bulk-edit__btn bulk-edit__btn--actions dropleft-mod">
                <span class="bulk-edit__btn-content dropdown-toggle" data-toggle="dropdown">{__("actions")} <span class="caret mobile-hide"></span></span>

                <ul class="dropdown-menu">
                    {include file="views/profiles/components/bulk_edit/actions.tpl"}
                </ul>
            </li>
        {/if}
        {/hook}
    </ul>

</div>
