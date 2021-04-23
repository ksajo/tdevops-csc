(function (_, $) {
  $.ceEvent('on', 'ce.commoninit', function (context) {
    $('.cm-yad-city', context).autocomplete({
      delay: 600,
      source: function source(request, response) {
        var url = 'yandex_delivery.autocomplete?type=locality&q=' + encodeURIComponent(request.term);
        $.ceAjax('request', fn_url(url), {
          callback: function callback(data) {
            response(data.autocomplete);
          }
        });
      }
    });
    $('.cm-yad-address', context).autocomplete({
      delay: 600,
      source: function source(request, response) {
        var city = $('.cm-yad-city').val();

        if (city) {
          var url = 'yandex_delivery.autocomplete?type=street&city=' + city + '&q=' + encodeURIComponent(request.term);
          $.ceAjax('request', fn_url(url), {
            callback: function callback(data) {
              response(data.autocomplete);
            }
          });
        }
      }
    });
  });
})(Tygh, Tygh.$);