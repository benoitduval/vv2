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
    var ratio = $('#ratiokillsPerFaults').attr('data-ratio');
    var labels = $('#ratiokillsPerFaults').attr('data-labels');
    Highcharts.chart('ratiokillsPerFaults', {
        chart: {
            type: 'areaspline'
        },
        title: {
            text: 'Ratio Kills / Errors'
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
            name: 'Kills / Errors',
            data: JSON.parse(ratio)
        }]
    });

    // var options = {
    //   showArea: true,
    //   low: 0,
    //   height: 300,
    //   fullWidth: true,
    //   axisX: {
    //     offset: 30
    //   },
    //   axisY: {
    //     offset: 30,
    //     labelInterpolationFnc: function labelInterpolationFnc(value) {
    //       if (value == 0) {
    //         return null;
    //       }
    //       return value;
    //     },
    //     scaleMinSpace: 50
    //   },
    //   chartPadding: {
    //     bottom: 12,
    //     left: 10
    //   },
    //   plugins: [Chartist.plugins.tooltip({
    //       transformTooltipTextFnc: function(x){
    //           return parseFloat(x).toLocaleString();
    //       }
    //   })]
    // };

    // // team total completed data
    // var ratio = $('#teamCompletedWidget .ct-chart').attr('data-ratio');

    // var series1List = {
    //   name: 'Ratio',
    //   data: JSON.parse(ratio)
    // };


    // var newScoreLineChart = function newScoreLineChart(chartId, series1List, options) {
    //   var lineChart = new Chartist.Line(chartId, {
    //     series: [series1List]
    //   }, options);

    //   //start create
    //   lineChart.on('draw', function (data) {
    //     var elem, parent;
    //     if (data.type === 'point') {
    //       elem = data.element;
    //       parent = new Chartist.Svg(elem._node.parentNode);

    //       parent.elem('line', {
    //         x1: data.x,
    //         y1: data.y,
    //         x2: data.x + 0.01,
    //         y2: data.y,
    //         "class": 'ct-point-content'
    //       });
    //     }
    //   });
    // };

    // newScoreLineChart("#teamCompletedWidget .ct-chart", series1List, options);
  })();

  // item dialog
  // -----------
  (function () {

    //handleSelective
    var handleSelective = function handleSelective(handleSelectiveItem) {
      var member = [{
        id: 'uid_1',
        name: 'Herman Beck',
        avatar: '../../../global/portraits/1.jpg'
      }, {
        id: 'uid_2',
        name: 'Mary Adams',
        avatar: '../../../global/portraits/2.jpg'
      }, {
        id: 'uid_3',
        name: 'Caleb Richards',
        avatar: '../../../global/portraits/3.jpg'
      }, {
        id: 'uid_4',
        name: 'June Lane',
        avatar: '../../../global/portraits/4.jpg'
      }];

      var items = handleSelectiveItem;

      (0, _jquery2.default)('.plugin-selective').selective({
        namespace: 'addMember',
        local: member,
        selected: items,
        buildFromHtml: false,
        tpl: {
          optionValue: function optionValue(data) {
            return data.id;
          },
          frame: function frame() {
            return '<div class="' + this.namespace + '">' + this.options.tpl.items.call(this) + '<div class="' + this.namespace + '-trigger">' + this.options.tpl.triggerButton.call(this) + '<div class="' + this.namespace + '-trigger-dropdown">' + this.options.tpl.list.call(this) + '</div>' + '</div>' + '</div>';
          },
          triggerButton: function triggerButton() {
            return '<div class="' + this.namespace + '-trigger-button"><i class="wb-plus"></i></div>';
          },
          listItem: function listItem(data) {
            return '<li class="' + this.namespace + '-list-item"><img class="avatar" src="' + data.avatar + '">' + data.name + '</li>';
          },
          item: function item(data) {
            return '<li class="' + this.namespace + '-item"><img class="avatar" src="' + data.avatar + '" title="' + data.name + '">' + this.options.tpl.itemRemove.call(this) + '</li>';
          },
          itemRemove: function itemRemove() {
            return '<span class="' + this.namespace + '-remove"><i class="wb-minus-circle"></i></span>';
          },
          option: function option(data) {
            return '<option value="' + this.options.tpl.optionValue.call(this, data) + '">' + data.name + '</option>';
          }
        }
      });
    };

    // add Item Dialog
    (0, _jquery2.default)('#addNewItemBtn').on('click', function () {
      //default handleSelectiveItem for add dialog
      var handleSelectiveItem = [{
        id: 'uid_1',
        name: 'Herman Beck',
        avatar: '../../../global/portraits/1.jpg'
      }, {
        id: 'uid_2',
        name: 'Caleb Richards',
        avatar: '../../../global/portraits/2.jpg'
      }];

      handleSelective(handleSelectiveItem);

      (0, _jquery2.default)('#addtodoItemForm').modal('show');
    });

    // edit Item Dialog
    (0, _jquery2.default)("#toDoListWidget .list-group-item input").on('click', function (e) {
      e.stopPropagation();
    });

    (0, _jquery2.default)('#toDoListWidget .list-group-item').on('click', function () {
      var oldTitle = (0, _jquery2.default)(this).find(".item-title").text();
      var dueDate = (0, _jquery2.default)(this).find(".item-due-date > span").text();
      if (dueDate == "No due date") {
        dueDate = null;
      } else {
        dueDate = "8/25/2015";
      }

      (0, _jquery2.default)("#editTitle").val(oldTitle);
      (0, _jquery2.default)("#editDueDate").val(dueDate);
      var handleSelectiveItem = [];
      handleSelective(handleSelectiveItem);

      (0, _jquery2.default)('#edittodoItemForm').modal('show');
    });
  })();
});