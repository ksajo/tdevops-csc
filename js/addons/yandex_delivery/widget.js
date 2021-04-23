(function (_, $) {
  $(document).ready(function () {
    if (window.ydwidget !== undefined) {
      ydwidget.ready(function () {
        ydwidget.initCartWidget({
          //получить указанный пользователем город
          'getCity': function getCity() {
            if (ydwidget.city) {
              return {
                value: ydwidget.city
              };
            } else {
              return false;
            }
          },
          //id элемента-контейнера
          'el': 'ydwidget',
          //габариты 1 единицы усредненного товара
          'length': 1,
          'width': 1,
          'height': 1,
          'city': '',
          'group_id': 0,
          'shipping_id': 0,
          //общее количество товаров в корзине
          'totalItemsQuantity': function totalItemsQuantity() {
            return 1;
          },
          //общий вес товаров в корзине
          'weight': function weight() {
            return 1;
          },
          //общая стоимость товаров в корзине
          'cost': function cost() {
            return 111;
          },
          'itemsDimensions': function itemsDimensions() {
            return [[1, 1, 1, 1]];
          },
          'setCity': function setCity(city, region) {//$('#city').val(city + ', ' + region)
          },
          'onDeliveryChange': function onDeliveryChange(delivery) {
            //если выбран вариант доставки, выводим его описание и закрываем виджет, иначе произошел сброс варианта,
            //очищаем описание
            if (delivery) {
              if (Tygh.area == 'C') {
                params = [];
                parents = $('#shipping_rates_list');
                radio = $('input[type=radio]:checked', parents);
                $.each(radio, function (id, elm) {
                  params.push({
                    name: elm.name,
                    value: elm.value
                  });
                });
                params.push({
                  name: 'tariff_id[' + ydwidget.group_id + ']',
                  value: delivery.tariffId
                });

                if (delivery.pickuppointId) {
                  params.push({
                    name: 'pickuppoint_id[' + ydwidget.group_id + ']',
                    value: delivery.pickuppointId
                  });
                } else {
                  params.push({
                    name: 'pickuppoint_id[' + ydwidget.group_id + ']',
                    value: 0
                  });
                }

                url = fn_url('checkout.checkout');

                for (i in params) {
                  url += '&' + params[i]['name'] + '=' + encodeURIComponent(params[i]['value']);
                }

                $.ceAjax('request', url, {
                  result_ids: 'shipping_rates_list,checkout_info_summary_*,checkout_info_order_info_*',
                  method: 'get',
                  full_render: true
                });
              } else {
                var url = 'order_management.update_shipping?shipping_id=' + ydwidget.shipping_id;
                url += '&price_id=' + delivery.price_id;

                if (delivery.pickuppointId) {
                  url += '&pickuppoint_id=' + delivery.pickuppointId;
                } else {
                  url += '&pickuppoint_id=0';
                }

                if (typeof supplier_id != 'undefined') {
                  url += '&supplier_id=' + supplier_id;
                }

                url = fn_url(url);
                $.ceAjax('request', url, {
                  result_ids: result_ids
                });
              }

              ydwidget.cartWidget.close();
            }
          },
          //завершение загрузки корзинного виджета
          'onLoad': function onLoad() {
            $(document).on('click', 'input[name="yd"]', function () {
              ydwidget.group_id = $(this).data('group-id');
              ydwidget.shipping_id = $(this).data('shipping-id');
              ydwidget.width = $(this).data('width');
              ydwidget.height = $(this).data('height');
              ydwidget.length = $(this).data('length');
              ydwidget.city = $(this).data('city');
            });
            $(document).on('click', 'a[name="yd"]', function () {
              ydwidget.group_id = $(this).data('group-id');
              ydwidget.shipping_id = $(this).data('shipping-id');
              ydwidget.width = $(this).data('width');
              ydwidget.height = $(this).data('height');
              ydwidget.length = $(this).data('length');
              ydwidget.city = $(this).data('city');
            });
          }
        });
      });
    }
  });
})(Tygh, Tygh.$);