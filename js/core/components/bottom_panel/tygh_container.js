import { params } from './params';

export const tyghContainer = {
    _addPadding: function () {
        (function (_, $) {
            $(document).ready(function () {
                $('#' + _.container).addClass(params.tyghMainContainerPaddingClass);
            });

        }(Tygh, Tygh.$));
    },

    _removePadding: function () {
        (function (_, $) {
            $(document).ready(function () {
                $('#' + _.container).removeClass(params.tyghMainContainerPaddingClass);
            });

        }(Tygh, Tygh.$));
    },
};
