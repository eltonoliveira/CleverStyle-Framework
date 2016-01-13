/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer.cs.behaviors.cs-icon = [
	Polymer.cs.behaviors.this
	Polymer.cs.behaviors.tooltip
	hostAttributes		:
		hidden	: true
	observers			: [
		'_icon_changed(icon, flipX, flipY, mono, rotate, spin, spinStep)'
	]
	properties			:
		icon			:
			reflectToAttribute	: true
			type				: String
		flipX			:
			reflectToAttribute	: true
			type				: Boolean
			value				: false
		flipY			:
			reflectToAttribute	: true
			type				: Boolean
			value				: false
		mono			:
			reflectToAttribute	: true
			type				: Boolean
			value				: false
		rotate			:
			reflectToAttribute	: true
			type				: Number
			value				: false
		spin			:
			reflectToAttribute	: true
			type				: Boolean
			value				: false
		spinStep		:
			reflectToAttribute	: true
			type				: Boolean
			value				: false
	ready : !->
		@scopeSubtree(@$.content, true)
	_icon_changed : (icon, flipX, flipY, mono, rotate, spin, spinStep) !->
		if !icon
			@setAttribute('hidden', '')
			return
		@removeAttribute('hidden')
		content			= ''
		icons			= icon.split(' ')
		multiple_icons	= icons.length > 1
		for icon, index in icons
			icon_class	= ['fa fa-' + icon]
			if flipX
				icon_class.push('fa-flip-horizontal')
			if flipY
				icon_class.push('fa-flip-vertical')
			if mono
				icon_class.push('fa-fw')
			if rotate
				icon_class.push('fa-rotate-' + rotate)
			if spin
				icon_class.push('fa-spin')
			if spinStep
				icon_class.push('fa-pulse')
			if multiple_icons
				icon_class.push(if index then 'fa-stack-1x fa-inverse' else 'fa-stack-2x')
			content += """<i class="#{icon_class.join(' ')}"></i>"""
		if multiple_icons
			content	= """<span class="fa-stack">#content</span>"""
		@$.content.innerHTML = content
]
