(function (_, $) {
  function fn_show_thread_dialog(thread_id, $container) {
    $container.ceDialog('open', {
      scroll: '.ty-vendor-communication-post__bottom',
      href: fn_url('vendor_communication.view?thread_id=' + thread_id),
      delayFocusTabbable: 150
    });
  }

  $(document).ready(function () {
    $(document).on('click touch', '.cm-vendor-communication-thread-dialog-opener', function (e) {
      $(this).closest('tr').find('.ty-new__label').hide(); // hide new message identifier

      e.preventDefault();
      var thread_id = $(this).data('caThreadId'),
          $container = $('#view_thread_' + thread_id);
      fn_show_thread_dialog(thread_id, $container);
      return false;
    });
    var $auto_open_dlg = $('.cm-vendor-communication-thread-dialog-auto-open').last();

    if ($auto_open_dlg.length) {
      var thread_id = $auto_open_dlg.data('caThreadId'),
          $container = $('#view_thread_auto_open_' + thread_id);
      fn_show_thread_dialog(thread_id, $container);
    }
  });
  $.ceEvent('on', 'ce.commoninit', function (context) {
    if (_.area == "A") {
      var $message_list_bottom = $(context).find('.vendor-communication-post__bottom');
    } else {
      var $message_list_bottom = $(context).find('.ty-vendor-communication-post__bottom');
    }

    if ($message_list_bottom.length) {
      $.scrollToElm($message_list_bottom);
    }
  });
})(Tygh, Tygh.$);