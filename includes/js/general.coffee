###*
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
$ ->
	async_call [
		->
			window.session_id	= getcookie('session')
			$.ajaxSetup
				type	: 'post'
				data	:
					session	: session_id
		->
			$('form:not(.cs-no-ui)').addClass('uk-form')
		->
			$(':radio:not(.cs-no-ui)').cs_radio()
		->
			$(':checkbox:not(.cs-no-ui)').cs_checkbox()
		->
			$('.cs-table').addClass('uk-table uk-table-condensed uk-table-hover')
		->
			$(':button:not(.cs-no-ui), .cs-button, .cs-button-compact')
				.addClass('uk-button')
				.disableSelection()
		->
			$('.cs-dialog').cs_modal()
		->
			$('textarea:not(.cs-no-ui)')
				.not('.cs-no-resize, .EDITOR, .SIMPLE_EDITOR')
				.addClass('cs-textarea-autosize')
				.autosize
					append	: "\n"
		->
			$(".cs-info").cs_tooltip()
		->
			$('.cs-tabs').cs_tabs()
		->
			$('.cs-header-login-slide').click ->
				$('.cs-header-guest-form').slideUp()
				$('.cs-header-login-form').slideDown()
				$('.cs-header-login-email').focus()
			$('.cs-header-registration-slide').click ->
				$('.cs-header-guest-form').slideUp()
				$('.cs-header-registration-form').slideDown()
				$('.cs-header-registration-email').focus()
			$('.cs-header-restore-password-slide').click ->
				$('.cs-header-login-form, .cs-header-registration-form').slideUp()
				$('.cs-header-restore-password-form').slideDown()
				$('.cs-header-restore-password-email').focus()
			$('.cs-header-login-email, .cs-header-user-password').keyup (event) ->
				if event.which == 13
					$('.cs-header-login-process').click()
			$('.cs-header-registration-email').keyup (event) ->
				if event.which == 13
					$('.cs-header-registration-process').click()
			$('.cs-header-login-process').click ->
				login($('.cs-header-login-email').val(), $('.cs-header-user-password').val())
			$('.cs-header-logout-process').click ->
				logout()
			$('.cs-show-password').click ->
				$this	= $(this)
				pass_input = $this.parent().next().children('input')
				if pass_input.prop('type') == 'password'
					pass_input.prop('type', 'text')
					$this.addClass('uk-icon-unlock').removeClass('uk-icon-lock')
				else
					pass_input.prop('type', 'password')
					$this.addClass('uk-icon-lock').removeClass('uk-icon-unlock')
			$('#current_password').click ->
				$this		= $(this)
				password	= $('.cs-profile-current-password')
				if password.prop('type') == 'password'
					password.prop('type', 'text')
					$this.addClass('uk-icon-unlock').removeClass('uk-icon-lock')
				else
					password.prop('type', 'password')
					$this.addClass('uk-icon-lock').removeClass('uk-icon-unlock')
			$('#new_password').click ->
				$this		= $(this)
				password	= $('.cs-profile-new-password')
				if password.prop('type') == 'password'
					password.prop('type', 'text')
					$this.addClass('uk-icon-unlock').removeClass('uk-icon-lock')
				else
					password.prop('type', 'password')
					$this.addClass('uk-icon-lock').removeClass('uk-icon-unlock')
			$('.cs-header-registration-process').click ->
				modal	= $('<div title="' + L.rules_agree + '"><div>' + rules_text + '<p class="cs-right"><button class="cs-registration-continue uk-button uk-button-primary">' + L.yes + '</button></p></div></div>')
					.appendTo('body')
					.cs_modal('show')
					.on(
						'uk.modal.hide'
						->
							$(this).remove()
					)
				modal
					.find('.cs-registration-continue')
					.click ->
						modal.cs_modal('close').remove()
						registration $('.cs-header-registration-email').val()
			$('.cs-header-restore-password-process').click ->
				restore_password $('.cs-header-restore-password-email').val()
			$('.cs-profile-change-password').click ->
				change_password $('.cs-profile-current-password').val(), $('.cs-profile-new-password').val()
			$('.cs-header-back').click ->
				$('.cs-header-guest-form').slideDown()
				$('.cs-header-registration-form, .cs-header-login-form, .cs-header-restore-password-form').slideUp()
		->
			if in_admin
				$('.cs-reload-button').click ->
					location.reload()
				$('#change_theme, #change_color_scheme, #change_language').click ->
					$('#apply_settings').click()
				$('#change_active_themes').change ->
					$(this)
						.find("option[value='" + $('#change_theme').val() + "']")
						.prop('selected', true)
				$('#change_active_languages').change ->
					$(this)
						.find("option[value='" + $('#change_language').val() + "']")
						.prop('selected', true)
				$('#cs-system-license-open').click ->
					$('#cs-system-license').cs_modal('show')
				$('.cs-permissions-invert').click ->
					$(this)
						.parentsUntil('div')
						.find(':radio:not(:checked)[value!=-1]')
						.prop('checked', true)
						.change()
				$('.cs-permissions-allow-all').click ->
					$(this)
						.parentsUntil('div')
						.find(':radio[value=1]')
						.prop('checked', true)
						.change()
				$('.cs-permissions-deny-all').click ->
					$(this)
						.parentsUntil('div')
						.find(':radio[value=0]')
						.prop('checked', true)
						.change()
				$('#cs-users-search-columns').selectable
					stop: ->
						result	= []
						li		= $(this).children('li')
						li
							.filter('.uk-button-primary:not(.ui-selected)')
							.removeClass('uk-button-primary')
						li
							.filter('.ui-selected')
							.addClass('uk-button-primary')
							.each ->
								result.push $(this).text().trim()
						$('#cs-users-search-selected-columns').val(result.join(';'))
				$('#block_users_search')
					.keyup (event) ->
						if event.which != 13
							return
						$('.cs-block-users-changed')
							.removeClass('cs-block-users-changed')
							.appendTo('#cs-block-users-changed-permissions')
							.each ->
								id		= $(this).find(':radio:first').attr('name')
								found	= $('#cs-block-users-search-found')
								found.val(
									found.val() + ',' + id.substring(6, id.length-1)
								)
						$.ajax
							url		: current_base_url + '/' + route[0] + '/' + route[1] + '/search_users'
							data	:
								found_users		: $('#cs-block-users-search-found').val(),
								permission		: $(this).attr('permission'),
								search_phrase	: $(this).val()
							,
							success	: (result) ->
								$('#block_users_search_results')
									.html(result)
									.find(':radio')
									.cs_radio()
									.change ->
										$(this)
											.parentsUntil('tr')
											.parent()
											.addClass('cs-block-users-changed')
					.keydown (event) ->
						event.which != 13
				$('#cs-top-blocks-items, #cs-left-blocks-items, #cs-floating-blocks-items, #cs-right-blocks-items, #cs-bottom-blocks-items')
					.disableSelection()
					.sortable
						connectWith	: '.cs-blocks-items'
						items		: 'li:not(:first)'
						cancel		: ':first'
						stop		: ->
							$('#cs-blocks-position').val(
								json_encode(
									top			: $('#cs-top-blocks-items').sortable('toArray')
									left		: $('#cs-left-blocks-items').sortable('toArray')
									floating	: $('#cs-floating-blocks-items').sortable('toArray')
									right		: $('#cs-right-blocks-items').sortable('toArray')
									bottom		: $('#cs-bottom-blocks-items').sortable('toArray')
								)
							)
				$('#cs-users-groups-list, #cs-users-groups-list-selected')
					.disableSelection()
					.sortable
						connectWith	: '#cs-users-groups-list, #cs-users-groups-list-selected'
						items		: 'li:not(:first)'
						cancel		: ':first'
						stop		: ->
							$('#cs-users-groups-list')
								.find('.uk-alert-success')
								.removeClass('uk-alert-success')
								.addClass('uk-alert-warning')
							selected	= $('#cs-users-groups-list-selected')
							selected
								.find('.uk-alert-warning')
								.removeClass('uk-alert-warning')
								.addClass('uk-alert-success')
							$('#cs-user-groups').val(
								json_encode(
									selected.sortable('toArray')
								)
							)
				$('#auto_translation_engine')
					.find('select')
					.change ->
						$('#auto_translation_engine_settings').html(
							base64_decode(
								$(this).children(':selected').data('settings')
							)
						)
		->
			if cookie = getcookie('setcookie')
				for own i of cookie
					$.post(cookie[i])
				setcookie('setcookie', '')
	]
	return