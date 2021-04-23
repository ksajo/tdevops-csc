// Bulk edit
(function (_, $) {
  var _doc = $(document);

  $.ceEvent('on', 'ce.commoninit', _bulkEditInit);
  $.ceEvent('on', 'ce.tap.toggle', function (selected) {
    (selected ? $('.bulkedit-toggler') : $('.bulkedit-disabler')).trigger('click');
  });
  $.ceEvent('on', 'ce.select_template_selection', function (object, list_elm, $container) {
    if (!$container.hasClass('cm-bulk-edit-object-categories-add') || !object.data) {
      return;
    }

    $(list_elm).toggleClass('no-bold', true);
  });
  $.ceEvent('on', 'ce.select2.init', function ($elm) {
    if (!$elm.hasClass('cm-bulk-edit-object-categories-add')) {
      return;
    }

    $elm.data('select2').dropdown.$dropdownParent.addClass('fixed-select2-dropdown');
    $elm.data('select2').on('selection:update', function () {
      var categories_items = $elm.data('caCategoryItems') || {};
      var item_ids = $elm.data('caSelectedItemIds') || [];
      $elm.data('select2').$container.find('.select2__category-status-checkbox').each(function () {
        var $checkbox = $(this),
            category_id = $checkbox.data('caCategoryId'),
            checked = typeof categories_items[category_id] === 'undefined' || item_ids.length === categories_items[category_id].length;

        if (checked) {
          $checkbox.prop('defaultChecked', true).prop('checked', true);
        } else {
          $checkbox.prop('defaultChecked', false).prop('checked', false).prop('indeterminate', true).prop('readOnly', true);
        }
      });
    });
  });
  /**
   * Init function, binds events
   */

  function _bulkEditInit(context) {
    if (!$(context).find('[data-ca-bulkedit-expanded-object="true"]').length) {
      return;
    } // FIXME: Remove this code when multiple context menus on the page are fixed


    if ($('.bulkedit-dropdown--legacy').length) {
      if ($('.bulkedit-dropdown--legacy li:not(.bulkedit-action--legacy)').length) {
        $('.bulkedit-dropdown--legacy').removeClass('hide');
      }

      if ($('.bulkedit-disabled').length) {
        $('.longtap-selection .bulkedit-toggler').attr('disabled', true);
        $('.longtap-selection .cm-item.hide').removeClass('hide');
        $('.longtap-selection tr').removeAttr('data-ca-longtap-action');
        $('.longtap-selection tr').removeAttr('data-ca-longtap-target');
        $('.bulkedit-buttons-disabled .bulkedit-dropdown--legacy.hide').removeClass('hide');
        $('.bulkedit-buttons-disabled .bulkedit-action--legacy').removeClass('hide');
        return;
      }
    }

    _doc.on('click', '.bulkedit-toggler', toggleBulkEditPanel);

    _doc.on('click', '.bulkedit-disabler', toggleBulkEditPanel);

    var dispatchParameter = $('.bulkedit-toggler').data('caBulkeditDispatchParameter');

    if (dispatchParameter) {
      _doc.on('click', '.bulkedit-toggler', setDispatchParameterBulkEditBtn);
    }

    _cat(context);
  }
  /**
   * Toggling bulk edit panel
   * @param {Event} event 
   */


  function toggleBulkEditPanel(event) {
    var $self = $(this),
        $enable = $($self.data('caBulkeditEnable')),
        $disable = $($self.data('caBulkeditDisable'));
    $enable.removeClass('hidden');
    $disable.addClass('hidden');
    $('[name="check_all"]').prop('checked', false);
  }
  /**
   * Add selected ids as a parameter in dispatch
   * @param {Event} event 
   */


  function setDispatchParameterBulkEditBtn(event) {
    var $self = $(this),
        parametrElmName = $self.data('caBulkeditDispatchParameter'),
        ids = [];
    $('[name="' + parametrElmName + '"]:checked').each(function () {
      ids.push($(this).val());
    });
    $('.bulk-edit [data-ca-pass-selected-object-ids-as]').each(function () {
      var dispatch = $(this).data('caDispatch');

      if (dispatch && ids.length > 0) {
        dispatch = dispatch.replace(']', '&' + $(this).data('caPassSelectedObjectIdsAs') + '={' + ids + '}');
        $(this).attr('data-ca-dispatch', dispatch);
      }
    });
  } // Bulk edit => Categories


  function _cat(context) {
    if (context.is(document)) {
      _doc.on('click', '.bulk-edit__btn-content--category', function () {
        if ($($(this).data('toggle')).hasClass('open')) {
          _updateCategoriesDropdown();
        } else {
          $('.bulk-edit--overlay').remove();
          $($(this).data('toggle')).toggleClass('open', false);
        }
      });

      $(_.doc).on('click', '[data-ca-bulkedit-mod-cat-cancel]', _resetter);
      $(_.doc).on('click', '[data-ca-bulkedit-mod-cat-update]', _applyNewCategories);
    }
  }
  /**
   * Update categories lists in dropdown (from backend)
   */


  function _updateCategoriesDropdown() {
    var $applyBtn = $('[data-ca-bulkedit-mod-cat-update]', _doc),
        $form = $($applyBtn.data('caBulkeditModTargetForm')),
        $selectedNodes = $form.find($applyBtn.data('caBulkeditModTargetFormActiveObjects')),
        $selecbox = $('#bulk_edit_categories_list_content').find('.cm-bulk-edit-object-categories-add'),
        categories_items = {},
        category_ids = [],
        item_ids = [],
        categories = [];
    $selecbox.val(null).empty().trigger('change');
    $selectedNodes.each(function (i, node) {
      var item_category_ids = $(node).data('caCategoryIds');
      var item_id = $(node).data('caId');

      for (var j in item_category_ids) {
        var category_id = item_category_ids[j];
        categories_items[category_id] = categories_items[category_id] || [];

        if (categories_items[category_id].indexOf(item_id) === -1) {
          categories_items[category_id].push(item_id);
        }

        if (category_ids.indexOf(category_id) === -1) {
          category_ids.push(category_id);
          categories.push({
            category: '',
            category_id: category_id
          });
        }
      }

      item_ids.push(item_id);
    });
    $selecbox.data('caCategoryItems', categories_items);
    $selecbox.data('caSelectedItemIds', item_ids);
    $.ceEvent('trigger', 'ce.select2_categories.add_categories', [categories, $selecbox]);
  }
  /**
   * Resets fields in dropdown
   * @param {Event} event 
   */


  function _resetter(event) {
    _updateCategoriesDropdown();

    event.preventDefault();
  }

  function _applyNewCategories(event) {
    event.preventDefault();
    var categoriesMap = {
      A: [],
      D: []
    },
        $self = $(this),
        itemIds = [],
        checkboxes = $('.cm-tristate', '.bulk-edit--reset-dropdown-menu'),
        selectedItems = $('.cm-longtap-target.selected');
    canAllCatBeDeleted = $self.data('caBulkeditModCanAllCategoriesBeDeleted'), dispatch = $self.data('caBulkeditModDispatch'), objectType = $self.data('caBulkeditModObjectType') ? $self.data('caBulkeditModObjectType') : 'items', resultIds = $self.data('caBulkeditModResultIds') ? $self.data('caBulkeditModResultIds') : ''; // calculate categories statuses map

    $.each(checkboxes, function (i, elm) {
      var jelm = $(elm);

      if (elm.indeterminate) {
        return;
      }

      if (elm.checked) {
        categoriesMap.A.push(jelm.data('caCategoryId'));
      } else {
        categoriesMap.D.push(jelm.data('caCategoryId'));
      }
    });

    if (!canAllCatBeDeleted && categoriesMap.D.length == checkboxes.length) {
      alert(_.tr('unable_to_delete_all_categories'));
      return;
    } // calculate current selected items


    $.each(selectedItems, function (i, elm) {
      itemIds.push($(elm).data('caId'));
    });
    var data = {};
    data['dispatch'] = dispatch;
    data['redirect_url'] = _.current_url;
    data['categories_map'] = categoriesMap;
    data["".concat(objectType, "_ids")] = itemIds;
    $.ceAjax('request', fn_url(''), {
      caching: false,
      method: 'POST',
      full_render: 'Y',
      result_ids: resultIds,
      data: data,
      callback: function callback() {
        $.each(selectedItems, function (i, elm) {
          var new_category_ids = [];
          var $elm = $(elm);
          var category_ids = $elm.data('caCategoryIds');
          category_ids = category_ids.concat(categoriesMap.A);

          for (var j in category_ids) {
            category_ids[j] = parseInt(category_ids[j]);

            if (categoriesMap.D.indexOf(category_ids[j]) === -1) {
              new_category_ids.push(category_ids[j]);
            }
          }

          $elm.data('caCategoryIds', new_category_ids);
        });

        _updateCategoriesDropdown();
      }
    });
  } // Bulk edit => Categories

})(Tygh, Tygh.$); // Bulk edit => Custom tristate checkbox


