/**
 * Enable multiple selection in admin.
 */
(function (_, $) {
  var longtap; // select an object if it has already been selected

  var reSelect = function reSelect() {
    if (!longtap) {
      return;
    }

    $('[data-ca-longtap-action]').each(function (index, item) {
      var $self = $(item);

      if ($self.data().caLongtapAction == 'setCheckBox') {
        var checkboxSelector = $self.data().caLongtapTarget;
        var $checkbox = $self.find(checkboxSelector);
        var checked = $checkbox.prop('checked');

        if (checked) {
          $self.removeClass('selected');
          longtap.selectObject(index);
        } else {
          if ($self.hasClass('selected')) {
            longtap.rejectObject(index);
          }
        }

        $checkbox.on('change', function (event) {
          if ($checkbox.prop('checked')) {
            longtap.storage.elements[index].handlersSuccess.select(event);
          } else {
            if ($self.hasClass('selected')) {
              longtap.storage.elements[index].handlersSuccess.reject(event);
            }
          }
        });
      }
    });
    $.ceEvent('trigger', 'ce.tap.toggle', [longtap.storage.selected]);
  };

  $.ceEvent('on', 'ce.commoninit', function (context) {
    if (!$(context).find('[data-ca-bulkedit-component]').length) {
      return;
    }

    function setCheckboxFlag(selfObj, targetSelector, flag) {
      selfObj.find(targetSelector).each(function (index, elm) {
        elm.checked = flag;
      });
    }

    function _check(selfObj) {
      return selfObj.hasClass('selected');
    } // initialize plugin


    longtap = $('[data-ca-longtap-action]').ceTap({
      timeout: 700,
      onStartDelay: 250,
      allowQuickMode: true,
      mouseMultipleSelection: true,
      preSuccess: function preSuccess(event, self) {
        return _check($(self));
      },
      preReject: function preReject(event, self) {
        return !_check($(self));
      },
      onStart: function onStart(event, self) {
        self.addClass('long-tap-start');
      },
      onSuccess: function onSuccess(event, self) {
        self.removeClass('long-tap-start');
        self.addClass('selected');

        if (self.data().caLongtapAction == 'setCheckBox') {
          setCheckboxFlag(self, self.data().caLongtapTarget, true);
          $('[data-ca-longtap-selected-counter=true]').text(longtap.storage.selected);
          $.ceEvent('trigger', 'ce.tap.toggle', [longtap.storage.selected]);
        }
      },
      onStop: function onStop(event, self) {
        self.removeClass('long-tap-start');
      },
      onReject: function onReject(event, self) {
        self.removeClass('long-tap-start');
        self.removeClass('selected');

        if (self.data().caLongtapAction == 'setCheckBox') {
          setCheckboxFlag(self, self.data().caLongtapTarget, false);
          $('[data-ca-longtap-selected-counter=true]').text(longtap.storage.selected);
          $.ceEvent('trigger', 'ce.tap.toggle', [longtap.storage.selected]);
        }
      }
    });
    reSelect();
  });
  $.ceEvent('on', 'ce.cm_cancel.clean_form', function ($form, $jelm) {
    if ($jelm.hasClass('bulkedit-unchanged')) {
      return;
    }

    reSelect();
  });
  $(_.doc).on('click', '.bulkedit-deselect', function () {
    var $form = $(this).closest('form');

    if ($form.find('[data-ca-bulkedit-component]').length) {
      $form.find('[data-ca-longtap-action] input[type="checkbox"]').prop('checked', false);
      reSelect();
    }
  });
})(Tygh, Tygh.$);