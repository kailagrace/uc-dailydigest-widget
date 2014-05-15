<?php
/**
 * UConn Daily Digest Widget
 *
 * @package   UConn_Daily_Digest_Widget
 * @author    Joseph Thibeault <joseph.thibeault@uconn.edu>
 * @license   MIT
 * @link      http://uconn.edu
 * @copyright 2014 University of Connecticut
 *
 * @wordpress-plugin
 * Plugin Name:       UConn Daily Digest Widget
 * Plugin URI:        @TODO
 * Description:       Displays the UConn Daily Digest
 * Version:           1.0.0
 * Author:            Joseph Thibeault
 * Author URI:        http://communications.uconn.edu
 * Text Domain:       uconn-daily-digest-widget
 * License:           MIT+
 * License URI:       http://opensource.org/licenses/MIT
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/uconn/uc-dailydigest-widget/
 */

class UConn_Daily_Digest_Widget extends WP_Widget {

    /**
     * Protected constants
     *
     * @since    1.0.0
     *
     * @var      string
     */

    protected $widget_slug = 'uconn-daily-digest-widget';

    protected $xml_transient_name = 'daily_digest_xml';
    protected $posts_xpath = '/rss/news';
    protected $expire_hours = 1;

    /*--------------------------------------------------*/
    /* Constructor
    /*--------------------------------------------------*/

