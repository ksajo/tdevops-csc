<div class="bulk-edit-inner bulk-edit-inner--parent">
    <div class="bulk-edit-inner__header">
         <span>{__("parent")}</span>
    </div>
    <div class="bulk-edit-inner__body">
        {include file="views/pages/components/parent_page_selector.tpl"
            show_label=false
            bulkedit_changer="data-ca-bulkedit-parent-changer"
        }
    </div>

    <div class="bulk-edit-inner__footer">
        <button class="btn bulk-edit-inner__btn bulkedit-parent-cancel" 
                role="button"
                data-ca-bulkedit-mod-parent-cancel
                data-ca-bulkedit-mod-parent-reset-changer="[data-ca-bulkedit-parent-changer]"
        >{__("reset")}</button>
        <button class="btn btn-primary bulk-edit-inner__btn bulkedit-parent-update" 
                role="button"
                data-ca-bulkedit-mod-parent-update
                data-ca-bulkedit-mod-values="{if !$parent_pages}[name='page_data[parent_id]']{else}[data-ca-bulkedit-parent-changer]{/if}"
                data-ca-bulkedit-mod-target-form="[name=pages_tree_form]"
                data-ca-bulkedit-mod-target-form-active-objects="tr.selected:has(input[type=checkbox].cm-item:checked)"
                data-ca-bulkedit-mod-dispatch="pages.m_update_parent_page"
        >{__("apply")}</button>
    </div>
</div> 