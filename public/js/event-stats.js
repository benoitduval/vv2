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

    $.each($('div[data-plugin="pieProgress"]'), function() {
      var value = $(this).attr('aria-valuenow');
      $(this).find('path').attr('stroke', getColorForPercentage(value / 100));
    });
  });

   // --------------------------
   (function () {

      $("#historyModal").on("show.bs.modal", function(e) {
          var link = $(e.relatedTarget);
          $(this).find(".modal-body").load(link.attr("href"));
      });

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

      $("form :input").change(function() {
        $('td.selected').removeClass('selected');
        var userIds = [];
        $("input:checked").each(function() {
          userIds.push($(this).val());
        });

        var eventId = $('#user-selection').attr('data-event-id');
        var url = '/api/stats/event/' + eventId;

        var request = $.ajax({
            type: "GET",
            url: url,
            data: {'userIds': JSON.stringify(userIds)}
        }).done(function(resp) {
          $('#stats-repartition').attr('data-stats', resp);
          var stats = JSON.parse(resp);

          var statsAllFrom = stats.allFrom;
          $.each(statsAllFrom, function( key, value ) {
            $('#' + key).find('path').attr('stroke', getColorForPercentage(value / 100));
            $('#' + key).asPieProgress('go', value);
            if (value == 0) {
              $('#' + key).parent('td').addClass('inactive');
              $('#' + key).parent('td').removeClass('active');
            } else {
              $('#' + key).parent('td').addClass('active');
              $('#' + key).parent('td').removeClass('inactive');
            }
          });

          var statsAllTo = stats.allTo;
          $.each(statsAllTo, function( key, value ) {
            $('#' + key).find('path').attr('stroke', getColorForPercentage(value / 100));
            $('#' + key).asPieProgress('go', value);
            if (value == 0) {
              $('#' + key).parent('td').addClass('inactive');
              $('#' + key).parent('td').removeClass('active');
            } else {
              $('#' + key).parent('td').addClass('active');
              $('#' + key).parent('td').removeClass('inactive');
            }
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
        $.each( stats, function( key, data ) {
          if (key != name) return;
          $.each(data, function( key2, value ) {
            $('#' + key2).find('path').attr('stroke', getColorForPercentage(value / 100));
            $('#' + key2).asPieProgress('go', value);
            if (value == 0) {
              $('#' + key2).parent('td').addClass('inactive');
              $('#' + key2).parent('td').removeClass('active');
            } else {
              $('#' + key2).parent('td').addClass('active');
              $('#' + key2).parent('td').removeClass('inactive');
            }
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
            if (value == 0) {
              $('#' + key2).parent('td').addClass('inactive');
              $('#' + key2).parent('td').removeClass('active');
            } else {
              $('#' + key2).parent('td').addClass('active');
              $('#' + key2).parent('td').removeClass('inactive');
            }
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