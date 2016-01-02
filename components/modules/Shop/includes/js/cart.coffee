###*
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
cs.shop.cart	= do ->
	items_storage	=
		get	: ->
			JSON.parse(localStorage.shop_cart_items || '{}')
		set	: (items) ->
			localStorage.shop_cart_items	= JSON.stringify(items)
	params			= do ->
		params_			= localStorage.shop_cart_params
		params_			= if params_ then JSON.parse(params_) else {}
		update_params	= ->
			localStorage.shop_cart_params = JSON.stringify(params_)
		`{
			get shipping_type () { return params_.shipping_type; },
			set shipping_type (val) { params_.shipping_type = val; update_params(); },

			get shipping_username () { return params_.shipping_username; },
			set shipping_username (val) { params_.shipping_username = val; update_params(); },

			get phone () { return params_.phone; },
			set phone (val) { params_.phone = val; update_params(); },

			get address () { return params_.address; },
			set address (val) { params_.address = val; update_params(); },

			get comment () { return params_.comment; },
			set comment (val) { params_.comment = val; update_params(); }
		}`
	get_items	= ->
		items_storage.get()
	get_item	= (id) ->
		get_items()[id] || 0
	add_item	= (id) ->
		items	= get_items()
		if items[id]
			++items[id]
		else
			items[id]	= 1
		items_storage.set(items)
		items[id]
	set_item	= (id, units) ->
		items		= get_items()
		items[id]	= units
		items_storage.set(items)
	del_item	= (id) ->
		items	= get_items()
		delete items[id]
		items_storage.set(items)
	clean		= ->
		items_storage.set({})
	return {
		get_all			: get_items
		get_calculated	: (callback) ->
			items	= get_items()
			if !items
				return
			$.ajax(
				url		: 'api/Shop/cart'
				data	:
					items			: items
					shipping_type	: params.shipping_type
				type	: 'get'
				success	: callback
			)
		get				: get_item
		add				: add_item
		set				: set_item
		del				: del_item
		clean			: clean
		params			: params
	}
