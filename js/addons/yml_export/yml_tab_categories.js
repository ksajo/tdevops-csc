(function (_, $) {
  $(document).ready(function () {
    $(_.doc).on('change', '#yml2_mode_select', function (event) {
      if ($(this).val() == "product.yml2_model" || $(this).val() == "" && $('#yml2_parent_model_category').val() != '') {
        $('#yml2_model').removeAttr('disabled').show();
      } else {
        $('#yml2_model').attr('disabled', 'disabled').hide();
      }
    });
    $(_.doc).on('change', '#yml2_type_prefix_select', function (event) {
      if ($(this).val() == "product.yml2_type_prefix" || $(this).val() == "" && $('#yml2_parent_type_prefix_select').val() != '') {
        $('#yml2_type_prefix').removeAttr('disabled').show();
      } else {
        $('#yml2_type_prefix').attr('disabled', 'disabled').hide();
      }
    });
    $(_.doc).on('change', '#yml2_offer_type', function (event) {
      var model_type = $(this).val();
      var parent_offer = $('#yml2_parent_offer_val').val();
      var offers = ['vendor', 'apparel', 'simple', 'apparel_simple'];
      var is_parent = offers.indexOf(parent_offer) >= 0;

      if (offers.indexOf(model_type) >= 0 || model_type == '' && is_parent) {
        $('#yml2_model_select_div').removeAttr('disabled').show();
        $('#yml2_type_prefix_select_div').removeAttr('disabled').show();
        $('#yml2_mode_select').trigger('change');
        $('#yml2_type_prefix_select').trigger('change');
      } else {
        $('#yml2_model_select_div').attr('disabled', 'disabled').hide();
        $('#yml2_type_prefix_select_div').attr('disabled', 'disabled').hide();
        $('#yml2_model').attr('disabled', 'disabled').hide();
        $('#yml2_type_prefix').attr('disabled', 'disabled').hide();
      }
    });
    $('#yml2_mode_select').trigger('change');
    $('#yml2_type_prefix_select').trigger('change');
    $('#yml2_offer_type').trigger('change');
  });
})(Tygh, Tygh.$);