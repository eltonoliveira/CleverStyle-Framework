﻿<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Config, $Page, $User, $L;
/**
 * If AJAX request from local referer, user is registered - change password, otherwise - show error
 */
if (
	!$Config->server['referer']['local'] ||
	!$Config->server['ajax'] ||
	!isset($_POST['verify_hash'], $_POST['new_password'])
) {
	sleep(1);
	return;
} elseif (!$User->user()) {
	return;
} elseif (!$_POST['new_password']) {
	$Page->content($L->please_type_new_password);
	return;
} elseif (hash('sha224', $User->password_hash.$User->get_session()) != $_POST['verify_hash']) {
	$Page->content($L->wrong_current_password);
	return;
} elseif (($new_password = xor_string($_POST['new_password'], $User->password_hash)) == $User->password_hash) {
	$Page->content($L->current_new_password_equal);
	return;
}
if ($User->set('password_hash', $new_password)) {
	$id	= $User->id;
	$User->add_session($id);
	$Page->content('OK');
} else {
	$Page->content($L->change_password_server_error);
}