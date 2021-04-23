(function (_, $) {
  $.ceEvent('on', 'ce.commoninit', function (context) {
    $(_.doc).on('click', '.cm-update-for-all-icon[href="#"]', function (e) {
      e.preventDefault();
    });
  });
})(Tygh, Tygh.$);