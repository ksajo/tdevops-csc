<div class="span16 mockup__mockups-container">
    <div class="span4 mockup">
        <div class="mockup__container">
            <div class="mockup__status-bar">

                <img src="{$images_dir}/addons/mobile_app/status_bar_example.png">

            </div>

            {include file="addons/mobile_app/components/atoms/navbar.tpl"}

            <div 
                class="mockup__body body mockup__category screenBackgroundColor__background"
                style="min-height: calc(100% - 65px); max-height: calc(100% - 65px);"
            >

            </div>

            <div class="mockup__overlay contentOverlayColor">
            </div>

            <div class="mockup__overlay-content navigator drawerBgColor__background">
                <div class="mockup__overlay-box logo-box drawerHeaderBackgroundColor__background drawerHeaderBorderColor__border">
                    <img class="navigator__store-logo" src="{$images_dir}/addons/mobile_app/logo.png"/>
                    <p class="drawerHeaderTextColor">Login | Registration</p>
                </div>

                <div class="mockup__overlay-box links-box drawerHeaderBackgroundColor__border">
                    <p class="links-box__container"><i class="fa fa-home drawerHeaderButtonColor"></i><span class="links-box__name">Home</span></p>
                    <p class="links-box__container"><i class="fa fa-cart-plus drawerHeaderButtonColor"></i><span class="links-box__name">Cart</span></p>
                </div>

                <div class="mockup__overlay-box links-box drawerHeaderBackgroundColor__border">
                    <p class="links-box__container"><span class="links-box__name">Contacts</span></p>
                    <p class="links-box__container"><span class="links-box__name">Returns and Exchanges</span></p>
                    <p class="links-box__container"><span class="links-box__name">Payment and shipping</span></p>
                </div>
            </div>

        </div>
    </div>

    <div class="span8">
        {include file="common/subheader.tpl" title=__("mobile_app.section.drawer")}

        {foreach $config_data.app_appearance.colors.drawer as $col_name => $color}
        <div class="control-group">
            <label class="control-label" for="">{$color.name}:</label>
            <div class="controls">
                <div class="colorpicker">
                    <input {if $color.type != "number"}type="text"{else}type="number"{/if} 
                        data-target="{$col_name}" 
                        {if $color.type == "color" || $color.type == "rgba"}
                            data-ca-spectrum-show-alpha="true"
                        {/if}
                        name="m_settings[app_appearance][colors][drawer][{$col_name}]"
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