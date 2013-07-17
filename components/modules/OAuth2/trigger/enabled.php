<?php
/**
 * @package		OAuth2
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\OAuth2;
use			cs\Config,
			cs\Page,
			cs\Trigger,
			cs\User;
Trigger::instance()->register(
	'System/User/del_all_sessions',
	function ($data) {
		OAuth2::instance()->del_access(0, $data['id']);
	}
);
Trigger::instance()->register(
	'System/User/construct/before',
	function () {
		if (API && isset($_POST['client_id'], $_POST['access_token'])) {
			header('Cache-Control: no-store');
			header('Pragma: no-cache');
			$OAuth2						= OAuth2::instance();
			$Page						= Page::instance();
			if (!($client = $OAuth2->get_client($_POST['client_id']))) {
				code_header(400);
				$Page->Content	= _json_encode([
					'error'				=> 'access_denied',
					'error_description'	=> 'Invalid client id'
				]);
				interface_off();
				__finish();
			} elseif (!$client['active']) {
				code_header(403);
				$Page->Content	= _json_encode([
					'error'				=> 'access_denied',
					'error_description'	=> 'Inactive client id'
				]);
				interface_off();
				__finish();
			}
			$_SERVER['HTTP_USER_AGENT']	= "OAuth2-$client[name]-$client[id]";
			if (isset($_POST['client_secret'])) {
				if ($_POST['client_secret'] != $client['secret']) {
					code_header(400);
					$Page->Content	= _json_encode([
						'error'				=> 'access_denied',
						'error_description'	=> 'client_secret do not corresponds client_id'
					]);
					interface_off();
					__finish();
				}
				$token_data	= $OAuth2->get_token($_POST['access_token'], $_POST['client_id'], $client['secret']);
			} else {
				$token_data	= $OAuth2->get_token($_POST['access_token'], $_POST['client_id'], $client['secret']);
				if ($token_data['type']	== 'code') {
					code_header(403);
					$Page->Content	= _json_encode([
						'error'				=> 'invalid_request',
						'error_description'	=> 'This access_token can\'t be used without client_secret'
					]);
					interface_off();
					__finish();
				}
			}
			if ($token_data['expire_in'] < 0) {
				code_header(403);
				$Page->Content	= _json_encode([
					'error'				=> 'access_denied',
					'error_description'	=> 'access_token expired'
				]);
				interface_off();
				__finish();
			}
			$_POST['session']	= $token_data['session'];
			if (!Config::instance()->module('OAuth2')->guest_tokens) {
				Trigger::instance()->register(
					'System/User/construct/after',
					function () {
						if (!User::instance()->user()) {
							code_header(403);
							Page::instance()->Content	= _json_encode([
								'error'				=> 'access_denied',
								'error_description'	=> 'Guest tokens disabled'
							]);
							interface_off();
							__finish();
						}
					}
				);
			}
		}
	}
);
Trigger::instance()->register(
	'System/Index/mainmenu',
	function ($data) {
		if ($data['path'] == 'OAuth2') {
			$data['hide']	= true;
			return false;
		}
		return true;
	}
);