(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('/event/profile', ['jquery', 'Site'], factory);
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

   // --------------------------
   (function () {
   	$('.delete-holiday').on('click', function() {
   		var td = $(this);
   		var id = $(this).attr('data-id');
   		var request = $.ajax({
   		    type: "GET",
   		    url: '/api/user/delete-holiday/' + id,
   		}).done(function(resp) {
   			td.parents('tr').fadeTo("quick", 0,  function() { $(this).remove() });
   		});
   	});
   })();
});