(function (_, $) {
  $(function () {
    setInterval(sec, 5000);

    function sec() {
      $.ceAjax('request', fn_url('yml.manage'), {
        data: {
          result_ids: 'generation_status_*,price_list_tool_*'
        },
        hidden: true
      });
    }
  });
})(Tygh, Tygh.$);