{$yandex_metrika_settings = [
    "id" => $addons.rus_yandex_metrika.counter_number|default:'',
    "collectedGoals" => array_filter($addons.rus_yandex_metrika.collect_stats_for_goals|default:[])
]}
{if $addons.rus_yandex_metrika.clickmap === "YesNo::YES"|enum}
    {$yandex_metrika_settings["clickmap"] = true}
{/if}
{if $addons.rus_yandex_metrika.external_links === "YesNo::YES"|enum}
    {$yandex_metrika_settings["trackLinks"] = true}
{/if}
{if $addons.rus_yandex_metrika.denial === "YesNo::YES"|enum}
    {$yandex_metrika_settings["accurateTrackBounce"] = true}
{/if}
{if $addons.rus_yandex_metrika.track_hash === "YesNo::YES"|enum}
    {$yandex_metrika_settings["trackHash"] = true}
{/if}
{if $addons.rus_yandex_metrika.visor === "YesNo::YES"|enum}
    {$yandex_metrika_settings["webvisor"] = true}
{/if}
{if $addons.rus_yandex_metrika.ecommerce === "YesNo::YES"|enum}
    {$yandex_metrika_settings["ecommerce"] = "dataLayerYM"}
{/if}
{$yandex_metrika_object = [
    "goalsSchema" => $yandex_metrika_goals_scheme|default:[],
    "settings" => $yandex_metrika_settings,
    "currentController" => $runtime.controller,
    "currentMode" => $runtime.mode
]}
<script type="text/javascript">
    (function (_, $, window) {
        window.dataLayerYM = window.dataLayerYM || [];
        $.ceEvent('one', 'ce.commoninit', function() {
            _.yandexMetrika = {$yandex_metrika_object|json_encode nofilter};
            $.ceEvent('trigger', 'ce:yandexMetrika:init');
        });
    })(Tygh, Tygh.$, window);
</script>


{if $addons.rus_yandex_metrika.is_obsolete_code_snippet_used|default:("YesNo::NO"|enum) === "YesNo::YES"|enum}
    {script src="js/addons/rus_yandex_metrika/providers/obsolete.js"}
{else}
    {script src="js/addons/rus_yandex_metrika/providers/default.js"}
{/if}
{script src="js/addons/rus_yandex_metrika/index.js"}

{if $addons.rus_yandex_metrika.ecommerce === "YesNo::YES"|enum
    && ($yandex_metrika.deleted|default:[]
        || $yandex_metrika.added|default:[]
        || $yandex_metrika.purchased|default:[]
    )
}
    {include file="addons/rus_yandex_metrika/views/components/datalayer.tpl"}
{/if}