<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

require_once __DIR__ . '/constants.php';

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.pestoai.com
 * @since      1.0.0
 *
 * @package    PestoAI
 * @subpackage PestoAI/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    PestoAI
 * @subpackage PestoAI/includes
 * @author     pestoai <support@pestoai.com>
 */
class PestoAI_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		 // Get the current user
		 $current_user = wp_get_current_user();

		 // Get current user's email and ID
		 $user_email = $current_user->user_email;
		 $user_id = $current_user->ID;
		 $app_username = $current_user->user_login;

		 // Get the URL of the site the plugin was installed in
		 $site_url = home_url();
		 

 
		 // Prepare data for API request
		 $data = array(
			 'signup_site_url' => $site_url,
			 'signup_source' => 'wordpress',
			 'email' => $user_email,
			 'wp_user_id' => $user_id,
		 );
 
		 // Convert data to JSON
		 $json_data = wp_json_encode($data);
 
		 // Send API request
		 $response = wp_remote_post(PESTOAI_API_DEACTIVATE_ENDPOINT, array(
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
			 error_log('PestoAI De-activation Error: ' . $response->get_error_message());

			 //Translators: No dynamic content in this message
			 $pestoai_translated_request_rejected_error_message = __('PestoAI De-activation Error: PestoAI rejected the request', 'pestoai');
			 wp_die(esc_html($pestoai_translated_request_rejected_error_message));
		 }
 
		 // Decode the response
		 $response_body = wp_remote_retrieve_body($response);
		 $result = json_decode($response_body, true);
 
		 $active = true;
		 if (is_array($result) && isset($result['active']) && $result['active'] === false) {
			 $active = false;
		 }

		 //Check if site is still active (it should not be)
		 if ($active === true) {
			error_log('PestoAI  De-activation Error: Something went wrong, still active');
		 }
 
		 // Store the result as key-value pairs
		 if (is_array($result) && isset($result['main_url'])) {
 
			 foreach ($result as $key => $value) {
				 update_option('pestoai_' . $key, $value);
			 }
 
		 } else {
			 error_log('PestoAI  De-activation Error: main_url not returned');

			 //Translators: No dynamic content in this message
			 $pestoai_translated_cannot_deactivate_error_message = __('PestoAI De-activation Error: Your site could not be deactivated in PestoAI', 'pestoai');
			 wp_die(esc_html($pestoai_translated_cannot_deactivate_error_message));

		 }
 

	}

}
