(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('/forms/advanced', ['jquery', 'Site'], factory);
  } else if (typeof exports !== "undefined") {
    factory(require('jquery'), require('Site'));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.formsAdvanced = mod.exports;
  }
})(this, function (_jquery, _Site) {
  'use strict';

  var _jquery2 = babelHelpers.interopRequireDefault(_jquery);

  (0, _jquery2.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  });

  (function () {
    (0, _jquery2.default)('#inputGroupName').on('change', function (e) {
      var groupNames = JSON.parse($(this).attr('data-groups'));
      var val = $(this).val()
              .trim()
              .toLowerCase()
              .replace(/ /g,'-')
              .replace(/[^\w-]+/g,'');

      if (jQuery.inArray(val, groupNames) !== -1) {
        $(this).addClass('form-control-danger');
        $(this).removeClass('form-control-success');
        $('.error').removeClass('hidden');
        $('#submit-form').addClass('disabled');
      } else {
        $(this).removeClass('form-control-danger');
        $(this).addClass('form-control-success');
        $('.error').addClass('hidden');
        $('#submit-form').removeClass('disabled');
      }
    });

    (0, _jquery2.default)('#inputTokenfieldEvents').on('tokenfield:createtoken', function (e) {
      var data = e.attrs.value.split('|');
      e.attrs.value = data[1] || data[0];
      e.attrs.label = data[1] ? data[0] + ' (' + data[1] + ')' : data[0];
    }).on('tokenfield:createdtoken', function (e) {
      // Über-simplistic e-mail validation
      var re = /\S+@\S+\.\S+/;
      var valid = re.test(e.attrs.value);
      if (!valid) {
        (0, _jquery2.default)(e.relatedTarget).addClass('invalid');
      }
    }).on('tokenfield:edittoken', function (e) {
      if (e.attrs.label !== e.attrs.value) {
        var label = e.attrs.label.split(' (');
        e.attrs.value = label[0] + '|' + e.attrs.value;
      }
    }).on('tokenfield:removedtoken', function (e) {
      if (e.attrs.length > 1) {
        var values = _jquery2.default.map(e.attrs, function (attrs) {
          return attrs.value;
        });
      }
    }).tokenfield();
  })();
});