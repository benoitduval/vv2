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
    $('input[name="training"]').each(function () {
      $(this).on('change', function(event, state) {
      // $(this).on('switchChange.bootstrapSwitch', function(event, state) {
          var id    = $(this).attr('data-id');
          var value = $(this).attr("value");
          if (value == 1) {
              $(this).attr("value", 2);
          } else {
              $(this).attr("value", 1);
          }
          var url = '/api/recurrent/enable/' + id + '/' + value;
          var request = $.ajax({
              type: "GET",
              url: url
          });
      });

      $('.delete-confirm').each(function() {
        var link = $(this).attr('data-link');
        $(this).on('click', function() {      
          swal({
            title: "Are you sure?",
            text: "Do you really want to delete this ?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-warning",
            confirmButtonText: 'Yes, cancel it!',
            closeOnConfirm: false
          }, function () {
            window.location.href = link;
          });
        });
      });

      $('#editUser').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var firstname = button.data('firstname');
        var lastname = button.data('lastname');
        var email = button.data('email');
        var licence = button.data('licence');
        var phone = button.data('phone');
        var numero = button.data('numero');
        var position = button.data('position');
        var avatar = button.data('avatar');
        var modal = $(this);
        modal.find('.modal-body input[name="id"]').val(id);
        modal.find('.modal-body input[name="firstname"]').val(firstname);
        modal.find('.modal-body input[name="lastname"]').val(lastname);
        modal.find('.modal-body input[name="email"]').val(email);
        modal.find('.modal-body input[name="licence"]').val(licence);
        modal.find('.modal-body input[name="phone"]').val(phone);
        modal.find('.modal-body input[name="numero"]').val(numero);
        modal.find('.modal-body img').attr('src', avatar);
        modal.find('#position' + position).attr("selected","selected");
        $('#update-user').on('click', function() {
          $('#form-update-user').ajaxSubmit({
            url: '/api/user/update',
            type: 'post',
            success:  function() {
              window.location.reload();
            }
          });
        });
      });
    });
  });
});