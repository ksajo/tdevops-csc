import { state } from './state';
import { params } from './params';

export const theme_editor = {
    _addLoadedEventHandler: function () {
        $.ceEvent('on', 'ce.themeeditor.loaded', function () {
            state.sidebars = $(params.sidebarsSelector);
            theme_editor._setSidebarsPadding();
        });
    },

    _setSidebarsPadding: function () {
        $(state.sidebars).each(function () {
            $(this).addClass(params.sidebarPaddingClass);
        });
    },
};
