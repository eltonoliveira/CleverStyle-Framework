// Generated by CoffeeScript 1.9.3

/**
 * @package   Plupload
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2016, Nazar Mokrynskyi
 * @license   GNU GPL v2, see license.txt
 */


/**
 * Files uploading interface
 *
 * @param {object}				button
 * @param {function}			success
 * @param {function}			error
 * @param {function}			progress
 * @param {bool}				multi
 * @param {object}|{object}[]	drop_element
 *
 * @return {function}
 */

(function() {
  cs.file_upload = function(button, success, error, progress, multi, drop_element) {
    var browse_button, files, ref, ref1, uploader;
    button = $(button);
    files = [];
    browse_button = $('<button id="plupload_' + (new Date).getTime() + '" style="display:none;"/>').appendTo('body');
    uploader = new plupload.Uploader({
      browse_button: browse_button.get(0),
      max_file_size: (ref = (ref1 = cs.plupload) != null ? ref1.max_file_size : void 0) != null ? ref : null,
      multi_selection: multi,
      multipart: true,
      runtimes: 'html5',
      url: '/Plupload',
      drop_element: drop_element || button.get(0)
    });
    uploader.init();
    uploader.bind('FilesAdded', function() {
      uploader.refresh();
      return uploader.start();
    });
    if (progress) {
      uploader.bind('UploadProgress', function(uploader, file) {
        return progress(file.percent, file.size, file.loaded, file.name);
      });
    }
    if (success) {
      uploader.bind('FileUploaded', function(uploader, files_, res) {
        var response;
        response = $.parseJSON(res.response);
        if (!response.error) {
          return files.push(response.result);
        } else {
          if (error) {
            return error(response.error);
          } else {
            return alert(response.error.message);
          }
        }
      });
      uploader.bind('UploadComplete', function() {
        if (files.length) {
          success(files);
          return files = [];
        }
      });
    }
    uploader.bind('Error', function(uploader, error_details) {
      if (error) {
        return error(error_details);
      } else {
        return alert(error_details.message);
      }
    });
    this.stop = function() {
      return uploader.stop();
    };
    this.destroy = function() {
      browse_button.nextAll('.moxie-shim:first').remove();
      browse_button.remove();
      button.off('click.cs-plupload');
      return uploader.destroy();
    };
    this.browse = function() {
      var input;
      input = browse_button.nextAll('.moxie-shim:first').children();
      if (!input.attr('accept')) {
        input.removeAttr('accept');
      }
      return browse_button.click();
    };
    if (button.length) {
      button.on('click.cs-plupload', this.browse);
    }
    return this;
  };

  return;

}).call(this);
