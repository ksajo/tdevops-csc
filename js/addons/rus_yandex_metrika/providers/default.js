(function (window, document, _, $) {
  var isCounterAvailable = function isCounterAvailable() {
    return typeof ym !== 'undefined';
  };

  $.ceEvent('on', 'ce:yandexMetrika:init', function () {
    _.yandexMetrika.provider = {
      id: 'default',
      setupHitHandlers: function setupHitHandlers() {
        $.ceEvent('on', 'ce.ajaxdone', function (elms, inline_scripts, params) {
          if (!isCounterAvailable()) {
            return;
          }

          if (params.original_url !== _.current_url) {
            ym(_.yandexMetrika.settings.id, 'hit', _.current_url);
          }
        });
      },
      setupReachGoalHandlers: function setupReachGoalHandlers() {
        $(document).on('click', 'button[type="submit"][name^="dispatch[checkout.add"]', function () {
          $.ceEvent('one', 'ce.formajaxpost_' + $(this).parents('form').prop('name'), function () {
            if (!isCounterAvailable()) {
              return;
            }

            if (!_.yandexMetrika.settings.collectedGoals.basket) {
              return;
            }

            ym(_.yandexMetrika.settings.id, 'reachGoal', 'basket', {});
          });
        });
        $(document).on('click', '.cm-submit[id^="button_wishlist"]', function () {
          $.ceEvent('one', 'ce.formajaxpost_' + $(this).parents('form').prop('name'), function () {
            if (!isCounterAvailable()) {
              return;
            }

            if (!_.yandexMetrika.settings.collectedGoals.wishlist) {
              return;
            }

            ym(_.yandexMetrika.settings.id, 'reachGoal', 'wishlist', {});
          });
        });
        $(document).on('click', 'a[id^="opener_call_request"]', function () {
          if (!isCounterAvailable()) {
            return;
          }

          if (!_.yandexMetrika.settings.collectedGoals.buy_with_one_click_form_opened) {
            return;
          }

          ym(_.yandexMetrika.settings.id, 'reachGoal', 'buy_with_one_click_form_opened', {});
        });
        $.ceEvent('on', 'ce.formajaxpost_call_requests_form_main', function () {
          if (!isCounterAvailable()) {
            return;
          }

          if (!_.yandexMetrika.settings.collectedGoals.call_request) {
            return;
          }

          ym(_.yandexMetrika.settings.id, 'reachGoal', 'call_request', {});
        });
        $.ceEvent('on', 'ce.commoninit', function () {
          var goalsSchema = _.yandexMetrika.goalsSchema;
          $.each(_.yandexMetrika.settings.collectedGoals, function (goalName) {
            if (goalsSchema.hasOwnProperty(goalName) && goalsSchema[goalName].controller && goalsSchema[goalName].controller === _.yandexMetrika.currentController && goalsSchema[goalName].mode === _.yandexMetrika.currentMode) {
              if (!isCounterAvailable()) {
                return;
              }

              ym(_.yandexMetrika.settings.id, 'reachGoal', 'order', {});
            }
          });
        });
      },
      setupEcommerceHandlers: function setupEcommerceHandlers() {
        $.ceEvent('on', 'ce.ajaxdone', function (elms, inline_scripts, params, data) {
          var products = data.yandex_metrika || {};

          if (products.added) {
            window.dataLayerYM.push({
              ecommerce: {
                add: {
                  products: products.added
                }
              }
            });
          }

          if (products.deleted) {
            window.dataLayerYM.push({
              ecommerce: {
                remove: {
                  products: products.deleted
                }
              }
            });
          }

          if (products.detail) {
            window.dataLayerYM.push({
              ecommerce: {
                detail: {
                  products: products.detail
                }
              }
            });
          }
        });
        var goalsSchema = _.yandexMetrika.goalsSchema;
        $.each(_.yandexMetrika.settings.collectedGoals, function (goalName) {
          if (goalsSchema.hasOwnProperty(goalName) && goalsSchema[goalName].controller && goalsSchema[goalName].controller === _.yandexMetrika.currentController && goalsSchema[goalName].mode === _.yandexMetrika.currentMode) {
            window.dataLayerYM.push({
              ecommerce: {
                currencyCode: _.yandexMetrika.settings.params.currencyCode,
                purchase: _.yandexMetrika.settings.params.purchase
              }
            });
          }
        });
      },
      load: function load() {
        (function (m, e, t, r, i, k, a) {
          m[i] = m[i] || function () {
            (m[i].a = m[i].a || []).push(arguments);
          };

          m[i].l = 1 * new Date();
          k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a);
        })(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js', 'ym');

        $.ceEvent('trigger', 'ce:yandexMetrika:dependencyLoaded');
      },
      run: function run() {
        window.dataLayerYM = window.dataLayerYM || [];
        _.yandexMetrika.settings.params = window.yaParams || {};
        ym(_.yandexMetrika.settings.id, 'init', _.yandexMetrika.settings);
      }
    };
    $.ceEvent('trigger', 'ce:yandexMetrika:providerReady');
  });
})(window, Tygh.doc, Tygh, Tygh.$);