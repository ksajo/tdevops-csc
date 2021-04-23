(function (_, $) {
  $(_.doc).ready(function () {
    $(_.doc).on('click', '.cm-sdek-select-store', function (e) {
      $.ceEvent('trigger', 'ce.shipping.select-store', []);
      fn_calculate_total_shipping_cost();
    });
    $(_.doc).on('click', '.cm-sdek-show-all-on-map', function (e) {
      var container_id = $(e.target).data('caTargetMapId'),
          $container = $('#' + container_id);

      if (!$container.length) {
        return false;
      }

      $container.ceGeoMap('adjustMapBoundariesToSeeAllMarkers');
    });
    $(_.doc).on('click', '.cm-sdek-view-location', function () {
      var $jelm = $(this),
          lat = $jelm.data('caLatitude'),
          lng = $jelm.data('caLongitude'),
          container_id = $jelm.data('caTargetMapId');
      $container = $('#' + container_id);

      if (!$container.length || !lat || !lng) {
        return false;
      }

      $container.ceGeoMap('setCenter', lat, lng);
      var scroll_to = $jelm.data('caScroll');

      if (scroll_to) {
        $.scrollToElm(scroll_to);
      }
    });
    $(_.doc).on('click', '.cm-sdek-select-location', function () {
      var $jelm = $(this),
          location = $jelm.data('caLocationId'),
          group_key = $jelm.data('caGroupKey'),
          shipping_id = $jelm.data('caShippingId'),
          delete_dummy_elm_after_calculate = false,
          target_map_id = $jelm.data('caTargetMapId'),
          $container = $('#' + target_map_id); // this workaround is required for checkboxes (offices) that are loaded by ajax request
      // and might not be present at the moment in the DOM tree

      if (!$('[data-ca-pickup-select-office]' + '[data-ca-shipping-id="' + shipping_id + '"]' + '[data-ca-group-key="' + group_key + '"]' + '[data-ca-location-id="' + location + '"]').length) {
        delete_dummy_elm_after_calculate = true;
        var $shipping_item_elm = $('<input>').addClass('hidden').attr('type', 'radio').attr('name', 'select_office[' + group_key + '][' + shipping_id + ']').val(location).attr('data-ca-pickup-select-office', true).attr('data-ca-shipping-id', shipping_id).attr('data-ca-group-key', group_key).attr('data-ca-location-id', location);
        $('#sdek_offices').append($shipping_item_elm);
      }

      $('[data-ca-pickup-select-office]' + '[data-ca-shipping-id="' + shipping_id + '"]' + '[data-ca-group-key="' + group_key + '"]' + '[data-ca-location-id="' + location + '"]').each(function () {
        $(this).prop('checked', true);
      });
      fn_calculate_total_shipping_cost();
      $container.ceGeoMap('exitFullscreen');

      if (delete_dummy_elm_after_calculate) {
        $shipping_item_elm.remove();
      }
    });
  });
})(Tygh, Tygh.$);