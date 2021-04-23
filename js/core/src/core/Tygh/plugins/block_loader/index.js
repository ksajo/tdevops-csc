import { createPlugin } from "../../core_methods";
import $ from "jquery";

function getMultiScripts(arr) {
  var _arr = $.map(arr, function (scr) {
    return $.getScript(scr);
  });

  _arr.push($.Deferred(function (deferred) {
    $(deferred.resolve);
  }));

  return $.when.apply($, _arr);
}

const blockLoaderMethods = {
  load() {
    $('.cm-block-loader').each((i, context) => {
      const classes = context.classList;
      let caObjectKey;
      classes.forEach(function(className) {
        if (className.indexOf('cm-block-loader--') === 0) {
          caObjectKey = className.split('--')[1];
        }
      });

      if (caObjectKey === undefined) {
        return;
      }

      const newContext = $(`<div class="cm-block-loader" data-ca-object-key="${caObjectKey}"></div>`);
      $(context).after(newContext);
      $(context).remove();

      $.ceAjax(
        'request',
        fn_url(`block_manager.render&object_key=${encodeURIComponent(caObjectKey)}`),
        {
          method: 'get',
          callback: blockLoaderMethods.processResponse(newContext),
          hidden: true
        }
      );
    });
  },

  processResponse(context) {
    return response => {
      const content = $(response.block_content);
      content.toggleClass('cm-block-loaded');

      let scripts = [];
      content.find('script').each((i, script) => {
        script.src ? scripts.push(script.src) : null;
      });

      getMultiScripts(scripts)
        .done(function () {
          content.find('script[src]').remove();

          $('.cm-block-loaded', $(context)).remove();
          $(context).append(content);

          $.commonInit(context);
        });
    };
  },
};

export const ceBlockLoaderInit = function () {
  createPlugin(
    'ceBlockLoader',
    blockLoaderMethods,
    'ce.block_loader'
  )
};
