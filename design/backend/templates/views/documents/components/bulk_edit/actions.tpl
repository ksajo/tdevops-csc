{hook name="documents:bulk_edit_actions"}
    <li>
        {btn type="list"
             text=__("export_selected")
             dispatch="dispatch[documents.export]"
             form="manage_documents_form"
        }
    </li>
{/hook}