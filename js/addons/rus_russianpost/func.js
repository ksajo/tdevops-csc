(function (_, $) {
  $.ceEvent('on', 'ce.update_object_status_callback', function () {
    var order_id = $('#order_id').val();
    $.ceAjax('request', fn_url('pochta.search_tracking&order_id=' + order_id), {
      result_ids: 'content_pochta_information'
    });
  });
})(Tygh, Tygh.$);