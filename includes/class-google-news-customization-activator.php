<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.theyoursite.com
 * @since      0.1
 *
 * @package    Google_News_Customization
 * @subpackage Google_News_Customization/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.1
 * @package    Google_News_Customization
 * @subpackage Google_News_Customization/includes
 * @author     Noora C <noora@noorachahine.com>
 */
class Google_News_Customization_Activator {

	/**
	 * Create table for holding news articles
	 *
	 * Upon activating the plugin, create a table with the name, {wp}_google_news,
	 * to hold all the articles in the database
	 *
	 * @since    0.1
	 */
	static function activate() {
		global $wpdb;
		global $google_news_db_version;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . "google_news";

		$google_news_db_version = '1.0';

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			news_keyword varchar(255) NULL,
			news_title varchar(255) NULL,
			news_author varchar(255) NULL,
			news_excerpt text NULL,
			news_url varchar(255) NULL,
			news_source varchar(255) NULL,
			news_published_date datetime NULL,
			approved BOOLEAN DEFAULT 0,
		  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			primary key (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		add_option( 'google_news_db_version', $google_news_db_version );

		$success = empty($wpdb->last_error);

		return $success;
	}
}
