(function (_, $) {
  $('document').ready(function () {
    var mode = $('#mode');
    var label = $('label[for^=payment_url]');
    var test = $('#payment_url_test');
    var live = $('#payment_url_live');
    var add = $('#opener_add_new_payments');
    /**
     * Switch input
     *
     * @param {string} mode    'Test/Live mode' (select)
     * @param {obj}    label   'Payment URL' label (JQuery)
     * @param {obj}    test     input for test url (JQuery)
     * @param {obj}    live     input for live url (JQuery)
     * @param {obj}    errors   errors for T/L input
     */

    var relay = function relay(mode, label, test, live, errors) {
      switch (mode) {
        case 'L':
          label.attr('for', 'payment_url_live');
          test.attr('type', 'hidden');
          live.attr('type', 'text');

          if (errors && errors.test) {
            errors.test.remove();
          }

          break;

        case 'T':
          label.attr('for', 'payment_url_test');
          test.attr('type', 'text');
          live.attr('type', 'hidden');

          if (errors && errors.live) {
            errors.live.remove();
          }

          break;
      }
    };

    add.click(function () {
      relay(mode.val(), label, test, live);
    });
    mode.change(function () {
      relay(mode.val(), label, test, live, {
        'test': $('#payment_url_test_error_message'),
        'live': $('#payment_url_live_error_message')
      });
    });
  });
})(Tygh, Tygh.$);