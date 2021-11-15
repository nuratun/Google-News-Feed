<?php

/**
  * Provide a admin area view for the plugin
  *
  * This file is used to markup the admin-facing aspects of the plugin.
  *
  * @link https://www.theyoursite.com
  * @since      0.1
  *
  * @package  Google_News_Customization
  * @subpackage  Google_News_Customization/admin/partials
  */

  function return_array_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . "google_news";

    // Now grab all the articles to display in the admin section
    $grab_news_articles_query = "SELECT * FROM $table_name ORDER BY news_published_date DESC";

    // Return this as a regular array, instead of an object
    $articles = $wpdb->get_results( $grab_news_articles_query, ARRAY_A );

    $format = '<div class="google-news-articles-container">';
    $format .= '<button type="button" class="deleteAllNewsButton button button-primary">Delete</button>';
    $format .= '<button type="button" class="publishAllNewsButton button button-primary">Publish</button>';
    $format .= '<table class="google-news-table">';
    $format .= '<tr>';
    $format .= '<th class="google-news-row"><input type="checkbox" class="gn-row-checkbox-all" /></th>';
    $format .= '<th class="google-news-row">Title</th>';
    $format .= '<th class="google-news-row">Keyword</th>';
    $format .= '<th class="google-news-row">Description</th>';
    $format .= '<th class="google-news-row">Source</th>';
    $format .= '<th class="google-news-row">Date</th>';
    $format .= '<th class="google-news-row">Approved</th>';
    $format .= '<th class="google-news-row">Publish</th>';
    $format .= '<th class="google-news-row">Delete</th>';
    $format .= '</tr>';

    foreach ( $articles as $key => $article ) {
      $format .= '<tr class="google-news-row-checkbox">';
      $format .= '<td class="google-news-row"><input id="' . $article['id'] . '" class="google-news-row-checkbox" type="checkbox" /></td>';
      $format .= '<td class="google-news-row">' . $article['news_title'] . '</td>';
      $format .= '<td class="google-news-row">' . $article['news_keyword'] . '</td>';
      $format .= '<td class="google-news-row">' . $article['news_excerpt'] . '</td>';
      $format .= '<td class="google-news-row">' . $article['news_source'] . '</td>';
      $format .= '<td class="google-news-row">' . $article['news_published_date'] . '</td>';

      if ( $article['approved'] == 1 ) {
        $format .= '<td class="google-news-row">Yes</td>';
        $format .= '<td class="google-news-row">Published</td>';
      } else {
        $format .= '<td class="google-news-row">No</td>';
        $format .= '<td class="google-news-row">
        <button type="button" id="' . $article['id'] . '" class="publishNewsButton">
        Publish
        </button>
        </td>';
      }

      $format .= '<td class="google-news-row">
      <button type="button" id="' . $article['id'] . '" class="deleteNewsButton">
      Delete
      </button>
      </td>';
      $format .= '</tr>';
    }

    $format .= '</table>';
    $format .= '</div>';

    echo $format;
  }
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
      <?php echo esc_html( get_admin_page_title() ); ?>
    </h1>

    <hr class="wp-header-end">

    <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin:5px 0 10px 0;">
        <p>This plugin uses the <a href="https://newsapi.org/docs">News API</a>, as recommended by Google News.</p>
        <p>To update the list of news articles in the feed, enter a list of comma separated names in the text area under list of names.</p>
        <p>This plugin is in beta and does not have error checking! So double-check your entries before hitting submit, if you don't want the site to explode, TYVM.</p>
    </div>

    <table>
      <form action="options.php" method="POST">
        <?php settings_fields('news_feed_all_settings'); ?>
        <?php do_settings_sections('news_feed_key_section'); ?>

        <tr class="settings-table-row">
          <td>
            <label for="key_setting">News API Key</label>
            <input type="text" name="news_feed_key_settings" id="key_setting" class="regular-text" value="<?php echo esc_attr( get_option('news_feed_key_settings') ); ?>">
          <td>
        </tr>

        <tr class="settings-table-row">
          <td>
            <label for="names_listing">List of names (separated by commas)</label>
            <textarea name="news_feed_names_settings" rows="10" id="names_listing" class="large-text settings-text-input"><?php echo esc_attr( get_option('news_feed_names_settings', '') );
            ?></textarea>
          </td>
        </tr>

        <tr class="settings-table-row">
          <td>
            <?php submit_button(); ?>
          </td>
        </tr>
      </form>
    </table>

    <hr />

    <h2 class="wp-heading-inline">
      Get the latest news from Google (optional)
    </h2>

    <div>
      <form action="" method="POST">
        <input type="hidden" name="get_news_from_google" id="get_news" value="true">
        <?php submit_button( "Manually update the news list below", "button-primary" ); ?>
      </form>
    </div>

    <hr />

    <h2 class="wp-heading-inline">
      Approve/disapprove articles
    </h2>

    <?php return_array_table(); ?>

</div><!-- .wrap -->
