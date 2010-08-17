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
	$wpkr_page_uri = 'tools.php?page=wp_keyring_directory';
?>
<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e( 'WP Keyring Directory', 'wp_keyring' ); ?></h2>
	<h3><?php _e( 'Edit Key' ); ?></h3>
	<?php
	if( $_REQUEST[ 'action' ] == 'wpkr_edit' ) {
		if( !isset($_REQUEST[ 'id' ] ) ) {
			wp_die( __( 'You are improperly attempting to modify a key.' ) );
		}
		check_admin_referer( 'edit-' . $_REQUEST[ 'id' ] . '-on-keyring' );
		$wpkr_key = wp_keyring_get_key( $_REQUEST[ 'id' ] );
		if( !$wpkr_key )
			wp_die( __( 'They requested key does not exist.' ) );
	?>
	<form name="wp_keyring-edit-form" method="POST" action="<?php echo $wpkr_page_uri; ?>">
		<?php wp_nonce_field( 'update-'. $_REQUEST[ 'id' ] . '-on-keyring' ); ?>
		<input type="hidden" name="action" value="wpkr_update" />
		<input type="hidden" name="id" value="<?php echo $_REQUEST[ 'id' ]; ?>" />
		<p><?php _e( 'Key Name:', 'wp_keyring' ); ?></p>
		<input type="text" name="key-name" value="<?php echo $wpkr_key[ 'wpkr_display' ]?>" size="60" />
		<p><?php _e( 'API Key:', 'wp_keyring' ); ?></p>
		<textarea name="key-value" cols="60" rows="5"><?php echo $wpkr_key[ 'wpkr_key' ] ?></textarea>
		<hr />
		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Edit Key' ); ?>" />
		</p>
	</form>
	<?php
	}
	else {
	?>
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
			<?php
				if( is_multisite() )
					$wpkr_keys = get_site_option( 'wp_keyring_keys' );
				else
					$wpkr_keys = get_option( 'wp_keyring_keys' );
					if( !$wpkr_keys ) {
			?>
			<tr>
				<td colspan="2"><?php _e( 'No keys added', 'wp_keyring' ); ?></td>
			</tr>
			<?php
				}
				else {
					foreach( $wpkr_keys as $wpkr_keyid => $wpkr_keyinfo ) {
						echo '<tr><td><strong>' . $wpkr_keyinfo[ 'wpkr_display' ] . '</strong></td>';
						echo '<td rowspan="2">' . $wpkr_keyinfo[ 'wpkr_key' ] . '</td></tr>';
						echo '<tr class="second"><td><a href="'. wp_nonce_url( $wpkr_page_uri . '&action=wpkr_edit&id=' . utf8_uri_encode( $wpkr_keyid ), 'edit-'. $wpkr_keyid. '-on-keyring' ) . '" title="' . __( 'Edit this key' ) . '">' . __( 'Edit' ) . '</a> | <a href="'. wp_nonce_url( $wpkr_page_uri . '&action=wpkr_delete&id=' . utf8_uri_encode( $wpkr_keyid ), 'remove-'. $wpkr_keyid. '-from-keyring' ) . '" title="' . __( 'Remove this key' ) . '">' . __( 'Remove' ) . '</a></td></tr>';
					}
				}
			?>
		</tbody>
	</table>
	<form name="wp_keyring-add-form" method="POST" action="<?php echo $wpkr_page_uri; ?>">
		<?php wp_nonce_field( 'add-key-to-keyring' ); ?>
		<input type="hidden" name="action" value="wpkr_add" />
		<p><?php _e( 'Key Name:', 'wp_keyring' ); ?></p>
		<input type="text" name="newkey-name" value="" size="60" />
		<p><?php _e( 'API Key:', 'wp_keyring' ); ?></p>
		<textarea name="newkey-value" value="" cols="60" rows="5"></textarea>
		<hr />
		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Add Key' ); ?>" />
		</p>
	</form>
	<?php
	} 
	echo '</div>';
}

function wp_keyring_handle_actions() {
	if ( isset( $_REQUEST[ 'action' ] ) ) {		
		switch( $_REQUEST[ 'action' ] ) {
			case 'wpkr_add' :
				check_admin_referer( 'add-key-to-keyring' );
				wp_keyring_add_key( $_REQUEST[ 'newkey-name' ], $_REQUEST[ 'newkey-value' ] );
				break;
			case 'wpkr_delete' :
				if( !isset( $_REQUEST[ 'id' ] ) )
					wp_die( __( 'You are trying to remove a key improperly.' ) );
				check_admin_referer( 'remove-' . $_REQUEST[ 'id' ] . '-from-keyring' );
				wp_keyring_remove_key ( $_REQUEST[ 'id' ] );
				break;
			case 'wpkr_update' :
				if( !isset( $_REQUEST[ 'id' ] ) )
					wp_die( __( 'You are trying to edit a key improperly.' ) );
				check_admin_referer( 'update-' . $_REQUEST[ 'id' ] . '-on-keyring' );
				wp_keyring_update_key( $_REQUEST[ 'id' ], $_REQUEST[ 'key-name' ], $_REQUEST[ 'key-value' ] );
		}
	}
}

