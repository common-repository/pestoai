<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

require_once __DIR__ . '/constants.php';

/**
 * Fired during plugin activation
 *
 * @link       https://www.pestoai.com
 * @since      1.0.0
 *
 * @package    PestoAI
 * @subpackage PestoAI/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    PestoAI
 * @subpackage PestoAI/includes
 * @author     pestoai <support@pestoai.com>
 */
class PestoAI_Activator {

	/**
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

        // Get the current user
        $current_user = wp_get_current_user();
        
        // Get current user's email and ID
        $user_email = $current_user->user_email;
        $user_id = $current_user->ID;
        $app_username = $current_user->user_login;

        // Get the URL of the site the plugin was installed in
        $site_url = home_url();
        
        // Define the app_id for PestoAI
        $app_id = 'pestoai';

        // Define app password name
        $app_password_name = 'PestoAI App Password';

        // Check if the user has an application password with the app_id 'pestoai'
        $app_passwords = WP_Application_Passwords::get_user_application_passwords($user_id);
        if ($app_passwords) {
            foreach ($app_passwords as $app_password) {
                if ($app_password['app_id'] === $app_id) {
                    //We delete the existing passwords
                    $app_password_uuid = $app_password['uuid'];
                    $did_delete = WP_Application_Passwords::delete_application_password($user_id, $app_password_uuid);
                }
            }
        }

        // If the user does not have an application password with app_id 'pestoai', create one
        $args = array(
            'name' => $app_password_name,
            'app_id' => $app_id,
        );
        $app_password_details = WP_Application_Passwords::create_new_application_password($user_id, $args);

        if (!$app_password_details || is_wp_error($app_password_details)) {

            error_log('PestoAI Activation Error: Could not create application password');

             //Translators: No dynamic content in this message
            $pestoai_translated_activate_error_permissions_message = __('PestoAI Activation Error: You do not have permissions to install this plugin. Please contact your administrator.','pestoai');
            wp_die(esc_html($pestoai_translated_activate_error_permissions_message));
        }

        $app_password = $app_password_details[0]; // The plaintext password

        

        // Prepare data for API request
        $data = array(
            'signup_site_url' => $site_url,
            'signup_source' => 'wordpress',
            'email' => $user_email,
            'wp_user_id' => $user_id,
            'wp_app_username' => $app_username,
            'wp_app_password' => $app_password
        );

        // Convert data to JSON
        $json_data = wp_json_encode($data);

        // Send API request
        $response = wp_remote_post(PESTOAI_API_SIGNUP_ENDPOINT, array(
            'body' => $json_data,
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-api-key' => PESTOAI_API_KEY
            ),
            'timeout' => 20
        ));

        // Check for errors
        $response_code = wp_remote_retrieve_response_code($response);
        if (is_null($response_code) || !is_numeric($response_code))  {
            error_log('PestoAI Activation Warning: $response_code is not numeric. Maybe due to timeout. continuing...');
        }
        else if ($response_code < 200 || $response_code >= 300) {
            error_log('PestoAI Activation Error: ' . $response->get_error_message());

            //Translators: No dynamic content in this message
            $pestoai_translated_request_rejected_error_message = __('PestoAI Activation Error: PestoAI rejected the request', 'pestoai');
            wp_die(esc_html($pestoai_translated_request_rejected_error_message));
        }

        // Decode the response
        $response_body = wp_remote_retrieve_body($response);
        $result = json_decode($response_body, true);

		$didsignup = false;
		if (is_array($result) && isset($result['did_signup']) && $result['did_signup'] === true) {
			$didsignup = true;
		}

        // Store the result as key-value pairs
        if (is_array($result) && isset($result['main_url'])) {

            foreach ($result as $key => $value) {
                update_option('pestoai_' . $key, $value);
            }

        } else {
            error_log('PestoAI Activation Error: main_url not returned');

            //Translators: No dynamic content in this message
            $pestoai_translated_cannot_connect_site_error_message = __('PestoAI Activation Error: Your site could not be connected to PestoAI', 'pestoai');
            wp_die(esc_html($pestoai_translated_cannot_connect_site_error_message));
        }

		// Set a transient to show an admin notice after activation
		if ($didsignup === true) {
            set_transient('pestoai_activation_notice', array('email' => $user_email), 5);
		}

        return;
        
    }

}
