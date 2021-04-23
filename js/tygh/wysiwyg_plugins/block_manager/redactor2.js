(function ($) {
  $.Redactor.prototype.blockManager = function () {
    return {
      areHandlersSetup: false,
      isBlockSelected: false,
      selectBlock: function selectBlock(uid) {
        $('.cm-select-bm-block').removeClass('select-block--active');
        $('[data-ca-block-uid="' + uid + '"]').addClass('select-block--active');
        this.blockManager.isBlockSelected = true;
        this.blockManager.insert();
      },
      loadBlocks: function loadBlocks() {
        var self = this.blockManager;

        if (self.areHandlersSetup) {
          return;
        }

        $.ceEvent('on', 'dispatch_event_pre', function (e, jelm, processed) {
          if (e.type !== 'click') {
            return;
          } // select existing block


          if (jelm.hasClass('cm-select-bm-block') || jelm.parents('.cm-select-bm-block').length) {
            var $selectedBlock = jelm.hasClass('cm-select-bm-block') ? jelm : jelm.parents('.cm-select-bm-block');
            self.selectBlock($selectedBlock.data('caBlockUid'));
            processed.status = processed.to_return = true;
          } // create new block


          if (jelm.hasClass('cm-create-bm-block') || jelm.parents('.cm-create-bm-block').length) {
            var $createdBlock = jelm.hasClass('cm-create-bm-block') ? jelm : jelm.parents('.cm-create-bm-block');
            var data = $createdBlock.data(),
                blockType = data.caBlockType,
                blockName = data.caBlockName;
            var newBlockHref = 'block_manager.update_block?' + 'block_data[type]=' + blockType + '&snapping_data[grid_id]=0' + '&ajax_update=1' + '&r_result_ids=block_selection' + '&r_url=' + encodeURIComponent(fn_url('block_manager.block_selection?purpose=wysiwyg'));
            var $newBlockDialogContainer = $('#new_block_' + blockType);

            if (!$newBlockDialogContainer.length) {
              $newBlockDialogContainer = $('<div id="new_block_' + blockType + '"></div>').appendTo('body');
            }

            $newBlockDialogContainer.ceDialog('open', {
              href: fn_url(newBlockHref),
              title: Tygh.tr('add_block') + ': ' + blockName,
              destroyOnClose: true
            });
            self.isBlockSelected = false;
            processed.status = processed.to_return = true;
          } // hightlight selected block


          $.ceEvent('on', 'ce.formajaxpost_block_0_update_form', function (response) {
            if (!response.block_data || self.isBlockSelected) {
              return;
            }

            self.selectBlock(response.block_data.unique_id);
          });
        });
        self.areHandlersSetup = true;
      },
      init: function init() {
        var toolbarButton = this.button.add('blockManager', Tygh.tr('block_manager'));
        this.button.setIcon(toolbarButton, '<i class="redactor-custom-icon icon-magic icon-dark"></i>');
        this.button.addCallback(toolbarButton, this.blockManager.openPicker);
      },
      openPicker: function openPicker() {
        this.selection.save();
        this.blockManager.isBlockSelected = false;
        container = $('#block_selection');

        if (!container.length) {
          container = $('<div id="block_selection" title="' + fn_strip_tags(Tygh.tr('select_block')) + '"></div>').appendTo('body');
        }

        container.ceDialog('open', {
          self: this,
          href: fn_url('block_manager.block_selection?purpose=wysiwyg'),
          onClose: function onClose() {
            this.self.selection.restore();
          }
        });
        $.ceEvent('on', 'ce.dialogshow', function (dialog) {
          if ($(dialog).attr('id') === 'block_selection') {
            $(dialog).dialog('option').self.blockManager.loadBlocks();
          }
        });
      },
      insert: function insert() {
        var $block = $('.select-block--active', $.ceDialog('get_last')),
            blockData = $block.data();
        $.ceDialog('get_last').ceDialog('close');
        this.placeholder.hide();
        this.buffer.set();
        this.air.collapsed();
        this.selection.restore();
        this.insert.html('<p><b' + ' title="' + blockData.caBlockName + '"' + ' class="wysiwyg-block-loader cm-block-loader cm-block-loader--' + blockData.caBlockUid + '"' + ' ></b></p>');
      }
    };
  };
})(jQuery);