(function (_, $) {
  $.ceEvent('on', 'ce:yandexMetrika:providerReady', function () {
    _.yandexMetrika.provider.setupHitHandlers();

    _.yandexMetrika.provider.setupReachGoalHandlers();

    _.yandexMetrika.provider.load();
  });
  $.ceEvent('on', 'ce:yandexMetrika:dependencyLoaded', function () {
    try {
      _.yandexMetrika.provider.run();

      _.yandexMetrika.provider.setupEcommerceHandlers();
    } catch (err) {}
  });
})(Tygh, Tygh.$);