    /**
     * Specifies the classname and description, instantiates the widget,
     * loads localization files, and includes necessary stylesheets and JavaScript.
     */
    public function __construct() {

        // load plugin text domain
        add_action( 'init', array( $this, 'widget_textdomain' ) );

        // Hooks fired when the Widget is activated and deactivated
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        parent::__construct(
            $this->get_widget_slug(),
            __( 'UConn Daily Digest Widget', $this->get_widget_slug() ),
            array(
                'classname'  => $this->get_widget_slug().'-class',
                'description' => __( 'Displays the UConn Daily Digest feeds in a widget.', $this->get_widget_slug() )
            )
        );

        // Register admin styles and scripts
        add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

        // Register site styles and scripts
        add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_scripts' ) );

        // Refreshing the widget's cached output with each new post
        add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
        add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
        add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );

    } // end constructor


    /**
     * Return the widget slug.
     *
     * @since    1.0.0
     *
     * @return    Plugin slug variable.
     */
    public function get_widget_slug() {
        return $this->widget_slug;
    }

    /**
     * Return the widget defaults.
     *
     * @since    1.0.0
     *
     * @return    array of widget default settings.
     */
    public function get_widget_defaults() {
        $feed_urls = $this->get_feed_urls();
        $default_title = array_keys($feed_urls);
        $default_url = array_values($feed_urls);

        return array(
            'feed_title' => $default_title[0],
            'feed_url' => $default_url[0],
            'num_posts' => 20,
            'exclude_categories' => ''
        );
    }

    /**
     * Return the daily digest feed urls.
     *
     * @since    1.0.0
     *
     * @return    array of daily digest feed urls.
     */
    public function get_feed_urls() {
        $feed_urls = array(
            "Student Daily Digest" => "http://dailydigest.uconn.edu/stoday.xml",
            "Faculty/Staff Daily Digest" => "http://dailydigest.uconn.edu/ftoday.xml"
        );

        return $feed_urls;
    }

    /**
     * Returns the daily digest news posts.
     *
     * @since    1.0.0
     *
     * @return    SimpleXMLElement of daily digest news posts.
     */
    public function get_feed_posts($feed_url, $exclude = null, $num_posts = 20) {

        $daily_digest_xml = get_transient( $this->xml_transient_name );

        if ( false === $daily_digest_xml ) {
            $context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
            $daily_digest_xml = file_get_contents( $feed_url, false, $context);
            set_transient( $this->xml_transient_name, $daily_digest_xml, $this->expire_hours * HOUR_IN_SECONDS );
        }

        $daily_digest = simplexml_load_string( $daily_digest_xml );
        $daily_digest_news_posts = $daily_digest->xpath($this->posts_xpath);

        if($exclude !== null) {
            $this->filter_simpleXML_posts( $daily_digest_news_posts, $exclude );
        }

        return array_slice($daily_digest_news_posts, 0, $num_posts);

    }

    /**
     * Modifies the array of posts passed in by reference based on the exclusion argument
     *
     * @since    1.0.0
     *
     * @return    void
     */
    private function filter_simpleXML_posts(&$posts, $exclude) {

        // clean up user input
        $filterArray = explode(';', strtolower($exclude));
        $filterArray = array_map('trim', $filterArray);

        // filter the array
        $posts = array_filter($posts, function($post) use($filterArray) {
            $post_category = strtolower($post->category);
            return !in_array($post_category, $filterArray);
        });

    }

    /*--------------------------------------------------*/
    /* Widget API Functions
    /*--------------------------------------------------*/

    /**
     * Outputs the content of the widget.
     *
     * @param array args  The array of form elements
     * @param array instance The current instance of the widget
     */
    public function widget( $args, $instance ) {

        // Check if there is a cached output
        $cache = wp_cache_get( $this->get_widget_slug(), 'widget' );

        if ( !is_array( $cache ) )
            $cache = array();

        if ( ! isset ( $args['widget_id'] ) )
            $args['widget_id'] = $this->id;

        if ( isset ( $cache[ $args['widget_id'] ] ) )
            return print $cache[ $args['widget_id'] ];

        extract( $args, EXTR_SKIP );

        $widget_string = $before_widget;

        $feed_title = $instance['feed_title'];
        $feed_url = $instance['feed_url'];
        $num_posts = $instance['num_posts'];
        $exclude_categories = $instance['exclude_categories'];

        $posts = $this->get_feed_posts($feed_url, $exclude_categories, $num_posts);

        ob_start();
        include( plugin_dir_path( __FILE__ ) . 'views/widget.php' );
        $widget_string .= ob_get_clean();
        $widget_string .= $after_widget;

        $cache[ $args['widget_id'] ] = $widget_string;

        wp_cache_set( $this->get_widget_slug(), $cache, 'widget' );

        print $widget_string;

    } // end widget


    public function delete_feed_cache() {
        delete_transient( $this->xml_transient_name );
    }

    public function flush_widget_cache() {
        $this->delete_feed_cache();
        wp_cache_delete( $this->get_widget_slug(), 'widget' );
    }

    /**
     * Processes the widget's options to be saved.
     *
     * @param array new_instance The new instance of values to be generated via the update.
     * @param array old_instance The previous instance of values before the update.
     */
    public function update( $new_instance, $old_instance ) {

        $instance = $old_instance;

        $feed_urls = $this->get_feed_urls();

        $instance['feed_title'] = array_search( $new_instance['feed_url'], $feed_urls );
        $instance['feed_url'] = strip_tags( $new_instance['feed_url'] );
        $instance['num_posts'] = absint( $new_instance['num_posts'] );
        $instance['exclude_categories'] = strip_tags(trim($new_instance['exclude_categories']));

        $this->flush_widget_cache();

        return $instance;

    } // end widget

    /**
     * Generates the administration form for the widget.
     *
     * @param array instance The array of keys and values for the widget.
     */
    public function form( $instance ) {

        $defaults = $this->get_widget_defaults();
        $feed_urls = $this->get_feed_urls();

        $instance = wp_parse_args(
            (array) $instance, $defaults
        );

        $feed_title = $instance['feed_title'];
        $feed_url = $instance['feed_url'];
        $num_posts = $instance['num_posts'];
        $exclude_categories = $instance['exclude_categories'];

        // Display the admin form
        include( plugin_dir_path(__FILE__) . 'views/admin.php' );

    } // end form

    /*--------------------------------------------------*/
    /* Public Functions
    /*--------------------------------------------------*/

    /**
     * Loads the Widget's text domain for localization and translation.
     */
    public function widget_textdomain() {

        load_plugin_textdomain( $this->get_widget_slug(), false, plugin_dir_path( __FILE__ ) . 'lang/' );

    } // end widget_textdomain

    /**
     * Fired when the plugin is activated.
     *
     * @param  boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
     */
    public function activate( $network_wide ) {
        // do nothing
    } // end activate

    /**
     * Fired when the plugin is deactivated.
     *
     * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
     */
    public function deactivate( $network_wide ) {
        $this->delete_feed_cache();
    } // end deactivate

    /**
     * Registers and enqueues admin-specific styles.
     */
    public function register_admin_styles() {

        wp_enqueue_style( $this->get_widget_slug().'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ) );

    } // end register_admin_styles

    /**
     * Registers and enqueues admin-specific JavaScript.
     */
    public function register_admin_scripts() {

        //wp_enqueue_script( $this->get_widget_slug().'-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array('jquery') );

    } // end register_admin_scripts

    /**
     * Registers and enqueues widget-specific styles.
     */
    public function register_widget_styles() {

        wp_enqueue_style( $this->get_widget_slug().'-widget-styles', plugins_url( 'css/widget.css', __FILE__ ) );

    } // end register_widget_styles

    /**
     * Registers and enqueues widget-specific scripts.
     */
    public function register_widget_scripts() {

        //wp_enqueue_script( $this->get_widget_slug().'-script', plugins_url( 'js/widget.js', __FILE__ ), array('jquery') );

    } // end register_widget_scripts

} // end class

add_action( 'widgets_init', create_function( '', 'register_widget("UConn_Daily_Digest_Widget");' ) );
