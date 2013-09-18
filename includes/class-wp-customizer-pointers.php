<?php

if (!defined('WPCUSTOMIZER_INCLUDE_SENTRY')) {
	die('The way is shut. It was made by those who are dead, and the dead keep it. The way is shut.');
}

if (!class_exists('WP_CustomizerPointers')) {
	class WP_CustomizerPointers extends WP_PluginBase_v1_1 {
		
		const POINTER_NAME = 'wp_customizer_pointer';
		
		private static $instance;
		
		private function __construct() {
			$this->hook('admin_enqueue_scripts');
		}
		
		public static function get_instance() {
			if (!isset(self::$instance)) {
				$class = __CLASS__;
				self::$instance = new $class();
			}
			return self::$instance;
		}

		public function admin_enqueue_scripts() {
			$dismissed = explode(',', get_user_meta(wp_get_current_user()->ID, 'dismissed_wp_pointers', true));
			$do_tour = !in_array(self::POINTER_NAME, $dismissed);
			
			if ($do_tour) {
				wp_enqueue_style('wp_pointer');

				$src = WPCUSTOMIZER_URL . 'css/wp-customizer-pointer';
				$src = WP_Customizer::make_css_path($src);
				$handle = 'wp-customizer-pointer-css';
				$deps = array('wp-pointer');
				wp_enqueue_style($handle, $src, $deps);

				wp_enqueue_script('jquery-ui');
				wp_enqueue_script('wp-pointer');
				wp_enqueue_script('utils');
				
				$this->hook('admin_print_footer_scripts');
			}
		}
		
		public function admin_print_footer_scripts() {
			global $pagenow;
			global $current_user;
			
			$tour = array(
				'functions' => array(
					'content' => '<h3>' . __('The Functions Tab', 'wp-customizer') . '</h3>'
						. '<p>'
						. sprintf(__('%s allows you to load your custom functions in three ways:', 'wp-customizer'), WP_Customizer::$name)
						. '<ul>'
						. '<li><strong>' . __('Front End: ', 'wp-customizer') . '</strong>' . __('Load on the front-end only; do not load when displaying admin screens', 'wp-customizer') . '</li>'
						. '<li><strong>' . __('Admin: ', 'wp-customizer') . '</strong>' . __('Load on admin screens only; do not load when displaying posts or pages', 'wp-customizer') . '</li>'
						. '<li><strong>' . __('Common: ', 'wp-customizer') . '</strong>' . __('Load on both the front-end and on admin screens', 'wp-customizer') . '</li>'
						. '</ul>'
						. '</p>',
					'button2' => __('Next', 'wp-customizer'),
					'function' => 'window.location="' . $this->get_tab_url('scripts') . '"'
				),
				'scripts' => array(
					'content' => '<h3>' . __('The Scripts Tab', 'wp-customizer') . '</h3>'
						. '<p>'
						. sprintf(__('%s allows you to load your custom scripts in three ways:', 'wp-customizer'), WP_Customizer::$name)
						. '<ul>'
						. '<li><strong>' . __('Front End: ', 'wp-customizer') . '</strong>' . __('Load on the front-end only; do not load when displaying admin screens', 'wp-customizer') . '</li>'
						. '<li><strong>' . __('Admin: ', 'wp-customizer') . '</strong>' . __('Load on admin screens only; do not load when displaying posts or pages', 'wp-customizer') . '</li>'
						. '<li><strong>' . __('Common: ', 'wp-customizer') . '</strong>' . __('Load on both the front-end and on admin screens', 'wp-customizer') . '</li>'
						. '</ul>'
						. '</p>',
					'button2' => __('Next', 'wp-customizer'),
					'function' => 'window.location="' . $this->get_tab_url('css') . '"'
				),
				'css' => array(
					'content' => '<h3>' . __('The CSS Tab', 'wp-customizer') . '</h3>'
						. '<p>'
						. sprintf(__('%s allows you to load your custom CSS in three ways:', 'wp-customizer'), WP_Customizer::$name)
						. '<ul>'
						. '<li><strong>' . __('Front End: ', 'wp-customizer') . '</strong>' . __('Load on the front-end only; do not load when displaying admin screens', 'wp-customizer') . '</li>'
						. '<li><strong>' . __('Admin: ', 'wp-customizer') . '</strong>' . __('Load on admin screens only; do not load when displaying posts or pages', 'wp-customizer') . '</li>'
						. '<li><strong>' . __('Common: ', 'wp-customizer') . '</strong>' . __('Load on both the front-end and on admin screens', 'wp-customizer') . '</li>'
						. '</ul>'
						. '</p>',
					'button2' => __('Next', 'wp-customizer'),
					'function' => 'window.location="' . $this->get_tab_url('colophon') . '"'
				),
				'colophon' => array(
					'content' => '<h3>' . __('The Colophon Tab', 'wp-customizer') . '</h3>'
						. '<p><strong>' . sprintf(__('About %s', 'wp-customizer'), WP_Customizer::$name) . '</p></strong>'
						. '<p>' . __('This tab contains the details on how this plugin was written; you can also find a helpful display of the plugin\'s configuration settings which you can use when asking for support on the <a href="http://wordpress.org/support/plugin/wp-customizer">WordPress forums</a>.', 'wp-customizer') . '</p>',
					'button2' => __('Next', 'wp-customizer'),
					'function' => 'window.location="' . $this->get_tab_url('debug') . '"'
				),
				'debug' => array(
					'content' => '<h3>' . __('The Debug Tab', 'wp-customizer') . '</h3>'
						. '<p>' . __('This tab allows you to check the characteristics of each type of customization you can make, specifying whether a customization is active, whether a path has been supplied, whether the path is valid and readable and whether any files found are valid, unreadable or disabled.', 'wp-customizer') . '</p>'
						. '<p>' . __('This is the end of the tour. To see this again you can click on the "<em>restart the plugin tour</em>" link, found on the <em>Help &amp; Support</em> side box on any of the plugin\'s admin tabs.', 'wp-customizer') . '</p>'
				)
			);
			
			$tab = null;
			if (isset($_GET['tab'])) {
				$tab = $_GET['tab'];
			}
			
			$sub_page = null;
			if (isset($_GET['page'])) {
				$sub_page = $_GET['page'];
			}
			
			$restart = false;
			if (isset($_GET['wp_customizer_restart_tour'])) {
				if (check_admin_referer('wp-customizer-restart-tour')) {
					$restart = true;
				}
			}
			
			$function = '';
			$button2 = '';
			$args = array();
			$show = false;
			
			if ($restart || ($pagenow != 'options-general.php' || !array_key_exists($tab, $tour))) {
				$show = true;
				$file_error = true;
				$content = '<h3>' . sprintf(__('This Is %s %s', 'wp-customizer'), WP_Customizer::$name, WP_Customizer::DISPLAY_VERSION) . '</h3>';
				$whatsnew_file = WPCUSTOMIZER_PATH . 'help/whatsnew-' . WP_Customizer::VERSION . '.html';
				if (file_exists($whatsnew_file)) {
					$whatsnew = file_get_contents($whatsnew_file);
					if (isset($whatsnew) && !empty($whatsnew)) {
						$file_error = false;
						$content .= $whatsnew;
					}
				}
				
				if ($file_error) {
						$content .= '<p>' . sprintf (__('Something seems to be wrong with your %s installation; the file %s could not be found', 'wp-customizer'), WP_Customizer::$name, $whatsnew_file) . '</p>';
				}

				$content .= '<p>' . __('Want to know more? Look in the plugin\'s <code>readme.txt</code> or just click the <em>Find Out More</em> button below.', 'wp-customizer' ) . '</p>';

				$button2 = __("Find Out More", 'wp-customizer');
				$function = 'document.location="' . $this->get_tab_url('functions') . '";';
				$id = '#menu-settings';
				
				$args = array(
					'content' => $content,
					'position' => array(
						'edge' => 'left',
						'align' => 'center'
					)
				);

			}
			
			else {
				if ($tab && in_array($tab, array_keys($tour))) {
					$show = true;

					$button2 = false;
					$function = '';
					$id = '#wp-customizer-tab-' . $tab;
					
					if (isset($tour[$tab]['button2'])) {
						$button2 = $tour[$tab]['button2'];
					}
					if (isset($tour[$tab]['function'])) {
						$function = $tour[$tab]['function'];
					}

					$args = array(
						'content' => $tour[$tab]['content'],
						'position' => array(
							'edge' => 'top',
							'align' => 'left'
						)
					);
					
				}
			}

			if ($show) {
				$this->make_pointer_script ($id, $args, __('Close', 'wp-customizer'), $button2, $function);
			}
		}
		
		function make_pointer_script ($id, $options, $button1, $button2=false, $function='') {
			?>
			<script type="text/javascript">
				(function ($) {
					var wp_customizer_tour_opts = <?php echo json_encode ($options); ?>, setup;
				
					wp_customizer_tour_opts = $.extend (wp_customizer_tour_opts, {
						buttons: function (event, t) {
							button = jQuery ('<a id="pointer-close" class="button-secondary">' + '<?php echo $button1; ?>' + '</a>');
							button.bind ('click.pointer', function () {
								t.element.pointer ('close');
							});
							return button;
						},
						close: function () {
							$.post (ajaxurl, {
								pointer: 'wp_customizer_pointer',
								action: 'dismiss-wp-pointer'
							});
						}
					});
				
					setup = function () {
						$('<?php echo $id; ?>').pointer(wp_customizer_tour_opts).pointer('open');
						<?php if ($button2) { ?>
							jQuery ('#pointer-close').after ('<a id="pointer-primary" class="button-primary">' + '<?php echo $button2; ?>' + '</a>');
							jQuery ('#pointer-primary').click (function () {
								<?php echo $function; ?>
							});
							jQuery ('#pointer-close').click (function () {
								$.post (ajaxurl, {
									pointer: 'wp_customizer_pointer',
									action: 'dismiss-wp-pointer'
								});
							})
						<?php } ?>
					};
				
					if (wp_customizer_tour_opts.position && wp_customizer_tour_opts.position.defer_loading) {
						$(window).bind('load.wp-pointers', setup);
					}
					else {
						setup ();
					}
				}) (jQuery);
			</script>
			<?php
		}
		
		private function get_tab_url($tab=null) {
			$url = array();
			
			$url[] = admin_url('options-general.php');
			$url[] = '?page=' . WPCUSTOMIZER_ADMIN_PATH;
			if (isset($tab) && !empty($tab)) {
				$url[] = '&tab=' . $tab;
			}
			
			return implode('', $url);
		}
		
	}	// end-class (...)
}	// end-if (!class_exists(...))

WP_CustomizerPointers::get_instance();

?>