(function (_, $) {
  $.ceEvent('on', 'ce.commoninit', function (context) {
    context.find('#city').autocomplete({
      source: function source(request, response) {
        var check_country = $('#field_sdek_country').length ? $('#field_sdek_country').val() : '';
        var check_state = $('#field_sdek_state').length ? $('#field_sdek_state').val() : '';

        if (!check_state) {
          check_state = $('#field_sdek_state_d').val();
        }

        fn_get_sdek_cities(check_country, check_state, request, response);
      }
    });
  });

  function fn_get_sdek_cities(check_country, check_state, request, response) {
    $.ceAjax('request', fn_url('city_sdek.autocomplete_city?q=' + encodeURIComponent(request.term) + '&check_country=' + check_country + '&check_state=' + check_state), {
      callback: function callback(data) {
        response(data.autocomplete);
      }
    });
  }

  function fn_get_states() {
    var country = $("#field_sdek_country").val();
    var state = $("#field_sdek_state").val();
    var url = fn_url('city_sdek.select_state');

    if (!state) {
      state = $("#field_sdek_state_d").val();
    }

    url += '&country=' + country + '&state=' + state;
    $.ceAjax('request', url, {
      result_ids: 'change_state',
      method: 'get'
    });
  }

  $(_.doc).ready(function () {
    $(_.doc).on('click', '#sdek_get_city_link', function (event) {
      fn_get_sdek_city();
      return false;
    });
    $(_.doc).on('change', '#field_sdek_country', function () {
      fn_get_states();
    });
    $(_.doc).on('change', '#field_sdek_state', function () {
      fn_get_states();
    });
    $(_.doc).on('change', '#field_sdek_state_d', function () {
      fn_get_states();
    });
  });

  function fn_get_sdek_city() {
    var city = $('#city').val();
    var check_country = $("#field_sdek_country").val();
    var check_state = $("#field_sdek_state").val();

    if (!check_state) {
      check_state = $("#field_sdek_state_d").val();
    }

    $.ceAjax('request', fn_url("city_sdek.sdek_get_city_data"), {
      data: {
        var_city: city,
        loc: 'shipping_settings',
        result_ids: 'sdek_city_div',
        check_country: check_country,
        check_state: check_state
      }
    });
  }
})(Tygh, Tygh.$);