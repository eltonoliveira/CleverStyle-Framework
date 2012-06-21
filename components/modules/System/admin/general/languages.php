<?php
global $Config, $Index, $L;
$Config->reload_languages();
$a = &$Index;
$a->content(
	h::{'table.cs-admin-table.cs-left-even.cs-right-odd tr'}([
		h::td([
			h::info('current_language'),
			h::{'select#change_language.cs-form-element'}(
				$Config->core['active_languages'],
				[
					'name'		=> 'core[language]',
					'selected'	=> $Config->core['language'],
					'size'		=> 5
				]
			)
		]),
		h::td([
			h::info('active_languages'),
			h::{'select#change_active_languages.cs-form-element'}(
				$Config->core['languages'],
				[
					'name'		=> 'core[active_languages][]',
					'selected'	=> $Config->core['active_languages'],
					'size'		=> 5,
					'multiple'
				]
			)
		]),
		h::td([
			h::info('multilanguage'),
			h::{'input[type=radio]'}([
					'name'			=> 'core[multilanguage]',
					'checked'		=> $Config->core['multilanguage'],
					'value'			=> [0, 1],
					'in'			=> [$L->off, $L->on],
			])
		])
	])
);