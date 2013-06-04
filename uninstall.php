<?php

if (defined('WP_UNINSTALL_PLUGIN')) {
	delete_option('wp_customizer_options');
	
	$fields = array(0 => 'ID');
	$query = new WP_User_Query(array('fields' => $fields));
	$users = $query->get_results();
	
	foreach ($users as $user) {
		$dismissed = explode(',', get_user_meta($user->ID, 'dismissed_wp_pointers', true));
		$key = array_search('wp_customizer_pointer', $dismissed);
		if ($key !== false) {
			unset($dismissed[$key]);
			update_user_meta($user->ID, 'dismissed_wp_pointers', implode(',', $dismissed));
		}
	}
}

else {
	exit();
}

?>