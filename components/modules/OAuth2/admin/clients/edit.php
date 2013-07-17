<?php
/**
 * @package		OAuth2
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */

namespace	cs\modules\OAuth2;
use			h,
			cs\Config,
			cs\Index,
			cs\Language,
			cs\Page;
$Index						= Index::instance();
$L							= Language::instance();
$client						= OAuth2::instance()->get_client(Config::instance()->route[2]);
Page::instance()->title($L->editing_of_client($client['name']));
$Index->apply_button		= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/OAuth2/clients/list';
$Index->content(
	h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
		$L->editing_of_client($client['name'])
	).
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr'}(
		h::{'th.ui-widget-header.ui-corner-all'}($L->client_name).
		h::{'td.ui-widget-content.ui-corner-all input[name=name]'}([
			'value'	=> $client['name']
		]),
		h::{'th.ui-widget-header.ui-corner-all'}('client_secret').
		h::{'td.ui-widget-content.ui-corner-all input[name=secret]'}([
			'value'	=> $client['secret']
		]),
		h::{'th.ui-widget-header.ui-corner-all'}($L->client_domain).
		h::{'td.ui-widget-content.ui-corner-all input[name=domain]'}([
			'value'	=> $client['domain']
		]),
		h::{'th.ui-widget-header.ui-corner-all'}($L->active).
		h::{'td.ui-widget-content.ui-corner-all input[type=radio][name=active]'}([
			'checked'	=> $client['active'],
			'value'		=> [0, 1],
			'in'		=> [$L->no, $L->yes]
		])
	).
	h::{'input[type=hidden][name=id]'}([
		'value'	=> $client['id']
	]).
	h::{'input[type=hidden][name=mode][value=edit]'}()
);