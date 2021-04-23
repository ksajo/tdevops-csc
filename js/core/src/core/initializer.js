import * as methods from "./Tygh/methods";
import * as coreMethods from "./Tygh/core_methods";
import { registerAllPlugins } from "./Tygh/plugins";

/**
 * Namespace initialization
 * @param {Tygh} _ Main namespace
 * @param {JQueryStatic} $ jQuery
 */
export const namespaceInitializer = function (_, $) {
    // Copy given jQuery to namespace
    _.$ = $;

    // Define utility functions
    $.fn.extend({
        select2Sortable: methods.select2Sortable,
        toggleBy: methods.toggleBy,
        moveOptions: methods.moveOptions,
        swapOptions: methods.swapOptions,
        selectOptions: methods.selectOptions,
        alignElement: methods.alignElement,
        formIsChanged: methods.formIsChanged,
        fieldIsChanged: methods.fieldIsChanged,
        disableFields: methods.disableFields,
        click: methods.click,
        switchAvailability: methods.switchAvailability,
        serializeObject: methods.serializeObject,
        positionElm: methods.positionElm
    });

    // Define core methods
    $.extend(coreMethods);

    // Register plugins
    registerAllPlugins($);

    /**
     * Bind css-classes to body for tracking web-page width
     */
    (function($) {

        var _timeout,
            _timeoutTime = 200,
            _first       = true,
            _widths      = {
                'screen--xs':       [0, 350],
                'screen--xs-large': [350, 480],
                'screen--sm':       [481, 767],
                'screen--sm-large': [768, 1024],
                'screen--md':       [1024, 1280],
                'screen--md-large': [1280, 1440],
                'screen--lg':       [1440, 1920],
                'screen--uhd':      [1920, 9999]
            };

        function customClearTimeout () {
            clearTimeout(_timeout);
            _timeout = undefined;
        }

        // would work after `_timeoutTime` ms
        var windowResizeHandler = function (event) {
            customClearTimeout();

            var classes = {
                    old: '',
                    new: ''
                },
                windowWidth = $(window).width();

            for (let className in _widths) {
                if ($('body').hasClass(className)) {
                    classes.old = className;
                    $('body').removeClass(className);
                }

                var width = _widths[className];
                if ((windowWidth >= width[0]) && (windowWidth <= width[1])) {
                    $('body').addClass(className);
                    classes.new = className;
                }
            }

            $.ceEvent('trigger', 'ce.window.resize', [event, classes]);

            if (_first) {
                _first = false;
                $.ceEvent('trigger', 'ce.responsive_classes.ready', []);
            }
        }

        $.ceEvent('on', 'ce.commoninit', () => {
            // bind onresize event handler to web page
            $(window).on('resize', function (event) {
                if (typeof(_timeout) != typeof(undefined)) {
                    customClearTimeout();
                }
                _timeout = setTimeout(windowResizeHandler, _timeoutTime, event);
            });

            // one-time setting class to body
            $(window).trigger('resize');
        });

    })($);

    // Post initialization
    // If page is loaded with URL in hash parameter, redirect to this URL
    if (!_.embedded && location.hash && decodeURIComponent(location.hash).indexOf('#!/') === 0) {
        var components = $.parseUrl(location.href)
        var uri = $.ceHistory('parseHash', location.hash);

        // FIXME: Remove this code when support for Internet Explorer 8 and 9 is dropped
        if($.browser.msie && $.browser.version >= 9) {
            $.redirect(components.protocol + '://' + components.host + uri);
        } else {
            $.redirect(components.protocol + '://' + components.host + components.directory + uri);
        }
    }
}
