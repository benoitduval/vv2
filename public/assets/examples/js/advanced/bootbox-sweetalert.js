(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('/advanced/bootbox-sweetalert', ['jquery', 'Site'], factory);
  } else if (typeof exports !== "undefined") {
    factory(require('jquery'), require('Site'));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.advancedBootboxSweetalert = mod.exports;
  }
})(this, function (_jquery, _Site) {
  'use strict';

  var _jquery2 = babelHelpers.interopRequireDefault(_jquery);

  (0, _jquery2.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  });

  // Example Examples
  // ----------------
  (function () {

    (0, _jquery2.default)('#warning-confirm').on("click", function () {
      swal({
        title: "Are you sure?",
        text: "You will not be able to recover this imaginary file!",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-warning",
        confirmButtonText: 'Yes, delete it!',
        closeOnConfirm: false
        //closeOnCancel: false
      }, function () {
        swal("Deleted!", "Your imaginary file has been deleted!", "success");
      });
    });
  })();
});