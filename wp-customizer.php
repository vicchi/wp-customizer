<?php

/*
Plugin Name: WP Customizer
Plugin URI: http://www.vicchi.org/codeage/wp-customizer/
Description: Manage and load custom function, script and CSS files into your theme without the need to edit your theme's functions.php or any other theme or plugin source file.
Version: 1.0.1
Author: Gary Gale
Author URI: http://www.garygale.com/
License: GPL2
Text Domain: wp-customizer
*/

define('WPCUSTOMIZER_PATH', plugin_dir_path(__FILE__));
define('WPCUSTOMIZER_URL', plugin_dir_url(__FILE__));
define('WPCUSTOMIZER_BASENAME', plugin_basename(__FILE__));
define('WPCUSTOMIZER_FILE', __FILE__);
define('WPCUSTOMIZER_INCLUDE_SENTRY', true);

//define('WPCUSTOMIZER_DEBUG', true);

define('WPCUSTOMIZER_SRC', WPCUSTOMIZER_PATH . 'includes/class-wp-customizer.php');
define('WPCUSTOMIZER_ADMIN_SRC', WPCUSTOMIZER_PATH . 'includes/class-wp-customizer-admin.php');
define('WPCUSTOMIZER_LOADER_SRC', WPCUSTOMIZER_PATH . 'includes/class-wp-customizer-loader.php');
define('WPCUSTOMIZER_UPGRADE_SRC', WPCUSTOMIZER_PATH . 'includes/class-wp-customizer-upgrade.php');
define('WPCUSTOMIZER_POINTERS_SRC', WPCUSTOMIZER_PATH . 'includes/class-wp-customizer-pointers.php');

define('WPCUSTOMIZER_ADMIN_PATH', 'wp-customizer/includes/class-wp-customizer-admin.php');

require_once(WPCUSTOMIZER_PATH . 'includes/wp-plugin-base/wp-plugin-base.php');
require_once(WPCUSTOMIZER_SRC);

?>