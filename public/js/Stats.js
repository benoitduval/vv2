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
        this.handleModal();
        this.handleSelect();
        this.handleSlideUp();
        this.handleDig();
        // this.handlePlusOne();
      }
    }, {
        key: 'handleSlideUp',
        value: function handleSlideUp() {
          $('.slide-up').on('click', function() {
            $('#slide-up-service').slideUp();
            $('#slide-up-reception').slideUp();
            $('#slide-down-point').slideDown();
          });

          $('.avatar-reception').on('click', function() {
            $('.btn-reception-quality').removeAttr('disabled');
          });
        }
    }, {
        key: 'handleDig',
        value: function handleDig() {
            $('.avatar-dig').on('click', function() {
              $('.btn-dig-quality').removeAttr('disabled');
            });

            $(".btn-dig-quality").click(function() {
              $(".btn-dig-quality").attr('disabled', 'disabled');
              $("#confirm-dig").toggle();
              $("#confirm-dig").fadeToggle(2000);
            });
        }
    }, {
        key: 'handleModal',
        value: function handleModal() {
            $('#positions').modal();
        }
    }, {
      key: 'handleSelect',
      value: function handleSelect() {
        var previous;
        $('.user-select').on('focus', function () {
          previous = this.value;
        }).on('change', function() {
          $('#select-P1, #select-P2, #select-P3, #select-P4, #select-P5, #select-P6, #libero').not(this)
              .children('option[value=' + this.value + ']')
              .attr('disabled', true);
          if (previous) {
            $('#select-P1, #select-P2, #select-P3, #select-P4, #select-P5, #select-P6, #libero').not(this)
              .children('option[value=' + previous + ']')
              .removeAttr('disabled');
            previous = this.value;
          }
        });
      }
    }, {
      key: 'handlePlusOne',
      value: function handlePlusOne() {
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
              var html = '<div class="btn-group btn-group-sm">' + '<a id="reset" class="btn btn-default btn-outline" href="#' + this.id + '" role="button">' + options.buttonLabels.back + '</a><a id="warning-confirm" data-url="<?= $deleteLink ?>" class="btn btn-danger btn-outline" href="#" role="button"><i class="icon wb-trash"></i> <span class="hidden-sm-down">Last Point</span></a><a class="btn btn-primary btn-outline" href="#" role="button"  data-toggle="modal" data-target="#positions"><i class="icon wb-random" aria-hidden="true"></i> Switch</a><a class="btn btn-success btn-outline disabled" id="submit-point" href="#' + this.id + '" data-wizard="finish" role="button">' + options.buttonLabels.finish + '</a></div>';
              return html;
            }
          },
          buttonLabels: {
            next: '<i class="icon wb-chevron-right" aria-hidden="true"></i>',
            back: '<i class="icon wb-refresh" aria-hidden="true"></i> <span class="hidden-sm-down">Cancel</span>',
            finish: '<i class="icon wb-check" aria-hidden="true"></i> Submit'
          },

          buttonsAppendTo: '.panel-actions'
        });

        $('#point-them').on('click', function() {
            $('#pointFor').val($(this).attr('data-value'));
            $('#attack-them').removeClass('hidden');
            $('#attack-fault-us').removeClass('hidden');  
            $('#service-fault-us').removeClass('hidden');
            $('#service-point-them').removeClass('hidden');
            $('#defensive-fault').removeClass('hidden');
            $('#block-point-them').removeClass('hidden');
        });

        $('#point-us').on('click', function() {
            $('#pointFor').val($(this).attr('data-value'));
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
              $('#table-title').html('Target Zone ?');
              $('#fromZone').val($(this).attr('data-attack'));
              $('.attack.active').removeClass('selected');
              $(this).addClass('selected');
              $('.target').each(function () {
                $(this).removeClass('inactive');
                $(this).addClass('active');
                $('.target.active').on('click', function() {
                    $('#toZone').val($(this).attr('data-target'));
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
                $('#table-title').html('Fault Zone ?');
                $('#fromZone').val($(this).attr('data-attack'));
                $('.attack.active').removeClass('selected');
                $(this).addClass('selected');
                $('.out').each(function () {
                  $(this).removeClass('inactive');
                  $(this).addClass('active');
                  $('.out.active').on('click', function() {
                      $('#toZone').val($(this).attr('data-target'));
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

        $('#block-point-us').on('click', function() {
          $('#table-title').html('Block From ?');
          $('.btn-avatar').on('click', function() {
              $('#userId').val($(this).attr('data-user-id'));

              var input = $(this).find('input');

              $('.attack.front').each(function () {
                $(this).removeClass('inactive');
                $(this).addClass('active');
              });

              $('.attack.front.active').on('click', function () {
                $('#fromZone').val($(this).attr('data-target'));
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
          $('#table-title').html('Blocked From ?');
          $('.btn-avatar').on('click', function() {
              $('#userId').val($(this).attr('data-user-id'));
              var input = $(this).find('input');

              $('.attack.front').each(function () {
                $(this).removeClass('inactive');
                $(this).addClass('active');
              });

              $('.attack.front.active').on('click', function () {
                $('#fromZone').val($(this).attr('data-target'));
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

        $('#service-point-us').on('click', function() {
          $('#table-title').html('Target Zone ?');
          $('#user-selection').hide();
          $("#statsWizard").wizard('goTo', 2);
          $('#pointFor').val($(this).attr('data-value'));
          $('#reason').val(1); // point service


          $('#userId').val($(this).attr('data-user-id'));
          var input = $(this).find('input');

          $('.target').each(function () {
            $(this).removeClass('inactive');
            $(this).addClass('active');
          });

          $('.target.active').on('click', function () {
            $('#toZone').val($(this).attr('data-target'));
            $('.target.active').removeClass('selected');
            $(this).addClass('selected');
            $('#submit-point').removeClass('disabled');
            $('#submit-point').on('click', function() {
                $('#game-stats').submit();
            });
          });
        });

        $('#service-fault-us').on('click', function() {
          $('#table-title').html('Fault Zone ?');
          $("#statsWizard").wizard('goTo', 2);
          $('#user-selection').hide();
          $('#pointFor').val($(this).attr('data-value'));
          $('#reason').val(4); // fault service

          $('#userId').val($(this).attr('data-user-id'));
          var input = $(this).find('input');

          $('.out').each(function () {
            $(this).removeClass('inactive');
            $(this).addClass('active');
          });

          $('.out.active').on('click', function () {
            $('#toZone').val($(this).attr('data-target'));
            $('.out.active').removeClass('selected');
            $(this).addClass('selected');
            $('#submit-point').removeClass('disabled');
            $('#submit-point').on('click', function() {
                $('#game-stats').submit();
            });
          });
        });

        $("#reset").on('click', function (event) {
          window.location.reload();
        });

        var deleteLink = $("table").attr('data-delete-link');
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