{hook name="import_presets:bulk_edit_actions"}
    <li>
        {btn type="delete_selected"
             dispatch="dispatch[import_presets.m_delete]"
             form="manage_import_presets_form"
             data=["data-ca-confirm-text" => "{__("advanced_import.file_will_be_deleted_are_you_sure_to_proceed")}"]
        }
    </li>
{/hook}