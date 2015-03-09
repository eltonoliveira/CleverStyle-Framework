// Generated by CoffeeScript 1.4.0

/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
*/


(function() {
  var L;

  L = cs.Language;

  Polymer({
    composer_updating_text: L.composer_composer_updating,
    ready: function() {
      var _this = this;
      return $.ajax({
        url: 'api/Composer',
        type: cs.composer.add ? 'post' : 'delete',
        data: {
          name: cs.composer.name,
          type: cs.composer.type
        },
        success: function(result) {
          if (result.description) {
            $(_this.$.result).show().html(result.description);
          }
          _this.status = (function() {
            switch (result.code) {
              case 0:
                return L.composer_updated_successfully;
              case 1:
                return L.composer_update_failed;
              case 2:
                return L.composer_dependencies_conflict;
            }
          })();
          if (!result.code) {
            setTimeout((function() {
              return cs.composer.modal.trigger('hide');
            }), 2000);
          }
          return cs.composer.button.off('click.cs-composer').click();
        }
      });
    }
  });

}).call(this);