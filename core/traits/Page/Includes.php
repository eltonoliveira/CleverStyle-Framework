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
	cs\User,
	h,
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
	protected $pcache_basename;
	protected function init_includes () {
		$this->core_html       = ['path' => [], 'plain' => ''];
		$this->core_js         = ['path' => [], 'plain' => ''];
		$this->core_css        = ['path' => [], 'plain' => ''];
		$this->core_config     = '';
		$this->html            = ['path' => [], 'plain' => ''];
		$this->js              = ['path' => [], 'plain' => ''];
		$this->css             = ['path' => [], 'plain' => ''];
		$this->config          = '';
		$this->pcache_basename = '';
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
		$this->pcache_basename = $this->theme.'_'.Language::instance()->clang;
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
			$includes = $this->get_includes_for_page_with_compression();
		} else {
			$this->webcomponents_polyfill($Request, false);
			/**
			 * Language translation is added explicitly only when compression is disabled, otherwise it will be in compressed JS file
			 */
			$this->config_internal(Language::instance(), 'cs.Language', true);
			$this->config_internal($this->get_requirejs_paths(), 'requirejs.paths', true);
			$includes = $this->get_includes_for_page_without_compression($Config);
		}
		$this->css_internal($includes['css'], 'file', true);
		$this->js_internal($includes['js'], 'file', true);
		$this->html_internal($includes['html'], 'file', true);
		$this->add_includes_on_page_manually_added($Config);
		return $this;
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
	 * @return array[]
	 */
	protected function get_includes_for_page_with_compression () {
		/**
		 * Rebuilding HTML, JS and CSS cache if necessary
		 */
		if (file_exists(PUBLIC_CACHE."/$this->pcache_basename.json")) {
			list($dependencies, $compressed_includes_map) = file_get_json(PUBLIC_CACHE."/$this->pcache_basename.json");
		} else {
			list($dependencies, $includes_map) = $this->includes_dependencies_and_map();
			$compressed_includes_map = [];
			foreach ($includes_map as $filename_prefix => $local_includes) {
				// We replace `/` by `+` to make it suitable for filename
				$filename_prefix                           = str_replace('/', '+', $filename_prefix);
				$compressed_includes_map[$filename_prefix] = $this->create_cached_includes_files($filename_prefix, $local_includes);
			}
			unset($includes_map, $filename_prefix, $local_includes);
			file_put_json(PUBLIC_CACHE."/$this->pcache_basename.json", [$dependencies, $compressed_includes_map]);
			Event::instance()->fire('System/Page/rebuild_cache');
		}
		return $this->get_normalized_includes($dependencies, $compressed_includes_map, '+');
	}
	/**
	 * @param array      $dependencies
	 * @param string[][] $includes_map
	 * @param string     $separator `+` or `/`
	 *
	 * @return array
	 */
	protected function get_normalized_includes ($dependencies, $includes_map, $separator) {
		$Request        = Request::instance();
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
			} elseif ($this->get_includes_is_dependency($dependencies, $path, '/')) {
				$dependencies_includes[] = $local_includes;
			} elseif (mb_strpos($current_url, $path) === 0) {
				$includes[] = $local_includes;
			}
		}
		return array_merge_recursive($system_includes, ...$dependencies_includes, ...$includes);
	}
	/**
	 * Creates cached version of given HTML, JS and CSS files.
	 * Resulting file name consists of `$filename_prefix` and `$this->pcache_basename`
	 *
	 * @param string $filename_prefix
	 * @param array  $includes Array of paths to files, may have keys: `css` and/or `js` and/or `html`
	 *
	 * @return array
	 */
	protected function create_cached_includes_files ($filename_prefix, $includes) {
		$local_includes = [];
		foreach ($includes as $extension => $files) {
			$content  = $this->create_cached_includes_files_process_files($extension, $filename_prefix, $files);
			$filename = "$this->pcache_basename:$filename_prefix.$extension";
			file_put_contents(PUBLIC_CACHE."/$filename", gzencode($content, 9), LOCK_EX | FILE_BINARY);
			$local_includes[$extension] = "storage/pcache/$filename?".substr(md5($content), 0, 5);
		}
		return $local_includes;
	}
	protected function create_cached_includes_files_process_files ($extension, $filename_prefix, $files) {
		$content = '';
		switch ($extension) {
			/**
			 * Insert external elements into resulting css file.
			 * It is needed, because those files will not be copied into new destination of resulting css file.
			 */
			case 'css':
				$callback = function ($content, $file) {
					return $content.Includes_processing::css(file_get_contents($file), $file);
				};
				break;
			/**
			 * Combine css and js files for Web Component into resulting files in order to optimize loading process
			 */
			case 'html':
				/**
				 * For CSP-compatible HTML files we need to know destination to put there additional JS/CSS files
				 */
				$destination = Config::instance()->core['vulcanization'] ? false : PUBLIC_CACHE;
				$callback    = function ($content, $file) use ($filename_prefix, $destination) {
					$base_filename = "$this->pcache_basename:$filename_prefix-".basename($file).'+'.substr(md5($file), 0, 5);
					return $content.Includes_processing::html(file_get_contents($file), $file, $base_filename, $destination);
				};
				break;
			case 'js':
				$callback = function ($content, $file) {
					return $content.Includes_processing::js(file_get_contents($file));
				};
				if ($filename_prefix == 'System') {
					$content = 'window.cs={Language:'._json_encode(Language::instance()).'};';
					$content .= 'window.requirejs={paths:'._json_encode($this->get_requirejs_paths()).'};';
				}
		}
		/** @noinspection PhpUndefinedVariableInspection */
		return array_reduce($files, $callback, $content);
	}
	/**
	 * @param Config $Config
	 *
	 * @return array[]
	 */
	protected function get_includes_for_page_without_compression ($Config) {
		// To determine all dependencies and stuff we need `$Config` object to be already created
		if ($Config) {
			list($dependencies, $includes_map) = $this->includes_dependencies_and_map();
			$includes = $this->get_normalized_includes($dependencies, $includes_map, '/');
		} else {
			$includes = $this->get_includes_list();
		}
		return $this->add_versions_hash($this->absolute_path_to_relative($includes));
	}
	/**
	 * @param array  $dependencies
	 * @param string $url
	 * @param string $separator `+` or `/`
	 *
	 * @return bool
	 */
	protected function get_includes_is_dependency ($dependencies, $url, $separator) {
		$url_exploded = explode($separator, $url);
		/** @noinspection NestedTernaryOperatorInspection */
		$url_module = $url_exploded[0] != 'admin' ? $url_exploded[0] : (@$url_exploded[1] ?: '');
		$Request    = Request::instance();
		return
			$url_module !== Config::SYSTEM_MODULE &&
			in_array($url_module, $dependencies) &&
			(
				$Request->admin_path || $Request->admin_path == ($url_exploded[0] == 'admin')
			);
	}
	/**
	 * @param string[][] $includes
	 *
	 * @return string[][]
	 */
	protected function add_versions_hash ($includes) {
		$content = array_map('file_get_contents', get_files_list(DIR.'/components', '/^meta\.json$/', 'f', true, true));
		$content = implode('', $content);
		$hash    = substr(md5($content), 0, 5);
		foreach ($includes as &$files) {
			foreach ($files as &$file) {
				$file .= "?$hash";
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
	/**
	 * Getting of HTML, JS and CSS files list to be included
	 *
	 * @return string[][]
	 */
	protected function get_includes_list () {
		$includes = [];
		/**
		 * Get includes of system and theme
		 */
		$this->get_includes_list_add_includes(DIR.'/includes', $includes);
		$this->get_includes_list_add_includes(THEMES."/$this->theme", $includes);
		$Config = Config::instance();
		foreach ($Config->components['modules'] as $module_name => $module_data) {
			if ($module_data['active'] == Config\Module_Properties::UNINSTALLED) {
				continue;
			}
			$this->get_includes_list_add_includes(MODULES."/$module_name/includes", $includes);
		}
		foreach ($Config->components['plugins'] as $plugin_name) {
			$this->get_includes_list_add_includes(PLUGINS."/$plugin_name/includes", $includes);
		}
		return [
			'html' => array_merge(...$includes['html']),
			'js'   => array_merge(...$includes['js']),
			'css'  => array_merge(...$includes['css'])
		];
	}
	/**
	 * @param string     $base_dir
	 * @param string[][] $includes
	 */
	protected function get_includes_list_add_includes ($base_dir, &$includes) {
		$includes['html'][] = $this->get_includes_list_add_includes_internal($base_dir, 'html');
		$includes['js'][]   = $this->get_includes_list_add_includes_internal($base_dir, 'js');
		$includes['css'][]  = $this->get_includes_list_add_includes_internal($base_dir, 'css');
	}
	/**
	 * @param string $base_dir
	 * @param string $ext
	 *
	 * @return array
	 */
	protected function get_includes_list_add_includes_internal ($base_dir, $ext) {
		return get_files_list("$base_dir/$ext", "/.*\\.$ext\$/i", 'f', true, true, 'name', '!include') ?: [];
	}
	/**
	 * Get dependencies of components between each other (only that contains some HTML, JS and CSS files) and mapping HTML, JS and CSS files to URL paths
	 *
	 * @return array[] [$dependencies, $includes_map]
	 */
	protected function includes_dependencies_and_map () {
		/**
		 * Get all includes
		 */
		$all_includes = $this->get_includes_list();
		$includes_map = [];
		/**
		 * Array [package => [list of packages it depends on]]
		 */
		$dependencies    = [];
		$functionalities = [];
		/**
		 * According to components's maps some files should be included only on specific pages.
		 * Here we read this rules, and remove from whole includes list such items, that should be included only on specific pages.
		 * Also collect dependencies.
		 */
		$Config = Config::instance();
		foreach ($Config->components['modules'] as $module_name => $module_data) {
			if ($module_data['active'] == Config\Module_Properties::UNINSTALLED) {
				continue;
			}
			$this->process_meta(MODULES."/$module_name", $dependencies, $functionalities);
			$this->process_map(MODULES."/$module_name", $includes_map, $all_includes);
		}
		unset($module_name, $module_data);
		foreach ($Config->components['plugins'] as $plugin_name) {
			$this->process_meta(PLUGINS."/$plugin_name", $dependencies, $functionalities);
			$this->process_map(PLUGINS."/$plugin_name", $includes_map, $all_includes);
		}
		unset($plugin_name);
		/**
		 * For consistency
		 */
		$includes_map['System'] = $all_includes;
		Event::instance()->fire(
			'System/Page/includes_dependencies_and_map',
			[
				'dependencies' => &$dependencies,
				'includes_map' => &$includes_map
			]
		);
		$dependencies = $this->normalize_dependencies($dependencies, $functionalities);
		$includes_map = $this->clean_includes_arrays_without_files($dependencies, $includes_map);
		$dependencies = array_map('array_values', $dependencies);
		$dependencies = array_filter($dependencies);
		return [$dependencies, $includes_map];
	}
	/**
	 * Process meta information and corresponding entries to dependencies and functionalities
	 *
	 * @param string $base_dir
	 * @param array  $dependencies
	 * @param array  $functionalities
	 */
	protected function process_meta ($base_dir, &$dependencies, &$functionalities) {
		if (!file_exists("$base_dir/meta.json")) {
			return;
		}
		$meta = file_get_json("$base_dir/meta.json");
		$meta += [
			'require'  => [],
			'optional' => [],
			'provide'  => []
		];
		$package = $meta['package'];
		foreach ((array)$meta['require'] as $r) {
			/**
			 * Get only name of package or functionality
			 */
			$r                        = preg_split('/[=<>]/', $r, 2)[0];
			$dependencies[$package][] = $r;
		}
		foreach ((array)$meta['optional'] as $o) {
			/**
			 * Get only name of package or functionality
			 */
			$o                        = preg_split('/[=<>]/', $o, 2)[0];
			$dependencies[$package][] = $o;
		}
		foreach ((array)$meta['provide'] as $p) {
			/**
			 * If provides sub-functionality for other component (for instance, `Blog/post_patch`) - inverse "providing" to "dependency"
			 * Otherwise it is just functionality alias to package name
			 */
			if (strpos($p, '/') !== false) {
				/**
				 * Get name of package or functionality
				 */
				$p                  = explode('/', $p)[0];
				$dependencies[$p][] = $package;
			} else {
				$functionalities[$p] = $package;
			}
		}
	}
	/**
	 * Process map structure, fill includes map and remove files from list of all includes (remaining files will be included on all pages)
	 *
	 * @param string $base_dir
	 * @param array  $includes_map
	 * @param array  $all_includes
	 */
	protected function process_map ($base_dir, &$includes_map, &$all_includes) {
		if (!file_exists("$base_dir/includes/map.json")) {
			return;
		}
		$this->process_map_internal(file_get_json("$base_dir/includes/map.json"), "$base_dir/includes", $includes_map, $all_includes);
	}
	/**
	 * Process map structure, fill includes map and remove files from list of all includes (remaining files will be included on all pages)
	 *
	 * @param array  $map
	 * @param string $includes_dir
	 * @param array  $includes_map
	 * @param array  $all_includes
	 */
	protected function process_map_internal ($map, $includes_dir, &$includes_map, &$all_includes) {
		foreach ($map as $path => $files) {
			foreach ((array)$files as $file) {
				$extension = file_extension($file);
				if (in_array($extension, ['css', 'js', 'html'])) {
					$file                              = "$includes_dir/$extension/$file";
					$includes_map[$path][$extension][] = $file;
					$all_includes[$extension]          = array_diff($all_includes[$extension], [$file]);
				} else {
					$file = rtrim($file, '*');
					/**
					 * Wildcard support, it is possible to specify just path prefix and all files with this prefix will be included
					 */
					$found_files = array_filter(
						get_files_list($includes_dir, '/.*\.(css|js|html)$/i', 'f', '', true, 'name', '!include') ?: [],
						function ($f) use ($file) {
							// We need only files with specified mask and only those located in directory that corresponds to file's extension
							return preg_match("#^(css|js|html)/$file.*\\1$#i", $f);
						}
					);
					// Drop first level directory
					$found_files = _preg_replace('#^[^/]+/(.*)#', '$1', $found_files);
					$this->process_map_internal([$path => $found_files], $includes_dir, $includes_map, $all_includes);
				}
			}
		}
	}
	/**
	 * Replace functionalities by real packages names, take into account recursive dependencies
	 *
	 * @param array $dependencies
	 * @param array $functionalities
	 *
	 * @return array
	 */
	protected function normalize_dependencies ($dependencies, $functionalities) {
		/**
		 * First of all remove packages without any dependencies
		 */
		$dependencies = array_filter($dependencies);
		/**
		 * First round, process aliases among keys
		 */
		foreach (array_keys($dependencies) as $d) {
			if (isset($functionalities[$d])) {
				$package = $functionalities[$d];
				/**
				 * Add dependencies to existing package dependencies
				 */
				foreach ($dependencies[$d] as $dependency) {
					$dependencies[$package][] = $dependency;
				}
				/**
				 * Drop alias
				 */
				unset($dependencies[$d]);
			}
		}
		unset($d, $dependency);
		/**
		 * Second round, process aliases among dependencies
		 */
		foreach ($dependencies as &$depends_on) {
			foreach ($depends_on as &$dependency) {
				if (isset($functionalities[$dependency])) {
					$dependency = $functionalities[$dependency];
				}
			}
		}
		unset($depends_on, $dependency);
		/**
		 * Third round, process recursive dependencies
		 */
		foreach ($dependencies as &$depends_on) {
			foreach ($depends_on as &$dependency) {
				if ($dependency != 'System' && isset($dependencies[$dependency])) {
					foreach (array_diff($dependencies[$dependency], $depends_on) as $new_dependency) {
						$depends_on[] = $new_dependency;
					}
				}
			}
		}
		return array_map('array_unique', $dependencies);
	}
	/**
	 * Includes array is composed from dependencies and sometimes dependencies doesn't have any files, so we'll clean that
	 *
	 * @param array $dependencies
	 * @param array $includes_map
	 *
	 * @return array
	 */
	protected function clean_includes_arrays_without_files ($dependencies, $includes_map) {
		foreach ($dependencies as &$depends_on) {
			foreach ($depends_on as $index => &$dependency) {
				if (!isset($includes_map[$dependency])) {
					unset($depends_on[$index]);
				}
			}
			unset($dependency);
		}
		return $includes_map;
	}
}
