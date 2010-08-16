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

// HOOKS AND ACTIONS
add_action( 'admin_menu', 'wp_keyring_menu' );
add_action( 'admin_init', 'wp_keyring_init' );

function wp_keyring_init() {
	// Store all .mo files underneath the <plugin_dir>/locale directory.
	$plugin_dir = basename( dirname( __FILE__ ) );
	load_plugin_textdomain( 'wp_keyring', null, $plugin_dir . '/languages' );
	if( !current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	wp_keyring_handle_actions();
}

function wp_keyring_menu() {
	add_management_page( 'WP Keyring Directory', 'WP Keyring', 'manage_options', 'wp_keyring_directory', 'wp_keyring_directory_page' );
}

function wp_keyring_directory_page() {
?>
<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e( 'WP Keyring Directory', 'wp_keyring' ); ?></h2>
	<table class="widefat" cellspacing="0" id="wp_keyring-directory-table">
		<thead>
			<tr>
				<th scope="col" class="manage-column"><?php _e( 'Name', 'wp_keyring' ); ?></th>
				<th scope="col" class="manage-column"><?php _e( 'Key', 'wp_keyring' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th scope="col" class="manage-column"><?php _e( 'Name', 'wp_keyring' ); ?></th>
				<th scope="col" class="manage-column"><?php _e( 'Key', 'wp_keyring' ); ?></th>
			</tr>
		</tfoot>
		<tbody class="plugins">
			<tr>
				<td colspan="2"><?php _e( 'No keys added', 'wp_keyring' ); ?></td>
			</tr>
		</tbody>
	</table>
	
	<form name="wp_keyring-add-form" method="POST" action="">
		<?php wp_nonce_field( 'add-key-to-keyring' ); ?>
		<input type="hidden" name="action" value="add" />
		<p><?php _e( 'Key Name:', 'wp_keyring' ); ?></p>
		<input type="text" name="newkey-name" value="" size="60" />
		<p><?php _e( 'API Key:', 'wp_keyring' ); ?></p>
		<textarea name="newkey-value" value="" cols="60" rows="5"></textarea>
		<hr />
		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Add Key' ); ?>" />
		</p>
	</form>
</div>
<?php
}

function wp_keyring_handle_actions() {
	global $wpdb;
	
	if ( isset( $_POST['action'] ) ) {
		switch( $_POST['action'] ) {
			case 'add' :
				check_admin_referer( 'add-key-to-keyring' );
				wp_keyring_add_key( $_POST['newkey-name'], $_POST['newkey-value'], $wpdb->siteid );
				break;
		}
	}
}

function wp_keyring_add_key( $wpkr_name, $wpkr_key, $wpkr_siteid=0 ) {
	if( is_multisite() )
		$current = get_site_option( 'wp_keyring_keys', array() );
	else
		$current = get_option( 'wp_keyring_keys', array() );
	
	$wpkr_id = sanitize_title_with_dashes( $wpkr_name );
	
	// Verify we don't have one key with the same name on this site.
	if( array_key_exists( $wpkr_id, $current ) ) {
		echo '<div id="message" class="error"><p><strong>' . __( 'A key with this name already exists. Please try again.' ) . '</strong></p></div>';
		return;
	}

	$current[sanitize_title_with_dashes( $wpkr_name )] = array( 'wpkr_display' => $wpkr_name, 'wpkr_key' => $wpkr_key, 'wpkr_siteid' => $wpkr_siteid );
	if( is_multisite() )
		update_site_option( 'wp_keyring_keys', $current );
	else
		update_option( 'wp_keyring_keys', $current );
	echo '<div id="message" class="updated fade"><p>' . __( 'Key added successfully.' ) . '</p></div>';	
}