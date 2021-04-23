(function (_, $) {
  $.ceEvent('on', 'ce.commoninit', function (context) {
    $('.cm-mask-time').mask('99:99:99');
  });
  $(_.doc).on('click', function (e) {
    var jelm = $(e.target);
    var elm = e.target;

    if ($.matchClass(elm, /cm-sdek_form_call(-[\w]+)?/gi)) {
      var p_elm = jelm.parents('.cm-sdek_form_call').length ? jelm.parents('.cm-sdek_form_call:first') : jelm.prop('id') ? jelm : jelm.parent();
      var id, prefix;

      if (p_elm.prop('id')) {
        prefix = p_elm.prop('id').match(/^(on_|off_|sw_)/)[0] || '';
        id = p_elm.prop('id').replace(/^(on_|off_|sw_)/, '');
      }

      var container = $('#' + id);
      var flag = prefix == 'on_' ? false : prefix == 'off_' ? true : container.is(':visible') ? true : false;
      container.removeClass('hidden');
      container.toggleBy(flag);
      $('#on_' + id).removeClass('hidden').toggleBy(!flag);
      $('#off_' + id).removeClass('hidden').toggleBy(flag);
    }

    return true;
  });
})(Tygh, Tygh.$);