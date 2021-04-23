(function (_, $) {
  $.ceEvent('on', 'ce.commoninit', function (context) {
    initMainProductVariationFeaturesPicker(context);
    initCartProductVariationFeaturesPicker(context);
  });

  function initMainProductVariationFeaturesPicker(context) {
    var $elems = $(context).find('.cm-picker-product-variation-features');

    if (!$elems.length) {
      return;
    }

    $elems.find('select,input[type="radio"]').on('change', function () {
      var $self = $(this),
          $option;

      if ($self.prop('tagName').toLowerCase() === 'select') {
        $option = $self.find('option:selected');
      } else {
        $option = $self;
      }

      if ($option.length) {
        if ($self.hasClass('cm-ajax')) {
          $.ceAjax('request', $option.data('caProductUrl'), {
            result_ids: $self.data('caTargetId'),
            save_history: $self.hasClass('cm-history'),
            force_exec: $self.hasClass('cm-ajax-force'),
            caching: true
          });
        } else {
          $.redirect($option.data('caProductUrl'));
        }
      }
    });
  }

  function initCartProductVariationFeaturesPicker(context) {
    var $elems = $(context).find('.cm-picker-cart-product-variation-features');

    if (!$elems.length) {
      return;
    }

    $elems.find('select').on('change', function () {
      var $self = $(this),
          $option = $self.find('option:selected');

      if ($option.length) {
        $.ceAjax('request', $option.data('caChangeUrl'), {
          method: 'post',
          full_render: true,
          result_ids: $self.data('caTargetId')
        });
      }
    });
  }
})(Tygh, Tygh.$);