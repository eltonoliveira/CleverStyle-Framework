// Generated by CoffeeScript 1.9.3

/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */

(function() {
  var L;

  L = cs.Language;

  Polymer({
    'is': 'cs-system-admin-groups-form',
    behaviors: [cs.Polymer.behaviors.Language],
    properties: {
      group_id: Number,
      group_title: '',
      description: ''
    },
    save: function() {
      return $.ajax({
        url: 'api/System/admin/groups' + (this.group_id ? '/' + this.group_id : ''),
        type: this.group_id ? 'put' : 'post',
        data: {
          id: this.group_id,
          title: this.group_title,
          description: this.description
        },
        success: function() {
          return cs.ui.notify(L.changes_saved.toString(), 'success', 5000);
        }
      });
    }
  });

}).call(this);
