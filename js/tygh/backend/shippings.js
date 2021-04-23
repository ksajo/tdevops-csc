(function (_, $) {
  $.ceEvent('on', 'ce.commoninit', function (context) {
    $('.cm-btn-weight').on('click', function () {
      var $selector = $(this).data('caExternalClickId');
      $('#' + $selector).val(this.value);
    });
  });
})(Tygh, Tygh.$);