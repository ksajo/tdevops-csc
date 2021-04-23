{if $addons.facebook_pixel.pixel_id}
<script type="text/javascript">
    {if $addons.facebook_pixel.track_add_to_cart == "YesNo::YES"|enum}
    if (fbq) {
        fbq('track', 'Added To Cart');
    }
    {/if}
</script>
{/if}