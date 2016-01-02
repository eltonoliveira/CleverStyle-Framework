// Generated by CoffeeScript 1.9.3

/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */

(function() {
  Polymer({
    'is': 'cs-blogs-posts',
    'extends': 'section',
    properties: {
      comments_enabled: false
    },
    ready: function() {
      this.jsonld = JSON.parse(this.querySelector('script').innerHTML);
      return this.posts = this.jsonld['@graph'];
    }
  });

}).call(this);
