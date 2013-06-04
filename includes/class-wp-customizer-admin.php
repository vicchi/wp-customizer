<?php

if (!defined('WPCUSTOMIZER_INCLUDE_SENTRY')) {
	die('The way is shut. It was made by those who are dead, and the dead keep it. The way is shut.');
}

if (!class_exists('WP_CustomizerAdmin')) {
	class WP_CustomizerAdmin extends WP_PluginBase_v1_1 {
		
		private static $instance;
		private static $tabs;
		private static $help;
		
		const DEFAULT_TAB = 'functions';
		
		/**
		 * Class constructor
		 */
		
		private function __construct() {
			self::$tabs = array(
				'functions' => __('Functions', 'wp-customizer'),
				'scripts' => __('Scripts', 'wp-customizer'),
				'css' => __('CSS', 'wp-customizer'),
				'colophon' => __('Colophon', 'wp-customizer'),
				'debug' => __('Debug', 'wp-customizer')
			);
			
			$overview_docs = __('Plugin Documentation', 'wp-customizer');
			$overview_faq = __('Frequently Asked Questions', 'wp-customizer');
			$overview_support = __('Plugin Support Forum', 'wp-customizer');
			$overview_content = array();
			$overview_content[] = '<p>';
			$overview_content[] = sprintf(__('This is %s. Here you can load site specific functions, scripts 
and CSS files to tweak your site without the need to edit your theme\'s <code>functions.php</code> or other source file.', 'wp-customizer'), WP_Customizer::$name);
			$overview_content[] = '</p>';
			$overview_content[] = '<p><strong>' . __(
'<strong>For more information:</strong>', 'wp-customizer') . '</strong></p>';
			$overview_content[] = '<ul>';
			$overview_content[] = sprintf('<li><a href="http://www.vicchi.org/codeage/wp-customizer/">%s</a></li>', $overview_docs);
			$overview_content[] = sprintf('<li><a href="http://www.vicchi.org/codeage/wp-customizer/#faq">%s</a></li>', $overview_faq);
			$overview_content[] = sprintf('<li><a href="http://wordpress.org/support/plugin/wp-customizer">%s</a></li>', $overview_support);
 			$overview_content[] = '</ul>';
			
			$types_content = array();
			$types_content[] = '<p>';
			$types_content[] = sprintf(__('%s allows you to load your custom functions, scripts and CSS in three ways:', 'wp-customizer'), WP_Customizer::$name);
			$types_content[] = '<ul>';
			$types_content[] = '<li><strong>' . __('Front End: ', 'wp-customizer') . '</strong>' . __('Load on the front-end only; do not load when displaying admin screens', 'wp-customizer') . '</li>';
			$types_content[] = '<li><strong>' . __('Admin: ', 'wp-customizer') . '</strong>' . __('Load on admin screens only; do not load when displaying posts or pages', 'wp-customizer') . '</li>';
			$types_content[] = '<li><strong>' . __('Common: ', 'wp-customizer') . '</strong>' . __('Load on both the front-end and on admin screens', 'wp-customizer') . '</li>';
			$types_content[] = '</ul>';
			$types_content[] = '</p>';
			
			$paths_content = array();
			$paths_content[] = '<p>';
			$paths_content[] = __('Your collection of custom functions, scripts and CSS files live in a set of specially named folders. You can use the plugin\'s default names or choose your own to fit your needs.', 'wp-customizer');
			$paths_content[] = '</p>';
			$paths_content[] = '<p>';
			$paths_content[] = __('The path names you specify must always be relative; they should not start with a <code>/</code> and should always be contained within the root of your WordPress installation.', 'wp-customizer');
			$paths_content[] = '</p>';
			$paths_content[] = '<p>';
			$paths_content[] = sprintf(__('Your WordPress installation root is currently set to: <code>%s</code>', 'wp-customizer'), ABSPATH);
			$paths_content[] = '</p>';
			
			$files_content = array();
			$files_content[] = '<p>';
			$files_content[] = sprintf(__('%s supports loading custom functions, scripts and CSS files. Any file name that is valid for the operating system you\'re running WordPress on are acceptable.', 'wp-customizer'), WP_Customizer::$name);
			$files_content[] = '<ul>';
			$files_content[] = '<li><strong>' . __('Functions: ', 'wp-customizer') . '</strong>' . __('Custom function files contain PHP; they should always have a file type of <code>.php</code>', 'wp-customizer') . '</li>';
			$files_content[] = '<li><strong>' . __('Scripts: ', 'wp-customizer') . '</strong>' . __('Custom script files contain JavaScript or jQuery; they should always have a file type of <code>.js</code>', 'wp-customizer') . '</li>';
			$files_content[] = '<li><strong>' . __('CSS: ', 'wp-customizer') . '</strong>' . __('Custom CSS files contain CSS; they should always have a file type of <code>.css</code>', 'wp-customizer') . '</li>';
			$files_content[] = '</ul>';
			$files_content[] = '</p>';
			
			$load_content = array();
			$load_content[] = '<p>';
			$load_content[] = sprintf(__('%s will always try to load the files it finds, as long as you have enabled that category of  files and the plugin is able to find and read the files in the folders you\'ve configured.','wp-customizer'), WP_Customizer::$name);
			$load_content[] = '</p>';
			$load_content[] = '<p>';
			$load_content[] = __('To stop the auto-loading process; you can simply rename any of your files to start with an underscore character. For example a file called <code>functions-library.php</code> will auto-load; to stop this, rename the file to <code>_functions-library.php</code>.', 'wp-customizer');
			$load_content[] = '</p>';

			self::$help = array(
				'overview' => array(
					'id' => 'wp-customizer-overview',
					'title' => __('Overview', 'wp-customizer'),
					'content' => implode(PHP_EOL, $overview_content)
					),
				'types' => array(
					'id' => 'wp-customizer-types',
					'title' => __('Customization Types', 'wp-customizer'),
					'content' => implode(PHP_EOL, $types_content)
					),
				'paths' => array(
					'id' => 'wp-customizer-paths',
					'title' => __('Customization Paths', 'wp-customizer'),
					'content' => implode(PHP_EOL, $paths_content)
					),
				'files' => array(
					'id' => 'wp-customizer-files',
					'title' => __('Customization Files', 'wp-customizer'),
					'content' => implode(PHP_EOL, $files_content)
					),
				'loading' => array(
					'id' => 'wp-customizer-disabling',
					'title' => __('Disabling Auto Loading', 'wp-customizer'),
					'content' => implode(PHP_EOL, $load_content)
					)
				);
			
			// Add action hooks ...
			$this->hook('admin_menu');
			$this->hook('admin_init');
			$this->hook('admin_enqueue_scripts');

			// Add filter hooks ...
			$this->hook(WP_Customizer::make_settings_link_hook(), 'admin_settings_link');
			$this->hook('contextual_help');
		}
		
		/**********************************************************************
		 * Admin Action Hooks
		 */

		/**
		 * "admin_menu" action hook; adds the plugin's admin page to the admin panel's menu.
		 */
		
		public function admin_menu() {
			if (function_exists('add_options_page')) {
				$page = __('WP Customizer', 'wp-customizer');
				$menu = __('WP Customizer', 'wp-customizer');
				$capability = 'manage_options';
				$slug = __FILE__;

				add_options_page($page, $menu, $capability, $slug, array($this, 'display_options'));
			}
		}

		/**
		 * "admin_init" action hook; triggered before the user accesses the admin panel.
		 */
		
		public function admin_init() {
			$upgrade = new WP_CustomizerUpgrade();
			$upgrade->upgrade();
			
			$skip = $this->is_pointer_set();
			if (isset($_GET['wp_customizer_restart_tour'])) {
				if (check_admin_referer('wp-customizer-restart-tour')) {
					$this->clear_pointer();
					$skip = false;
				}
			}
			
			if (!$skip) {
				require_once(WPCUSTOMIZER_POINTERS_SRC);
			}
		}

		/**
		 * "admin_enqueue_scripts" action hook; enqueue the plugin's custom scripts and
		 * styles for the admin panel
		 *
		 * Code Health Warning: Don't use the admin_print_scripts or admin_print_styles
		 * action hooks. See :
		 *	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_print_styles
		 *	http://codex.wordpress.org/Plugin_API/Action_Reference/admin_print_scripts
		 *	http://make.wordpress.org/core/2011/12/12/use-wp_enqueue_scripts-not-wp_print_styles-to-enqueue-scripts-and-styles-for-the-frontend/
		 */
		
		public function admin_enqueue_scripts() {
			global $pagenow;
			
			if ($pagenow === 'options-general.php' &&
					isset($_GET['page']) &&
					strstr($_GET['page'], 'wp-customizer')) {
				wp_enqueue_style('dashboard');
				wp_enqueue_style('global');
				wp_enqueue_style('wp-admin');

				$src = WPCUSTOMIZER_URL . 'css/wp-customizer-admin';
				$src = WP_Customizer::make_css_path($src);
				$handle = 'wp-customizer-admin-css';
				wp_enqueue_style($handle, $src);

				wp_enqueue_script('postbox');
				wp_enqueue_script('dashboard');
				
				$src = WPCUSTOMIZER_URL . 'js/wp-customizer-admin';
				$src = WP_Customizer::make_js_path($src);
				$handle = 'wp-customizer-admin-js';
				wp_enqueue_script($handle, $src);
			}
		}


		/**********************************************************************
		 * Admin Filter Hooks
		 */

		/**
		 * "${prefix}plugin_action_links_${file}" filter hook; adds a link to the plugin's
		 * options page to the plugin's entry on the Installed Plugins admin page.
		 *
		 * Code Health Warning: We're adding the settings link to the standard plugins page.
		 * If this was a Network/Multi Site specific plugin, we'd add the link to the 
		 * Network Admin Page by settings ${prefix} to 'network_admin_', thus hooking to
		 * 'network_admin_plugin_action_links_${file}.
		 *
		 * You might see references to "plugins_action_links${file}" being deprecated; it sort
		 * of is, but as we're not adding a Network plugin, this is semantically identical in
		 * effect. I think.
		 */

		public function admin_settings_link($links) {
			$link = '<a href="' . $this->get_options_url() . '">'
				. __('Settings', 'wp-customizer')
				. '</a>';

			return array_merge(array('settings' => $link), $links);
		}

		/**
		 * "contextual_help" filter hook; adds the plugin's help tabs to the plugin's
		 * admin page via WP_Screen::add_help_tab().
		 */
		
		public function contextual_help($help, $screen_id, $screen) {
			if (!method_exists($screen, 'add_help_tab')) {
				return $help;
			}

			if ($screen->parent_file === 'options-general.php' &&
					isset($_GET['page']) &&
					strstr($_GET['page'], 'wp-customizer')) {
				$tab = $this->validate_tab();
				
				foreach (self::$help as $item => $meta) {
					$screen->add_help_tab ($meta);
				}
			}
			
			return $help;
		}
		
		/**********************************************************************
		 * Public Class Functions
		 */

		/**
		 * "add_options_page" callback; displays the plugin's options screen.
		 *
		 * Code Health Warning: this *must* be a public function to enable WordPress to invoke
		 * this as a callback. If you see the following (with WP_DEBUG enabled) you're doing
		 * it wrong ...
		 *
		 * Warning: call_user_func_array() expects parameter 1 to be a valid callback, cannot access private method
		 */
		
		public function display_options() {
			$options = $this->save_options();
			
			$display = array();
			$content = array();
			
			$functions_options = array();
			$scripts_options = array();
			$css_options = array();
			$colophon_options = array();
			
			if (function_exists('wp_nonce_field')) {
				$content[] = wp_nonce_field('wp-customizer-update-options', '_wpnonce', true, false);
			}

			$tab = $this->validate_tab();
			switch ($tab) {
				case 'scripts':
					$display[] = $this->make_intro_section();
					$display[] = $this->make_section('scripts');
					$display[] = $this->make_section('admin_scripts');
					$display[] = $this->make_section('common_scripts');

					$content[] = $this->postbox('wp-customizer-scripts-options',
						__('Scripts Options', 'wp-customizer'),
						implode('', $display));
					break;
					
				case 'css':
					$display[] = $this->make_intro_section();
					$display[] = $this->make_section('css');
					$display[] = $this->make_section('admin_css');
					$display[] = $this->make_section('common_css');

					$content[] = $this->postbox('wp-customizer-css-options',
						__('CSS Options', 'wp-customizer'),
						implode('', $display));
					break;
					
				case 'colophon':
					$display[] = '<p><em>' . __('"When it comes to software, I much prefer free software, because I have very seldom seen a program that has worked well enough for my needs and having sources available can be a life-saver"</em>&nbsp;&hellip;&nbsp;Linus Torvalds', 'wp-customizer') . '</p><p>';
					$display[] = sprintf(__('For the inner nerd in you, the latest version of %s was written using <a href="http://macromates.com/">TextMate</a> on a MacBook Pro running OS X 10.8.3 Mountain Lion and tested on the same machine running <a href="http://mamp.info/en/index.html">MAMP</a> (Mac/Apache/MySQL/PHP) before being let loose on the author\'s <a href="http://www.vicchi.org/">blog</a>.', 'wp-customizer'), WP_Customizer::$name);
					$display[] = '</p><p>';
					$display[] = sprintf(__('The official home for %s is on <a href="http://www.vicchi.org/codeage/wp-customizer/">Mostly Maps</a>; it\'s also available from the official <a href="http://wordpress.org/extend/plugins/wp-customizer/">WordPress plugins repository</a>. If you\'re interested in what lies under the hood, the code is also on <a href="https://github.com/vicchi/wp-customizer">GitHub</a> to download, fork and otherwise hack around.', 'wp-customizer'), WP_Customizer::$name);
					$display[] = '</p>';

					$content[] = $this->postbox('wp-customizer-colophon-about',
						__('Colophon', 'wp-customizer'),
						implode('', $display));

					$display = array();
					$display[] = '<p>';
					$display[] = sprintf(__('For those times when you need help and support with this plugin, one of the first things you\'ll probably be asked for is the plugin\'s current configuration. If this happens, just <em>copy-and-paste</em> the dump of the <em>%s Options</em> below into any support forum post or email.', 'wp-customizer'), WP_Customizer::$name);
					$display[] = '</p>';
					$display[] = '<pre>';
					$display[] = print_r ($options, true);
					$display[] = '</pre>';

					$content[] = $this->postbox('wp-customizer-colophon-options',
						__('Plugin Options', 'wp-customizer'),
						implode('', $display));
					break;

				case 'debug':
					$display[] = WP_CustomizerLoader::get_instance()->debug();
					
					$content[] = $this->postbox('wp-customizer-debug-options',
						__('Debug', 'wp-customizer'),
						implode('', $display));
					break;
					
				case 'functions':
				default:
					$display[] = $this->make_intro_section();
					$display[] = $this->make_section('functions');
					$display[] = $this->make_section('admin_functions');
					$display[] = $this->make_section('common_functions');

					$content[] = $this->postbox('wp-customizer-functions-options',
						__('Functions Options', 'wp-customizer'),
						implode('', $display));
					break;
			}	// end-switch(...)
			
			$this->wrap($tab,
				sprintf(__('%s %s - Options', 'wp-customizer'),
					WP_Customizer::$name,
					WP_Customizer::DISPLAY_VERSION),
				implode('', $content));
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
		
		/**
		 * Helper function to clear the plugin's tour pointer.
		 */

		public static function clear_pointer () {
			$user_id = get_current_user_id();
			$dismissed = explode(',', get_user_meta($user_id, 'dismissed_wp_pointers', true));
			$key = array_search ('wp_customizer_pointer', $dismissed);
			if ($key !== false) {
				unset($dismissed[$key]);
				update_user_meta($user_id, 'dismissed_wp_pointers', implode(',', $dismissed));
			}
		}
		
		/**********************************************************************
		 * Private Class Functions
		 */

		/**
		 * Displays the plugin's tabs on the admin screen
		 */
		
		private function display_tabs($current=self::DEFAULT_TAB) {
			$content = array();
			
			$content[] = '<div id="icon-tools" class="icon32"><br /></div>';
			$content[] = '<h2 class="nav-tab-wrapper">';
			
			foreach (self::$tabs as $tab => $name) {
				$class = ($tab === $current) ? ' nav-tab-active' : '';
				$format = '<a class="nav-tab%s" id="wp-customizer-tab-%s" href="%s">%s</a>';
				$content[] = sprintf($format, $class, $tab, $this->get_options_url($tab), $name);
			}
			$content[] = '</h2>';
			
			return implode('', $content);
		}
		
		/**
		 * Validates the current page's tab parameter.
		 *
		 *	/wp-admin/options-general.php?page=wp-customizer/includes/class-wp-customizer-admin.php&tab=xxx
		 */

		private function validate_tab() {
			$tab = filter_input(INPUT_GET, 'tab', FILTER_SANITIZE_STRING);
			if ($tab !== false && $tab !== null) {
				if (array_key_exists($tab, self::$tabs)) {
					return $tab;
				}
			}
			
			return self::DEFAULT_TAB;
		}
		
		/**
		 * Gets the URL for the plugin's admin page, including the current tab
		 *
		 *	/wp-admin/options-general.php?page=wp-customizer/includes/class-wp-customizer-admin.php&tab=xxx
		 */
		
		private function get_options_url($tab=null) {
			$url = array();
			
			$url[] = admin_url('options-general.php');
			$url[] = '?page=' . WPCUSTOMIZER_ADMIN_PATH;
			if (isset($tab) && !empty($tab)) {
				$url[] = '&tab=' . $tab;
			}
			
			return implode('', $url);
		}

		/**
		 * Called from "display_options"; saves the plugin's options.
		 */

		private function save_options() {
			if (!empty($_POST['wp_customizer_option_submitted'])) {
				if (strstr($_GET['page'], 'wp-customizer') &&
						check_admin_referer('wp-customizer-update-options')) {
					$tab = $this->validate_tab();
					$update = true;
					$options = WP_Customizer::get_option();
					$update_msg = self::$tabs[$tab];
					$action_msg = __('Updated', 'wp-customizer');

					switch ($tab) {
						case 'functions':
							$options['functions_enabled'] = $this->option('functions_enabled');
							$options['functions_path'] = $this->option('functions_path');
							$options['admin_functions_enabled'] = $this->option('admin_functions_enabled');
							$options['admin_functions_path'] = $this->option('admin_functions_path');
							$options['common_functions_enabled'] = $this->option('common_functions_enabled');
							$options['common_functions_path'] = $this->option('common_functions_path');
							break;
							
						case 'scripts':
							$options['scripts_enabled'] = $this->option('scripts_enabled');
							$options['scripts_path'] = $this->option('scripts_path');
							$options['admin_scripts_enabled'] = $this->option('admin_scripts_enabled');
							$options['admin_scripts_path'] = $this->option('admin_scripts_path');
							$options['common_scripts_enabled'] = $this->option('common_scripts_enabled');
							$options['common_scripts_path'] = $this->option('common_scripts_path');
							break;
							
						case 'css':
							$options['css_enabled'] = $this->option('css_enabled');
							$options['css_path'] = $this->option('css_path');
							$options['admin_css_enabled'] = $this->option('admin_css_enabled');
							$options['admin_css_path'] = $this->option('admin_css_path');
							$options['common_css_enabled'] = $this->option('common_css_enabled');
							$options['common_css_path'] = $this->option('common_css_path');
							break;

						case 'colophon':
						default:
							$update = false;
							break;
					}
					
					if ($update) {
						update_option(WP_Customizer::OPTIONS, $options);
						
						$content = array();
						$content[] = '<div id="updatemessage" class="updated fade">';
						$content[] = '<p>';
						$fmt = __('%s Options %s');
						$content[] = sprintf($fmt, $update_msg, $action_msg);
						$content[] = '</p>';
						$content[] = '</div>';
						$content[] = '<script type="text/javascript">';
						$content[] = "setTimeout(function() { jQuery('#updatemessage').hide('slow'); }, 3000);";
						$content[] = '</script>';
						echo implode(PHP_EOL, $content);
					}
				}
			}
			
			$options = WP_Customizer::get_option();
			return $options;
		}
		
		/**
		 * Outputs a collapsable admin screen postbox
		 */
		
		private function postbox($id, $title, $content) {
			$handle_title = __('Click to toggle', 'wp-customizer');
			$wrapper = array();
			
			$wrapper[] = '<div id="' . $id . '" class="postbox">';
			$wrapper[] = '<div class="handlediv" title="' . $handle_title . '"><br /></div>';
			$wrapper[] = '<h3 class="hndle"><span>' . $title . '</span></h3>';
			$wrapper[] = '<div class="inside">' . $content . '</div>';
			$wrapper[] = '</div>';
			
			return implode('', $wrapper);
		}
		
		/**
		 * Wraps all the current components of the plugin's admin screen
		 */
		
		private function wrap($tab, $title, $content) {
			$action = $this->get_options_url($tab);
			?>
			<div class="wrap">
				<h2><?php echo $title; ?></h2>
				<?php echo $this->display_tabs($tab); ?>
				<form method="post" action="<?php echo $action; ?>">
					<div class="postbox-container wp-customizer-postbox-options">
						<div class="metabox-holder">
							<div class="meta-box-sortables">
								<?php echo $content; echo $this->submit_button($tab); ?>
								<br /><br />
							</div>
						</div>
					</div>
					<div class="postbox-container wp-customizer-postbox-sidebar">
						<div class="metabox-holder">
							<div class="meta-box-sortables">
								<?php echo $this->help(); ?>
							</div>
						</div>
					</div>
					<?php
					wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
					wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);
					?>
				</form>
			</div>
			<?php
		}
		
		/**
		 * Emits the submit button for the plugin's admin screen, customized for the
		 * current tab
		 */
		
		private function submit_button($tab) {
			switch ($tab) {
				case 'functions':
				case 'scripts':
				case 'css':
					$content = array();
					$fmt = __('Save %s Options', 'wp-customizer');
					$content[] = '<p class="submit">';
					$content[] = '<input type="submit" name="wp_customizer_option_submitted" class="button-primary" value="';
					$content[] = sprintf($fmt, self::$tabs[$tab]);
					$content[] = '" />';
					$content[] = '</p>';
					return implode('', $content);
					break;

				case 'colophon':
				case 'debug':
				default:
					break;
			}
		}
		
		/**
		 * Emits the contents of the plugin's "Help & Support" postbox
		 */
		
		private function help() {
			$id = 'wp-customizer-help';
			$title = __('Help &amp; Support', 'wp-customizer');
			$email_address = antispambot ("gary@vicchi.org");

			$restart_url = $this->get_options_url ('functions');
			$restart_url .= '&wp_customizer_restart_tour';
			$restart_url = wp_nonce_url ($restart_url, 'wp-customizer-restart-tour');

			$content = array ();

			$content[] = '<p>';
			$content[] =  sprintf(__('For help and support with %s, here\'s what you can do, in order of preference:', 'wp-customizer'), WP_Customizer::$name);
			$content[] = '<ul><li>';
			$content[] = __('Look at the plugin\'s contextual help; click on the <em>Help</em> link at the top of this page.', 'wp-customizer');
			$content[] = '</li><li>';
			$content[] = __('Read the plugin\'s <a href="http://www.vicchi.org/codeage/wp-customizer/#faq" target="_blank">Frequently Asked Questions</a></li>', 'wp-customizer');
			$content[] = '</li><li>';
			$content[] = __('Ask a question on the <a href="http://wordpress.org/support/plugin/wp-customizer" target="_blank">WordPress support forum</a>; this is by far the best way so that other users can follow the conversation.', 'wp-customizer');
			$content[] = '</li><li>';
			$content[] = __('Ask me a question on Twitter; I\'m <a href="http://twitter.com/vicchi" target="_blank">@vicchi</a>.', 'wp-customizer');
			$content[] = '</li><li>';
			$content[] = sprintf (__('Drop me an <a href="mailto:%s">email </a>instead.', 'wp-customizer'), $email_address);
			$content[] = '</li></ul></p><p>';
			$content[] = __('But help and support is a two way street; here\'s what you might want to do:', 'wp-customizer');
			$content[] = '<ul><li>';
			$content[] = sprintf (__('If you like this plugin and use it on your WordPress site, or if you write about it online, <a href="http://www.vicchi.org/codeage/wp-customizer/">link to the plugin</a> and drop me an <a href="mailto:%s">email</a> telling me about this.', 'wp-customizer'), $email_address);
			$content[] = '</li><li>';
			$content[] = __('Rate the plugin on the <a href="http://wordpress.org/extend/plugins/wp-customizer/">WordPress plugin repository</a>.', 'wp-customizer');
			$content[] = '</li><li>';
			$content[] = sprintf(__('%s is free; no premium or light version, no ads. If you\'d like to support this plugin <a href="http://www.vicchi.org/codeage/donate/">here\'s how</a>.', 'wp-customizer'), WP_Customizer::$name);
			$content[] = '</li></ul></p>';
			$content[] = sprintf (__('<p>Find out what\'s new and get an overview of %s; <a href="%s">restart the plugin tour</a>.</p>', 'wp-customizer'), WP_Customizer::$name, $restart_url);

			return $this->postbox($id, $title, implode('', $content));
		}

		/**
		 * Extracts a specific settings/option field from the $_POST array.
		 *
		 * @param string field Field name.
		 * @return string Contents of the field parameter if present, else an empty string.
		 */

		private function option ($field) {
			$field = 'wp_customizer_' . $field;
			return (isset ($_POST[$field]) ? $_POST[$field] : "");
		}
		
		/**
		 * Called from "show_info"; validates a path and gets path status metadata
		 */
		
		private function validate_path($path, $type) {
			$meta = array(
				'path' => $path,
				'type' => $type,
				'dir' => null,
				'url' => null,
				'exists' => false,
				'readable' => false,
				'file-count' => 0,
				'file-readable' => 0,
				'file-unreadable' => 0,
				'file-skipped' => 0
				);

			if (!empty($path)) {
				$dir = ABSPATH . $path . DIRECTORY_SEPARATOR;
				$url = network_site_url($path);
				$meta['dir'] = $dir;
				$meta['url'] = $url;
				
				if (file_exists($dir)) {
					$meta['exists'] = true;
					if (is_readable($dir)) {
						$meta['readable'] = true;
					}
				}
			}
			
			return $meta;
		}

		/**
		 * Called from "make_section"; gets the current status of the the path settings for
		 * the functions, scripts and css tabs.
		 */
		
		private function show_info($type) {
			$info = '';
			$display = array();
			$option = $type . '_path';
			$id = 'wp-customizer-' . $type . '-info';
			$options = WP_Customizer::get_option();
			
			$meta = $this->validate_path($options[$option], $type);
			$info = '<code>' . $meta['dir'] . '</code>';
			
			if ($meta['exists']) {
				if ($meta['readable']) {
					$info .= ' exists and is readable';
					$info .= '<br />';
					$info .= 'Your ' . WP_Customizer::$types[$type]['descr'] . ' will be accessible at <code>';
					$info .= network_site_url($options[$option]) . DIRECTORY_SEPARATOR. '</code>';
					$class = 'wp-customizer-success';
				}
				else {
					$info .= ' exits but is not readable';
					$class = 'wp-customizer-warning';
				}
			}
			else {
				$info .= ' does not exist';
				$class = 'wp-customizer-error';
			}

			$display[] = '<div id="' . $id . '" class="wp-customizer-info ' . $class . '">';
			$display[] = $info;
			$display[] = '</div>';

			return implode(PHP_EOL, $display);
		}
		
		/**
		 * Called from "display_options"; outputs the common introduction section for the
		 * functions, scripts and css tabs
		 */
		
		private function make_intro_section() {
			$display = array();
			
			$display[] = '<div id="wp-customizer-path-info" class="wp-customizer-warning wp-customizer-info">';
			$display[] = __('The path names you specify must always be relative; they should not start with a <code>/</code> and should always be contained within the root of your WordPress installation; ', 'wp-customizer');
			$display[] = sprintf(__('this is currently <code>%s</code>', 'wp-customizer'), ABSPATH);
			$display[] = '</div>';
			
			return implode(PHP_EOL, $display);
		}
		
		/**
		 * Called from "display_options"; outputs the configuration options for a given
		 * customisation type
		 */
		
		private function make_section($type) {
			$options = WP_Customizer::get_option();
			
			$enabled_option = $type . '_enabled';
			$path_option = $type . '_path';

			$enabled_name = 'wp_customizer_' . $type . '_enabled';
			$enabled_id = 'wp-customizer-' . WP_Customizer::$types[$type]['id_stub'] . '-enabled';
			
			$path_name = 'wp_customizer_' . $type . '_path';
			$path_id = 'wp-customizer-' . WP_Customizer::$types[$type]['id_stub'] . '-path';

			$wrap_id = 'wp-customizer-' . WP_Customizer::$types[$type]['id_stub'] . '-wrap';
			
			$descr = WP_Customizer::$types[$type]['descr'];
			$ucf_descr = ucfirst($descr);
			$ucw_descr = ucwords($descr);

			$display = array();
			$display[] = '<p><strong>' . sprintf(__('Enable %s', 'wp-customizer'), $ucw_descr) . '</strong><br />
				<input type="checkbox" name="'. $enabled_name . '" id="' . $enabled_id . '" '
				. checked($options[$enabled_option], 'on', false)
				. ' />
				<small>' . sprintf(__('Enable loading of %s', 'wp-customizer'), $descr) . '</small></p>';

			$display[] = '<div id="' . $wrap_id . '"';
			if (empty($options[$enabled_option]) || $options[$enabled_option] !== 'on') {
				$display[] = ' style="display: none;"';
			}
			$display[] = '>';

			$display[] = '<p><strong>' . sprintf(__('%s Path', 'wp-customizer'), $ucw_descr) . '</strong><br />
				<input type="text" name="' . $path_name . '" id="' . $path_id . '" value="'
				. $options[$path_option]
				. '" /><br />
				<small>' . sprintf(__('Path to the directory which contains %s', 'wp-customizer'), $descr) . '</small></p>';

			$display[] = $this->show_info($type);
			$display[] = '</div>';

			return implode(PHP_EOL, $display);
		}

		/**
		 * Helper function to get the status of the plugin's tour pointer.
		 */

		private function is_pointer_set () {
			$user_id = get_current_user_id();
			$dismissed = explode(',', get_user_meta($user_id, 'dismissed_wp_pointers', true));
			return in_array('wp_customizera_pointer', $dismissed);
		}

		
	}	// end-class (...)
}	// end-if (!class_exists( ... ))

WP_CustomizerAdmin::get_instance();

?>