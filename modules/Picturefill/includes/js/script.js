// Generated by LiveScript 1.4.0
/**
 * @package   Picturefill
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License
 */
(function(){
  Polymer({
    'is': 'cs-picturefill-img',
    'extends': 'img',
    ready: function(){
      picturefill({
        elements: [this]
      });
    }
  });
  Polymer({
    'is': 'cs-picturefill-picture',
    'extends': 'picture',
    ready: function(){
      picturefill({
        elements: [this.querySelector('img')]
      });
    }
  });
}).call(this);
