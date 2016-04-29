<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blogs\admin;
use
	h,
	cs\Config,
	cs\Language,
	cs\Page,
	cs\modules\Blogs\Posts,
	cs\modules\Blogs\Sections;
use function
	cs\modules\Blogs\get_posts_rows;

class Controller {
	static function index () {
		if (!isset($_POST['mode'])) {
			return;
		}
		$L        = Language::prefix('blogs_');
		$Page     = Page::instance();
		$Posts    = Posts::instance();
		$Sections = Sections::instance();
		switch ($_POST['mode']) {
			case 'delete_post':
				if ($Posts->del($_POST['id'])) {
					$Page->success($L->changes_saved);
				} else {
					$Page->warning($L->changes_save_error);
				}
				break;
		}
	}
	static function general () {
		$L = Language::prefix('blogs_');
		Page::instance()
			->title($L->general)
			->content(
				h::cs_blogs_admin_general()
			);
	}
	static function browse_sections () {
		$L = Language::prefix('blogs_');
		Page::instance()
			->title($L->browse_sections)
			->content(
				h::cs_blogs_admin_sections_list()
			);
	}
	/**
	 * @param \cs\Request $Request
	 */
	static function browse_posts ($Request) {
		$Config = Config::instance();
		$L      = Language::prefix('blogs_');
		$page   = isset($Request->route[1]) ? (int)$Request->route[1] : 1;
		$page   = $page > 0 ? $page : 1;
		$total  = Posts::instance()->get_total_count();
		Page::instance()
			->title($L->browse_posts)
			->content(
				h::{'table.cs-table[center][list]'}(
					h::{'tr th'}(
						[
							$L->post_title,
							[
								'style' => 'width: 30%'
							]
						],
						[
							$L->post_sections,
							[
								'style' => 'width: 25%'
							]
						],
						[
							$L->post_tags,
							[
								'style' => 'width: 20%'
							]
						],
						[
							$L->author_date,
							[
								'style' => 'width: 15%'
							]
						],
						$L->action
					).
					h::{'tr| td'}(
						get_posts_rows($page)
					)
				).
				(
				$total ? h::{'.cs-block-margin.cs-text-center.cs-margin nav[is=cs-nav-pagination]'}(
					pages(
						$page,
						ceil($total / $Config->module('Blogs')->posts_per_page),
						function ($page) {
							return $page == 1 ? 'admin/Blogs/browse_posts' : "admin/Blogs/browse_posts/$page";
						}
					)
				) : ''
				)
			);
	}
	/**
	 * @param \cs\Request $Request
	 */
	static function delete_post ($Request) {
		$post = Posts::instance()->get($Request->route[1]);
		$L    = Language::prefix('blogs_');
		Page::instance()
			->title($L->deletion_of_post($post['title']))
			->content(
				h::{'form[is=cs-form][action=admin/Blogs/browse_posts]'}(
					h::{'h2.cs-text-center'}(
						$L->sure_to_delete_post($post['title'])
					).
					h::p(
						h::{'button[is=cs-button][type=submit][name=mode][value=delete_post]'}($L->yes).
						h::{'button[is=cs-button]'}(
							$L->cancel,
							[
								'type'    => 'button',
								'onclick' => 'history.go(-1);'
							]
						)
					).
					h::{'input[type=hidden][name=id]'}(
						[
							'value' => $post['id']
						]
					)
				)
			);
	}
	/**
	 * @param \cs\Request $Request
	 */
	static function delete_section ($Request) {
		$section = Sections::instance()->get($Request->route[1]);
		$L       = Language::prefix('blogs_');
		Page::instance()
			->title($L->deletion_of_posts_section($section['title']))
			->content(
				h::{'form[is=cs-form][action=admin/Blogs/browse_sections]'}(
					h::{'h2.cs-text-center'}(
						$L->sure_to_delete_posts_section($section['title'])
					).
					h::p(
						h::{'button[is=cs-button][type=submit][name=mode][value=delete_section]'}($L->yes).
						h::{'button[is=cs-button]'}(
							$L->cancel,
							[
								'type'    => 'button',
								'onclick' => 'history.go(-1);'
							]
						)
					).
					h::{'input[type=hidden][name=id]'}(
						[
							'value' => $section['id']
						]
					)
				)
			);
	}
}
