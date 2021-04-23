(function (_, $) {
  $(document).ready(function () {
    $(document).on('focus', '.cm-yad-zipcode', function (e) {
      var address = $('.cm-yad-address').val();
      var city = $('.cm-yad-city').val();
      var url = 'yandex_delivery.get_index';
      url += '?address=' + encodeURIComponent(address);
      url += '&city=' + encodeURIComponent(city);
      $.ceAjax('request', fn_url(url), {
        callback: function callback(data) {
          if (typeof data.get_index !== 'undefined') {
            $('.cm-yad-zipcode').val(data.get_index.value);
          }
        }
      });
    });
  });
})(Tygh, Tygh.$);