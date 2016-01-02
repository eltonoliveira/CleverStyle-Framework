// Generated by CoffeeScript 1.9.3

/**
 * @package		Prism
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */

(function() {
  document.removeEventListener('DOMContentLoaded', Prism.highlightAll);

  Prism.highlightAll = function(async, callback) {
    var element, elements, i, len, results;
    elements = document.querySelectorAll('html /deep/ code[class*="language-"], html /deep/ [class*="language-"] code, html /deep/ code[class*="lang-"], html /deep/ [class*="lang-"] code');
    results = [];
    for (i = 0, len = elements.length; i < len; i++) {
      element = elements[i];
      if (element.matches('html /deep/ [contenteditable=true] *')) {
        continue;
      }
      (element.parentNode.tagName === 'PRE' ? element.parentNode : element).classList.add('line-numbers');
      results.push(Prism.highlightElement(element, async === true, callback));
    }
    return results;
  };

  $(Prism.highlightAll);

}).call(this);
