// Generated by LiveScript 1.4.0
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
(function(){
  var normalize_properties;
  Polymer.Base._registerFeatures_original = Polymer.Base._registerFeatures;
  normalize_properties = function(properties){
    var property, value, type;
    if (properties) {
      for (property in properties) {
        value = properties[property];
        type = (fn$());
        if (type) {
          properties[property] = {
            type: type,
            value: value
          };
        }
      }
    }
    function fn$(){
      switch (typeof value) {
      case 'boolean':
        return Boolean;
      case 'number':
        return Number;
      case 'string':
        return String;
      default:
        if (value instanceof Date) {
          return Date;
        } else if (value instanceof Array) {
          return Array;
        }
      }
    }
  };
  Polymer.Base._addFeature({
    _registerFeatures: function(){
      normalize_properties(this.properties);
      if (this.behaviors) {
        this.behaviors.forEach(function(behavior){
          normalize_properties(behavior.properties);
        });
      }
      this._registerFeatures_original();
    }
  });
}).call(this);
