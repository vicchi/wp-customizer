<?php

if (!defined('WPCUSTOMIZER_INCLUDE_SENTRY')) {
	die('The way is shut. It was made by those who are dead, and the dead keep it. The way is shut.');
}

require_once(WPCUSTOMIZER_LOADER_SRC);

if (!class_exists('WP_Customizer')) {
	class WP_Customizer extends WP_PluginBase_v1_1 {

		private static $instance;
		
		const OPTIONS = 'wp_customizer_options';
		const VERSION = '100';
		const DISPLAY_VERSION = 'v1.0.0';

		public static $name = 'WP Customizer';
		public static $types;
		
		private function __construct() {
			self::$types = array(
				'functions' => array(
					'descr' => __('front end custom functions', 'wp-customizer'),
					'file_type' => 'php',
					'id_stub' => 'functions',
					'config' => 'functions'
				),
				'admin_functions' => array(
					'descr' => __('admin custom functions', 'wp-customizer'),
					'file_type' => 'php',
					'id_stub' => 'admin-functions',
					'config' => 'admin_functions'
				),
				'common_functions' => array(
					'descr' => __('common custom functions', 'wp-customizer'),
					'file_type' => 'php',
					'id_stub' => 'common-functions',
					'config' => 'common_functions'
				),
				'scripts' => array(
					'descr' => __('front end custom scripts', 'wp-customizer'),
					'file_type' => 'js',
					'id_stub' => 'scripts',
					'config' => 'scripts'
				),
				'admin_scripts' => array(
					'descr' => __('admin custom scripts', 'wp-customizer'),
					'file_type' => 'js',
					'id_stub' => 'admin-scripts',
					'config' => 'admin_scripts'
				),
				'common_scripts' => array(
					'descr' => __('common custom scripts', 'wp-customizer'),
					'file_type' => 'js',
					'id_stub' => 'common-scripts',
					'config' => 'common_scripts'
				),
				'css' => array(
					'descr' => __('front end custom CSS', 'wp-customizer'),
					'file_type' => 'css',
					'id_stub' => 'css',
					'config' => 'css'
				),
				'admin_css' => array(
					'descr' => __('admin custom CSS', 'wp-customizer'),
					'file_type' => 'css',
					'id_stub' => 'admin-css',
					'config' => 'admin_css'
				),
				'common_css' => array(
					'descr' => __('common custom CSS', 'wp-customizer'),
					'file_type' => 'css',
					'id_stub' => 'common-css',
					'config' => 'common_css'
				)
			);

			register_activation_hook(WPCUSTOMIZER_FILE, array($this, 'activation_hook'));
			$this->hook('plugins_loaded');
		}
		
		public static function get_instance() {
			if (!isset(self::$instance)) {
				$class = __CLASS__;
				self::$instance = new $class();
			}
			return self::$instance;
		}
		
		public static function get_option() {
			$num_args = func_num_args();
			$options = get_option(self::OPTIONS);
			
			if ($num_args > 0) {
				$args = func_get_args();
				$key = $args[0];
				$value = '';
				if (isset($options[$key])) {
					$value = $options[$key];
				}
				return $value;
			}
			
			else {
				return $options;
			}
		}
		
		public static function set_option($key, $value) {
			$options = self::get_option();
			$options[$key] = $value;
			update_option(self::OPTIONS, $options);
		}
		
		public static function make_settings_link_hook() {
			return 'plugin_action_links_' . WPCUSTOMIZER_BASENAME;
		}
		
		public function activation_hook() {
			$options = self::get_option();
			
			if (!is_array($options)) {
				$options = apply_filters('wp_customizer_defaults',
					array(
						'installed' => 'on',
						'version' => self::VERSION,
						'functions_enabled' => '',
						'functions_path' => 'functions',
						'admin_functions_enabled' => '',
						'admin_functions_path' => 'admin_functions',
						'common_functions_enabled' => '',
						'common_functions_path' => 'common_functions',
						'scripts_enabled' => '',
						'scripts_path' => 'scripts',
						'admin_scripts_enabled' => '',
						'admin_scripts_path' => 'admin_scripts',
						'common_scripts_enabled' => '',
						'common_scripts_path' => 'common_scripts',
						'css_enabled' => '',
						'css_path' => 'css',
						'admin_css_enabled' => '',
						'admin_css_path' => 'admin_css',
						'common_css_enabled' => '',
						'common_css_path' => 'common_css'
					)
				);
				update_option(self::OPTIONS, $options);
			}
		}
		
		public function plugins_loaded() {
			$this->hook('init');

			
			if (is_admin()) {
				require_once(WPCUSTOMIZER_ADMIN_SRC);
				require_once(WPCUSTOMIZER_UPGRADE_SRC);
			}
		}
		
		public function init() {
			$lang_dir = dirname(WPCUSTOMIZER_BASENAME) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR;
			load_plugin_textdomain('wp-customizer', false, $lang_dir);
			
			self::$name = __('WP Customizer', 'wp-customizer');
		}
		
		public static function is_debug() {
			return ((defined('WP_DEBUG') && WP_DEBUG == true) ||
				(defined('WPCUSTOMIZER_DEBUG') && WPCUSTOMIZER_DEBUG == true));
		}
		
		public static function make_css_path($stub) {
			if (self::is_debug()) {
				return $stub . '.css';
			}
			
			return $stub . '.min.css';
		}
		
		public static function make_js_path($stub) {
			if (self::is_debug()) {
				return $stub . '.js';
			}
			
			return $stub . '.min.js';
		}
		
	}	// end-class (...)
}	// end-if (!class_exists (...))

WP_Customizer::get_instance();

?>