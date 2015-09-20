// Generated by LiveScript 1.4.0
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
(function(){
  var L, behaviors;
  L = cs.Language;
  behaviors = cs.Polymer.behaviors;
  behaviors.admin = behaviors.admin || {};
  behaviors.admin.System = {
    components: {
      _enable_component: function(component, component_type, meta){
        var component_type_s, this$ = this;
        component_type_s = component_type + 's';
        $.getJSON("api/System/admin/" + component_type_s + "/" + component + "/dependencies", function(dependencies){
          var translation_key, title, message, modal;
          delete dependencies.db_support;
          delete dependencies.storage_support;
          translation_key = component_type === 'module' ? 'enabling_of_module' : 'enabling_of_plugin';
          title = "<h3>" + L[translation_key](component) + "</h3>";
          message = '';
          if (Object.keys(dependencies).length) {
            message = this$._compose_dependencies_message(component, dependencies);
            if (cs.simple_admin_mode) {
              cs.ui.notify(message, 'error', 5);
              return;
            }
          }
          modal = cs.ui.confirm(title + "" + message, function(){
            cs.Event.fire("admin/System/components/" + component_type_s + "/enable/before", {
              name: component
            }).then(function(){
              $.ajax({
                url: "api/System/admin/" + component_type_s + "/" + component,
                type: 'enable',
                success: function(){
                  this$.reload();
                  cs.ui.notify(L.changes_saved, 'success', 5);
                  cs.Event.fire("admin/System/components/" + component_type_s + "/enable/after", {
                    name: component
                  });
                }
              });
            });
          });
          modal.ok.innerHTML = L[!message ? 'enable' : 'force_enable_not_recommended'];
          modal.ok.primary = !message;
          modal.cancel.primary = !modal.ok.primary;
          $(modal).find('p').addClass('cs-text-error cs-block-error');
        });
      },
      _compose_dependencies_message: function(component, dependencies){
        var message, what, components_types, component_type, details, i$, len$, detail, translation_key, conflict;
        message = '';
        for (what in dependencies) {
          components_types = dependencies[what];
          for (component_type in components_types) {
            details = components_types[component_type];
            for (i$ = 0, len$ = details.length; i$ < len$; ++i$) {
              detail = details[i$];
              message += "<p>" + (fn$()) + "</p>";
            }
          }
        }
        return message + "<p>" + L.dependencies_not_satisfied + "</p>";
        function fn$(){
          var i$, ref$, len$, results$ = [], results1$ = [];
          switch (what) {
          case 'update_problem':
            return L.module_cant_be_updated_from_version_to_supported_only(component, detail.from, detail.to, detail.can_update_from);
          case 'provide':
            translation_key = component_type === 'modules' ? 'module_already_provides_functionality' : 'plugin_already_provides_functionality';
            return L[translation_key](detail.name, detail.features.join('", "'));
          case 'require':
            for (i$ = 0, len$ = (ref$ = detail.conflicts).length; i$ < len$; ++i$) {
              conflict = ref$[i$];
              if (component_type === 'unknown') {
                results$.push(L.package_or_functionality_not_found(conflict.name + conflict.required.join(' ')));
              } else {
                translation_key = component_type === 'modules' ? 'unsatisfactory_version_of_the_module' : 'unsatisfactory_version_of_the_plugin';
                results$.push(L[translation_key](detail.name, conflict.join(' '), detail.existing));
              }
            }
            return results$;
            break;
          case 'conflict':
            for (i$ = 0, len$ = (ref$ = detail.conflicts).length; i$ < len$; ++i$) {
              conflict = ref$[i$];
              results1$.push(L.package_is_incompatible_with(conflict['package'], conflict.conflicts_with, conflict.of_versions.join(' ')));
            }
            return results1$;
            break;
          case 'db_support':
            return L.compatible_databases_not_found(detail.supported.join('", "'));
          case 'storage_support':
            return L.compatible_storages_not_found(detail.supported.join('", "'));
          }
        }
      },
      _disable_component: function(component, component_type){
        var component_type_s, this$ = this;
        component_type_s = component_type + 's';
        $.getJSON("api/System/admin/" + component_type_s + "/" + component + "/dependent_packages", function(dependent_packages){
          var translation_key, title, message, type, packages, i$, len$, _package, modal;
          translation_key = component_type === 'module' ? 'disabling_of_module' : 'disabling_of_plugin';
          title = "<h3>" + L[translation_key](component) + "</h3>";
          message = '';
          if (Object.keys(dependent_packages).length) {
            for (type in dependent_packages) {
              packages = dependent_packages[type];
              translation_key = type === 'modules' ? 'this_package_is_used_by_module' : 'this_package_is_used_by_plugin';
              for (i$ = 0, len$ = packages.length; i$ < len$; ++i$) {
                _package = packages[i$];
                message += "<p>" + L[translation_key](_package) + "</p>";
              }
            }
            message += "<p>" + L.dependencies_not_satisfied + "</p>";
            if (cs.simple_admin_mode) {
              cs.ui.notify(message, 'error', 5);
              return;
            }
          }
          modal = cs.ui.confirm(title + "" + message, function(){
            cs.Event.fire("admin/System/components/" + component_type_s + "/disable/before", {
              name: component
            }).then(function(){
              $.ajax({
                url: "api/System/admin/" + component_type_s + "/" + component,
                type: 'disable',
                success: function(){
                  this$.reload();
                  cs.ui.notify(L.changes_saved, 'success', 5);
                  cs.Event.fire("admin/System/components/" + component_type_s + "/disable/after", {
                    name: component
                  });
                }
              });
            });
          });
          modal.ok.innerHTML = L[!message ? 'disable' : 'force_disable_not_recommended'];
          modal.ok.primary = !message;
          modal.cancel.primary = !modal.ok.primary;
          $(modal).find('p').addClass('cs-text-error cs-block-error');
        });
      },
      _remove_completely_component: function(component, component_type){
        var component_type_s, translation_key, this$ = this;
        component_type_s = component_type + 's';
        translation_key = component_type === 'module' ? 'completely_remove_module' : 'completely_remove_plugin';
        cs.ui.confirm(L[translation_key](component), function(){
          $.ajax({
            url: "api/System/admin/" + component_type_s + "/" + component,
            type: 'delete',
            success: function(){
              this$.reload();
              cs.ui.notify(L.changes_saved, 'success', 5);
            }
          });
        });
      }
    }
  };
}).call(this);
