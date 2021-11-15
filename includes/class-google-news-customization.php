<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.theyoursite.com
 * @since      0.1
 *
 * @package    Google_News_Customization
 * @subpackage Google_News_Customization/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.1
 * @package    Google_News_Customization
 * @subpackage Google_News_Customization/includes
 * @author     Noora C <noora@noorachahine.com>
 */
class Google_News_Customization {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.1
	 * @access   protected
	 * @var      Google_News_Customization_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.1
	 */
	public function __construct() {
		if ( defined( 'GOOGLE_NEWS_CUSTOMIZATION' ) ) {
			$this->version = GOOGLE_NEWS_CUSTOMIZATION;
		} else {
			$this->version = '1.0';
		}
		$this->plugin_name = 'google-news-customization';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->news_shortcode();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Google_News_Customization_Loader. Orchestrates the hooks of the plugin.
	 * - Google_News_Customization_i18n. Defines internationalization functionality.
	 * - Google_News_Customization_Admin. Defines all hooks for the admin area.
	 * - Google_News_Customization_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.1
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-google-news-customization-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-google-news-customization-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-google-news-customization-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-google-news-customization-public.php';

		$this->loader = new Google_News_Customization_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Google_News_Customization_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.1
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Google_News_Customization_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.1
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Google_News_Customization_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );

		$this->loader->add_action( 'wp_ajax_publish_news_row', $plugin_admin, 'publish_news_row' );
		$this->loader->add_action( 'wp_ajax_delete_news_row', $plugin_admin, 'delete_news_row' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Create the admin menu and settings fields
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_add_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'admin_init' );

		// If the user clicks the submit button to get a new list of articles
		if ( $_POST['get_news_from_google'] ) {
			$this->loader->add_action( 'admin_init', $plugin_admin, 'grab_news_articles' );
		}

		// Check if enough time has passed to call the Google News API again
		$this->loader->add_action( 'init', $plugin_admin, 'google_news_cronjob' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.1
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Google_News_Customization_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	/**
	 * Register the shortcode
	 *
	 * @since    0.1
	 */
	public function news_shortcode() {
		add_shortcode( 'get_news_feed', array( $this , 'google_news_api' ) );
	}

	/**
	 * Set up the Google News API to grab data related to the
	 * returned array
	 *
	 * @since    0.1
	 * @return   String    A formatted string of articles, with HTML/CSS included
	 */
	public function google_news_api() {
		// Array to hold all the articles
		$articles = array();
		$format_articles = '';

		$articles = $this->grab_news_articles();

		if ( !empty( $articles ) ) {
			// Send it to the function to format the articles with HTML/CSS
			$format_articles = $this->format_news_articles( $articles );
		}

		return $format_articles;
	}

	/**
	 * Get a list of articles from Google News API
	 *
	 * @since    0.1
	 * @return   Array    An array of articles.
	 */
	 public function grab_news_articles() {
		 global $wpdb;

		 // Get the table where the news articles are saved
		 $table_name = $wpdb->prefix . "google_news";

		 // Get the list of approved articles from the db
		 $grab_news_articles_query = "
			 SELECT * FROM $table_name WHERE `approved` = 1 ORDER BY news_published_date DESC
		";

		// Return this as a regular array, instead of an object
		$articles =  $wpdb->get_results( $grab_news_articles_query, ARRAY_A );

		return $articles;
	}

	public function format_news_articles( $articles ) {
		$format = '';

		if ( !empty( $articles ) ) {
			foreach ( $articles as $article ) {
				$format .= '<span class="newsfeed-date">Date: '
				. $article['news_published_date'] . '</span><br />';
				$format .= '<span class="newsfeed-tag">Keyword: '
				. $article['news_keyword'] . '</span><br />';
				$format .= '<span class="newsfeed-title">'
				. $article['news_title'] . '</span><p>'
				. $article['news_published_date'] . '</span><br />'
				. $article['news_author'] . '<br />'
				. $article['news_excerpt'] . '<br /><a href="'
				. $article['news_url'] . '" target="_blank">View Article</a></p><hr />';
			}
		}

		return $format;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.1
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Google_News_Customization_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
