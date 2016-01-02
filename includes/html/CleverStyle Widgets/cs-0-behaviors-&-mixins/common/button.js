// Generated by LiveScript 1.4.0
/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
(function(){
  var ref$;
  ((ref$ = Polymer.cs || (Polymer.cs = {})).behaviors || (ref$.behaviors = {})).button = {
    properties: {
      action: {
        type: String,
        value: 'button_action'
      },
      active: {
        notify: true,
        reflectToAttribute: true,
        type: Boolean
      },
      bind: {
        observer: '_bind_changed',
        type: Object
      },
      empty: {
        reflectToAttribute: true,
        type: Boolean
      },
      icon: {
        reflectToAttribute: true,
        type: String
      },
      iconAfter: {
        reflectToAttribute: true,
        type: String
      },
      primary: {
        reflectToAttribute: true,
        type: Boolean
      }
    },
    listeners: {
      tap: '_tap'
    },
    attached: function(){
      this.empty = !this.childNodes.length;
    },
    _bind_changed: function(){
      var bind_element, observer, this$ = this;
      if (this.bind) {
        bind_element = this.bind;
        this.bind = null;
        this._tap = bind_element[this.action].bind(bind_element);
        observer = new MutationObserver(function(mutations){
          mutations.forEach(function(mutation){
            var i$, ref$, len$, node;
            if (!mutation.removedNodes) {
              return;
            }
            for (i$ = 0, len$ = (ref$ = mutation.removedNodes).length; i$ < len$; ++i$) {
              node = ref$[i$];
              if (node !== bind_element) {
                return;
              }
              observer.disconnect();
              setTimeout(fn$, 1000);
            }
            function fn$(){
              if (!bind_element.parentNode) {
                this$._tap = function(){};
              } else {
                observer.observe(bind_element.parentNode, {
                  childList: true
                });
              }
            }
          });
        });
        observer.observe(bind_element.parentNode, {
          childList: true,
          subtree: false
        });
      }
    },
    _tap: function(){}
  };
}).call(this);
