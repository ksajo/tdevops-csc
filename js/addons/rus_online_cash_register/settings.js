(function (_, $) {
  $(document).ready(function () {
    $('#rus_online_cash_register_settings_connect_link').on('click', function (event) {
      event.preventDefault();
      $.ceNotification('closeAll');
      $.ceAjax('request', $(this).attr('href'), {
        method: 'post',
        obj: $(this),
        data: {
          atol_inn: $('#addon_option_rus_online_cash_register_atol_inn').val(),
          atol_group_code: $('#addon_option_rus_online_cash_register_atol_group_code').val(),
          atol_payment_address: $('#addon_option_rus_online_cash_register_atol_payment_address').val(),
          atol_login: $('#addon_option_rus_online_cash_register_atol_login').val(),
          atol_password: $('#addon_option_rus_online_cash_register_atol_password').val(),
          mode: $('#addon_option_rus_online_cash_register_mode').val(),
          api_version: $('#addon_option_rus_online_cash_register_api_version').val()
        }
      });
      return false;
    });
    $('#elm_price_includes_tax').on('change', function () {
      var is_checked = $(this).prop('checked');
      $('#control_group_cash_register_tax_included').toggleClass('hidden', !is_checked).find('select').prop('disabled', !is_checked);
      $('#control_group_cash_register_tax_excluded').toggleClass('hidden', is_checked).find('select').prop('disabled', is_checked);
    });
  });
})(Tygh, Tygh.$);