// Generated by CoffeeScript 1.9.3

/**
 * @package   Static Pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */

(function() {
  $(function() {
    var content, title;
    title = $('.cs-static-pages-page-title');
    if (title.length) {
      window.onbeforeunload = function() {
        return true;
      };
    }
    content = $('.cs-static-pages-page-content');
    return $('.cs-static-pages-page-form').parents('form').submit(function() {
      var form;
      window.onbeforeunload = null;
      form = $(this);
      form.append($('<input name="title" hidden/>').val(title.text()));
      if (!content.is('textarea')) {
        return form.append($('<textarea name="content" hidden/>').val(content.html()));
      }
    });
  });

}).call(this);
