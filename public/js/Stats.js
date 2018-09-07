(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('/App/Stats', ['exports', 'Site', 'Config'], factory);
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
        this.handleRating();
        this.handleNavigation();
      }
    }, {
        key: 'handleNavigation',
        value: function handleNavigation() {
          $('.btn-next').on('click', function(i) {
              var slideUp = $(this).attr('data-hide');
              if (slideUp) $(slideUp).slideUp();
              var slideDown = $(this).attr('data-show');
              if (slideDown) $(slideDown).slideDown();
              var title = $(this).attr('data-next-title');
              $('h3.panel-title').html(title);
              var hideUser =  $(this).attr('data-hide-users');
              if (hideUser) $('#user-selection').hide();
          });
        }      
    }, {
        key: 'handleRating',
        value: function handleRating() {
          $('.avatar-dig, .avatar-reception, .avatar-set').on('click', function() {
            var userId = $('#game-userId').val($(this).attr('data-user-id'));
            var target = $(this).attr('data-target');
            $('.slide-down-rating[data-type="' + target + '"]').slideDown();
          });

          $('.rating').each(function(i) {
              var thisRating = $(this);
              thisRating.raty({
                  targetKeep: true,
                  icon: 'font',
                  starType: 'i',
                  hints: false,
                  starOff: 'icon wb-star',
                  starOn: 'icon wb-star orange-600',
                  cancelOff: 'icon wb-minus-circle',
                  cancelOn: 'icon wb-minus-circle orange-600',
                  starHalf: 'icon wb-star-half orange-500',
                  score: function () {
                      return $(this).data('score');
                  },
                  click: function (quality, evt) {
                      
                      var slideUp = thisRating.attr('data-hide');
                      if (slideUp) $(slideUp).slideUp();
                      var slideDown = thisRating.attr('data-show');
                      if (slideDown) $(slideDown).slideDown();
                      var title = thisRating.attr('data-next-title');
                      $('h3.panel-title').html(title);

                      $('#game-type').val($(this).attr('data-type'));
                      $('#game-quality').val(quality);
                      $("#game").ajaxSubmit({
                        url: '/api/game/add',
                        type: 'post',
                        success: function () {
                            $('#confirm-dig').show();
                            $('#confirm-dig').delay(300).fadeToggle()
                            $('.btn-avatar').removeClass('avatar-checked');
                            thisRating.raty('cancel');
                            $('.rating-stats').slideUp();
                        },
                        error: function () {
                          console.log('Upload error');
                        }
                      });
                      return false;
                  }
              });
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
      key: 'handleWizard',
      value: function handleWizard() {

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

        $('#service-them-point-us, #service-them-point-them').on('click', function() {
          $('#pointFor').val($(this).attr('data-value'));
          $('#reason').val($(this).attr('data-reason'));
          $('#reason').val(4); // fault service
          $('#game-stats').submit();
        });     

        $('#service-us-point-us').on('click', function() {
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

        $('#service-us-fault-us').on('click', function() {
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

        var cancelLink = $("#reset").attr('data-url');
        $('#reset').on("click", function () {
          var elem = $(this);
          swal({
            title: "Are you sure?",
            text: "All your record for this point will be deleted",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-warning",
            confirmButtonText: 'Yes, cancel it!',
            closeOnConfirm: false
            //closeOnCancel: false
          }, function () {
            window.location.href = cancelLink;
          });
        });

        var deleteLink = $("#warning-confirm").attr('data-url');
        $('#warning-confirm').on("click", function () {
          var elem = $(this);
          swal({
            title: "Are you sure?",
            text: "You last point will be deleted",
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