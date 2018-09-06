(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('/App/Calendar', ['exports', 'Site', 'Config'], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports, require('Site'), require('Config'));
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports, global.Site, global.Config);
    global.AppCalendar = mod.exports;
  }
})(this, function (exports, _Site2, _Config) {
  'use strict';

  Object.defineProperty(exports, "__esModule", {
    value: true
  });
  exports.getInstance = exports.run = exports.AppCalendar = undefined;

  var _Site3 = babelHelpers.interopRequireDefault(_Site2);

  var AppCalendar = function (_Site) {
    babelHelpers.inherits(AppCalendar, _Site);

    function AppCalendar() {
      babelHelpers.classCallCheck(this, AppCalendar);
      return babelHelpers.possibleConstructorReturn(this, (AppCalendar.__proto__ || Object.getPrototypeOf(AppCalendar)).apply(this, arguments));
    }

    babelHelpers.createClass(AppCalendar, [{
      key: 'initialize',
      value: function initialize() {
        babelHelpers.get(AppCalendar.prototype.__proto__ || Object.getPrototypeOf(AppCalendar.prototype), 'initialize', this).call(this);

        this.$actionToggleBtn = $('.site-action-toggle');
        this.$addNewCalendarForm = $('#addNewCalendar').modal({
          show: false
        });
      }
    }, {
      key: 'process',
      value: function process() {
        babelHelpers.get(AppCalendar.prototype.__proto__ || Object.getPrototypeOf(AppCalendar.prototype), 'process', this).call(this);

        this.handleFullcalendar();
        this.handleEventShow();
        // this.handleEventList();
      }
    }, {
      key: 'handleFullcalendar',
      value: function handleFullcalendar() {
        var body = document.body,
            html = document.documentElement;
        var height = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight) - 170;

        var groupId = $('#calendar').attr('data-groupId');

        var myOptions = {
          selectLongPressDelay: 100,
          lazyFetching: true,
          events: {
              url: '/api/event/get/all',
              cache: true,
              type: 'GET',
              data: function() { // a function that returns an object
                  return {
                      // dynamic_value: Math.random(),
                      groupId: groupId
                  };
              }
          },
          loading: function( isLoading, view ) {
              if(isLoading) {// isLoading gives boolean value
                  $('#calendar-loading').show();
                  
              } else {
                  $('#calendar-loading').hide();
              }
          },
          height: height,
          defaultDate: new Date(),
          views: {
              month: { // name of view
                  titleFormat: 'MMM YYYY'
              }
          },
          firstDay: 1,
          eventLimit: false, // allow "more" link when too many events
          timeFormat: 'H:mm',
          header: {
            left: null,
            center: 'prev,title,next',
            right: null
          },
          selectable: true,
          selectHelper: true,
          select: function select(date) {
            if ($('#addNewEvent').lenght !== 'undefined') {
              var eventDate = new Date(date);
              var now = new Date();
              now.setHours(0,0,0,0);
              if ( eventDate.getTime() >= now.getTime()) {
                $('#addNewEvent').modal('show');
                $("#starts").datepicker("update", new Date(date));
              }
            }
          },
          editable: true,
          eventClick:  function(event, jsEvent, view) { 
            jsEvent.preventDefault();

            var myEvent = event;
            var cal = $('#calendar');
            
            var calendarModal = $('#editNewEvent');
            $("#editNewEvent").off('show.bs.modal').on("show.bs.modal", function(e) {
                var modalBody = $(this).find(".body-event");

                modalBody.html('<div class="col-12"><div class="example-wrap mt-50px"><div class="example-loading vertical-align text-center pt-100"><div class="loader vertical-align-middle loader-tadpole"></div></div></div></div>')
                
                modalBody.load('/event/detail/' + myEvent.id, function() {
                  var url = '/api/guest/response/' + myEvent.id ;
                  $('.event-response').off('click').on('click', function(e, state) {
                      var response  = $(this).attr('data-response');
                      url = url + '/' + response;
                      var request = $.ajax({
                          type: "GET",
                          url: url
                      }).done(function(resp) {
                          switch(response) {
                              case '1':
                                  myEvent.className = ['event-green'];
                                  break;
                              case '2':
                                  myEvent.className = ['event-red'];
                                  break;
                              case '3':
                                  myEvent.className = ['event-azure'];
                                  break;
                          }
                          cal.fullCalendar('updateEvent', myEvent);
                          $('#editNewEvent').modal('hide');
                      });
                  });

                  $('#submit-comment').off('click').on('click', function(e, state) {
                    // apply nl2br + htmlentities
                    var text = $('#comment-text').val().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/\n/g, '<br />');
                    var avatar = $('#comment-text').attr('data-avatar');
                    var html = '<li class="list-group-item"><div class="row"><div class="col-2"><span class="avatar"><img src="' + avatar + '";"></span></div><div class="media-body col-10"><h5 class="list-group-item-heading mt-0 mb-5"><small class="float-right">now</small> Benoit Duval</h5><p class="list-group-item-text">' + text + '</p></div></div></li>';
                    $(html).hide().prependTo("#comments-list").fadeIn();
                    $('#comment-text').val('');

                    $('#form-comment').ajaxSubmit({
                      url: '/api/event/comment',
                      type: 'post',
                      data: {
                        'eventId': myEvent.id,
                        'comment': text,
                      }
                    });
                  });

                  $("#live-score").click(function(ev) {
                      ev.preventDefault();
                      modalBody.load($(this).attr("href"));
                  });
                });
            });

            $("#editNewEvent").modal('show');
          },
          droppable: false
        };

        var _options = void 0;
        var myOptionsMobile = Object.assign({}, myOptions);

        myOptionsMobile.aspectRatio = 0.5;
        _options = $(window).outerWidth() < 667 ? myOptionsMobile : myOptions;
        $('#editNewEvent').modal();
        $('#calendar').fullCalendar(_options);
      }
    }, {
      key: 'handleEventList',
      value: function handleEventList() {
        $('#addNewEventBtn').on('click', function () {
          $('#addNewEvent').modal('show');
        });

        $('.calendar-list .calendar-event').each(function () {
          var $this = $(this),
              color = $this.data('color').split('-');
          $this.data('event', {
            title: $this.data('title'),
            stick: $this.data('stick'),
            backgroundColor: (0, _Config.colors)(color[0], color[1]),
            borderColor: (0, _Config.colors)(color[0], color[1])
          });
          $this.draggable({
            zIndex: 999,
            revert: true,
            revertDuration: 0,
            appendTo: '.page',
            helper: function helper() {
              return '<a class="fc-day-grid-event fc-event fc-start fc-end" style="background-color:' + (0, _Config.colors)(color[0], color[1]) + ';border-color:' + (0, _Config.colors)(color[0], color[1]) + '">\n          <div class="fc-content">\n            <span class="fc-title">' + $this.data('title') + '</span>\n          </div>\n          </a>';
            }
          });
        });
      }
    }, {
      key: 'handleEventShow',
      value: function handleEventShow() {
        var eventId = exports.getUrlParameter;
        if(eventId) {
          $('#calendar-loading').hide();

          var calendarModal = $('#editNewEvent');
          var modalBody = $(".body-event");
          modalBody.html('<div class="col-12"><div class="example-wrap mt-50px"><div class="example-loading vertical-align text-center pt-100"><div class="loader vertical-align-middle loader-tadpole"></div></div></div></div>')

          var url = '/api/guest/response/' + eventId ;
          modalBody.load('/event/detail/' + eventId, function() {
            $('.event-response').off('click').on('click', function(e, state) {
              var response  = $(this).attr('data-response');
              url = url + '/' + response;
              var request = $.ajax({
                  type: "GET",
                  url: url
              }).done(function(resp) {
                  $('#calendar').fullCalendar('refetchEvents');
                  $('#editNewEvent').modal('hide');
              });
            });
          });
          calendarModal.modal('show');
        }
      }
    }]);
    return AppCalendar;
  }(_Site3.default);

  var instance = null;

  function getInstance() {
    if (!instance) {
      instance = new AppCalendar();
    }
    return instance;
  }

  function run() {
    var app = getInstance();
    app.run();
  }

  function getUrlParameter(sParam) {
      var sPageURL = decodeURIComponent(window.location.search.substring(1)),
          sURLVariables = sPageURL.split('&'),
          sParameterName,
          i;

      for (i = 0; i < sURLVariables.length; i++) {
          sParameterName = sURLVariables[i].split('=');

          if (sParameterName[0] === sParam) {
              return sParameterName[1] === undefined ? true : sParameterName[1];
          }
      }
  };

  exports.default = AppCalendar;
  exports.AppCalendar = AppCalendar;
  exports.getUrlParameter = getUrlParameter('eventId');
  exports.run = run;
  exports.getInstance = getInstance;
});