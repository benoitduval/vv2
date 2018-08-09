(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('/forms/wizard', ['jquery', 'Site'], factory);
  } else if (typeof exports !== "undefined") {
    factory(require('jquery'), require('Site'));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.formsWizard = mod.exports;
  }
})(this, function (_jquery, _Site) {
  'use strict';

  var _jquery2 = babelHelpers.interopRequireDefault(_jquery);

  (0, _jquery2.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  });

  // Example Wizard Accordion
  // ------------------------
  (function () {
    var defaults = Plugin.getDefaults("wizard");
    var options = _jquery2.default.extend(true, {}, defaults, {
      step: '.panel-title[data-toggle="collapse"]',
      classes: {
        step: {
          //done: 'color-done',
          error: 'color-error'
        }
      },
      templates: {
        buttons: function buttons() {
          return '<div class="panel-footer">' + defaults.templates.buttons.call(this) + '</div>';
        }
      },
      onBeforeShow: function onBeforeShow(step) {
        step.$pane.collapse('show');
      },

      onBeforeHide: function onBeforeHide(step) {
        step.$pane.collapse('hide');
      },

      onFinish: function onFinish() {
        alert('finish');
      },

      buttonsAppendTo: '.panel-collapse'
    });

    (0, _jquery2.default)("#exampleWizardAccordion").wizard(options);
  })();
});