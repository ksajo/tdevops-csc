(function (_, $) {
  $.ceEvent('on', 'ce.commoninit', function (context) {
    // find all processing elements, and set processing handler once
    context.find('.cm-processing-personal-data').each(function (index, target) {
      var $elm = $(target);

      if ($elm.prop('personal_data_processing') == undefined) {
        if ($elm.data('caProcessingPersonalDataWithoutClick')) {
          var showMsg = true;
          pdpProcessingHandler(null, true, $elm);
        } else {
          $elm.on('click', pdpProcessingHandler);
        }
      }

      $elm.prop('personal_data_processing', true);
    });

    function pdpProcessingHandler(event, isHideLoader, elm) {
      var $elm = elm || $(this);
      var $placeholder = $elm.find('.cm-block-add-subscribe');
      var $subscribePolicyBlock = $elm.find('.cm-subscribe-policy');
      var $subscribePersonalDataBlock = $elm.find('.cm-subscribe-personal-data');
      var autoclicked = isHideLoader || false;
      var autoclickedClassName = 'ty-footer-form-block-policy__input--autoclicked';
      var allowRequest = !$subscribePolicyBlock.length && $subscribePersonalDataBlock.length < 1 && !$elm.data('caIsLoadingPolicy');

      if (allowRequest) {
        $elm.data('caIsLoadingPolicy', true);
        $.ceAjax('request', fn_url('personal_data.subscribe_policy'), {
          method: 'get',
          callback: pdpSuccessHandler,
          hidden: autoclicked,
          data: {
            autoclicked: autoclicked
          }
        });
      }

      function pdpSuccessHandler(data, params, text) {
        $elm.data('caIsLoadingPolicy', false);
        var response = $(text);

        if (event === null) {
          response.addClass(autoclickedClassName);
        }

        var processingWhenAutoclicked = response.data('caProcessingPersonalDataAllowAutoclick') && autoclicked;

        if (processingWhenAutoclicked || !autoclicked) {
          response.insertBefore($placeholder);
        } // if this happen in dialog, resize dialog


        setTimeout(function () {
          $.ceDialog('get_last').ceDialog('resize');
        }, 100);
      }
    }
  });
})(Tygh, Tygh.$);