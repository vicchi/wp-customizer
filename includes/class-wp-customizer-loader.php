<?php

if (!defined('WPCUSTOMIZER_INCLUDE_SENTRY')) {
	die('The way is shut. It was made by those who are dead, and the dead keep it. The way is shut.');
}

if (!class_exists('WP_CustomizerLoader')) {
	class WP_CustomizerLoader extends WP_PluginBase_v1_1 {
		
		private static $instance;
		
		/**
		 * Class constructor
		 */
		
		private function __construct() {
			$this->hook('init');
			if (is_admin()) {
				$this->hook('admin_enqueue_scripts');
			}
			else {
				$this->hook('wp_enqueue_scripts');
			}
		}
		
		/**********************************************************************
		 * Action Hooks
		 */

		/**
		 * "init" action hook; runs after WordPress has loaded but before any headers are
		 * sent. Loads (via include_once) the custom functions.
		 */
		
		public function init() {
			if (is_admin()) {
				$this->load_functions('admin_functions');
			}

			else {
				$this->load_functions('functions');
			}

			$this->load_functions('common_functions');
		}

		/**********************************************************************
		 * Admin Action Hooks
		 */

		/**
		 * "admin_enqueue_scripts" action hook; enqueue the custom scripts and CSS for the
		 *  admin panel
		 *
		 * Code Health Warning: Don't use the admin_print_scripts or admin_print_styles
		 * action hooks. See :
		 *	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_print_styles
		 *	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_print_scripts
		 *	http://make.wordpress.org/core/2011/12/12/use-wp_enqueue_scripts-not-wp_print_styles-to-enqueue-scripts-and-styles-for-the-frontend/
		 */

		public function admin_enqueue_scripts() {
			$this->load_scripts('admin_scripts');
			$this->load_scripts('common_scripts');
			
			$this->load_css('admin_css');
			$this->load_css('common_css');
		}
		
		/**********************************************************************
		 * Action Hooks
		 */

		/**
		 * "wp_enqueue_scripts" action hook; enqueue the custom front-end scripts and css
		 */
		
		public function wp_enqueue_scripts() {
			$this->load_scripts('scripts');
			$this->load_scripts('common_scripts');

			$this->load_css('css');
			$this->load_css('common_css');
		}
		
		/**********************************************************************
		 * Public Class Functions
		 */

		/**
		 * Perform a "dry run" of loading all configured functions, scripts and css. Called
		 * from WP_CustomizerAdmin::display_options and forms the body of the debug tab.
		 */
		
		public function debug() {
			$content = array();
			
			foreach (WP_Customizer::$types as $type => $meta) {
				$content[] = '<strong>' . ucwords(WP_Customizer::$types[$type]['descr']) . '</strong><br />';
				$content[] = '<ul>';
				$content[] = $this->debug_files($type);
				$content[] = '</ul>';
			}

			return implode(PHP_EOL, $content);
		}
		
		/**
		 * Called from WPCustomizerLoader::debug; performs a "dry run" for a specific file
		 * type (functions, scripts or css)
		 */
		
		public function debug_files($type) {
			$options = WP_Customizer::get_option();
			$content = array();

			$enabled = WP_Customizer::$types[$type]['config'] . '_enabled';
			$path = WP_Customizer::$types[$type]['config'] . '_path';

			if (isset($options[$enabled])) {
				$content[] = '<li class="wp-customizer-info wp-customizer-success"><code>';
				$content[] = 'options[' . $enabled . '] => ' . $options[$enabled];
				$content[] = '</code></li>';
			}
			else {
				$content[] = '<li class="wp-customizer-info wp-customizer-error"><code>';
				$content[] = 'options[' . $enabled . '] => ' . __('(empty)', 'wp-customizer');
				$content[] = '</code></li>';
			}
			
			if (isset($options[$enabled]) && $options[$enabled] === 'on' && isset($options[$path])) {
				$content[] = '<li class="wp-customizer-info wp-customizer-success"><code>';
				$content[] = 'options[' . $path . '] => ' . $options[$path];
				$content[] = '</code></li>';

				$dir = ABSPATH . $options[$path];
				if (is_readable($dir)) {
					$content[] = '<li class="wp-customizer-info wp-customizer-success"><code>';
					$content[] = $dir . ' => (OK)';
					$content[] = '</code></li>';

					if (isset($options[$enabled]) && $options[$enabled] === 'on') {
						$files = $this->get_files(
								$options[$path],
								WP_Customizer::$types[$type]['file_type']);

						if (!empty($files)) {
							$content[] = '<li>' . sprintf(__('%d files found', 'wp-customizer'), count($files)) . '</li>';
							foreach ($files as $meta) {
								if ($meta['enabled']) {
									$class = 'wp-customizer-info ';
									$class .= ($meta['readable'] ? 'wp-customizer-success' : 'wp-customizer-error');
									$content[] = '<li class="' . $class . '"><code>';
									switch (WP_Customizer::$types[$type]['file_type']) {
										case 'php':
											$content[] = $meta['file'] . ' => ' . ($meta['readable'] ? __('(OK)', 'wp-customizer') : __('(unreadable)', 'wp-customizer'));
											break;

										case 'js':
										case 'css':
											$content[] = $meta['file'] . ' => ' . ($meta['readable'] ? $meta['src'] : __('(unreadable)', 'wp-customizer'));
											break;
									}
									$content[] = '</code></li>';
								}

								else {
									$content[] = '<li class="wp-customizer-info wp-customizer-warning"><code>';
									$content[] = $meta['file'] . ' => ' . __('(disabled)', 'wp-customizer');
									$content[] = '</code></li>';
								}
							}
						}

						else {
							$content[] = '<li>' . __('No files found', 'wp-customizer') . '</li>';
						}
					}
				}

				else {
					$content[] = '<li class="wp-customizer-info wp-customizer-error"><code>';
					$content[] = $dir . ' => ' . __('(unreadable)', 'wp-customizer');
					$content[] = '</code></li>';
				}
			}

			elseif (empty($options[$path])) {
				$content[] = '<li class="wp-customizer-info wp-customizer-error"><code>';
				$content[] = 'options[' . $path . '] => ' . __('(empty)', 'wp-customizer');
				$content[] = '</code></li>';
			}


			return implode(PHP_EOL, $content);
		}
		
		/**********************************************************************
		 * Public Static Class Functions
		 */

		/**
		 * Class singleton factory helper
		 */

		public static function get_instance() {
			if (!isset(self::$instance)) {
				$class = __CLASS__;
				self::$instance = new $class();
			}
			return self::$instance;
		}

		/**********************************************************************
		 * Private Class Functions
		 */

		/**
		 * Gets a list of valid files for a given type (functions, scripts, css)
		 */
		
		private function get_files($path, $file_type) {
			$meta = array();
			
			$query = ABSPATH . $path . DIRECTORY_SEPARATOR . '*.' . $file_type;
			$suffix = '.' . $file_type;
			$url_root = network_site_url($path);
			
			$files = glob($query);
			if (!empty($files)) {
				foreach ($files as $file) {
					$basename = basename($file, $suffix);
					$pos = strpos($basename, '_');
					if ($pos === false || $pos !== 0) {
						$url = $url_root . DIRECTORY_SEPARATOR . basename($file);
						$handle = 'wp-customizer-' . preg_replace('/[^A-Za-z0-9-]/', '', $basename) . '-' . $file_type;
						$readable = is_readable($file);
						switch ($file_type) {
							case 'php':
								$meta[] = array('file' => $file, 'enabled' => true, 'readable' => $readable);
								break;
								
							case 'js':
								$meta[] = array('file' => $file, 'enabled' => true, 'readable' => $readable, 'handle' => $handle, 'src' => $url, 'deps' => array(), 'ver' => false, 'in_footer' => false);
								break;
								
							case 'css':
								$meta[] = array('file' => $file, 'enabled' => true, 'readable' => $readable, 'handle' => $handle, 'src' => $url, 'deps' => array(), 'ver' => false, 'media' => 'all');
								break;
						}
					}
					
					else {
						$meta[] = array('file' => $file, 'enabled' => false, 'readable' => false);
					}
				}
			}

			return $meta;
		}
		
		/**
		 * Makes the plugin's filter tag for a given type
		 */
		
		private function make_filter_tag($type) {
			return 'wp_customizer' . WP_Customizer::$types[$type]['config'];
		}
		
		/**
		 * Loads all valid functions for the current context (front-end, admin, common)
		 */
		
		private function load_functions($type) {
			$options = WP_Customizer::get_option();

			$enabled = WP_Customizer::$types[$type]['config'] . '_enabled';
			$path = WP_Customizer::$types[$type]['config'] . '_path';

			if ((isset($options[$enabled]) && ($options[$enabled] === 'on')) &&
					(isset($options[$path]) && !empty($options[$path]))) {
				$functions = apply_filters(
					$this->make_filter_tag($type),
					$this->get_files(
						$options[$path],
						WP_Customizer::$types[$type]['file_type']));

				if (!empty($functions)) {
					foreach ($functions as $meta) {
						if ($meta['enabled'] && $meta['readable']) {
							include_once($meta['file']);
						}
					}
				}
			}
		}
		
		/**
		 * Loads all valid scripts for the current context (front-end, admin, common)
		 */

		private function load_scripts($type) {
			$options = WP_Customizer::get_option();

			$enabled = WP_Customizer::$types[$type]['config'] . '_enabled';
			$path = WP_Customizer::$types[$type]['config'] . '_path';

			if ((isset($options[$enabled]) && ($options[$enabled] === 'on')) &&
					(isset($options[$path]) && !empty($options[$path]))) {
				$scripts = apply_filters(
					$this->make_filter_tag($type),
					$this->get_files(
						$options[$path],
						WP_Customizer::$types[$type]['file_type']));
				
				if (!empty($scripts)) {
					foreach ($scripts as $meta) {
						if ($meta['enabled'] && $meta['readable']) {
							wp_enqueue_script($meta['handle'], $meta['src'], $meta['deps'], $meta['ver'], $meta['in_footer']);
						}
					}
				}
			}
		}

		/**
		 * Loads all valid CSS for the current context (front-end, admin, common)
		 */

		private function load_css($type) {
			$options = WP_Customizer::get_option();

			$enabled = WP_Customizer::$types[$type]['config'] . '_enabled';
			$path = WP_Customizer::$types[$type]['config'] . '_path';

			if ((isset($options[$enabled]) && ($options[$enabled] === 'on')) &&
					(isset($options[$path]) && !empty($options[$path]))) {
				$css = apply_filters(
					$this->make_filter_tag($type),
					$this->get_files(
						$options[$path],
						WP_Customizer::$types[$type]['file_type']));

				if (!empty($css)) {
					foreach ($css as $meta) {
						if ($meta['enabled'] && $meta['readable']) {
							wp_enqueue_style($meta['handle'], $meta['src'], $meta['deps'], $meta['ver'], $meta['media']);
						}
					}
				}
			}
		}

	}	// end-class (...)
}	// end-if (!class_exists(...))

WP_CustomizerLoader::get_instance();

?>