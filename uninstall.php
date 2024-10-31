<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://www.pestoai.com
 * @since      1.0.0
 *
 * @package    PestoAI
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}


// Function to retrieve and delete all options related to the plugin
function pestoai_cleanup_options() {

	// Get current user 
	$current_user = wp_get_current_user();

	if ( $current_user && $current_user->ID ) {
		// Define the app_id for PestoAI
		$app_id = 'pestoai';

		// Check if the user has an application password with the app_id 'pestoai' and delete them
		$app_passwords = WP_Application_Passwords::get_user_application_passwords($current_user->ID);
		if ($app_passwords) {
			foreach ($app_passwords as $app_password) {
				if ($app_password['app_id'] === $app_id) {
					WP_Application_Passwords::delete_application_password( $current_user->ID, $app_password['uuid'] );
					break;
				}
			}
		}
	}

	


	$prefix = 'pestoai_';

	// Get all options with the prefix
	$options = wp_load_alloptions();
	foreach ( $options as $option_name => $option_value ) {
		if ( strpos( $option_name, $prefix ) === 0 ) {
			// Delete each option
			delete_option( $option_name );
		}
	}



}


pestoai_cleanup_options();
