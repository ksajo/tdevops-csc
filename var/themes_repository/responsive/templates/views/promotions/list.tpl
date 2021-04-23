<div class="grid-list ty-grid-promotions">
    {foreach $promotions as $promotion_id => $promotion}
        <div class="ty-column3">
            <div class="ty-grid-list__item ty-grid-promotions__item">
                {hook name="promotions:list_item"}
                    {if $promotion.image}
                        {include file="common/image.tpl"
                            images=$promotion.image
                            image_id="promotion_image"
                            class="ty-grid-promotions__image"
                        }
                    {/if}
                    <div class="ty-grid-promotions__content">
                        {if $promotion.to_date}
                            <div class="ty-grid-list__available">
                                {__("avail_till")}: {$promotion.to_date|date_format:$settings.Appearance.date_format}
                            </div>
                        {/if}
                        {if "MULTIVENDOR"|fn_allowed_for && ($company_name || $promotion.company_id)}
                            <div class="ty-grid-promotions__company">
                                <a href="{"companies.products?company_id=`$company_id`"|fn_url}" class="ty-grid-promotions__company-link">
                                    {if $company_name}{$company_name}{else}{$promotion.company_id|fn_get_company_name}{/if}
                                </a>
                            </div>
                        {/if}
                        <h2 class="ty-grid-promotions__header">{$promotion.name}</h2>
                        {if $promotion.detailed_description || $promotion.short_description}
                            <div class="ty-wysiwyg-content ty-grid-promotions__description">
                                {$promotion.detailed_description|default:$promotion.short_description nofilter}
                            </div>
                        {/if}
                    </div>
                {/hook}
            </div>
        </div>
    {foreachelse}
        <p>{__("text_no_active_promotions")}</p>
    {/foreach}
</div>

{capture name="mainbox_title"}{__("active_promotions")}{/capture}