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
}

function wp_keyring_menu() {
	add_management_page( 'WP Keyring Directory', 'WP Keyring', 'manage_options', 'wp_keyring_directory', 'wp_keyring_directory_page' );
}

function wp_keyring_directory_page() {
	if( !current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
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
		<textarea name="neykey-value" value="" cols="60" rows="5"></textarea>
		<hr />
		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Add Key' ); ?>" />
		</p>
	</form>
</div>
<?php
}


// Actual loading stuff
if ( !empty( $_REQUEST['action'] ) )
	switch( $_REQUEST['action'] ) {
		case 'add':
			check_admin_referer( 'add-key-to-keyring' );
			break;
	}