(function (_, $) {
  $(document).ready(function () {
    $('#ship_russian_post_object_type').on('change', function () {
      var request_data = $('.russian-post-service-item:checked').serializeObject();
      var object_id = $(this).val();
      request_data['object_id'] = object_id;
      $.ceAjax('request', fn_url('russian_post.get_services_list'), {
        method: 'get',
        result_ids: 'russian_post_services_list',
        data: request_data
      });
    });
    $('#russian_post_services_list').on('click touch', '.russian-post-service-item', function () {
      var exclude_ids = $(this).data('caExcludeIds');

      for (var key in exclude_ids) {
        $('#russian_post_service_item_' + exclude_ids[key]).prop('checked', false);
      }
    });
  });
})(Tygh, Tygh.$);