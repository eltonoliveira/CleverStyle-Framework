<a name="up" />
Event - special feature of CleverStyle Framework, that allows to change behaviour of some system processes or react on them.

### [Naming](#naming) [Subscribing](#subscribing) [Unsubscribing](#unsubscribing) [One-time subscribing](#one-time-subscribing) [Dispatching](#dispatching) [System events](#system-events)

General workflow:
* someone subscribe for event somewhere in code (even several times)
* event is dispatched from another place

NOTE: all examples will be shown for backend `\cs\Event` class, but are the same for frontend `cs.Event` object.

<a name="naming" />
###[Up](#up) Events naming

All events are named like paths in file system (sometimes it corresponds to the page, on which this event is called), this allows to determine easily for what purpose by what component of system this event is used:

* admin/System/modules/disable
* System/Page/rebuild_cache

Usually, system events are stated with `System/`, other modules similarly starts with module name. Events, that are used in admin section are prefixed by `admin`, for api with `api` and for CLI with `cli`.

It is a good practice to put into file, that runs event such comment section:
```
/**
 * Provides next events:
 *  System/general/languages/load
 *  [
 *   'clanguage'        => clanguage
 *   'clang'            => clang
 *   'cregion'          => cregion
 *  ]
 */
```
<a name="subscribing" />
###[Up](#up) Subscribing for event

Events are registered with help of method `Event::instance()->on()`. Method accepts 2 arguments: first - event name, second - callback, that will be called at event dispatching.

Example:
```php
<?php
\cs\Event::instance()->on(
    'admin/System/modules/disable',
    function ($data) {
        if ($data['name'] == basename(__DIR__)) {
            clean_pcache();
        }
    }
);
```
Parameter `$data` in callback is used to put some addition information of context into callback.
In this case, `$data['name']` contains name of installable module, and module can perform some additional operations when it installs, for example, set default configuration parameters.

<a name="unsubscribing" />
###[Up](#up) Unsubscribing from event
Sometimes it may be necessary to unsubscribe from event, this can be done with `::off()` method:
Example:
```php
<?php
$callback = function () {};
$Event    = \cs\Event::instance();
$Event->on('Module/event', $callback);
$Event->off('Module/event', $callback);
// Or, alternatively unsubscribe all callbacks from event
$Event->off('Module/event');
```

<a name="one-time-subscribing" />
###[Up](#up) One-time subscribing for event
Sometimes it may be useful to unsubscribe from event right after first dispatching, it is possible with method `::once()`:
```php
<?php
\cs\Event::instance()->once('Module/event', $callback);
```

<a name="dispatching" />
###[Up](#up) Dispatching of event

Events dispatching is performed by method `Event::instance()->fire()`, and it is quite simple too:
```php
<?php
\cs\Event::instance()->fire('System/Page/rebuild_cache');
```
Event with parameter:
```php
<?php
\cs\Event::instance()->fire(
    'System/Request/routing_replace/before',
    [
        'rc' => &$rc
    ]
);
```

Result of event dispatching may be checked:
```php
<?php
if (!\cs\Event::instance()->fire(
    'System/User/registration/before',
    [
        'email' => $email
    ]
)) {
    return false;
}
```
`\cs\Event->fire()` returns `false` only if callback returns boolean `false`, otherwise `true` will be returned. Also, if there are several callbacks, that registered to the same event, and one of them returns `false` - next callbacks will not be executed.

<a name="system-events" />
###[Up](#up) List of system events

Backend events:
* System/robots.txt
* admin/System/general/optimization/clean_pcache
* admin/System/modules/default
* admin/System/modules/update/before
* admin/System/modules/update/after
* admin/System/modules/update_system/before
* admin/System/modules/update_system/after
* admin/System/modules/enable/before
* admin/System/modules/enable/after
* admin/System/modules/disable/before
* admin/System/modules/disable/after
* admin/System/modules/install/before
* admin/System/modules/install/after
* admin/System/modules/uninstall/before
* admin/System/modules/uninstall/after
* admin/System/Menu
* System/App/render/before
* System/App/execute_router/before
* System/App/execute_router/after
* System/App/block_render
* System/App/render/after
* System/Config/init/before
* System/Config/init/after
* System/Config/changed
* System/general/languages/load
* System/Page/render/before
* System/Page/render/after
* System/Page/rebuild_cache
* System/Page/requirejs
* System/Request/routing_replace/before
* System/Request/routing_replace/after
* System/Session/init/before
* System/Session/init/before
* System/Session/del_session/before
* System/Session/del_session/after
* System/Session/del_all_sessions
* System/User/construct/before
* System/User/construct/after
* System/User/registration/before
* System/User/registration/after
* System/User/registration/confirmation/before
* System/User/registration/confirmation/after
* System/User/del/before
* System/User/del/after
* System/User/Group/add
* System/User/Group/del/before
* System/User/Group/del/after

Frontend events:
* admin/System/modules/default/before
* admin/System/modules/default/after
* admin/System/modules/disable/before
* admin/System/modules/disable/after
* admin/System/modules/enable/before
* admin/System/modules/enable/after
* admin/System/modules/update/before
* admin/System/modules/update/after
* admin/System/modules/uninstall/before
* admin/System/modules/uninstall/after
* admin/System/modules/install/before
* admin/System/modules/install/after
* admin/System/modules/update_system/before
* admin/System/modules/update_system/after
* admin/System/themes/current/before
* admin/System/themes/current/after
* admin/System/themes/update/before
* admin/System/themes/update/after
* cs-system-sign-in

For more details look corresponding classes and source files. It is easy to find any of these events by name.
