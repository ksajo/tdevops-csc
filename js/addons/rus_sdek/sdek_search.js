(function (_, $) {
  $.ceEvent('on', 'ce.commoninit', function (context) {
    context.find('#sdek_office_search').autocomplete({
      source: function source(request, response) {
        var str_search = $('#sdek_office_search').val();
        $('div#sdek_office').css('display', 'none');
        $.expr[':'].contains_case_insensitive = $.expr.createPseudo(function (arg) {
          return function (elem) {
            return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
          };
        });
        $('#sdek_offices div:contains_case_insensitive("' + str_search + '")').css('display', 'block');
      }
    });
    $('#sdek_office_search').keyup(function () {
      var str_search = $('#sdek_office_search').val();

      if (!str_search) {
        $('div#sdek_office').css('display', 'block');
      }
    });
  });
  $(_.doc).on('click', '.cm-show-all-point', function () {
    getSdekOffices($(this).attr('href'));
    $(this).toggleClass('hidden', true);
  });

  function getSdekOffices(url) {
    $.ceAjax('request', url, {
      method: 'get',
      result_ids: 'sdek_offices',
      append: false,
      caching: false,
      callback: function callback(data) {
        $('.ty-sdek-office-search').show();
        $('.ty-sdek-checkout-select-office').addClass('ty-sdek-list-office');
        $('#sdek_office_search').focus();
      }
    });
  }
})(Tygh, Tygh.$);