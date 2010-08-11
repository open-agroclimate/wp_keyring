<?php
/*
Plugin Name: wp_keyring
Plugin URI:  http://git.agroclimate.org/oactools/wp_keyring
Description: Coming soon
Version: 1.0
Author: The Open AgroClimate Team
Author URI: http://open.agroclimate.org/
License: BSD Modified
*/

add_action( 'admin_menu', 'wp_keyring_menu' );

function wp_keyring_menu() {
	add_management_page( 'WP Keyring Settings', 'WP Keyring', 'manage_options', 'wp_keyring_settings', 'wp_keyring_settings' );
}

function wp_keyring_settings() {
	if( !current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	echo '<div class="wrap">';
	echo '<p>The Form Goes Here</p>';
	echo '</div>';
}
?>
