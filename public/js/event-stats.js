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

      var percentColors = [
        { pct: 0.0, color: { r: 56, g: 246, b: 255 } },
        { pct: 0.25, color: { r: 48, g: 232, b: 188 } },
        { pct: 0.5, color: { r: 52, g: 255, b: 149 } },
        { pct: 0.75, color: { r: 39, g: 232, b: 73 } },
        { pct: 1.0, color: { r: 80, g: 255, b: 56 } } ];

      var percentRedColors = [
          { pct: 0, color: { r: 255, g: 198, b: 58 } },
          { pct: 0.25, color: { r: 232, g: 149, b: 36 } },
          { pct: 0.5, color: { r: 255, g: 123, b: 33 } },
          { pct: 0.75, color: { r: 232, g: 77, b: 31 } },
          { pct: 1.0, color: { r: 255, g: 50, b: 37 } }];

      var getColorForPercentage = function(pct, color = 'green') {
          if (color == 'red') {
            color = percentRedColors;
          } else {
            color = percentColors;
          }
          for (var i = 1; i < color.length - 1; i++) {
              if (pct < color[i].pct) {
                  break;
              }
          }
          var lower = color[i - 1];
          var upper = color[i];
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

    var color = 'green';
    $.each($('div[data-plugin="pieProgress"]'), function() {
      var value = $(this).attr('aria-valuenow');
      if ($.inArray( $(this).attr('id'), ['toOutNet', 'toOutRight', 'toOutLeft', 'toOutLong'] ) !== -1) {
        color = 'red';
      } else {
        color = 'green';
      }

      $(this).find('path').attr('stroke', getColorForPercentage(value / 100, color));
    });
  });

   // --------------------------
   (function () {

      var set1Us = $('#match-comparaison').attr('data-set1-us');
      var set1Them = $('#match-comparaison').attr('data-set1-them');
      var set2Us = $('#match-comparaison').attr('data-set2-us');
      var set2Them = $('#match-comparaison').attr('data-set2-them');
      var set3Us = $('#match-comparaison').attr('data-set3-us');
      var set3Them = $('#match-comparaison').attr('data-set3-them');
      var set4Us = $('#match-comparaison').attr('data-set4-us');
      var set4Them = $('#match-comparaison').attr('data-set4-them');
      var set5Us = $('#match-comparaison').attr('data-set5-us');
      var set5Them = $('#match-comparaison').attr('data-set5-them');
      var chart = Highcharts.chart('match-comparaison', {

          chart: {
              type: 'column'
          },

          title: {
              text: ''
          },

          xAxis: {
              categories: ['Kills', 'Attack Fault', 'Aces', 'Service Fault', 'Blocks', 'Defence Fault', 'Faults']
          },

          yAxis: {
              allowDecimals: false,
              min: 0,
              title: {
                  text: ''
              }
          },

          tooltip: {
              formatter: function () {
                  return '<b>' + this.x + '</b><br/>' +
                      this.series.name + ': ' + this.y + '<br/>' +
                      'Total: ' + this.point.stackTotal;
              }
          },

          plotOptions: {
              column: {
                  stacking: 'normal'
              }
          },
          credits: {
            enabled: false
          },

          series: [{
              name: 'Set 5 - Us',
              data: JSON.parse(set5Us),
              stack: 'Us',
              color: '#80FF15',
              dataLabels: {
                  enabled: true,
                  color: '#FFFFFF',
                  align: 'center',
                  style: {
                      fontSize: '13px',
                      fontFamily: 'Verdana, sans-serif'
                  },
                  formatter:function(){
                      if(this.y > 0)
                          return this.y;
                  }
              }
          }, {
              name: 'Set 4 - Us',
              data: JSON.parse(set4Us),
              stack: 'Us',
              color: '#21E827',
              dataLabels: {
                  enabled: true,
                  color: '#FFFFFF',
                  align: 'center',
                  style: {
                      fontSize: '13px',
                      fontFamily: 'Verdana, sans-serif'
                  },
                  formatter:function(){
                      if(this.y > 0)
                          return this.y;
                  }
              }
          }, {
              name: 'Set 3 - Us',
              data: JSON.parse(set3Us),
              stack: 'Us',
              color: '#31FF8A',
              dataLabels: {
                  enabled: true,
                  color: '#FFFFFF',
                  align: 'center',
                  style: {
                      fontSize: '13px',
                      fontFamily: 'Verdana, sans-serif'
                  },
                  formatter:function(){
                      if(this.y > 0)
                          return this.y;
                  }
              }
          }, {
              name: 'Set 2 - Us',
              data: JSON.parse(set2Us),
              stack: 'Us',
              color: '#21E8C7',
              dataLabels: {
                  enabled: true,
                  color: '#FFFFFF',
                  align: 'center',
                  style: {
                      fontSize: '13px',
                      fontFamily: 'Verdana, sans-serif'
                  },
                  formatter:function(){
                      if(this.y > 0)
                          return this.y;
                  }
              }
          }, {
              name: 'Set 1 - Us',
              data: JSON.parse(set1Us),
              stack: 'Us',
              color: '#19CDFF',
              dataLabels: {
                  enabled: true,
                  color: '#FFFFFF',
                  align: 'center',
                  style: {
                      fontSize: '13px',
                      fontFamily: 'Verdana, sans-serif'
                  },
                  formatter:function(){
                      if(this.y > 0)
                          return this.y;
                  }
              }
          },{
              name: 'Set 5 - Them',
              data: JSON.parse(set5Them),
              stack: 'Them',
              color: '#FF129E',
              dataLabels: {
                  enabled: true,
                  color: '#FFFFFF',
                  align: 'center',
                  style: {
                      fontSize: '13px',
                      fontFamily: 'Verdana, sans-serif'
                  },
                  formatter:function(){
                      if(this.y > 0)
                          return this.y;
                  }
              }
          }, {
              name: 'Set 4 - Them',
              data: JSON.parse(set4Them),
              stack: 'Them',
              color: '#E8261F',
              dataLabels: {
                  enabled: true,
                  color: '#FFFFFF',
                  align: 'center',
                  style: {
                      fontSize: '13px',
                      fontFamily: 'Verdana, sans-serif'
                  },
                  formatter:function(){
                      if(this.y > 0)
                          return this.y;
                  }
              }
          }, {
              name: 'Set 3 - Them',
              data: JSON.parse(set3Them),
              stack: 'Them',
              color: '#FF612E',
              dataLabels: {
                  enabled: true,
                  color: '#FFFFFF',
                  align: 'center',
                  style: {
                      fontSize: '13px',
                      fontFamily: 'Verdana, sans-serif'
                  },
                  formatter:function(){
                      if(this.y > 0)
                          return this.y;
                  }
              }
          }, {
              name: 'Set 2 - Them',
              data: JSON.parse(set2Them),
              stack: 'Them',
              color: '#E8791F',
              dataLabels: {
                  enabled: true,
                  color: '#FFFFFF',
                  align: 'center',
                  style: {
                      fontSize: '13px',
                      fontFamily: 'Verdana, sans-serif'
                  },
                  formatter:function(){
                      if(this.y > 0)
                          return this.y;
                  }
              }
          }, {
              name: 'Set 1 - Them',
              data: JSON.parse(set1Them),
              stack: 'Them',
              color: '#FFA916',
              dataLabels: {
                  enabled: true,
                  color: '#FFFFFF',
                  align: 'center',
                  style: {
                      fontSize: '13px',
                      fontFamily: 'Verdana, sans-serif'
                  },
                  formatter:function(){
                      if(this.y > 0)
                          return this.y;
                  }
              }
          }]
      });

      var set1Button = $('#set1Button');
      var set2Button = $('#set2Button');
      var set3Button = $('#set3Button');
      var set4Button = $('#set4Button');
      var set5Button = $('#set5Button');
      var seriesSet5Us = chart.series[0];
      var seriesSet4Us = chart.series[1];
      var seriesSet3Us = chart.series[2];
      var seriesSet2Us = chart.series[3];
      var seriesSet1Us = chart.series[4];
      var seriesSet5Them = chart.series[5];
      var seriesSet4Them = chart.series[6];
      var seriesSet3Them = chart.series[7];
      var seriesSet2Them = chart.series[8];
      var seriesSet1Them = chart.series[9];
      set1Button.click(function () {
          if (seriesSet1Them.visible) {
              seriesSet1Them.hide();
              seriesSet1Us.hide();
              set1Button.html('<i class="icon wb-eye-close"></i> Set1');
          } else {
              seriesSet1Them.show();
              seriesSet1Us.show();
              set1Button.html('<i class="icon wb-eye"></i> Set1');
          }
      });

      set2Button.click(function () {
          if (seriesSet2Them.visible) {
              seriesSet2Them.hide();
              seriesSet2Us.hide();
              set2Button.html('<i class="icon wb-eye-close"></i> Set2');
          } else {
              seriesSet2Them.show();
              seriesSet2Us.show();
              set2Button.html('<i class="icon wb-eye"></i> Set2');
          }
      });

      set3Button.click(function () {
          if (seriesSet3Them.visible) {
              seriesSet3Them.hide();
              seriesSet3Us.hide();
              set3Button.html('<i class="icon wb-eye-close"></i> Set3');
          } else {
              seriesSet3Them.show();
              seriesSet3Us.show();
              set3Button.html('<i class="icon wb-eye"></i> Set3');
          }
      });

      set4Button.click(function () {
          if (seriesSet4Them.visible) {
              seriesSet4Them.hide();
              seriesSet4Us.hide();
              set4Button.html('<i class="icon wb-eye-close"></i> Set4');
          } else {
              seriesSet4Them.show();
              seriesSet4Us.show();
              set4Button.html('<i class="icon wb-eye"></i> Set4');
          }
      });

      set5Button.click(function () {
          if (seriesSet5Them.visible) {
              seriesSet5Them.hide();
              seriesSet5Us.hide();
              set5Button.html('<i class="icon wb-eye-close"></i> Set5');
          } else {
              seriesSet5Them.show();
              seriesSet5Us.show();
              set5Button.html('<i class="icon wb-eye"></i> Set5');
          }
      });

      $("#historyModal, #userStats").on("show.bs.modal", function(e) {
          var link = $(e.relatedTarget);
          $(this).find(".modal-body").load(link.attr("href"), function() {

            var recepData = $('#chart-reception').attr('data-score');
            Highcharts.chart('chart-reception', {

                title: {
                    text: 'Receptions Quality'
                },

                yAxis: {
                    title: {
                        text: ''
                    }
                },
                legend: {
                    enabled: false
                },

                labels: {
                   enabled:false
                },

                credits: {
                  enabled: false
                },

                series: [{
                    data: JSON.parse(recepData)
                }],

                responsive: {
                    rules: [{
                        condition: {
                            maxWidth: 500
                        },
                        chartOptions: {
                            legend: {
                                layout: 'horizontal',
                                align: 'center',
                                verticalAlign: 'bottom'
                            }
                        }
                    }]
                }

            });

            var serviceData = $('#service-reception').attr('data-score');
            Highcharts.chart('service-reception', {

                title: {
                    text: 'Service Quality'
                },

                yAxis: {
                    title: {
                        text: ''
                    }
                },
                legend: {
                    enabled: false
                },

                labels: {
                   enabled:false
                },

                credits: {
                  enabled: false
                },

                series: [{
                    data: JSON.parse(serviceData)
                }],

                responsive: {
                    rules: [{
                        condition: {
                            maxWidth: 500
                        },
                        chartOptions: {
                            legend: {
                                layout: 'horizontal',
                                align: 'center',
                                verticalAlign: 'bottom'
                            }
                        }
                    }]
                }

            });

            $('.rating').each(function() {
              $(this).raty({
                targetKeep: true,
                score: $(this).attr('data-score'),
                number: $(this).attr('data-number'),
                hints: ['', '', '', '', ''],
                readOnly: true,
                icon: 'font',
                starType: 'i',
                starOff: 'icon wb-star',
                starOn: 'icon wb-star orange-600',
                cancelOff: 'icon wb-minus-circle',
                cancelOn: 'icon wb-minus-circle orange-600',
                starHalf: 'icon wb-star-half orange-500'
              });
            });
          });
      });

      var percentColors = [
          { pct: 0.0, color: { r: 56, g: 246, b: 255 } },
          { pct: 0.25, color: { r: 48, g: 232, b: 188 } },
          { pct: 0.5, color: { r: 52, g: 255, b: 149 } },
          { pct: 0.75, color: { r: 39, g: 232, b: 73 } },
          { pct: 1.0, color: { r: 80, g: 255, b: 56 } } ];

      var percentRedColors = [
          { pct: 0, color: { r: 255, g: 198, b: 58 } },
          { pct: 0.25, color: { r: 232, g: 149, b: 36 } },
          { pct: 0.5, color: { r: 255, g: 123, b: 33 } },
          { pct: 0.75, color: { r: 232, g: 77, b: 31 } },
          { pct: 1.0, color: { r: 255, g: 50, b: 37 } }];

      var getColorForPercentage = function(pct, color = 'green') {
          if (color == 'red') {
            color = percentRedColors;
          } else {
            color = percentColors;
          }
          for (var i = 1; i < color.length - 1; i++) {
              if (pct < color[i].pct) {
                  break;
              }
          }
          var lower = color[i - 1];
          var upper = color[i];
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

      $('.post-selection').on('change', function() {
        if ($(this).prop('value') == 'none') {
          $('input[type=checkbox]').prop('checked', false);
        } else if ($(this).prop('value') == 'all') {
          $('input[type=checkbox]').prop('checked', 'checked');
        } else {
          $('input[type=checkbox]').prop('checked', false);
          var checkbox = $('.' + $(this).prop('value'));
          checkbox.prop('checked', 'checked');
        }
      });

      $("#stats-filter-submit").click(function(e) {
        $('td.selected').removeClass('selected');

        var userIds = [];
        $("input.user-checkbox:checked").each(function() {
          userIds.push($(this).val());
        });

        var setterPosition = null;
        $(".setter-position").each(function() {
          setterPosition = $(".setter-position").prop('value');
        });

        var type = [];
        $("input.type:checked").each(function() {
          type.push($(this).val());
        });

        var eventId = $('#user-selection').attr('data-event-id');
        var url = '/api/stats/event/' + eventId;

        var request = $.ajax({
            type: "GET",
            url: url,
            data: {
              'userIds': JSON.stringify(userIds),
              'type': JSON.stringify(type),
              'setter': setterPosition,
            }
        }).done(function(resp) {
          var color = 'green';  
          $('#stats-repartition').attr('data-stats', resp);
          var stats = JSON.parse(resp);

          var statsAllFrom = stats.allFrom;
          $.each(statsAllFrom, function( key, value ) {
            if ($.inArray( key, ['toOutNet', 'toOutRight', 'toOutLeft', 'toOutLong'] ) !== -1) {
              color = 'red';
            } else {
              color = 'green';
            }
            $('#' + key).find('path').attr('stroke', getColorForPercentage(value / 100, color));
            $('#' + key).asPieProgress('go', value);
          });

          var statsAllTo = stats.allTo;
          $.each(statsAllTo, function( key, value ) {
            if ($.inArray( key, ['toOutNet', 'toOutRight', 'toOutLeft', 'toOutLong'] ) !== -1) {
              color = 'red';
            } else {
              color = 'green';
            }
            $('#' + key).find('path').attr('stroke', getColorForPercentage(value / 100, color));
            $('#' + key).asPieProgress('go', value);
          });
        });
      });

    // Create the chart
      $('.zone-court').on('click', function () {
        if ($(this).parent('td').hasClass('inactive')) return;
        $('.zone-court').parent().removeClass('selected');
        $(this).parent().addClass('selected');
      });

      $('.zone-attack').on('click', function () {
        if ($(this).parent('td').hasClass('inactive')) return;
        var stats = JSON.parse($('#stats-repartition').attr('data-stats'));
        var name = $(this).prop('id');
        var color = 'green';
        $.each( stats, function( key, data ) {
          if (key != name) return;
          $.each(data, function( key2, value ) {
            if ($.inArray( key2, ['toOutNet', 'toOutRight', 'toOutLeft', 'toOutLong'] ) !== -1) {
              color = 'red';
            } else {
              color = 'green';
            }
            $('#' + key2).find('path').attr('stroke', getColorForPercentage(value / 100, color));
            $('#' + key2).asPieProgress('go', value);
          });
        });
        $.each(['fromP1', 'fromP2', 'fromP3', 'fromP4', 'fromP5', 'fromP6'], function( k, keyFrom ) {
          $('#' + keyFrom).find('path').attr('stroke', getColorForPercentage(0));
          $('#' + keyFrom).asPieProgress('go', 0);
        });
        $(this).find('path').attr('stroke', getColorForPercentage(1));
        $(this).asPieProgress('go', 100);
      });

      $('.zone-to').on('click', function () {
        if ($(this).parent('td').hasClass('inactive')) return;
        var stats = JSON.parse($('#stats-repartition').attr('data-stats'));
        var name = $(this).prop('id');
        $.each( stats, function( key, data ) {
          if (key != name) return;
          $.each(data, function( key2, value ) {
            $('#' + key2).find('path').attr('stroke', getColorForPercentage(value / 100));
            $('#' + key2).asPieProgress('go', value);
          });
        });
        $.each(['toP1', 'toP2', 'toP3', 'toP4', 'toP5', 'toP6'], function( k, keyTo ) {
          $('#' + keyTo).find('path').attr('stroke', getColorForPercentage(0));
          $('#' + keyTo).asPieProgress('go', 0);
        });
        $(this).find('path').attr('stroke', getColorForPercentage(1));
        $(this).asPieProgress('go', 100);
      });
   })();
});