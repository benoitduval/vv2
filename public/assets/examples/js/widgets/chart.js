(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('/widgets/chart', ['jquery', 'Site'], factory);
  } else if (typeof exports !== "undefined") {
    factory(require('jquery'), require('Site'));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.widgetsChart = mod.exports;
  }
})(this, function (_jquery, _Site) {
  'use strict';

  var _jquery2 = babelHelpers.interopRequireDefault(_jquery);

  // Widget Chart
  (0, _jquery2.default)(document).ready(function (jQuery) {
    (0, _Site.run)();
  });

  // Chart line Pie
  // --------------------------
  (function () {
    $('.chart-line').each(function() {
      var data = $(this).attr('data-stats');
      new Chartist.Line(this, {
        series: JSON.parse(data)
      }, {
        low: 0,
        showArea: false,
        showPoint: true,
        showLine: true,
        fullWidth: true,
        lineSmooth: false,
        chartPadding: {
          top: 4,
          right: 4,
          bottom: 5,
          left: 4
        },
        axisX: {
          showLabel: false,
          showGrid: false,
          offset: 0
        },
        axisY: {
          showLabel: false,
          showGrid: true,
          offset: 0
        }
      });
    });

    $('.chart-pie').each(function() {
      var data = $(this).attr('data-stats');
      console.log(data);
      new Chartist.Pie(this, {
        series: JSON.parse(data)
      }, {
        donut: true,
        donutWidth: 10,
        startAngle: 0,
        showLabel: false
      });
    });
  })();
});