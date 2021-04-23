(function (_, $) {
  $.ceEvent('on', 'ce.commoninit', function (context) {
    var $mapTables = $(context).find('.cm-commerceml-map-table');

    if (!$mapTables.length) {
      return;
    }

    $mapTables.each(function () {
      var $mapTable = $(this),
          $featureSelectElems = $mapTable.find('.cm-commerceml-map-product-feature'),
          $featureVariantSelectElems = $mapTable.find('.cm-commerceml-map-product-feature-variant');
      $featureSelectElems.each(function () {
        var $featureSelectElem = $(this),
            externalId = $featureSelectElem.data('externalId');
        $featureSelectElem.find('.cm-object-picker').on('change', function () {
          var $featurePicker = $(this);
          $featureVariantSelectElems.each(function () {
            var $featureVariantSelectElem = $(this);

            if ($featureVariantSelectElem.data('featureExternalId') !== externalId) {
              return;
            }

            $featureVariantSelectElem.find('.cm-object-picker').val(0).trigger('change').ceObjectPicker('extendSearchRequestData', {
              feature_id: $featurePicker.val()
            });
          });
        });
      });
    });
  });
})(Tygh, Tygh.$);