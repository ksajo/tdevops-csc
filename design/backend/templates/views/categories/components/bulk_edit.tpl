<div class="bulk-edit clearfix hidden" data-ca-bulkedit-expanded-object="true" data-ca-bulkedit-component="expandedObject">
    <ul class="btn-group bulk-edit__wrapper">
        {hook name="categories:bulk_edit_items"}
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
                    check_statuses=''|fn_get_default_status_filters:true
                }
            </li>

            <li class="btn bulk-edit__btn bulk-edit__btn--status dropleft-mod">
                <span class="bulk-edit__btn-content dropdown-toggle" data-toggle="dropdown">{__("status")} <span class="caret mobile-hide"></span></span>

                <ul class="dropdown-menu">
                    {include file="views/categories/components/bulk_edit/status.tpl"}
                </ul>
            </li>

            <li class="btn bulk-edit__btn bulk-edit__btn--edit-categories mobile-hide">
                <span class="bulk-edit__btn-content">
                    {btn type="dialog" 
                        class="cm-process-items" 
                        text=__("edit_selected") 
                        target_id="content_select_fields_to_edit" 
                        form="category_tree_form"
                    }
                </span>
            </li>

            <li class="btn bulk-edit__btn bulk-edit__btn--actions dropleft-mod">
                <span class="bulk-edit__btn-content dropdown-toggle" data-toggle="dropdown">{__("actions")} <span class="caret mobile-hide"></span></span>

                <ul class="dropdown-menu">
                    {include file="views/categories/components/bulk_edit/actions.tpl"}
                </ul>
            </li>
        {/hook}
    </ul>

</div>
