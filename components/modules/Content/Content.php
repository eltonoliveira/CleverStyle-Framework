<?php
/**
 * @package   Content
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Content;

use
	cs\Cache\Prefix,
	cs\Config,
	cs\Language,
	cs\CRUD_helpers,
	cs\Singleton;

/**
 * @method static $this instance($check = false)
 */
class Content {
	use
		CRUD_helpers,
		Singleton;

	protected $data_model                  = [
		'key'     => 'text',
		'title'   => 'ml:text',
		'content' => 'ml:',
		'type'    => 'set:text,html'
	];
	protected $table                       = '[prefix]content';
	protected $data_model_ml_group         = 'Content';
	protected $data_model_files_tag_prefix = 'Content';
	/**
	 * @var Prefix
	 */
	protected $cache;

	protected function construct () {
		$this->cache = new Prefix('Content');
	}
	/**
	 * @inheritdoc
	 */
	protected function cdb () {
		return Config::instance()->module('Content')->db('content');
	}
	/**
	 * Add new content
	 *
	 * @param string $key     Key associated with content, works like id
	 * @param string $title   Content title
	 * @param string $content Content itself
	 * @param string $type    Type of content: <b>text</b> or <b>html</b>. Influences on editor type
	 *
	 * @return bool
	 */
	function add ($key, $title, $content, $type) {
		$key    = str_replace(['/', '?', '#', '"', '<', '>'], '_', $key);
		$result = $this->create($key, $title, $content, $type);
		if ($result) {
			$this->clean_cache($key);
		}
		return (bool)$result;
	}
	/**
	 * @param string $key
	 */
	protected function clean_cache ($key) {
		$this->cache->del("$key/".Language::instance()->clang);
	}
	/**
	 * Get content
	 *
	 * @param string|string[] $key
	 *
	 * @return false|mixed
	 */
	function get ($key) {
		if (is_array($key)) {
			foreach ($key as &$k) {
				$k = $this->get($k);
			}
			return $key;
		}
		$key = str_replace(['/', '?', '#', '"', '<', '>'], '_', $key);
		return $this->cache->get(
			"$key/".Language::instance()->clang,
			function () use ($key) {
				return $this->read($key);
			}
		);
	}
	/**
	 * Get keys of all content items
	 *
	 * @return int[]|false
	 */
	function get_all () {
		return $this->search([], 1, PHP_INT_MAX, 'key', true);
	}
	/**
	 * Set content
	 *
	 * @param string $key     Key associated with content, works like id
	 * @param string $title   Content title
	 * @param string $content Content itself
	 * @param string $type    Type of content: <b>text</b> or <b>html</b>. Influences on editor type
	 *
	 * @return bool
	 */
	function set ($key, $title, $content, $type) {
		$result = $this->update($key, $title, $content, $type);
		if ($result) {
			$this->clean_cache($key);
		}
		return $result;
	}
	/**
	 * Delete content
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	function del ($key) {
		$result = $this->delete($key);
		if ($result) {
			$this->clean_cache($key);
		}
		return $result;
	}
}
