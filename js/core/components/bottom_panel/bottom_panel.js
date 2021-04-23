import { params } from './params';
import { state } from './state';
import { tyghContainer } from './tygh_container';
import { bottomButtons } from './bottom_buttons';

export const bottomPanel = {
    _activate: function () {
        state.isBottomPanelOpen = true;
        bottomButtons._hide();
        bottomPanel._show();
        tyghContainer._addPadding();
        bottomPanel._setOpenCookie(true);
    },

    _deactivate: function () {
        state.isBottomPanelOpen = false;
        bottomButtons._show();
        bottomPanel._hide();
        tyghContainer._removePadding();
        bottomPanel._setOpenCookie(false);
    },

    _hide: function () {
        state.bottomPanel.addClass(params.bottomPanelHiddenClass);
    },

    _show: function () {
        state.bottomPanel.removeClass(params.bottomPanelHiddenClass);
    },

    _setOpenCookie: function (isOpen) {
        $.cookie.set('pb_is_bottom_panel_open', isOpen);
    },

    _getCookie: function () {
        var bottomPanelOpenCookie = $.cookie.get('pb_is_bottom_panel_open');
        if (!!bottomPanelOpenCookie) {
            state.isBottomPanelOpen  = bottomPanelOpenCookie;
        } else {
            state.isBottomPanelOpen = true;
        }
    },

    _addActivateListeners: function () {
        $(Tygh.doc).on('click', params.onBottomPanelSelector, function () {
            return bottomPanel._activate();
        });
    },

    _addDeactivateListeners: function () {
        $(Tygh.doc).on('click', params.offBottomPanelSelector, function () {
            return bottomPanel._deactivate();
        });
    },
};
