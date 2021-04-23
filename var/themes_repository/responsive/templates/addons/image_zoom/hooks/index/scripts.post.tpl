{script src="js/addons/image_zoom/lib/easyzoom.min.js"}
{script src="js/addons/image_zoom/index.js"}

<script type="application/javascript">
    (function (_, $) {
        $.ceEvent('on', 'ce.commoninit', function (context) {
            if (!Modernizr.touchevents) {
                var positionId = {$addons.image_zoom.cz_zoom_position};
                if ('{$language_direction}' === 'rtl') {
                    positionId = $.ceImageZoom('translateFlyoutPositionToRtl', positionId);
                }

                $('.cm-previewer', context).each(function (i, elm) {
                    $.ceImageZoom('init', $(elm), positionId);
                });
            }
        });
    })(Tygh, Tygh.$);
</script>
