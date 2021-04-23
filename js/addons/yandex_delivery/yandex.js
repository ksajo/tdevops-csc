(function (_, $) {
  (function ($) {
    var maps = [];
    var saved_point = null;
    var map_params = [];
    var latitude = 0;
    var longitude = 0;
    var zoom = 0;
    var latitude_name = '';
    var longitude_name = '';
    var map_container = '';
    var start_init_ydmaps = false;
    var finish_init_ydmaps = false;
    var methods = {
      init: function init(options, callback) {
        var group_key = options.group_key;

        if (!('ydmaps' in window)) {
          if (!start_init_ydmaps) {
            start_init_ydmaps = true;
            $.getScript('//api-maps.yandex.ru/2.1/?ns=ydmaps&lang=' + options.language, function () {
              ydmaps.ready(function () {
                finish_init_ydmaps = true;
                $.ceYdPickup('init', options, callback);
              });
            });
          } else {
            setTimeout(function () {
              $.ceYdPickup('init', options, callback);
            }, 500);
          }

          return false;
        }

        if (!start_init_ydmaps || start_init_ydmaps && !finish_init_ydmaps) {
          setTimeout(function () {
            $.ceYdPickup('init', options, callback);
          }, 500);
          return false;
        }

        latitude = options.latitude;
        longitude = options.longitude;
        map_container = options.map_container;
        zoom = options.zoom; // Required fields - zoom, center

        map_params[group_key] = {
          zoom: 12,
          type: 'yandex#map',
          center: [latitude, longitude],
          controls: ['default']
        };

        if (_.area == 'A') {
          $.extend(map_params[group_key], {
            draggableCursor: 'crosshair',
            draggingCursor: 'pointer'
          });
        } else {
          $.extend(map_params[group_key], {
            zoom: zoom
          });
        }

        if (typeof callback == 'function') {
          callback();
        }
      },
      destroyMaps: function destroyMaps(group_key) {
        maps.forEach(function (element, index) {
          if ($('#yd_map_' + index).length) {
            maps[index].destroy();
          }
        });
        maps = [];
      },
      show: function show(options) {
        if (typeof options == "undefined") {
          return false;
        }

        var group_key = options.group_key;

        if (!map_params[group_key]) {
          return $.ceYdPickup('init', options, function () {
            $.ceYdPickup('show', options);
          });
        }

        if (maps[group_key]) {
          $.ceYdPickup('destroyMaps');
        }

        if (!maps[group_key] || typeof maps[group_key].layers == "undefined" || !$('ymaps').length) {
          maps[group_key] = new ydmaps.Map(document.getElementById(options.map_container), map_params[group_key]);
          maps[group_key].controls.remove('searchControl');
          maps[group_key].behaviors.disable(['scrollZoom']);
          var marker;
          storeData = options.storeData;

          for (var keyvar = 0; keyvar < storeData.length; keyvar++) {
            var marker_html = '<div style="padding-right: 10px"><strong>' + storeData[keyvar]['name'];
            marker_html += '</strong><p>';

            if (storeData[keyvar]['city'] != '') {
              marker_html += storeData[keyvar]['city'] + ', ';
            }

            if (typeof storeData[keyvar]['pickup_address'] !== 'undefined') {
              marker_html += storeData[keyvar]['pickup_address'];
            }

            if (typeof storeData[keyvar]['pickup_phone'] !== 'undefined') {
              marker_html += '<br/>' + storeData[keyvar]['pickup_phone'];
            }

            if (typeof storeData[keyvar]['pickup_time'] !== 'undefined') {
              marker_html += '<br/>' + storeData[keyvar]['pickup_time'];
            }

            if (typeof storeData[keyvar]['description'] !== 'undefined') {
              marker_html += '<br/>' + storeData[keyvar]['description'];
            }

            if (options['selectStore'] === true) {
              marker_html += '<p><a data-ca-shipping-id="' + storeData[keyvar]['shipping_id'] + '" data-ca-group-key="' + storeData[keyvar]['group_key'] + '" data-ca-location-id="' + storeData[keyvar]['store_location_id'] + '" class="cm-yd-select-location ty-btn ty-btn__tertiary text-button">Выбрать</a></p>';
            }

            marker_html += '</p></div>';
            marker = new ydmaps.Placemark([storeData[keyvar]['latitude'], storeData[keyvar]['longitude']], {
              balloonContentBody: marker_html
            });
            maps[group_key].geoObjects.add(marker);
          }

          if (storeData.length == 1) {
            maps[group_key].setCenter(marker.geometry.getCoordinates());
            maps[group_key].setZoom(zoom);
          } else {
            ydmaps.geoQuery(maps[group_key].geoObjects).applyBoundsToMap(maps[group_key]);
            var select = $('.ty-yd-store__radio-' + group_key + ':checked').attr('value');
            $('input.ty-yd-store__radio-' + group_key + '[value="' + select + '"]').parent('.ty-yd-store').addClass('ty-yd-store__selected').show();

            if (!select) {
              var select = $('.ty-yd-store__radio:checked').attr('value');
            }

            if (select) {
              $.each(storeData, function (key, value) {
                if (value['store_location_id'] == select) {
                  maps[group_key].setCenter([value['latitude'], value['longitude']]);
                  maps[group_key].setZoom(zoom);
                  return false;
                }
              });
            }
          }
        }
      },
      saveLocation: function saveLocation() {
        if (saved_point) {
          $('#' + latitude_name).val(saved_point[0]);
          $('#' + latitude_name + '_hidden').val(saved_point[0]);
          $('#' + longitude_name).val(saved_point[1]);
          $('#' + longitude_name + '_hidden').val(saved_point[1]);
        }

        saved_point = null;
      },
      selectLocation: function selectLocation(location, group_key, shipping_id) {
        if (maps[group_key]) {
          maps[group_key].destroy();
        }

        $('[data-ca-pickup-select-store]' + '[data-ca-shipping-id="' + shipping_id + '"]' + '[data-ca-group-key="' + group_key + '"]' + '[data-ca-location-id="' + location + '"]').each(function () {
          $(this).prop('checked', true);
        });
        fn_calculate_total_shipping_cost();
      },
      viewLocation: function viewLocation(latitude, longitude, group_key) {
        maps[group_key].setCenter([latitude, longitude]);
        maps[group_key].setZoom(zoom);
      },
      viewLocations: function viewLocations(group_key) {
        ydmaps.geoQuery(maps[group_key].geoObjects).applyBoundsToMap(maps[group_key]);
      }
    };
    $.extend({
      ceYdPickup: function ceYdPickup(method) {
        if (methods[method]) {
          return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else {
          $.error('ty.map: method ' + method + ' does not exist');
        }
      }
    });
  })($);

  $(document).ready(function () {
    $(document).on('click', '.cm-yd-select-store', function (e) {
      $.ceYdPickup('destroyMaps');
      fn_calculate_total_shipping_cost();
    });
    $(document).on('click', '.cm-yd-save-location', function () {
      $.ceYdPickup('saveLocation');
    });
    $(document).on('click', '.cm-yd-select-location', function () {
      var jelm = $(this);
      var location = jelm.data('ca-location-id');
      var group_key = jelm.data('ca-group-key');
      var shipping_id = jelm.data('ca-shipping-id');
      $.ceYdPickup('selectLocation', location, group_key, shipping_id);
    });
    $(document).on('click', '.cm-yd-view-location', function () {
      var jelm = $(this);
      var latitude = jelm.data('ca-latitude');
      var longitude = jelm.data('ca-longitude');
      var group_key = jelm.data('ca-group-key');
      $.ceYdPickup('viewLocation', latitude, longitude, group_key);

      if ($(this).data('ca-scroll')) {
        var id = $(this).data('ca-scroll');
        $.scrollToElm(id);
      }
    });
    $(document).on('click', '.cm-yd-view-locations', function () {
      var jelm = $(this);
      var group_key = jelm.data('ca-group-key');
      $.ceYdPickup('viewLocations', group_key);

      if ($(this).data('ca-scroll')) {
        var id = $(this).data('ca-scroll');
        $.scrollToElm(id);
      }
    });
    $(_.doc).on('click', '.cm-show-all-point', function (e) {
      var pickpoints = $('.ty-yd-store');
      $(this).hide();
      $.each(pickpoints, function (key, value) {
        $(value).show();
      });
    });
  });
})(Tygh, Tygh.$);