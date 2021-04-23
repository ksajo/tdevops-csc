(function (_, $) {
  (function ($) {
    var methods = {
      /**
       * Initiates refund amount recalculator when refunding an order paid with Yandex.Checkpoint.
       *
       * @param recalc_trigger_elm JQuery selector of an element that triggers totals recalculation
       * @param amount_elm         JQuery selector of an element that contains refund totals
       */
      init_refund_form: function init_refund_form(recalc_trigger_elm, amount_elm) {
        $(recalc_trigger_elm).on('change', function (e) {
          var sum = 0.0;
          $('[data-ca-refund-value]:checked').each(function (i) {
            sum += parseFloat($(this).data('caRefundValue')) * parseFloat($('[data-ca-refund-amount-' + $(this).data('caCartId') + ']').val());
          });
          $(amount_elm).val(sum).trigger('blur');
        });
        setTimeout(function () {
          $(recalc_trigger_elm + ':first').trigger('change');
        }, 100);
      }
    };
    $.extend({
      ceYandexCheckpoint: function ceYandexCheckpoint(method) {
        if (methods[method]) {
          return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else {
          $.error('ty.yandex_checkpoint: method ' + method + ' does not exist');
        }
      }
    });
  })($);

  $(document).ready(function () {
    // refund form
    $.ceYandexCheckpoint('init_refund_form', '.yc-refund-recalculator', '#rus_payments_refund_amount');
  });
})(Tygh, Tygh.$);