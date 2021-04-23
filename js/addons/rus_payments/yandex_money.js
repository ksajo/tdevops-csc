(function (_, $) {
  $.ceEvent('on', 'ce.commoninit', function () {
    var elms = $('.cm-yandex-money-mws-enabled');

    if (elms.length) {
      elms.on('change', function () {
        var is_mws_enabled = !!elms.filter(':checked').length;
        $('div#yandex_payment_types_' + $(this).data("caPaymentId") + ' .cm-yandex-money-payment-type').each(function () {
          var $this = $(this);

          if (is_mws_enabled) {
            if (!$this.data('prev_checked')) {
              $this.data('prev_checked', $this.prop('checked'));
            }

            if ($this.val() == 'AC') {
              $this.prop('checked', true);
              $('<input type="hidden" name="' + $this.prop('name') + '" value="' + $this.val() + '" />').insertBefore($this);
            } else {
              $this.prop('checked', false);
            }

            $this.prop('disabled', true);
          } else {
            $this.prop('disabled', false);
            $this.prop('checked', $this.data('prev_checked'));
            $this.data('prev_checked', null);
          }
        });
      });
      $('.cm-yandex-money-mws-enabled').each(function () {
        if ($(this).prop('checked')) {
          $(this).trigger('change');
        }
      });
    }
  });
})(Tygh, Tygh.$);