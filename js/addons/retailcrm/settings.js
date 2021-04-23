(function (_, $) {
  $(document).ready(function () {
    $('#retailcrm_settings_container').on('click', '#retailcrm_settings_connect_link', function (event) {
      event.preventDefault();
      $.ceAjax('request', $(this).attr('href'), {
        method: 'get',
        result_ids: $(this).data('caTargetId'),
        obj: $(this),
        data: {
          retailcrm_host: $('#addon_option_retailcrm_retailcrm_host').val(),
          retailcrm_api_key: $('#addon_option_retailcrm_retailcrm_api_key').val()
        }
      });
      return false;
    });
  });
})(Tygh, Tygh.$);