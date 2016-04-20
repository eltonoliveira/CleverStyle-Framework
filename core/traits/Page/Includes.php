<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Page;
use
	cs\App,
	cs\Core,
	cs\Config,
	cs\Event,
	cs\Language,
	cs\Request,
	cs\Response,
	cs\User,
	h,
	cs\Page\Includes\Cache,
	cs\Page\Includes\Collecting,
	cs\Page\Includes\RequireJS;

/**
 * Includes management for `cs\Page` class
 *
 * @property string $Title
 * @property string $Description
 * @property string $canonical_url
 * @property string $Head
 * @property string $post_Body
 * @property string $theme
 */
trait Includes {
	use
		Cache,
		Collecting,
		RequireJS;
	/**
	 * @var array
	 */
	protected $core_html;
	/**
	 * @var array
	 */
	protected $core_js;
	/**
	 * @var array
	 */
	protected $core_css;
	/**
	 * @var string
	 */
	protected $core_config;
	/**
	 * @var array
	 */
	protected $html;
	/**
	 * @var array
	 */
	protected $js;
	/**
	 * @var array
	 */
	protected $css;
	/**
	 * @var string
	 */
	protected $config;
	/**
	 * Base name is used as prefix when creating CSS/JS/HTML cache files in order to avoid collisions when having several themes and languages
	 * @var string
	 */
	protected $pcache_basename_path;
	protected function init_includes () {
		$this->core_html            = ['path' => [], 'plain' => ''];
		$this->core_js              = ['path' => [], 'plain' => ''];
		$this->core_css             = ['path' => [], 'plain' => ''];
		$this->core_config          = '';
		$this->html                 = ['path' => [], 'plain' => ''];
		$this->js                   = ['path' => [], 'plain' => ''];
		$this->css                  = ['path' => [], 'plain' => ''];
		$this->config               = '';
		$this->pcache_basename_path = '';
	}
	/**
	 * Including of Web Components
	 *
	 * @param string|string[] $add  Path to including file, or code
	 * @param string          $mode Can be <b>file</b> or <b>code</b>
	 *
	 * @return \cs\Page
	 */
	function html ($add, $mode = 'file') {
		return $this->html_internal($add, $mode);
	}
	/**
	 * @param string|string[] $add
	 * @param string          $mode
	 * @param bool            $core
	 *
	 * @return \cs\Page
	 */
	protected function html_internal ($add, $mode = 'file', $core = false) {
		return $this->include_common('html', $add, $mode, $core);
	}
	/**
	 * Including of JavaScript
	 *
	 * @param string|string[] $add  Path to including file, or code
	 * @param string          $mode Can be <b>file</b> or <b>code</b>
	 *
	 * @return \cs\Page
	 */
	function js ($add, $mode = 'file') {
		return $this->js_internal($add, $mode);
	}
	/**
	 * @param string|string[] $add
	 * @param string          $mode
	 * @param bool            $core
	 *
	 * @return \cs\Page
	 */
	protected function js_internal ($add, $mode = 'file', $core = false) {
		return $this->include_common('js', $add, $mode, $core);
	}
	/**
	 * Including of CSS
	 *
	 * @param string|string[] $add  Path to including file, or code
	 * @param string          $mode Can be <b>file</b> or <b>code</b>
	 *
	 * @return \cs\Page
	 */
	function css ($add, $mode = 'file') {
		return $this->css_internal($add, $mode);
	}
	/**
	 * @param string|string[] $add
	 * @param string          $mode
	 * @param bool            $core
	 *
	 * @return \cs\Page
	 */
	protected function css_internal ($add, $mode = 'file', $core = false) {
		return $this->include_common('css', $add, $mode, $core);
	}
	/**
	 * @param string          $what
	 * @param string|string[] $add
	 * @param string          $mode
	 * @param bool            $core
	 *
	 * @return \cs\Page
	 */
	protected function include_common ($what, $add, $mode, $core) {
		if (!$add) {
			return $this;
		}
		if (is_array($add)) {
			foreach (array_filter($add) as $style) {
				$this->include_common($what, $style, $mode, $core);
			}
		} else {
			if ($core) {
				$what = "core_$what";
			}
			$target = &$this->$what;
			if ($mode == 'file') {
				$target['path'][] = $add;
			} elseif ($mode == 'code') {
				$target['plain'] .= "$add\n";
			}
		}
		return $this;
	}
	/**
	 * Add config on page to make it available on frontend
	 *
	 * @param mixed  $config_structure        Any scalar type or array
	 * @param string $target                  Target is property of `window` object where config will be inserted as value, nested properties like `cs.sub.prop`
	 *                                        are supported and all nested properties are created on demand. It is recommended to use sub-properties of `cs`
	 *
	 * @return \cs\Page
	 */
	function config ($config_structure, $target) {
		return $this->config_internal($config_structure, $target);
	}
	/**
	 * @param mixed  $config_structure
	 * @param string $target
	 * @param bool   $core
	 *
	 * @return \cs\Page
	 */
	protected function config_internal ($config_structure, $target, $core = false) {
		$config = h::script(
			json_encode($config_structure, JSON_UNESCAPED_UNICODE),
			[
				'target' => $target,
				'class'  => 'cs-config',
				'type'   => 'application/json'
			]
		);
		if ($core) {
			$this->core_config .= $config;
		} else {
			$this->config .= $config;
		}
		return $this;
	}
	/**
	 * Getting of HTML, JS and CSS includes
	 *
	 * @return \cs\Page
	 */
	protected function add_includes_on_page () {
		$Config = Config::instance(true);
		if (!$Config) {
			return $this;
		}
		/**
		 * Base name for cache files
		 */
		$this->pcache_basename_path = PUBLIC_CACHE.'/'.$this->theme.'_'.Language::instance()->clang;
		/**
		 * Some JS configs required by system
		 */
		$this->add_system_configs();
		// TODO: I hope some day we'll get rid of this sh*t :(
		$this->ie_edge();
		$Request = Request::instance();
		/**
		 * If CSS and JavaScript compression enabled
		 */
		if ($Config->core['cache_compress_js_css'] && !($Request->admin_path && isset($Request->query['debug']))) {
			$this->webcomponents_polyfill($Request, true);
			list($includes, $preload) = $this->get_includes_and_preload_resource_for_page_with_compression($Config, $Request);
			$this->add_preloads($preload);
		} else {
			$this->webcomponents_polyfill($Request, false);
			/**
			 * Language translation is added explicitly only when compression is disabled, otherwise it will be in compressed JS file
			 */
			$this->config_internal(Language::instance(), 'cs.Language', true);
			$this->config_internal($this->get_requirejs_paths(), 'requirejs.paths', true);
			$includes = $this->get_includes_for_page_without_compression($Config, $Request);
		}
		$this->css_internal($includes['css'], 'file', true);
		$this->js_internal($includes['js'], 'file', true);
		$this->html_internal($includes['html'], 'file', true);
		$this->add_includes_on_page_manually_added($Config);
		return $this;
	}
	/**
	 * Add JS polyfills for IE/Edge
	 */
	protected function ie_edge () {
		if (!preg_match('/Trident|Edge/', Request::instance()->header('user-agent'))) {
			return;
		}
		$this->js_internal(
			get_files_list(DIR."/includes/js/microsoft_sh*t", "/.*\\.js$/i", 'f', "includes/js/microsoft_sh*t", true),
			'file',
			true
		);
	}
	protected function add_system_configs () {
		$Config         = Config::instance();
		$Request        = Request::instance();
		$User           = User::instance();
		$current_module = $Request->current_module;
		$this->config_internal(
			[
				'base_url'              => $Config->base_url(),
				'current_base_url'      => $Config->base_url().'/'.($Request->admin_path ? 'admin/' : '').$current_module,
				'public_key'            => Core::instance()->public_key,
				'module'                => $current_module,
				'in_admin'              => (int)$Request->admin_path,
				'is_admin'              => (int)$User->admin(),
				'is_user'               => (int)$User->user(),
				'is_guest'              => (int)$User->guest(),
				'password_min_length'   => (int)$Config->core['password_min_length'],
				'password_min_strength' => (int)$Config->core['password_min_strength'],
				'debug'                 => (int)DEBUG,
				'route'                 => $Request->route,
				'route_path'            => $Request->route_path,
				'route_ids'             => $Request->route_ids
			],
			'cs',
			true
		);
		if ($User->admin()) {
			$this->config_internal((int)$Config->core['simple_admin_mode'], 'cs.simple_admin_mode', true);
		}
	}
	/**
	 * Hack: Add WebComponents Polyfill for browsers without native Shadow DOM support
	 *
	 * @param Request $Request
	 * @param bool    $with_compression
	 */
	protected function webcomponents_polyfill ($Request, $with_compression) {
		if ($Request->cookie('shadow_dom') == 1) {
			return;
		}
		$file = 'includes/js/WebComponents-polyfill/webcomponents-custom.min.js';
		if ($with_compression) {
			$compressed_file = PUBLIC_CACHE.'/webcomponents.js';
			if (!file_exists($compressed_file)) {
				$content = file_get_contents(DIR."/$file");
				file_put_contents($compressed_file, gzencode($content, 9), LOCK_EX | FILE_BINARY);
				file_put_contents("$compressed_file.hash", substr(md5($content), 0, 5));
			}
			$hash = file_get_contents("$compressed_file.hash");
			$this->js_internal("storage/pcache/webcomponents.js?$hash", 'file', true);
		} else {
			$this->js_internal($file, 'file', true);
		}
	}
	/**
	 * @param string[] $preload
	 */
	protected function add_preloads ($preload) {
		$Response = Response::instance();
		foreach ($preload as $resource) {
			$extension = explode('?', file_extension($resource))[0];
			switch ($extension) {
				case 'jpeg':
				case 'jpe':
				case 'jpg':
				case 'gif':
				case 'png':
				case 'svg':
				case 'svgz':
					$as = 'image';
					break;
				case 'ttf':
				case 'ttc':
				case 'otf':
				case 'woff':
				case 'woff2':
				case 'eot':
					$as = 'font';
					break;
				case 'css':
					$as = 'style';
					break;
				case 'js':
					$as = 'script';
					break;
				case 'html':
					$as = 'document';
					break;
				default:
					continue 2;
			}
			$resource = str_replace(' ', '%20', $resource);
			$Response->header('Link', "<$resource>; rel=preload; as=$as'", false);
		}
	}
	/**
	 * @param Config  $Config
	 * @param Request $Request
	 *
	 * @return array
	 */
	protected function get_includes_and_preload_resource_for_page_with_compression ($Config, $Request) {
		/**
		 * Rebuilding HTML, JS and CSS cache if necessary
		 */
		if (!file_exists("$this->pcache_basename_path.json")) {
			list($dependencies, $includes_map) = $this->get_includes_dependencies_and_map($Config);
			$compressed_includes_map    = [];
			$not_embedded_resources_map = [];
			foreach ($includes_map as $filename_prefix => $local_includes) {
				// We replace `/` by `+` to make it suitable for filename
				$filename_prefix                           = str_replace('/', '+', $filename_prefix);
				$compressed_includes_map[$filename_prefix] = $this->cache_compressed_includes_files(
					"$this->pcache_basename_path:$filename_prefix",
					$local_includes,
					$Config->core['vulcanization'],
					$not_embedded_resources_map
				);
			}
			unset($includes_map, $filename_prefix, $local_includes);
			file_put_json("$this->pcache_basename_path.json", [$dependencies, $compressed_includes_map, array_filter($not_embedded_resources_map)]);
			Event::instance()->fire('System/Page/rebuild_cache');
		}
		list($dependencies, $compressed_includes_map, $not_embedded_resources_map) = file_get_json("$this->pcache_basename_path.json");
		$includes = $this->get_normalized_includes($dependencies, $compressed_includes_map, '+', $Request);
		$preload  = [];
		foreach (array_merge(...array_values($includes)) as $path) {
			if (isset($not_embedded_resources_map[$path])) {
				$preload[] = $not_embedded_resources_map[$path];
			}
		}
		return [$includes, array_merge(...$preload)];
	}
	/**
	 * @param array      $dependencies
	 * @param string[][] $includes_map
	 * @param string     $separator `+` or `/`
	 * @param Request    $Request
	 *
	 * @return array
	 */
	protected function get_normalized_includes ($dependencies, $includes_map, $separator, $Request) {
		$current_module = $Request->current_module;
		/**
		 * Current URL based on controller path (it better represents how page was rendered)
		 */
		$current_url = array_slice(App::instance()->controller_path, 1);
		$current_url = ($Request->admin_path ? "admin$separator" : '')."$current_module$separator".implode($separator, $current_url);
		/**
		 * Narrow the dependencies to current module only
		 */
		$dependencies          = array_merge(
			isset($dependencies[$current_module]) ? $dependencies[$current_module] : [],
			$dependencies['System']
		);
		$system_includes       = [];
		$dependencies_includes = [];
		$includes              = [];
		foreach ($includes_map as $path => $local_includes) {
			if ($path == 'System') {
				$system_includes = $local_includes;
			} elseif ($this->is_dependency($dependencies, $path, '/', $Request)) {
				$dependencies_includes[] = $local_includes;
			} elseif (mb_strpos($current_url, $path) === 0) {
				$includes[] = $local_includes;
			}
		}
		return array_merge_recursive($system_includes, ...$dependencies_includes, ...$includes);
	}
	/**
	 * @param array   $dependencies
	 * @param string  $url
	 * @param string  $separator `+` or `/`
	 * @param Request $Request
	 *
	 * @return bool
	 */
	protected function is_dependency ($dependencies, $url, $separator, $Request) {
		$url_exploded = explode($separator, $url);
		/** @noinspection NestedTernaryOperatorInspection */
		$url_module = $url_exploded[0] != 'admin' ? $url_exploded[0] : (@$url_exploded[1] ?: '');
		return
			$url_module !== Config::SYSTEM_MODULE &&
			in_array($url_module, $dependencies) &&
			(
				$Request->admin_path || $Request->admin_path == ($url_exploded[0] == 'admin')
			);
	}
	/**
	 * @param Config  $Config
	 * @param Request $Request
	 *
	 * @return string[][]
	 */
	protected function get_includes_for_page_without_compression ($Config, $Request) {
		// To determine all dependencies and stuff we need `$Config` object to be already created
		list($dependencies, $includes_map) = $this->get_includes_dependencies_and_map($Config);
		$includes = $this->get_normalized_includes($dependencies, $includes_map, '/', $Request);
		return $this->add_versions_hash($this->absolute_path_to_relative($includes));
	}
	/**
	 * @param string[]|string[][] $path
	 *
	 * @return string[]|string[][]
	 */
	protected function absolute_path_to_relative ($path) {
		return _substr($path, strlen(DIR) + 1);
	}
	/**
	 * @param string[][] $includes
	 *
	 * @return string[][]
	 */
	protected function add_versions_hash ($includes) {
		$content     = array_reduce(
			get_files_list(DIR.'/components', '/^meta\.json$/', 'f', true, true),
			function ($content, $file) {
				return $content.file_get_contents($file);
			}
		);
		$content_md5 = substr(md5($content), 0, 5);
		foreach ($includes as &$files) {
			foreach ($files as &$file) {
				$file .= "?$content_md5";
			}
			unset($file);
		}
		return $includes;
	}
	/**
	 * @param Config $Config
	 */
	protected function add_includes_on_page_manually_added ($Config) {
		$configs = $this->core_config.$this->config;
		/** @noinspection NestedTernaryOperatorInspection */
		$styles =
			array_reduce(
				array_merge($this->core_css['path'], $this->css['path']),
				function ($content, $href) {
					return "$content<link href=\"/$href\" rel=\"stylesheet\" shim-shadowdom>\n";
				}
			).
			h::style($this->core_css['plain'].$this->css['plain'] ?: false);
		/** @noinspection NestedTernaryOperatorInspection */
		$scripts      =
			array_reduce(
				array_merge($this->core_js['path'], $this->js['path']),
				function ($content, $src) {
					return "$content<script src=\"/$src\"></script>\n";
				}
			).
			h::script($this->core_js['plain'].$this->js['plain'] ?: false);
		$html_imports =
			array_reduce(
				array_merge($this->core_html['path'], $this->html['path']),
				function ($content, $href) {
					return "$content<link href=\"/$href\" rel=\"import\">\n";
				}
			).
			$this->core_html['plain'].$this->html['plain'];
		$this->Head .= $configs.$styles;
		if ($Config->core['put_js_after_body']) {
			$this->post_Body .= $scripts.$html_imports;
		} else {
			$this->Head .= $scripts.$html_imports;
		}
	}
}
