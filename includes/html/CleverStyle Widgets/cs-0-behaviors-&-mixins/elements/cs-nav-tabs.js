// Generated by LiveScript 1.4.0
/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
(function(){
  Polymer.cs.behaviors.csNavTabs = [
    Polymer.cs.behaviors['this'], Polymer.cs.behaviors.tooltip, {
      hostAttributes: {
        role: 'group'
      },
      properties: {
        selected: {
          notify: true,
          observer: '_selected_changed',
          type: Number
        }
      },
      listeners: {
        tap: '_tap'
      },
      ready: function(){
        var i$, ref$, len$, element;
        for (i$ = 0, len$ = (ref$ = this.children).length; i$ < len$; ++i$) {
          element = ref$[i$];
          if (element.active) {
            return;
          }
        }
        this.selected = 0;
      },
      _tap: function(e){
        var target, i$, ref$, len$, index, element, this$ = this;
        target = function(){
          var i$, ref$, len$, index, path;
          for (i$ = 0, len$ = (ref$ = e.path).length; i$ < len$; ++i$) {
            index = i$;
            path = ref$[i$];
            if (path === this$) {
              return e.path[index - 3];
            }
          }
        }();
        if (!target) {
          return;
        }
        for (i$ = 0, len$ = (ref$ = this.children).length; i$ < len$; ++i$) {
          index = i$;
          element = ref$[i$];
          if (element.tagName === 'TEMPLATE') {
            continue;
          }
          if (element === target) {
            this.selected = index;
            element.setAttribute('active', '');
          } else {
            element.removeAttribute('active');
          }
        }
      },
      _selected_changed: function(){
        var i$, ref$, len$, index, element;
        for (i$ = 0, len$ = (ref$ = this.children).length; i$ < len$; ++i$) {
          index = i$;
          element = ref$[i$];
          if (element.tagName === 'TEMPLATE') {
            continue;
          }
          element.active = index === this.selected;
          if (index === this.selected) {
            element.setAttribute('active', '');
          } else {
            element.removeAttribute('active');
          }
        }
        if (((ref$ = this.nextElementSibling) != null ? ref$.is : void 8) === 'cs-section-switcher') {
          this.nextElementSibling.selected = this.selected;
        }
      }
    }
  ];
}).call(this);
