function fn_open_pickpoint(group_key) {
  fn_select_pickpoint_terminal(group_key);
  var pickpoint_state = $('#elm_state').val();

  if (!('elm_city' in window)) {
    $.ceAjax('request', fn_url('pickpoint.pickpoint_city?to_state=' + pickpoint_state + '&country=' + 'RU'), {
      callback: function callback(data) {
        PickPoint.open(addressPostamatCart, {
          fromcity: pickpoint_state,
          city: data['pickpoint_city']
        });
      }
    });
  } else {
    var pickpoint_city = $('#elm_city').val();
    PickPoint.open(addressPostamatCart, {
      fromcity: pickpoint_state,
      city: pickpoint_city
    });
  }

  return false;
}

function fn_select_pickpoint_terminal(group_key) {
  parents = $('#shipping_rates_list');
  elms = $('.ty-one-pickpoint-terminal', parents);

  if (elms.length > 0) {
    $.each(elms, function (id, elm) {
      if ($('#pickpoint_select_' + id).prop('checked')) {
        $('#pickpoint_select_' + id).prop('checked', false);
      }
    });
  }

  $('#pickpoint_select_' + group_key).prop('checked', true);
}

function fn_click_pickpoint_terminal(group_key) {
  $('#pickpoint_select').val(group_key);
}

(function (_, $) {
  addressPostamat = function addressPostamat(result) {
    var group_key;
    params = [];
    parents = $('#shipping_rates_list');
    terminal = $('.ty-one-pickpoint-terminal:checked', parents);
    $.each(terminal, function (id, elm) {
      group_key = elm.value;
    });
    $('#pickpoint_id_' + group_key).val(result['id']);
    $('#address_pickpoint_' + group_key).innerHTML = result['name'] + '<br />' + result['address'];
    radio = $('input[type=radio]:checked', parents);
    $.each(radio, function (id, elm) {
      params.push({
        name: elm.name,
        value: elm.value
      });
    });
    url = fn_url('checkout.checkout');

    for (var i in params) {
      url += '&' + params[i]['name'] + '=' + encodeURIComponent(params[i]['value']);
    }

    $.ceAjax('request', url, {
      result_ids: 'shipping_rates_list,checkout_info_summary_*,checkout_info_order_info_*',
      method: 'get',
      full_render: true,
      data: {
        group_key: group_key,
        pickpoint_id: result['id'],
        city: result['cityname'],
        address_pickpoint: result['name'] + ', ' + result['address']
      }
    });
  };

  addressPostamatCart = function addressPostamatCart(result) {
    var group_key;
    params = [];
    parents = $('#shipping_estimation');
    terminal = $('.ty-one-pickpoint-terminal:checked', parents);
    $.each(terminal, function (id, elm) {
      group_key = elm.value;
    });
    $('#pickpoint_id_' + group_key).val(result['id']);
    $('#address_pickpoint_' + group_key).innerHTML = result['name'] + '<br />' + result['address'];
    params = [];
    radio = $('input[type=radio]:checked', parents);
    $.each(radio, function (id, elm) {
      params.push({
        name: elm.name,
        value: elm.value
      });
    });
    params.push({
      name: $('#elm_country').prop('name'),
      value: $('#elm_country').val()
    });
    params.push({
      name: $('#elm_state').prop('name'),
      value: $('#elm_state').val()
    });
    params.push({
      name: $('#elm_zipcode').prop('name'),
      value: $('#elm_zipcode').val()
    });
    url = fn_url('checkout.shipping_estimation');

    if ('elm_city' in window) {
      url += '&' + $('#elm_city').prop('name') + '=' + $('#elm_city').val();
    }

    for (i in params) {
      url += '&' + params[i]['name'] + '=' + encodeURIComponent(params[i]['value']);
    }

    $.ceAjax('request', url, {
      result_ids: 'shipping_estimation',
      method: 'post',
      full_render: true,
      data: {
        group_key: group_key,
        pickpoint_id: result['id'],
        address_pickpoint: result['name'] + ', ' + result['address']
      }
    });
  };

  addressPostamatOrder = function addressPostamatOrder(result) {
    $('#pickpoint_id').val(result['id']);
    $('#address_pickpoint').val(result['name'] + ', ' + result['address']);
    group_key = $('#pickpoint_select').val();
    params = [];
    parents = $('#om_ajax_update_shipping');
    selected = $('option:selected', parents);
    $.each(selected, function (id, elm) {
      params.push({
        name: 'shipping_id',
        value: elm.value
      });
    });
    url = fn_url('order_management.update_shipping');

    for (var i in params) {
      url += '&' + params[i]['name'] + '=' + encodeURIComponent(params[i]['value']);
    }

    $.ceAjax('request', url, {
      result_ids: 'om_ajax_update_shipping,om_ajax_update_totals',
      method: 'post',
      full_render: true,
      data: {
        group_key: group_key,
        pickpoint_id: result['id'],
        address_pickpoint: result['name'] + ', ' + result['address']
      }
    });
  };
})(Tygh, Tygh.$);