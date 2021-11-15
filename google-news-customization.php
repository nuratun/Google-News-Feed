<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.theyoursite
 * @since             0.1
 * @package           Google_News_Customization
 *
 * @wordpress-plugin
 * Plugin Name:       Google News Customization
 * Plugin URI:        https://www.yoursite.com
 * Description:       Generate a news feed for the Top 50. Shortcode: [get_news_feed]
 * Version:           1.0
 * Author:            Noora C
 * Author URI:        https://www.theyoursite.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       google-news-customization
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
define( 'GOOGLE_NEWS_CUSTOMIZATION', '1.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-google-news-customization-activator.php
 */
function activate_Google_News_Customization() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-google-news-customization-activator.php';
	Google_News_Customization_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-google-news-customization-deactivator.php
 */
function deactivate_Google_News_Customization() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-google-news-customization-deactivator.php';
	Google_News_Customization_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_Google_News_Customization' );
register_deactivation_hook( __FILE__, 'deactivate_Google_News_Customization' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-google-news-customization.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1
 */
function run_Google_News_Customization() {
	$plugin = new Google_News_Customization();
	$plugin->run();

}
run_Google_News_Customization();
