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

    var percentColors = [
        { pct: 0.0, color: { r: 56, g: 246, b: 255 } },
        { pct: 0.25, color: { r: 48, g: 232, b: 188 } },
        { pct: 0.5, color: { r: 52, g: 255, b: 149 } },
        { pct: 0.75, color: { r: 39, g: 232, b: 73 } },
        { pct: 1.0, color: { r: 80, g: 255, b: 56 } } ];

    // var percentColors = [
    //     { pct: 0, color: { r: 255, g: 198, b: 58 } },
    //     { pct: 0.35, color: { r: 232, g: 149, b: 36 } },
    //     { pct: 0.5, color: { r: 255, g: 123, b: 33 } },
    //     { pct: 0.75, color: { r: 232, g: 77, b: 31 } },
    //     { pct: 1.0, color: { r: 255, g: 50, b: 37 } }];

    var getColorForPercentage = function(pct) {
        for (var i = 1; i < percentColors.length - 1; i++) {
            if (pct < percentColors[i].pct) {
                break;
            }
        }
        var lower = percentColors[i - 1];
        var upper = percentColors[i];
        var range = upper.pct - lower.pct;
        var rangePct = (pct - lower.pct) / range;
        var pctLower = 1 - rangePct;
        var pctUpper = rangePct;
        var color = {
            r: Math.floor(lower.color.r * pctLower + upper.color.r * pctUpper),
            g: Math.floor(lower.color.g * pctLower + upper.color.g * pctUpper),
            b: Math.floor(lower.color.b * pctLower + upper.color.b * pctUpper)
        };
        return 'rgb(' + [color.r, color.g, color.b].join(',') + ')';
    }

    // Create the chart
      $('.zone-court').on('click', function () {
        $('.zone-court').parent().removeClass('selected');
        $(this).parent().addClass('selected');
      });

      $('.zone-attack').on('click', function () {
        $('.zone-attack').find('.pie-progress-svg').removeClass('hidden');
        $('.zone-attack').find('.pie-progress-svg').addClass('hidden');
        $(this).find('.pie-progress-svg').removeClass('hidden');
        var test = {
          '#to-p1': Math.floor(Math.random() * 100),
          '#to-p2': Math.floor(Math.random() * 100),
          '#to-p3': Math.floor(Math.random() * 100),
          '#to-p4': Math.floor(Math.random() * 100),
          '#to-p5': Math.floor(Math.random() * 100),
          '#to-p6': Math.floor(Math.random() * 100),
        };

        $.each( test, function( key, value ) {
          $(key).find('path').attr('stroke', getColorForPercentage(value / 100));
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
          $(key).find('path').attr('stroke', getColorForPercentage(value / 100));
          $(key).asPieProgress('go', value);
        });
      });
   })();
});