(function (_, $) {
  $.ceEvent('on', 'ce.commoninit', function (context) {
    $.ceEvent('on', 'ce.tap.toggle', function (selected) {
      if (selected && event && $(event.target).is('td')) {
        $('.categories-company .cm-combination:visible[id^="on_"]').click();
      }
    });
  });
})(Tygh, Tygh.$);