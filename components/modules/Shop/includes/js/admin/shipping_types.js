// Generated by LiveScript 1.4.0
/**
 * @package   Shop
 * @shipping-type  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
(function(){
  require(['jquery'], function($){
    $(function(){
      var L, make_modal;
      L = cs.Language('shop_');
      make_modal = function(title, action){
        return $(cs.ui.simple_modal("<form is=\"cs-form\">\n	<h3 class=\"cs-text-center\">" + title + "</h3>\n	<label>" + L.title + "</label>\n	<input is=\"cs-input-text\" name=\"title\" required>\n	<label>" + L.price + "</label>\n	<input is=\"cs-input-text\" name=\"price\" type=\"number\" min=\"0\" value=\"0\" required>\n	<label>" + L.phone_needed + "</label>\n	<div>\n		<label is=\"cs-label-button\"><input type=\"radio\" name=\"phone_needed\" value=\"1\" checked> " + L.yes + "</label>\n		<label is=\"cs-label-button\"><input type=\"radio\" name=\"phone_needed\" value=\"0\"> " + L.no + "</label>\n	</div>\n	<label>" + L.address_needed + "</label>\n	<div>\n		<label is=\"cs-label-button\"><input type=\"radio\" name=\"address_needed\" value=\"1\" checked> " + L.yes + "</label>\n		<label is=\"cs-label-button\"><input type=\"radio\" name=\"address_needed\" value=\"0\"> " + L.no + "</label>\n	</div>\n	<label>" + L.description + "</label>\n	<textarea is=\"cs-textarea\" autosize name=\"description\"></textarea>\n	<br>\n	<button is=\"cs-button\" primary type=\"submit\">" + action + "</button>\n</form>"));
      };
      $('html').on('mousedown', '.cs-shop-shipping-type-add', function(){
        var $modal;
        $modal = make_modal(L.shipping_type_addition, L.add);
        $modal.find('form').submit(function(){
          cs.api('post api/Shop/admin/shipping_types', this).then(function(){
            return cs.ui.alert(L.added_successfully);
          }).then(bind$(location, 'reload'));
          return false;
        });
      }).on('mousedown', '.cs-shop-shipping-type-edit', function(){
        var id;
        id = $(this).data('id');
        cs.api("get api/Shop/admin/shipping_types/" + id).then(function(shipping_type){
          var $modal;
          $modal = make_modal(L.shipping_type_edition, L.edit);
          $modal.find('form').submit(function(){
            cs.api("put api/Shop/admin/shipping_types/" + id, this).then(function(){
              return cs.ui.alert(L.edited_successfully);
            }).then(bind$(location, 'reload'));
            return false;
          });
          $modal.find('[name=title]').val(shipping_type.title);
          $modal.find('[name=price]').val(shipping_type.price);
          $modal.find("[name=phone_needed][value=" + shipping_type.phone_needed + "]").prop('checked', true);
          $modal.find("[name=address_needed][value=" + shipping_type.address_needed + "]").prop('checked', true);
          $modal.find('[name=description]').val(shipping_type.description);
        });
      }).on('mousedown', '.cs-shop-shipping-type-delete', function(){
        var id;
        id = $(this).data('id');
        cs.ui.confirm(L.sure_want_to_delete).then(function(){
          return cs.api("delete api/Shop/admin/shipping_types/" + id);
        }).then(function(){
          return cs.ui.alert(L.deleted_successfully);
        }).then(bind$(location, 'reload'));
      });
    });
  });
  function bind$(obj, key, target){
    return function(){ return (target || obj)[key].apply(obj, arguments) };
  }
}).call(this);
