<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
/**
 * Base system functions, do not edit this file, or make it very carefully
 * otherwise system workability may be broken
 *
 * This particular file contains functions that work with global state (cookies, headers, status codes, etc.)
 */
use
	cs\Request,
	cs\Response;

/**
 * Function for setting cookies on all mirrors and taking into account cookies prefix. Parameters like in system function, but $path, $domain and $secure
 * are skipped, they are detected automatically, and $api parameter added in the end.
 *
 * @deprecated Use `cs\Response::cookie()` instead
 * @todo       Remove in 4.x
 *
 * @param string $name
 * @param string $value
 * @param int    $expire
 * @param bool   $httponly
 *
 * @return bool
 */
function _setcookie ($name, $value, $expire = 0, $httponly = false) {
	Response::instance()->cookie($name, $value, $expire, $httponly);
	return true;
}

/**
 * Function for getting of cookies, taking into account cookies prefix
 *
 * @deprecated Use `cs\Request::$cookie` instead
 * @todo       Remove in 4.x
 *
 * @param string $name
 *
 * @return false|string
 */
function _getcookie ($name) {
	$Request = Request::instance();
	return isset($Request->cookie[$name]) ? $Request->cookie[$name] : false;
}

/**
 * Is current path from administration area?
 *
 * @deprecated Use `cs\Response::$admin` instead
 * @todo       Remove in 4.x
 *
 * @param bool|null $admin_path
 *
 * @return bool
 */
function admin_path ($admin_path = null) {
	$Request = Request::instance();
	if ($admin_path !== null) {
		$Request->admin = (bool)$admin_path;
	}
	return $Request->admin;
}

/**
 * Is current path from api area?
 *
 * @deprecated Use `cs\Response::$api` instead
 * @todo       Remove in 4.x
 *
 * @param bool|null $api_path
 *
 * @return bool
 */
function api_path ($api_path = null) {
	$Request = Request::instance();
	if ($api_path !== null) {
		$Request->api = (bool)$api_path;
	}
	return $Request->api;
}

/**
 * Name of currently used module (for generation of current page)
 *
 * @deprecated Use `cs\Response::$current_module` instead
 * @todo       Remove in 4.x
 *
 * @param null|string $current_module
 *
 * @return string
 */
function current_module ($current_module = null) {
	$Request = Request::instance();
	if ($current_module !== null) {
		$Request->current_module = $current_module;
	}
	return $Request->current_module;
}

/**
 * Is current page a home page?
 *
 * @deprecated Use `cs\Response::$home_page` instead
 * @todo       Remove in 4.x
 *
 * @param bool|null $home_page
 *
 * @return bool
 */
function home_page ($home_page = null) {
	$Request = Request::instance();
	if ($home_page !== null) {
		$Request->home_page = (bool)$home_page;
	}
	return $Request->home_page;
}

/**
 * Send a raw HTTP header
 *
 * @deprecated Use `cs\Response` instead
 * @todo       Remove in 4.x
 *
 * @param string   $string             There are two special-case header calls. The first is a header that starts with the string "HTTP/" (case is not
 *                                     significant), which will be used to figure out the HTTP status code to send. For example, if you have configured Apache
 *                                     to use a PHP script to handle requests for missing files (using the ErrorDocument directive), you may want to make sure
 *                                     that your script generates the proper status code.
 * @param bool     $replace            The optional replace parameter indicates whether the header should replace a previous similar header,
 *                                     or add a second header of the same type. By default it will replace
 * @param int|null $http_response_code Forces the HTTP response code to the specified value
 */
function _header ($string, $replace = true, $http_response_code = null) {
	$Response = Response::instance();
	list($field, $value) = explode(';', $string, 2);
	$Response->header(trim($field), trim($value), $replace);
	if ($http_response_code !== null) {
		$Response->code = $http_response_code;
	}
}

