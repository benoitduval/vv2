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
    global.AppStats = mod.exports;
  }
})(this, function (exports, _Site2, _Config) {
  'use strict';

  Object.defineProperty(exports, "__esModule", {
    value: true
  });
  exports.getInstance = exports.run = exports.AppStats = undefined;

  var _Site3 = babelHelpers.interopRequireDefault(_Site2);

  var AppStats = function (_Site) {
    babelHelpers.inherits(AppStats, _Site);

    function AppStats() {
      babelHelpers.classCallCheck(this, AppStats);
      return babelHelpers.possibleConstructorReturn(this, (AppStats.__proto__ || Object.getPrototypeOf(AppStats)).apply(this, arguments));
    }

    babelHelpers.createClass(AppStats, [{
      key: 'initialize',
      value: function initialize() {
        babelHelpers.get(AppStats.prototype.__proto__ || Object.getPrototypeOf(AppStats.prototype), 'initialize', this).call(this);

      }
    }, {
      key: 'process',
      value: function process() {
        babelHelpers.get(AppStats.prototype.__proto__ || Object.getPrototypeOf(AppStats.prototype), 'process', this).call(this);

        this.handleWizard();
        this.handleSelective();
      }
    }, {
      key: 'handleWizard',
      value: function handleWizard() {
        var defaults = Plugin.getDefaults("wizard");

        var options = $.extend(true, {}, defaults, {
          step: '.wizard-pane',
          templates: {
            buttons: function buttons() {
              var options = this.options;
              var html = '<div class="btn-group btn-group-sm">' + '<a id="reset" class="btn btn-default btn-outline" href="#' + this.id + '" role="button">' + options.buttonLabels.back + '</a><a id="warning-confirm" data-url="<?= $deleteLink ?>" class="btn btn-danger btn-outline" href="#" role="button"><i class="icon wb-trash"></i> Last Point</a><a class="btn btn-success btn-outline disabled" id="submit-point" href="#' + this.id + '" data-wizard="finish" role="button">' + options.buttonLabels.finish + '</a></div>';
              return html;
            }
          },
          buttonLabels: {
            next: '<i class="icon wb-chevron-right" aria-hidden="true"></i>',
            back: '<i class="icon wb-refresh" aria-hidden="true"></i> Cancel',
            finish: '<i class="icon wb-check" aria-hidden="true"></i> Submit'
          },

          buttonsAppendTo: '.panel-actions'
        });

        $('.btn-counter').each(function () {
            $(this).on('click', function() {
                var input = $($(this).attr('data-input-target'));
                var value = input.val();
                value = parseInt(value) + 1;
                input.val(value);
                var counter = $(this).find('.counter');
                counter.html(value);
            });
        });

        $('#point-them').on('click', function() {
            $('#point-for').val($(this).attr('data-value'));
            $('#attack-them').removeClass('hidden');
            $('#attack-fault-us').removeClass('hidden');
            $('#service-fault-us').removeClass('hidden');
            $('#service-point-them').removeClass('hidden');
            $('#defensive-fault').removeClass('hidden');
            $('#block-point-them').removeClass('hidden');
        });

        $('#point-us').on('click', function() {
            $('#point-for').val($(this).attr('data-value'));
            $('#attack-us').removeClass('hidden');
            $('#attack-fault-them').removeClass('hidden');
            $('#service-point-us').removeClass('hidden');
            $('#service-fault-them').removeClass('hidden');
            $('#defensive-fault').removeClass('hidden');
            $('#block-point-us').removeClass('hidden');
        });

        $('#attack-us').on('click', function() {
          $('.btn-avatar').on('click', function() {
            $('#userId').val($(this).attr('data-user-id'));
            var input = $(this).find('input');

            $('.attack').each(function () {
              $(this).removeClass('inactive');
              $(this).addClass('active');
            });

            $('.attack.active').on('click', function () {
              $('#from-zone').val($(this).attr('data-attack'));
              $('.attack.active').removeClass('selected');
              $(this).addClass('selected');
              $('.target').each(function () {
                $(this).removeClass('inactive');
                $(this).addClass('active');
                $('.target.active').on('click', function() {
                    $('#to-zone').val($(this).attr('data-target'));
                    $('.target.active').removeClass('selected');
                    $(this).addClass('selected');
                    $('#submit-point').removeClass('disabled');
                    $('#submit-point').on('click', function() {
                        $('#game-stats').submit();
                    });
                });
              });
            });
          });
        });

        $('#attack-fault-us').on('click', function() {
          $('.btn-avatar').on('click', function() {
              $('#userId').val($(this).attr('data-user-id'));
              var input = $(this).find('input');

              $('.attack').each(function () {
                $(this).removeClass('inactive');
                $(this).addClass('active');
              });

              $('.attack.active').on('click', function () {
                $('#from-zone').val($(this).attr('data-attack'));
                $('.attack.active').removeClass('selected');
                $(this).addClass('selected');
                $('.out').each(function () {
                  $(this).removeClass('inactive');
                  $(this).addClass('active');
                  $('.out.active').on('click', function() {
                      $('#to-zone').val($(this).attr('data-target'));
                      $('.out.active').removeClass('selected');
                      $(this).addClass('selected');
                      $('#submit-point').removeClass('disabled');
                      $('#submit-point').on('click', function() {
                          $('#game-stats').submit();
                      });
                  });
                });
              });
          });
        });

        $('#service-point-us').on('click', function() {
          $('.btn-avatar').on('click', function() {
              $('#userId').val($(this).attr('data-user-id'));
              var input = $(this).find('input');

              $('.target').each(function () {
                $(this).removeClass('inactive');
                $(this).addClass('active');
              });

              $('.target.active').on('click', function () {
                $('#to-zone').val($(this).attr('data-target'));
                $('.target.active').removeClass('selected');
                $(this).addClass('selected');
                $('#submit-point').removeClass('disabled');
                $('#submit-point').on('click', function() {
                    $('#game-stats').submit();
                });
              });
          });
        });

        $('#service-fault-us').on('click', function() {
          $('.btn-avatar').on('click', function() {
              $('#userId').val($(this).attr('data-user-id'));
              var input = $(this).find('input');

              $('.out').each(function () {
                $(this).removeClass('inactive');
                $(this).addClass('active');
              });

              $('.out.active').on('click', function () {
                $('#to-zone').val($(this).attr('data-target'));
                $('.out.active').removeClass('selected');
                $(this).addClass('selected');
                $('#submit-point').removeClass('disabled');
                $('#submit-point').on('click', function() {
                    $('#game-stats').submit();
                });
              });
          });
        });

        $('#block-point-us').on('click', function() {
          $('.btn-avatar').on('click', function() {
              $('#userId').val($(this).attr('data-user-id'));
              var input = $(this).find('input');

              $('.attack.front').each(function () {
                $(this).removeClass('inactive');
                $(this).addClass('active');
              });

              $('.attack.front.active').on('click', function () {
                $('#from-zone').val($(this).attr('data-target'));
                $('.attack.front.active').removeClass('selected');
                $(this).addClass('selected');
                $('#submit-point').removeClass('disabled');
                $('#submit-point').on('click', function() {
                    $('#game-stats').submit();
                });
              });
          });
        });

        $('#block-point-them').on('click', function() {
          $('.btn-avatar').on('click', function() {
              $('#userId').val($(this).attr('data-user-id'));
              var input = $(this).find('input');

              $('.attack.front').each(function () {
                $(this).removeClass('inactive');
                $(this).addClass('active');
              });

              $('.attack.front.active').on('click', function () {
                $('#from-zone').val($(this).attr('data-target'));
                $('.attack.front.active').removeClass('selected-error');
                $(this).addClass('selected-error');
                $('#submit-point').removeClass('disabled');
                $('#submit-point').on('click', function() {
                    $('#game-stats').submit();
                });
              });
          });
        });

        $("button[name=reason]").each(function () {
            $(this).on('click', function() {
              $('#reason').val($(this).attr('value'));
              if (jQuery.inArray($(this).prop('id'), ['defensive-fault', 'service-point-them', 'service-fault-them', 'attack-them', 'attack-fault-them']) !== -1) {
                $('#game-stats').submit();
              }
            });
        });

        $('.btn-avatar').on('click', function() {
            $('.btn-avatar').removeClass('avatar-checked');
            $(this).addClass('avatar-checked');
        });

        $("#statsWizard").wizard(options);
        $('.btn-next').on('click', function() {
          $("#statsWizard").wizard('next');
        });

        $("#reset").on('click', function (event) {
          window.location.reload();
        });

        var deleteLink = $("svg").attr('data-delete-link');
        $('#warning-confirm').on("click", function () {
          var elem = $(this);
          swal({
            title: "Are you sure?",
            text: "You last point will be delete",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-warning",
            confirmButtonText: 'Yes, delete it!',
            closeOnConfirm: false
            //closeOnCancel: false
          }, function () {
            window.location.href = deleteLink;
          });
        });
      }
    }, {
      key: 'handleSelective',
      value: function handleSelective() {
        
        var _this3 = this;

        var self = this;
        var member = [{
          id: 'uid_1',
          name: 'Herman Beck',
          avatar: '../../../../global/portraits/1.jpg'
        }, {
          id: 'uid_2',
          name: 'Mary Adams',
          avatar: '../../../../global/portraits/2.jpg'
        }, {
          id: 'uid_3',
          name: 'Caleb Richards',
          avatar: '../../../../global/portraits/3.jpg'
        }, {
          id: 'uid_4',
          name: 'June Lane',
          avatar: '../../../../global/portraits/4.jpg'
        }, {
          id: 'uid_5',
          name: 'June Lane',
          avatar: '../../../../global/portraits/5.jpg'
        }, {
          id: 'uid_6',
          name: 'June Lane',
          avatar: '../../../../global/portraits/6.jpg'
        }, {
          id: 'uid_7',
          name: 'June Lane',
          avatar: '../../../../global/portraits/7.jpg'
        }];

        var getNum = function getNum(num) {
          return Math.ceil(Math.random() * (num + 1));
        };

        var getMember = function getMember() {
          return member[getNum(member.length - 1) - 1];
        };

        var isSame = function isSame(items) {
          var _items = items;
          var _member = getMember();

          if (_items.indexOf(_member) === -1) {
            return _member;
          }
          return isSame(_items);
        };

        var pushMember = function pushMember(num) {
          var items = [];
          for (var i = 0; i < num; i++) {
            items.push(isSame(items));
          }
          _this3.items = items;
        };

        var setItems = function setItems(membersNum) {
          var num = getNum(membersNum - 1);
          pushMember(num);
        };

        $('.plugin-selective').each(function () {
          setItems(member.length);

          var items = self.items;

          $(this).selective({
            namespace: 'addMember',
            local: member,
            selected: items,
            buildFromHtml: false,
            tpl: {
              optionValue: function optionValue(data) {
                return data.id;
              },
              frame: function frame() {
                return '<div class="' + this.namespace + '">\n                ' + this.options.tpl.items.call(this) + '\n                <div class="' + this.namespace + '-trigger">\n                ' + this.options.tpl.triggerButton.call(this) + '\n                <div class="' + this.namespace + '-trigger-dropdown">\n                ' + this.options.tpl.list.call(this) + '\n                </div>\n                </div>\n                </div>';

                // i++;
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
        });
      }
    }]);
    return AppStats;
  }(_Site3.default);

  var instance = null;

  function getInstance() {
    if (!instance) {
      instance = new AppStats();
    }
    return instance;
  }

  function run() {
    var app = getInstance();
    app.run();
  }

  exports.default = AppStats;
  exports.AppStats = AppStats;
  exports.run = run;
  exports.getInstance = getInstance;
});