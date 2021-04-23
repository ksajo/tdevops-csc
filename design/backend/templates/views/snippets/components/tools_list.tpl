{capture name="tools_list"}
    {hook name="snippets:update_tools_list_snippets"}
        <li>{btn type="list" text=__("delete_selected") class="cm-ajax cm-confirm" dispatch="dispatch[snippets.delete]" form="snippets_form"}</li>
    {/hook}
{/capture}
{dropdown content=$smarty.capture.tools_list id="tools_snippets" icon=$icon text=$text}