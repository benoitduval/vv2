(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('/event/stats', ['jquery', 'Site'], factory);
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
    // Create the chart
        $('#stats').highcharts({
            chart: {
                type: 'column'
            },
            title: {
                text: 'Basic drilldown'
            },
            xAxis: {
                type: 'category'
            },
 
            legend: {
                enabled: false
            },
 
            plotOptions: {
                series: {
                    borderWidth: 0,
                    dataLabels: {
                        enabled: true,
                    }
                }
            },

            series: [{
                name: 'Positions',
                colorByPoint: true,
                data: [{
                    name: 'P1',
                    y: 5,
                    drilldown: 'post1'
                },
                {
                     name: 'P2',
                     y: 4,
                     drilldown: 'post2'
                 }]
            }],
            drilldown: {
                series: [{
                    id: 'post1',
                    name: 'P1',
                    data: [{
                        name: 'Players',
                        y: 4,
                        drilldown: 'cats'
                    }]
                },
                {
                    id: 'cats',
                    data: [1, 2, 3]
                }]
            }
        })
   })();

   (function () {
    // Create the chart
      $('.zone-court').on('click', function () {
        $('.zone-court').parent().removeClass('selected');
        $(this).parent().addClass('selected');
      });

      $('.zone-attack').on('click', function () {
        var test = {
          '#to-p1': Math.floor(Math.random() * 100),
          '#to-p2': Math.floor(Math.random() * 100),
          '#to-p3': Math.floor(Math.random() * 100),
          '#to-p4': Math.floor(Math.random() * 100),
          '#to-p5': Math.floor(Math.random() * 100),
          '#to-p6': Math.floor(Math.random() * 100),
        };

        $.each( test, function( key, value ) {
          $(key).asPieProgress('go', value);
        });
      });

      $('.zone-to').on('click', function () {
        var test = {
          '#from-p1': Math.floor(Math.random() * 100),
          '#from-p2': Math.floor(Math.random() * 100),
          '#from-p3': Math.floor(Math.random() * 100),
          '#from-p4': Math.floor(Math.random() * 100),
          '#from-p5': Math.floor(Math.random() * 100),
          '#from-p6': Math.floor(Math.random() * 100),
        };

        $.each( test, function( key, value ) {
          $(key).asPieProgress('go', value);
        });
      });
   })();
});