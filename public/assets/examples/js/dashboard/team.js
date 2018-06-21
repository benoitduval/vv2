(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('/dashboard/team', ['jquery', 'Site'], factory);
  } else if (typeof exports !== "undefined") {
    factory(require('jquery'), require('Site'));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.dashboardTeam = mod.exports;
  }
})(this, function (_jquery, _Site) {
  'use strict';

  var _jquery2 = babelHelpers.interopRequireDefault(_jquery);

  (0, _jquery2.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  });

  // Top Line Chart With Tooltips
  // ----------------------------
  (function () {
    var point = $('#ratiokillsPerFaults').attr('data-ratio-point');
    var fault = $('#ratiokillsPerFaults').attr('data-ratio-fault');
    var labels = $('#ratiokillsPerFaults').attr('data-labels');
    Highcharts.chart('ratiokillsPerFaults', {
        chart: {
            type: 'areaspline'
        },
        title: {
            text: 'Attacks'
        },
        tooltip: {
            shared: true,
            valueSuffix: '%'
        },
        xAxis: {
            categories: JSON.parse(labels),
            title: {
                text: null
            }
        },
        yAxis: {
            title: {
                text: null
            }
        },
        credits: {
            enabled: false
        },
        plotOptions: {
            areaspline: {
                fillOpacity: 0.5
            }
        },
        series: [{
            name: 'Points',
            data: JSON.parse(point)
        },
        {
          name: 'Faults',
          data: JSON.parse(fault)
        }]
    });
  })();
});