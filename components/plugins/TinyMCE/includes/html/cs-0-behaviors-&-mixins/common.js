// Generated by LiveScript 1.4.0
/**
 * @package   TinyMCE
 * @category  plugins
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   GNU Lesser General Public License 2.1, see license.txt
 */
(function(){
  var ref$;
  ((ref$ = Polymer.cs.behaviors).TinyMCE || (ref$.TinyMCE = {})).editor = {
    listeners: {
      tap: '_style_fix'
    },
    properties: {
      value: {
        notify: true,
        observer: '_value_changed',
        type: String
      }
    },
    ready: function(){
      this._when_ready(bind$(this, '_initialize_editor'));
    },
    _when_ready: function(action){
      var callback;
      if (document.readyState !== 'complete') {
        callback = function(){
          setTimeout(action);
          document.removeEventListener('WebComponentsReady', callback);
        };
        document.addEventListener('WebComponentsReady', callback);
      } else {
        setTimeout(action);
      }
    },
    _initialize_editor: function(){
      var this$ = this;
      if (this._init_started) {
        return;
      }
      this._init_started = true;
      this._detached = false;
      if (this._tinymce_editor) {
        this._tinymce_editor.load();
        this._tinymce_editor.remove();
        delete this._tinymce_editor;
      }
      tinymce.init(importAll$({
        target: this.firstElementChild,
        init_instance_callback: function(editor){
          var target;
          this$._tinymce_editor = editor;
          this$._init_started = false;
          if (this$.value !== undefined && this$.value !== editor.getContent()) {
            editor.setContent(this$.value);
            editor.save();
          } else {
            editor.load();
          }
          target = editor.targetElm;
          target._original_focus = target.focus;
          target.focus = bind$(editor, 'focus');
          editor.on('remove', function(){
            target.focus = target._original_focus;
          });
          this$._editor_change_callback_init(editor);
        }
      }, this.editor_config));
    },
    detached: function(){
      var this$ = this;
      if (!this._tinymce_editor) {
        return;
      }
      this._detached = true;
      setTimeout(function(){
        if (this$._detached) {
          this$._tinymce_editor.remove();
          delete this$._tinymce_editor;
        }
      });
    },
    _style_fix: function(){
      var this$ = this;
      Array.prototype.forEach.call(document.querySelectorAll('body > [class^=mce-]'), function(node){
        this$.scopeSubtree(node, true);
      });
    },
    _editor_change_callback_init: function(editor){
      var this$ = this;
      editor.once('change', function(){
        this$._editor_change_callback(editor);
      });
    },
    _editor_change_callback: function(editor){
      var event;
      editor.save();
      this.value = editor.getContent();
      event = document.createEvent('Event');
      event.initEvent('change', false, true);
      editor.getElement().dispatchEvent(event);
      this._editor_change_callback_init(editor);
    },
    _value_changed: function(){
      if (this._tinymce_editor && this.value !== this._tinymce_editor.getContent()) {
        this._tinymce_editor.setContent(this.value || '');
        this._tinymce_editor.save();
      }
    }
  };
  function bind$(obj, key, target){
    return function(){ return (target || obj)[key].apply(obj, arguments) };
  }
  function importAll$(obj, src){
    for (var key in src) obj[key] = src[key];
    return obj;
  }
}).call(this);
