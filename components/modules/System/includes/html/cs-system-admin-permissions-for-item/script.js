// Generated by LiveScript 1.4.0
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
(function(){
  var L;
  L = cs.Language;
  Polymer({
    'is': 'cs-system-admin-permissions-for-item',
    behaviors: [cs.Polymer.behaviors.Language('system_admin_permissions_')],
    properties: {
      group: '',
      label: '',
      permissions: Object,
      users: [],
      found_users: [],
      groups: Array
    },
    ready: function(){
      var $shadowRoot, $search, this$ = this;
      Promise.all([
        $.getJSON('api/System/admin/permissions/for_item', {
          group: this.group,
          label: this.label
        }), $.getJSON('api/System/admin/groups')
      ]).then(function(arg$){
        var permissions, groups, user;
        permissions = arg$[0], groups = arg$[1];
        this$.permissions = permissions;
        this$.groups = groups;
        if (!Object.keys(this$.permissions.users).length) {
          return;
        }
        $.getJSON('api/System/admin/users', {
          ids: (function(){
            var results$ = [];
            for (user in this.permissions.users) {
              results$.push(user);
            }
            return results$;
          }.call(this$)).join(',')
        }, function(users){
          this$.set('users', users);
        });
      });
      $shadowRoot = $(this.shadowRoot);
      $(this.$.form).submit(function(){
        return false;
      });
      $search = $(this.$.search);
      $search.keyup(function(event){
        var text;
        text = $search.val();
        if (event.which !== 13 || !text) {
          return;
        }
        $shadowRoot.find('tr.changed').removeClass('changed').clone().appendTo(this$.$.users);
        this$.set('found_users', []);
        $.getJSON('api/System/admin/users', {
          search: text
        }, function(found_users){
          found_users = found_users.filter(function(user){
            return !$shadowRoot.find("[name='users[" + user + "]']").length;
          });
          if (!found_users.length) {
            cs.ui.notify('404 Not Found', 'warning', 5);
            return;
          }
          $.getJSON('api/System/admin/users', {
            ids: found_users.join(',')
          }, function(users){
            this$.set('found_users', users);
          });
        });
      }).keydown(function(event){
        event.which !== 13;
      });
      $(this.$['search-results']).on('change', ':radio', function(){
        $(this).closest('tr').addClass('changed');
      });
    },
    save: function(){
      $.ajax({
        url: 'api/System/admin/permissions/for_item',
        data: $(this.$.form).serialize() + '&label=' + this.label + '&group=' + this.group,
        type: 'post',
        success: function(){
          cs.ui.notify(L.changes_saved, 'success', 5);
        }
      });
    },
    invert: function(e){
      $(e.currentTarget).closest('div').find(':radio:not(:checked)[value!=-1]').parent().click();
    },
    allow_all: function(e){
      $(e.currentTarget).closest('div').find(':radio[value=1]').parent().click();
    },
    deny_all: function(e){
      $(e.currentTarget).closest('div').find(':radio[value=0]').parent().click();
    },
    permission_state: function(type, id, expected){
      var permission;
      permission = this.permissions[type][id];
      return permission == expected || (expected == '-1' && permission === undefined);
    },
    group_permission_state: function(id, expected){
      return this.permission_state('groups', id, expected);
    },
    user_permission_state: function(id, expected){
      return this.permission_state('users', id, expected);
    },
    username: function(user){
      return user.username || user.login;
    }
  });
}).call(this);