(function (_, $) {
  $(document).on('click', '.cm-readonly', function (e) {
    e.preventDefault();
  });
  $.ceEvent('on', 'ce.commoninit', function (context) {
    $('[data-set-indeterminate="true"]', $(context)).prop('indeterminate', true);
  });
  $(document).on('mouseup', '.cm-tristate', function (e) {
    e.preventDefault();
    var scope = this;
    setTimeout(function () {
      _onclick.call(scope);
    }, 1);
  });

  function _onclick() {
    if ($(this).data('caTristateJustClick')) {
      return;
    }

    var elm = $(this).get(0);
    if (elm.readOnly) elm.checked = elm.readOnly = false;else if (!elm.checked) elm.readOnly = elm.indeterminate = true;
  }
})(Tygh, Tygh.$); // Bulk edit => Custom dropdown


(function (_, $) {
  $(document).on('click', '.bulk-edit-toggle', function () {
    $($(this).data('toggle')).toggleClass('open');
    var scope = this;
    $('.bulk-edit--overlay').one('click', function () {
      $($(scope).data('toggle')).toggleClass('open', false);
    });
  });
  $(document).on('click', '.cm-toggle', function (e) {
    var self = $(this);

    if (self.data('state') == 'show') {
      self.data('state', 'hide');
      self.html(self.data('hideText'));
      $(self.data('toggle')).toggleClass('hidden', false);
    } else {
      self.data('state', 'show');
      self.html(self.data('showText'));
      $(self.data('toggle')).toggleClass('hidden', true);
    }

    e.preventDefault();
  });
})(Tygh, Tygh.$);