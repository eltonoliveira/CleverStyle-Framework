<?php
/**
 * @package   Shop
 * @attribute modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Event,
	cs\Language\Prefix,
	cs\Menu,
	cs\Request;

if (!function_exists(__NAMESPACE__.'\\add_menu_section_item')) {
	function add_menu_section_item ($section) {
		$L     = new Prefix('shop_');
		$Menu  = Menu::instance();
		$route = Request::instance()->route_path;
		$Menu->add_item(
			'Shop',
			$L->$section,
			[
				'href'    => "admin/Shop/$section",
				'primary' => isset($route[0]) && $route[0] == $section
			]
		);
	}
}
Event::instance()->on(
	'admin/System/Menu',
	function () {
		add_menu_section_item('general');
		add_menu_section_item('orders');
		add_menu_section_item('categories');
		add_menu_section_item('items');
		add_menu_section_item('attributes');
		add_menu_section_item('order_statuses');
		add_menu_section_item('shipping_types');
	}
);
