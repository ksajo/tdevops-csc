<div class="span16 mockup__mockups-container">
    <div class="span4 mockup">
        <div class="mockup__container">
            <div class="mockup__status-bar drawerBgColor__background">

                <img src="{$images_dir}/addons/mobile_app/status_bar_example.png">

            </div>

            {include file="addons/mobile_app/components/atoms/navbar.tpl"}

            <div 
                class="mockup__body body categoriesBackgroundColor__background mockup__category"
                style="min-height: calc(100% - 65px); max-height: calc(100% - 65px);"
            >
                
                <h3 class="mockup__main-heading categoriesHeaderColor">Categories</h3>

                <div class="mockup__category-container">
                    <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                        <img 
                            src="{$images_dir}/addons/mobile_app/no_image.png" 
                            class="mockup__category-preview categoryEmptyImage__background"
                        />
                        <p class="mockup__category-name categoryBlockTextColor">Electronics</p>
                    </div>
                    <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                        <img 
                            src="{$images_dir}/addons/mobile_app/no_image.png" 
                            class="mockup__category-preview categoryEmptyImage__background"
                        />
                        <p class="mockup__category-name categoryBlockTextColor">Books</p>
                    </div>
                    <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                        <img 
                            src="{$images_dir}/addons/mobile_app/no_image.png" 
                            class="mockup__category-preview categoryEmptyImage__background"
                        />
                        <p class="mockup__category-name categoryBlockTextColor">Music</p>
                    </div>
                    <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                        <img 
                            src="{$images_dir}/addons/mobile_app/no_image.png" 
                            class="mockup__category-preview categoryEmptyImage__background"
                        />
                        <p class="mockup__category-name categoryBlockTextColor">Music</p>
                    </div>
                    <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                        <img 
                            src="{$images_dir}/addons/mobile_app/no_image.png" 
                            class="mockup__category-preview categoryEmptyImage__background"
                        />
                        <p class="mockup__category-name categoryBlockTextColor">Music</p>
                    </div>
                    <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                        <img 
                            src="{$images_dir}/addons/mobile_app/no_image.png" 
                            class="mockup__category-preview categoryEmptyImage__background"
                        />
                        <p class="mockup__category-name categoryBlockTextColor">Music</p>
                    </div>
                    <div class="mockup__category-item categoryBlockBackgroundColor__background categoryBorderRadius">
                        <img 
                            src="{$images_dir}/addons/mobile_app/no_image.png" 
                            class="mockup__category-preview categoryEmptyImage__background"
                        />
                        <p class="mockup__category-name categoryBlockTextColor">Music</p>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <div class="span8">
        {include file="common/subheader.tpl" title=__("mobile_app.section.category")}

        {foreach $config_data.app_appearance.colors.categories as $col_name => $color}
        <div class="control-group">
            <label class="control-label" for="">{$color.name}:</label>
            <div class="controls">
                <div class="colorpicker">
                    <input {if $color.type != "number"}type="text"{else}type="number"{/if} 
                        data-target="{$col_name}" 
                        {if $color.type == "color" || $color.type == "rgba"}
                            data-ca-spectrum-show-alpha="true"
                        {/if}
                        name="m_settings[app_appearance][colors][categories][{$col_name}]"
                        id="{$col_name}" 
                        value="{$color.value}"
                        {if $color.type == "color" || $color.type == "rgba"}
                            class="js-mobile-app-input cm-colorpicker"
                        {else}
                            class="js-mobile-app-input"
                        {/if}
                    />
                </div>
                <p class="muted description">{$color.description}</p>
            </div>
        </div>
        {/foreach}
    </div>
</div>