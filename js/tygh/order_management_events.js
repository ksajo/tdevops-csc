(function (_, $) {
  $.ceEvent('one', 'ce.commoninit', function () {
    $(document).on('click', '.select2-search__field', function (event) {
      var isMobile = $('body').hasClass('screen--xs') || $('body').hasClass('screen--xs-large') || $('body').hasClass('screen--sm');

      if (isMobile) {
        var self = $(event.target);
        var topBlock = $('.btn-bar.btn-toolbar.dropleft.pull-right');
        var offset = topBlock.offset().top + topBlock.height() - 15;
        $.scrollToElm('#product_add');
      }
    });
  });
})(Tygh, Tygh.$);