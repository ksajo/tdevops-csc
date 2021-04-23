(function (_, $) {
  $(document).ready(function () {
    $(_.doc).on('change', '.cm-ym-option-select', function (event) {
      var option = $(this).find('option:selected');
      var option_id = $(this).data('option-id');
      $('.ym-option').each(function () {
        $(this).hide();
      });
      $(".cm-ym-option-type-select").each(function () {
        $(this).attr('disabled', 'disabled');
      });
      $(".option_param_input").each(function () {
        $(this).attr('disabled', 'disabled').hide();
      });
      $("#ym_option_" + option_id + "_" + option.val()).show();
      $("#elm_option_yml2_option_param_" + option_id + "_" + option.val()).removeAttr('disabled');
      $("#elm_yml2_option_param_input_" + option_id + "_" + option.val()).removeAttr('disabled');
    });
    $(_.doc).on('change', '.cm-ym-option-type-select', function (event) {
      var option = $(this).find('option:selected');
      var option_id = $(this).data('option-id');
      $(".option_param_input").each(function () {
        $(this).attr('disabled', 'disabled').hide();
      });

      if (option.val() == 'customer') {
        $('#elm_yml2_option_param_input_' + option_id).removeAttr('disabled').show();
      } else {
        $('#elm_yml2_option_param_input_' + option_id).attr('disabled', 'disabled').hide();
      }
    });
  });
})(Tygh, Tygh.$);