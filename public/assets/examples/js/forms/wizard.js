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

  // Example Wizard Pager
  // --------------------------
  (function () {
    var defaults = Plugin.getDefaults("wizard");

    var options = _jquery2.default.extend(true, {}, defaults, {
      step: '.wizard-pane',
      templates: {
        buttons: function buttons() {
          var options = this.options;
          var html = '<div class="btn-group btn-group-sm">' + '<a class="btn btn-default btn-outline" href="#' + this.id + '" data-wizard="back" role="button">' + options.buttonLabels.back + '</a><a class="btn btn-danger btn-outline" href="#" role="button"><i class="icon wb-trash"></i> Last Point</a></div>';
          return html;
        }
      },
      buttonLabels: {
        next: '<i class="icon wb-chevron-right" aria-hidden="true"></i>',
        back: '<i class="icon wb-chevron-left" aria-hidden="true"></i>',
        finish: '<i class="icon wb-check" aria-hidden="true"></i>'
      },

      buttonsAppendTo: '.panel-actions'
    });

    $('.btn-counter').each(function () {
        $(this).on('click', function() {
            var input = $(this).find('input');
            var value = input.val();
            value = parseInt(value) + 1;
            input.val(value);
            var counter = $(this).find('.counter');
            counter.html(value);
        });
    });

    $('#point-them').on('click', function() {
        $('#attack-us').addClass('hidden');
        $('#attack-them').removeClass('hidden');
        $('#attack-fault-them').addClass('hidden');
        $('#attack-fault-us').removeClass('hidden');
    });

    $('#point-us').on('click', function() {
        $('#attack-us').removeClass('hidden');
        $('#attack-them').addClass('hidden');
        $('#attack-fault-us').addClass('hidden');
        $('#attack-fault-them').removeClass('hidden');
    });

    $('#attack-fault-us').on('click', function() {
        $('#attack-point-detail').addClass('hidden');
        $('#attack-fault-detail').removeClass('hidden');
    });

    $('#attack-us').on('click', function() {
        $('#attack-point-detail').removeClass('hidden');
        $('#attack-fault-detail').addClass('hidden');
    });

    (0, _jquery2.default)("#statsWizard").wizard(options);
    $('.btn-next').on('click', function() {
      (0, _jquery2.default)("#statsWizard").wizard('next');
    });

    $('.btn-avatar').each(function () {
        $(this).on('click', function() {
            var input = $(this).find('input');
            input.attr('checked', 'checked');
        });
    });
  })();
});