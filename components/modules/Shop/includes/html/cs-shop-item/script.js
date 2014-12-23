// Generated by CoffeeScript 1.4.0

/**
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
*/


(function() {

  Polymer({
    ready: function() {
      var $this, attributes;
      this.header_title = this.querySelector('h1').innerHTML;
      $(this.$.images).fotorama({
        data: Array.prototype.map.call(this.querySelectorAll('#images > img'), function(img) {
          return {
            img: img.src
          };
        }),
        allowfullscreen: 'native',
        controlsonstart: false,
        fit: 'scaledown',
        keyboard: true,
        'loop': true,
        nav: 'thumbs',
        ratio: 4 / 3,
        trackpad: true
      });
      $this = $(this);
      this.price = $this.data('price');
      this.in_stock = $this.data('in_stock');
      attributes = $(this.querySelector('#attributes'));
      if (attributes.length) {
        this.show_attributes = true;
        return attributes.find('table').addClass('uk-table uk-table-hover').find('td:first-of-type').addClass('uk-text-bold');
      }
    }
  });

}).call(this);
