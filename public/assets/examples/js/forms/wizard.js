(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('/forms/wizard', ['jquery', 'Site'], factory);
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

  (function () {

  }),

  // Example Wizard Pager
  // --------------------------
  (function () {
    var defaults = Plugin.getDefaults("wizard");

    var options = _jquery2.default.extend(true, {}, defaults, {
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

    (0, _jquery2.default)("#statsWizard").wizard(options);
    $('.btn-next').on('click', function() {
      (0, _jquery2.default)("#statsWizard").wizard('next');
    });

    $("#reset").on('click', function (event) {
      window.location.reload();
    });

    var deleteLink = $("svg").attr('data-delete-link');
    (0, _jquery2.default)('#warning-confirm').on("click", function () {
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
  })();
});