function wp_keyring_add_key( $wpkr_name, $wpkr_key ) {
	$current = wp_keyring_get_keys();
	$wpkr_keyid = wp_keyring_get_id( $wpkr_name );
	// Verify we don't have one key with the same name on this site.
	if( array_key_exists( $wpkr_keyid, $current ) ) {
		echo '<div id="message" class="error"><p><strong>' . __( 'A key with this name already exists. Please try again.' ) . '</strong></p></div>';
		return;
	}

	$current[ $wpkr_keyid ] = array( 'wpkr_display' => $wpkr_name, 'wpkr_key' => $wpkr_key );
	if( wp_keyring_save_keys( $current ) )
		echo '<div id="message" class="updated"><p>' . __( 'Key added successfully.' ) . '</p></div>';	
	else
		echo '<div id="message" class="error"><p><strong>' . __( 'Something has happened and your key was not added properly.' ) . '</strong></p></div>';
}

function wp_keyring_update_key( $wpkr_keyid, $wpkr_name, $wpkr_key ) {
	$current = wp_keyring_get_keys();
	if( !array_key_exists( $wpkr_keyid, $current ) ) {
		echo '<div id="message" class="error"><p><strong>' . __( 'This key does not exist.' ) . '</strong></p></div>';
		return;
	}
	$current[ $wpkr_keyid ] = array( 'wpkr_display' => $wpkr_name, 'wpkr_key' => $wpkr_key );
	if( wp_keyring_save_keys( $current ) )
		echo '<div id="message" class="updated"><p>' . __( 'Key updated successfully.' ) . '</p></div>';
	else
		echo '<div id="message" class="error"><p><strong>' . __( 'Something has happened and your key was not updated properly.' ) . '</strong></p></div>';
}

function wp_keyring_remove_key( $wpkr_keyid ) {
	$current = wp_keyring_get_keys();
	if( !array_key_exists( $wpkr_keyid, $current ) ) {
		echo '<div id="message" class="error"><p><strong>' . __( 'This key does not exist.' ) . '</strong></p></div>';
		return;
	}
		
	unset( $current[ $wpkr_keyid ]);
	if( wp_keyring_save_keys( $current ) )
		echo '<div id="message" class="updated"><p>' . __( 'Key removed successfully.' ) . '</p></div>';
	else
		echo '<div id="message" class="error"><p><strong>' . __( 'Something has happened and your key was not removed properly.' ) . '</strong></p></div>';
	
}

function wp_keyring_get_keys() {
	if ( is_multisite() ) 
		$keys = get_site_option( 'wp_keyring_keys', array() );
	else
		$keys = get_option( 'wp_keyring_keys', array() );
	return $keys;
}

function wp_keyring_get_key( $wpkr_keyid, $wpkr_only_the_key=false ) {
	$current = wp_keyring_get_keys();
	if ( isset( $current[ $wpkr_keyid ] ) ) {
		if( $wpkr_only_the_key )
			return $current[ $wpkr_keyid ][ 'wpkr_key' ];
		else
			return $current[ $wpkr_keyid ];
	}
	else {
		return false;
	}
}

function wp_keyring_save_keys( $wpkr_keys ) {
	if( is_multisite() )
		return update_site_option( 'wp_keyring_keys', $wpkr_keys );
	else
		return update_option( 'wp_keyring_keys', $wpkr_keys );
}

function wp_keyring_get_id( $wpkr_name ) {
	global $wpdb;
	return sanitize_title_with_dashes( $wpkr_name ) . '-' . $wpdb->siteid;
}

function wp_keyring_generate_dropdown( $varname='wpkr_id', $selected='') {		
	$ddl = '<select name="' . $varname . '">' . "\n";
	$current = wp_keyring_get_keys();
	if( count( $current ) == 0 ) {
		$ddl = 'Go To Settings -> WP Keyring to add keys';
	}
	else {
		// Give people a way out. -ish
		$ddl = $ddl . '<option value="" ' . ( $selected == '' ? 'selected' : '' ) . '>-----</option>';
		foreach( $current as $wpkr_keyid => $wpkr_keyinfo )	 {
			$ddl = $ddl . '<option value="'. $wpkr_keyid . '" ' . ( $selected == $wpkr_keyid ? 'selected' : '' ) . '>' . $wpkr_keyinfo[ 'wpkr_display' ] . '</option>';
		}
	}
	$ddl = $ddl . '</select>' . "\n";
	
	return $ddl;
}