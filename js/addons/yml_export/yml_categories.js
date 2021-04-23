(function (_, $) {
  $(function () {
    $('.cm-yml-categories').autocomplete({
      source: function source(request, response) {
        $.ceAjax('request', fn_url('yml_categories.autocomplete?q=' + encodeURIComponent(request.term)), {
          callback: function callback(data) {
            response(data.autocomplete);
          }
        });
      }
    });
    $(_.doc).on('click', '.cm-yml-category-select', function (event) {
      var elm = $(event.target),
          form_name = elm.data('caTargetForm'),
          form = $('form#' + form_name),
          checked = form.find(':checked'),
          box_id = form_name.replace('_form', '') + '_box',
          box = $('#' + box_id);

      if (checked.length) {
        box.find('input[type=text]').val(checked.data('caCategory'));
        $.ceDialog('get_last').ceDialog('close');
      } else {
        fn_alert(_.tr('error_no_items_selected'));
      }

      return false;
    });
  });
})(Tygh, Tygh.$);