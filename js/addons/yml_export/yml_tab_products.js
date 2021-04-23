(function (_, $) {
  $(document).ready(function () {
    $(_.doc).on('change', '#yml2_offer_type', function () {
      var offers = {
        'model': ['vendor', 'apparel', 'simple', 'apparel_simple'],
        'type_prefix': ['vendor', 'apparel']
      };
      var model_type = $(this).val();

      if (model_type == '') {
        model_type = $('#yml2_parent_offer_val').val();
      }

      if (offers['model'].indexOf(model_type) >= 0) {
        $('#yml2_model_div').removeAttr('disabled').show();
      } else {
        $('#yml2_model_div').attr('disabled', 'disabled').hide();
      }

      if (offers['type_prefix'].indexOf(model_type) >= 0) {
        $('#yml2_type_prefix_div').removeAttr('disabled').show();
      } else {
        $('#yml2_type_prefix_div').attr('disabled', 'disabled').hide();
      }
    });
    $('#yml2_offer_type').trigger('change');
  });
})(Tygh, Tygh.$);