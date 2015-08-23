###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	'is'		: 'cs-section-switcher'
	'extends'	: 'section'
	properties	:
		active	:
			observer	: 'active_changed'
			type		: Number
	ready : ->
		do =>
			for element in @children
				if element.active
					return
			@active = 0
			return
	active_changed : ->
		for element, index in @children
			if element.tagName == 'TEMPLATE'
				continue
			element.active = index == @active
			if index == @active
				element.setAttribute('active', '')
			else
				element.removeAttribute('active')
		return
)
