<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.pestoai.com
 * @since             1.0.0
 * @package           PestoAI
 *
 * @wordpress-plugin
 * Plugin Name:       PestoAI
 * Plugin URI:        https://www.pestoai.com
 * Description:       PestoAI generates and publishes daily SEO content for your site using AI
 * Version:           1.0.1
 * Author:            PestoAI Inc.
 * Author URI:        https://www.pestoai.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pestoai
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */



/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pestoai-activator.php
 */
function pestoai_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pestoai-activator.php';
	PestoAI_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pestoai-deactivator.php
 */
function pestoai_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pestoai-deactivator.php';
	PestoAI_Deactivator::deactivate();
}


// Add action to display admin notice
add_action('admin_notices', 'pestoai_display_activation_notice');

function pestoai_display_activation_notice() {
    // Check if the transient is set
    $notice_data = get_transient('pestoai_activation_notice');
    if ($notice_data) {


        
        $pestoai_translated_email_message = sprintf(
            // Translators: %s is the email address where the info was sent
            __('IMPORTANT! We sent an email to %s with info on setting your PestoAI account password.', 'pestoai'),
            esc_html($notice_data['email'])
        );

        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($pestoai_translated_email_message); ?></p>
        </div>
        <?php
        // Delete the transient after displaying the notice
        delete_transient('pestoai_activation_notice');
    }
}

add_action('admin_notices', 'pestoai_display_activation_fail_notice');

function pestoai_display_activation_fail_notice() {
    // Check if the transient is set
    if (get_transient('pestoai_activation_fail_notice')) {


        //Translators: No dynamic content in this message
        $pestoai_translated_activate_error_message = __('ERROR! The activation was not successful. Please visit https://www.pestoai.com and signup there first, then install the plugin', 'pestoai');


        ?>
        <div class="notice notice-fail is-dismissible">
            <p>
                <?php 
                echo esc_html($pestoai_translated_activate_error_message);
                ?>
            </p>
        </div>
        <?php
        // Delete the transient after displaying the notice
        delete_transient('pestoai_activation_fail_notice');
    }
}



/**
 * This adds a link to the plugin settings page in the plugin list that will open the console
 */

function pestoai_action_links($links) {
    // Get the URL from the database or use the default
    $console_url = get_option('pestoai_console_url', 'https://console.pestoai.com');
    
    // Create the custom link
    $settings_link = '<a href="' . esc_url($console_url) . '" target="_blank">Manage site settings in PestoAI</a>';
    
    // Add the custom link to the beginning of the existing links
    array_unshift($links, $settings_link);
    
    return $links;
}

// Hook the function into the plugin_action_links filter
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'pestoai_action_links');





register_activation_hook( __FILE__, 'pestoai_activate' );
register_deactivation_hook( __FILE__, 'pestoai_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-pestoai.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function pestoai_run() {

	$plugin = new PestoAI();
	$plugin->run();

}
pestoai_run();
