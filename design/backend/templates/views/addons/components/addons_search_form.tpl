<div class="sidebar-row">
    <h6>{__("search")}</h6>

    <form action="{""|fn_url}" name="addons_search_form" method="get" class="{$form_meta} addons-search-form">
        {$extra nofilter}
        
        <div class="sidebar-field ">
            <label for="elm_addon">{__("name")}</label>
            <input type="text" name="q" id="elm_addon" value="{$search.q}" size="30" autofocus />
            <i class="icon icon-remove hidden" id="elm_addon_clear" title="{__("remove")}"></i>
        </div>
        {if !$hide_for_vendor}
        <div class="sidebar-field">
            <label for="elm_addon_status">{__("status")}</label>

            <select id="elm_addon_status" name="type">
                <option value="any" {if empty($search.type) || $search.type == 'any'} selected="selected"{/if}>{__("any")}</option>
                <option value="not_installed" {if $search.type == 'not_installed'} selected="selected"{/if}>{__("not_installed")}</option>
                <option value="installed" {if $search.type == 'installed'} selected="selected"{/if}>{__("installed")}</option>
                <option value="active" {if $search.type == 'active'} selected="selected"{/if}>{__("active")}</option>
                <option value="disabled" {if $search.type == 'disabled'} selected="selected"{/if}>{__("disabled")}</option>
            </select>
        </div>
        <div class="sidebar-field">
            <label for="elm_addon_source">{__("developer")}</label>
            <select id="elm_addon_source" name="supplier">
                <option value="" {if empty($search.supplier)} selected="selected"{/if}>{__("any")}</option>
                {foreach $developers as $developer}
                    <option value="{$developer}" {if $search.supplier == $developer} selected="selected"{/if}>{$developer}</option>
                {/foreach}
            </select>
        </div>
        {/if}

        <div class="sidebar-field">
            <input class="btn" type="submit" name="dispatch[{$dispatch}]" value="{__("search")}">
        </div>
    </form>
</div>