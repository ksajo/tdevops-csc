(function (_, $) {
  $(document).ready(function () {
    script_url = (document.location.protocol == "https:" ? "https:" : "http:") + "//points.boxberry.de/js/boxberry.js";
    $.getScript(script_url);
    $(document).on('click', '.select_pvz_link', function (e) {
      e.preventDefault();
      var selectPointLink = e.target;
      var city = selectPointLink.getAttribute('data-boxberry-city') || undefined;
      var callbackInputAttr = selectPointLink.getAttribute('data-boxberry-point-input');
      var paymentSum = selectPointLink.getAttribute('data-paymentsum');
      var orderSum = selectPointLink.getAttribute('data-ordersum');
      var callbackInput;

      if (callbackInputAttr[0] == '#') {
        callbackInput = document.getElementById(callbackInputAttr.substr(1));
      } else {
        callbackInput = document.getElementsByName(callbackInputAttr).item(0);
      }

      var boxberry_checkout_handler = function boxberry_checkout_handler(result) {
        callbackInput.value = result.id;
        var data = {};
        data[callbackInput.getAttribute('name')] = result.id;
        data['update_step'] = $('.ty-step__container-active').find('input[name="update_step"]').val() || 'step_three';
        $.ceAjax('request', fn_url('checkout.update_steps'), {
          method: 'post',
          caching: false,
          recalculate: true,
          data: data,
          callback: function callback() {
            fn_calculate_total_shipping_cost();
          }
        });
      };

      var boxberry_backend_handler = function boxberry_backend_handler(result) {
        callbackInput.value = result.id;
        var jelem = $(callbackInput);
        $.submitForm(jelem);
      };

      var token = selectPointLink.getAttribute('data-boxberry-token');
      var targetStart = selectPointLink.getAttribute('data-boxberry-target-start');
      var weight = selectPointLink.getAttribute('data-boxberry-weight');

      if (_.area == 'C') {
        boxberry.open(boxberry_checkout_handler, token, city, targetStart, orderSum, weight, paymentSum, 0, 0, 0);
      } else {
        boxberry.open(boxberry_backend_handler, token, city, targetStart, orderSum, weight, paymentSum, 0, 0, 0);
      }

      return false;
    });
  });
})(Tygh, Tygh.$);

function token_callback(result) {
  shipping_id = $('form[name="shippings_form"]').find('input[name="shipping_id"]').val();

  if (result.token != undefined) {
    $('#password').val(result.token);
    reg_link = $('#reg_to_boxberry');
    $.ceAjax('request', fn_url('boxberry.save_token'), {
      method: 'post',
      caching: false,
      data: {
        token: result.token,
        shipping_id: shipping_id
      },
      callback: function callback() {
        reg_link.remove();
      }
    });
  }
}