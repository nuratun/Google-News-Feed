<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.theyoursite.com
 * @since      0.1
 *
 * @package    Google_News_Customization
 * @subpackage Google_News_Customization/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Google_News_Customization
 * @subpackage Google_News_Customization/admin
 * @author     Noora C <noora@noorachahine.com>
 */
class Google_News_Customization_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.1
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Google_News_Customization_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Google_News_Customization_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/google-news-customization-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.1
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Google_News_Customization_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Google_News_Customization_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/google-news-customization-admin.js', array( 'jquery' ), $this->version, false );
		// Need to localize the script, so jQuery knows where the ajax url is
		wp_localize_script( $this->plugin_name, 'ajaxcall', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

	}

	/**
	 * Get the API key from News API
	 *
	 * @since    0.1
	 */
	public static function get_news_api_key() {
		// The key should have been saved in the wp_options database by the user
		// Otherwise, return a default empty array
		return get_option( 'news_feed_key_settings', array() );
	}

	/**
	 * Set up an array with all the top 50 names of the current M500
	 *
	 * @since    0.1
	 */
	public static function get_all_keywords() {
		// The names should also have been saved in the wp_options database
		// Otherwise, return an empty array
		return get_option( 'news_feed_names_settings', array() );
	}

	/**
	 * Setup a cronjob to grab the latest articles from Google and save to the db
	 *
	 * @since    0.1
	 */
	 public function google_news_cronjob() {
		 // Get the current time and the updated time, if it's time
		 // for the cronjob to run
		 $current_time = time();
		 $new_time = strtotime( '+1 day', $current_time );

		 $saved_time = get_option( 'news_feed_cronjob', array() );

		 if ( !empty( $saved_time ) ) {
			 if ( $current_time - $saved_time > 8400 ) {
				 // Update the saved time in the db
				 update_option( 'news_feed_cronjob', $new_time );

				 // Grab the latest news articles
				 $this->grab_news_articles();
			 }
		 } else {
			 // Create the cronjob time in the db upon initialization
			 add_option( 'news_feed_cronjob', $new_time, '', true );
		 }

		 // Delete articles that are over 7 days old (unpublished)
		 $this->delete_old_articles();
	 }

 	/**
 	 * Delete articles in the db that are over 7 days old (unpublished)
 	 *
 	 * @since    0.1
	 * @return	NULL
 	 */
 	 public function delete_old_articles() {
		 global $wpdb;
		 $table_name = $wpdb->prefix . "google_news";

		 // Now grab all the articles to display in the admin section
		 $wpdb->get_results( "DELETE FROM $table_name WHERE `created_at` < ( NOW() - INTERVAL 7 DAY ) AND `approved` = 0" );
	 }

	/**
	 * Get a list of articles from Google News API and save to the db
	 *
	 * @since    0.1
	 * @return   NULL
	 */
	 public function grab_news_articles() {
		 // To hold the final list of articles
		 $articles = array();
		 $list = array();

		 // Get the Google News API key
		 $key = $this->get_news_api_key();

		 // Get all the keywords as
		 $names_string = $this->get_all_keywords();

		 // Convert the $names string into an array
		 $names = explode( ',', $names_string );

		 // We need to get articles for the past 15 days (360 hours)
		 $from = date( 'Y-m-d', time() - 60 * 60 * 360 );

		 // Initiate CURL...
		 $news_curl = curl_init();

		 // Grab articles for each profile name entered by the user
		 foreach ( $names as $name ) {
			 // Encode the name to send as a parameter
			 $data = array( 'q' => $name );
			 $parameter = http_build_query( $data );

			 // Call the News API to get a list of 5 articles per name
			 curl_setopt( $news_curl, CURLOPT_URL, "https://newsapi.org/v2/everything?" . $parameter . "&language=en&sortBy=relevancy&from=" . $from . "&pageSize=3" );

			 // Send the API key for authorization
			 curl_setopt( $news_curl, CURLOPT_HTTPHEADER, array( 'Authorization: ' . $key ) );
			 curl_setopt( $news_curl, CURLOPT_RETURNTRANSFER, true );

			 // Execute request and read responce
			 $session_response = curl_exec( $news_curl );
			 $response = json_decode( json_encode( $session_response ), true );

			 // Grab the article as an array
			 $articles[$name] = $response;
		 }

		 // Parse through the return data and only save responses
		 // that actually return content
		 foreach ( $articles as $key => $art ) {
			 $article = json_decode( $art );

			 // So long as there are articles returned
			 if ( !empty( $article->totalResults ) && $article->totalResults != 0 ) {
				 $list[$key] = $article->articles;
			 }
		 }

		$this->save_articles_db( $list );
	}

	/**
	 * Send the list of articles to the database for approval or deletion.
	 *
	 * @since    0.1
	 * @return   Boolean
	 */
	 public function save_articles_db( $articles ) {
		 global $wpdb;
		 $table_name = $wpdb->prefix . "google_news";

		 // Now grab all the articles to display in the admin section
		 $grab_from_db_query = "SELECT `news_url` FROM $table_name";

		 // Return this as a regular array, instead of an object
		 $saved_urls = $wpdb->get_results( $grab_from_db_query, ARRAY_A );

		 // Check each article grabbed from Google News API
		 foreach ( $articles as $tag => $article ) {
			 foreach ( $article as $details ) {
				 // Keep a running tally of what's already in the db
				 $match = false;

				 // Check that this isn't a repeat of an article in the db
				 foreach ( $saved_urls as $url ) {
					 if ( $details->url == $url['news_url'] ) {
						 $match = true;
					 }
				 }

				 // Save to db, so long as this isn't a repeat
				 if ( $match == false ) {
					 $wpdb->insert(
						 $table_name, array(
							 'news_keyword' => $tag,
							 'news_title' => $details->title,
							 'news_author' => $details->author,
							 'news_excerpt' => $details->description,
							 'news_url' => $details->url,
							 'news_source' => $details->source->name,
							 'news_published_date' => $details->publishedAt
						 )
					 );
				 }
			 }
		 }

		 return true;
	 }

	 public function publish_news_row() {
		 if ( isset( $_REQUEST ) ) {
			 global $wpdb;
			 $table_name = $wpdb->prefix . "google_news";

			 // Grab the row id from ajax
			 $id = $_REQUEST['google_news_id'];

			 if ( !is_array( $id ) ) {
				 error_log("publishing: Not an array");

	 			 $wpdb->query(
	 				 'UPDATE ' . $table_name . ' SET approved = 1 WHERE id = "' . $id . '"'
	 			 );
			 } else {
				 foreach ( $id as $row ) {
					 error_log("publishing: This is an array");

					 $wpdb->query(
		 				 'UPDATE ' . $table_name . ' SET approved = 1 WHERE id = "' . $row . '"'
		 			 );
				 }
			 }

			 // Required when echoing ajax content
			 die();
		 }
	 }

	 public function delete_news_row() {
		 if ( isset( $_REQUEST ) ) {
			 global $wpdb;
			 $table_name = $wpdb->prefix . "google_news";

			 // Grab the row id from ajax
			 $id = $_REQUEST['google_news_id'];

			 if ( !is_array( $id ) ) {
				 error_log("Deletion: Not an array");

				 $wpdb->query(
					 'DELETE FROM ' . $table_name . ' WHERE id = "' . $id . '"'
				 );
			 } else {
				 foreach ( $id as $row ) {
					 error_log("Deletion: This is an array");

					 $wpdb->query(
						 'DELETE FROM ' . $table_name . ' WHERE id = "' . $row . '"'
					 );
				 }
			 }

			 // Required when echoing ajax content
			 die();
		 }
	 }

	 public function admin_add_menu() {
		 add_options_page( 'News Feed Settings', 'View News Feed Settings', 'manage_options', 'news_feed_settings', array( $this, 'admin_display' ) );
	 }

	 public function admin_init() {
		 add_settings_section(
			'news_feed_key_section',
			'News API Key',
			array( $this, 'admin_display' ),
			'news_feed_settings'
		);

    register_setting(
			'news_feed_all_settings',
			'news_feed_key_settings'
		);

		register_setting(
			'news_feed_all_settings',
			'news_feed_names_settings'
		);

    add_settings_field(
			'news_feed_key_settings',
			'Enter Key',
			array( $this, 'admin_display' ),
			'news_feed_settings',
			'news_feed_key_section'
		);

		add_settings_field(
			'news_feed_names_settings',
			'Enter Names (separate with comma)',
			array( $this, 'admin_display' ),
			'news_feed_settings',
			'news_feed_key_section'
		);
	}

	function admin_display() {
    include plugin_dir_path(__FILE__) . 'partials/google-news-customization-admin-display.php';
	}
}
