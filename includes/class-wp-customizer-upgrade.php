<?php

if (!defined('WPCUSTOMIZER_INCLUDE_SENTRY')) {
	die('The way is shut. It was made by those who are dead, and the dead keep it. The way is shut.');
}

if (!class_exists('WP_CustomizerUpgrade')) {
	class WP_CustomizerUpgrade {
		
		public function upgrade() {
			$options = null;
			$upgrade = false;
			$current_version = null;
			
			$options = WP_Customizer::get_option();
			
			if (is_array($options)) {
				if ($options['version'] == WP_Customizer::VERSION) {
					return;
				}
			}
			
			if (!is_array($options)) {
				WP_Customizer::get_instance()->activation_hook();
			}
			
			else {
				$current_version = $options['version'];
				
				switch ($current_version) {
					case '000':
					case '100':
					case '101':
						$options['version'] = WP_Customizer::VERSION;
						$upgrade = true;
						
					default:
						break;
				}	// end-switch(...)
				
				if ($upgrade) {
					update_option(WP_Customizer::OPTIONS, $options);
				}
			}
		}
	}	// end-class (...)
}	// end-if (!class_exists(...))

?>