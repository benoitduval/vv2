(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define('/forms/image-cropping', ['jquery', 'Site'], factory);
  } else if (typeof exports !== "undefined") {
    factory(require('jquery'), require('Site'));
  } else {
    var mod = {
      exports: {}
    };
    factory(global.jQuery, global.Site);
    global.formsImageCropping = mod.exports;
  }
})(this, function (_jquery, _Site) {
  'use strict';

  var _jquery2 = babelHelpers.interopRequireDefault(_jquery);

  (0, _jquery2.default)(document).ready(function ($$$1) {
    (0, _Site.run)();
  });

  // Example Cropper Simple
  // ----------------------
  (function () {
    (0, _jquery2.default)("#simpleCropper img").cropper({
      preview: "#simpleCropperPreview >.img-preview",
      responsive: true
    });
  })();

  // Example Cropper Full
  // --------------------
  (function () {
    var $exampleFullCropper = (0, _jquery2.default)("#exampleFullCropper img");

    var options = {
      aspectRatio: 1 / 1,
      preview: "#exampleFullCropperPreview > .img-preview",
      responsive: true
    };
    // set up cropper
    $exampleFullCropper.cropper(options);

    // set up method buttons
    (0, _jquery2.default)(document).on("click", "[data-cropper-method]", function () {
      var data = (0, _jquery2.default)(this).data(),
          method = (0, _jquery2.default)(this).data('cropper-method'),
          result;
      if (method) {
        result = $exampleFullCropper.cropper(method, data.option);
      }

      if (method === 'getCroppedCanvas') {
        (0, _jquery2.default)('#getDataURLModal').modal().find('.modal-body').html(result);
      }
    });

    // deal with uploading
    var $inputImage = (0, _jquery2.default)("#inputImage");

    if (window.FileReader) {
      $inputImage.change(function () {
        var fileReader = new FileReader(),
            files = this.files,
            file;

        if (!files.length) {
          return;
        }

        file = files[0];

        if (/^image\/\w+$/.test(file.type)) {
          fileReader.readAsDataURL(file);
          fileReader.onload = function () {
            $exampleFullCropper.cropper("reset", true).cropper("replace", this.result);
            $inputImage.val("");
          };
        } else {
          showMessage("Please choose an image file.");
        }
      });
    } else {
      $inputImage.addClass("hide");
    }

    // upload to server
    (0, _jquery2.default)("#uploadCropperData").click(function () {
      $exampleFullCropper.cropper('getCroppedCanvas').toBlob(function (blob) {

        var formData = new FormData();

        formData.append('croppedImage', blob);

        $.ajax('/api/user/upload', {
          method: "POST",
          data: formData,
          processData: false,
          contentType: false,
          success: function () {
            console.log('Upload success');
          },
          error: function () {
            console.log('Upload error');
          }
        });
      });
    });
  })();
});