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
        this.handleEventList();
      }
    }, {
      key: 'handleFullcalendar',
      value: function handleFullcalendar() {
        var body = document.body,
            html = document.documentElement;
        var height = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight) - 170;

        var groupId = $('#calendar').attr('data-groupId');

        var myOptions = {
          lazyFetching: true,
          events: {
              url: '/api/event/get/all',
              cache: true,
              type: 'GET',
              data: function() { // a function that returns an object
                  return {
                      dynamic_value: Math.random(),
                      groupId: groupId
                  };
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
          select: function select() {
            $('#addNewEvent').modal('show');
          },
          editable: true,
          eventClick:  function(event, jsEvent, view) {
            jsEvent.preventDefault();
            var calendarModal = $('#editNewEvent');
            var myEvent = event;

            var url = '/api/event/' + myEvent.id;
            var request = $.ajax({
                type: "GET",
                url: url
            }).done(function(resp) {
                resp = JSON.parse(resp);
                $('#table-resp-ok').empty();
                if (resp.count.ok) {
                  $.each(resp.team.ok, function( index, user ) {
                    $('#table-resp-ok').append('<tr class="text-center"><td><div class="avatar avatar-md"><img class="img-fluid" src="' + user.avatarUrl + '" onerror="if (this.src != \'/img/default-avatar.png\') this.src = \'/img/default-avatar.png\';"></div></td><td>' + user.firstname + '</td><td>' + user.lastname + '</td></tr>');
                  });
                }

                $('#table-resp-no').empty();
                if (resp.count.no) {
                  $.each(resp.team.no, function( index, user ) {
                    $('#table-resp-no').append('<tr class="text-center"><td><div class="avatar avatar-md"><img class="img-fluid" src="' + user.avatarUrl + '" onerror="if (this.src != \'/img/default-avatar.png\') this.src = \'/img/default-avatar.png\';"></div></td><td>' + user.firstname + '</td><td>' + user.lastname + '</td></tr>');
                  });
                }

                $('#table-resp-uncertain').empty();
                if (resp.count.uncertain) {
                  $.each(resp.team.uncertain, function( index, user ) {
                    $('#table-resp-uncertain').append('<tr class="text-center"><td><div class="avatar avatar-md"><img class="img-fluid" src="' + user.avatarUrl + '" onerror="if (this.src != \'/img/default-avatar.png\') this.src = \'/img/default-avatar.png\';"></div></td><td>' + user.firstname + '</td><td>' + user.lastname + '</td></tr>');
                  });
                }

                $('#table-resp-no-answer').empty();
                if (resp.count.noanswer) {
                  $.each(resp.team.noanswer, function( index, user ) {
                    $('#table-resp-no-answer').append('<tr class="text-center"><td><div class="avatar avatar-md"><img class="img-fluid" src="' + user.avatarUrl + '" onerror="if (this.src != \'/img/default-avatar.png\') this.src = \'/img/default-avatar.png\';"></div></td><td>' + user.firstname + '</td><td>' + user.lastname + '</td></tr>');
                  });
                }

                $('#event-count-ok').html(resp.count.ok);
                $('#event-count-no').html(resp.count.no);
                $('#event-count-uncertain').html(resp.count.uncertain);
                $('#event-count-no-answer').html(resp.count.noanswer);
            });

            $('#modal-title').html(myEvent.title);
            $('#modal-date').html(myEvent.date);
            $('.event-url').attr('href', myEvent.url);
            $('#modal-count').html(myEvent.count);
            $('#modal-place').html(myEvent.place);
            $('#modal-city').html(myEvent.city);
            $('#modal-zipcode').html(myEvent.zipcode);
            $('#modal-address').html(myEvent.address);
            $('#modal-month').html(myEvent.month);
            $('#modal-day').html(myEvent.day);
            $('#modal-date').html(myEvent.date);
            $('#event-place-url').attr('href', 'https://maps.google.com/?q=' + myEvent.address + '+' + myEvent.city + '+' + myEvent.zipcode);
            calendarModal.modal('show');

            var url = '/api/guest/response/' + myEvent.id;
            $('#event-url-ok').off('click').on('click', function(e, state) {
                url = url + '/1';
                var request = $.ajax({
                    type: "GET",
                    url: url
                }).done(function(resp) {
                    if (myEvent.className != 'event-green') {
                        myEvent.count = myEvent.count + 1;
                    }
                    myEvent.className = ['event-green'];
                    $('#calendar').fullCalendar('updateEvent', myEvent);
                    calendarModal.modal('hide');
                });
            });

            $('#event-url-no').off('click').on('click', function(e, state) {
                url = url + '/2';
                var request = $.ajax({
                    type: "GET",
                    url: url
                }).done(function(resp) {
                    if (myEvent.className == 'event-green') {
                        myEvent.count = myEvent.count - 1;
                    }
                    myEvent.className = ['event-red'];
                    $('#calendar').fullCalendar('updateEvent', myEvent);
                    calendarModal.modal('hide');
                });
            });

            $('#event-url-incertain').off('click').on('click', function(e, state) {
                url = url + '/3';
                var request = $.ajax({
                    type: "GET",
                    url: url
                }).done(function(resp) {
                    if (myEvent.className == 'event-green') {
                        myEvent.count = myEvent.count - 1;
                    }
                    myEvent.className = ['event-azure'];
                    $('#calendar').fullCalendar('updateEvent', myEvent);
                    calendarModal.modal('hide');
                });
            });
          },
          eventDragStart: function eventDragStart() {
            $('.site-action').data('actionBtn').show();
          },
          eventDragStop: function eventDragStop() {
            $('.site-action').data('actionBtn').hide();
          },
          droppable: true
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

  exports.default = AppCalendar;
  exports.AppCalendar = AppCalendar;
  exports.run = run;
  exports.getInstance = getInstance;
});