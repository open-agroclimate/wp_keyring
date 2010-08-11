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

// GLOBAL VARIABLES
global $wp_keyring_db_version;
$wp_keyring_db_version = "1.0";

// HOOKS AND ACTIONS
add_action( 'admin_menu', 'wp_keyring_menu' );
register_activation_hook( __FILE__, 'wp_keyring_installer');


function wp_keyring_installer() {
	global $wp_keyring_db_version;
	global $wpdb;
	
	// Only run this if you don't have the correct version (or any version at all).
	// NOTE: I know you could short circuit this by creating it all manually, but you can mess up your own install if you want.	
	if ( ( !get_option( "wp_keyring_db_version") ) || ( get_option( "wp_keyring_db_version" ) != $wp_keyring_db_version) ) {
		$table_name = $wpdb->prefix . 'keyring_plugin';
		$structure = "CREATE TABLE $table_name (
			id INT(9) NOT NULL AUTO_INCREMENT,
			blog_id INT(11) NOT NULL DEFAULT '0',
			key_id VARCHAR(25) NOT NULL,
			name VARCHAR(100) NOT NULL DEFAULT '',
			api_key TEXT NOT NULL DEFAULT '',
			PRIMARY KEY  (id),
			KEY  (key_id)
			);";
	
	
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $structure );
	}
	
	// If this is the first time running the structure, create the option. If you are upgrading, update the version as well.
	if (!get_option( "wp_keyring_db_version" ) )	
		add_option( "wp_keyring_db_version", $wp_keyring_db_version );
	if ( get_option( "wp_keyring_db_version" ) != $wp_keyring_db_version )
		update_option( "wp_keyring_db_version", $wp_keyring_db_version );
}

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
