<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
require __DIR__.'/loader_base.php';      //Inclusion of loader base
require __DIR__.'/functions_global.php'; //Inclusion of functions that work with global state
/**
 * Wrapper around default `$_SERVER` superglobal
 */
$_SERVER = new _SERVER($_SERVER);
/**
 * Including of custom files
 */
foreach (glob(CUSTOM.'/*.php') ?: [] as $custom) {
	include $custom;
}
unset($custom);
Core::instance();
try {
	try {
		/**
		 * System running
		 */
		try {
			Request::instance()->init_from_globals();
			Response::instance()->init_with_typical_default_settings();
			Index::instance();
		} catch (ExitException $e) {
			if ($e->getCode()) {
				throw $e;
			}
		}
		try {
			Index::instance(true)->__finish();
			Page::instance()->__finish();
			User::instance(true)->__finish();
		} catch (ExitException $e) {
			if ($e->getCode()) {
				throw $e;
			}
		}
	} catch (ExitException $e) {
		if ($e->getCode() >= 400) {
			Page::instance()->error($e->getMessage() ?: null, $e->getJson(), $e->getCode());
		}
	}
} catch (ExitException $e) {
}
Response::instance()->standard_output();
