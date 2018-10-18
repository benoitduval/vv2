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
    if ($('.chart-line').length > 0) {

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
    }

    if ($('.chart-pie').length > 0) {    
      $('.chart-pie').each(function() {
        var data = $(this).attr('data-stats');
        new Chartist.Pie(this, {
          series: JSON.parse(data)
        }, {
          donut: true,
          donutSolid: true,
          donutWidth: 12,
          startAngle: 0,
          showLabel: false
        });
      });
    }
  })();

  // barChart
  // ------------------------------
  (function () {
    if ($('.barChart').lengtt > 0) {
      var data = $('.barChart').attr('data-stats');
      var barChart = new Chartist.Bar('.barChart', {
        labels: ['ATTACKS', 'BLOCKS'],
        series: JSON.parse(data)
      }, {
        axisX: {
          showGrid: false
        },
        axisY: {
          showGrid: true,
          scaleMinSpace: 0,
          onlyInteger: true
        },
        height: 220,
        seriesBarDistance: 24
      });

      barChart.on('draw', function (data) {
        if (data.type === 'bar') {

          // $("#ecommerceRevenue .ct-labels").attr('transform', 'translate(0 15)');
          var parent = new Chartist.Svg(data.element._node.parentNode);
          parent.elem('line', {
            x1: data.x1,
            x2: data.x2,
            y1: data.y2,
            y2: 0,
            "class": 'ct-bar-fill'
          });
        }
      });
    }
  })();
});