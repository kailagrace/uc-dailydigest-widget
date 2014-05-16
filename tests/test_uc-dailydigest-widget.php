<?php
class WP_Test_UC_DailyDigest_Widget extends WP_UnitTestCase {
    protected $widget_slug = 'uconn-daily-digest-widget';
    private $defaults;
    private $posts;
    private $testfeed;

    function setUp() {
        global $uc_dailydigest_widget;

        parent::setUp();

        include_once( plugin_dir_path( dirname( __FILE__ ) ) . '/uconn-daily-digest-widget.php' );

        $this->testfeed = plugin_dir_path( dirname( __FILE__ ) ) . 'tests/testfeed.xml';

        // Allow for a plugin to insert a different class to handle requests.
        $uc_dailydigest_widget_class = apply_filters('uc_dailydigest_widget_class', 'UConn_Daily_Digest_Widget');
        $uc_dailydigest_widget = new $uc_dailydigest_widget_class;
        }

    function test_tests() {
        $this->assertTrue( true );
    }

    function test_is_plugin_active() {
        $this->assertTrue( is_plugin_active( 'uc-dailydigest-widget/uconn-daily-digest-widget.php' ) );
    }

    function test_init_hook_was_added() {
        global $uc_dailydigest_widget;
        $this->assertGreaterThan( 0, has_filter(
            'init',
            array( $uc_dailydigest_widget, 'widget_textdomain')
        ) );
    }

    function test_get_widget_slug() {
        global $uc_dailydigest_widget;
        $this->assertEquals( 'uconn-daily-digest-widget', $uc_dailydigest_widget->get_widget_slug() );
    }

    function test_widget_styles_enqueued() {
        global $uc_dailydigest_widget;
        $this->assertGreaterThan( 0, has_action(
            'wp_enqueue_scripts',
            array($uc_dailydigest_widget, 'register_widget_styles')
        ) );
    }

    function test_admin_styles_enqueued() {
        global $uc_dailydigest_widget;
        $this->assertGreaterThan( 0, has_action(
            'admin_print_styles',
            array($uc_dailydigest_widget, 'register_admin_styles')
        ) );
    }

    function test_get_feed_urls() {
        global $uc_dailydigest_widget;
        $feed_urls = $uc_dailydigest_widget->get_feed_urls();
        $this->assertInternalType( 'array', $feed_urls );
        $this->assertArrayHasKey( 'Student Daily Digest', $feed_urls );
        $this->assertArrayHasKey( 'Faculty/Staff Daily Digest', $feed_urls );
    }

    function test_get_widget_defaults() {
        global $uc_dailydigest_widget;
        $this->defaults = $uc_dailydigest_widget->get_widget_defaults();
        $this->assertInternalType( 'array', $this->defaults );
        $this->assertGreaterThan( 0, sizeof($this->defaults) );
        $this->assertArrayHasKey( 'feed_title', $this->defaults );
        $this->assertArrayHasKey( 'feed_url', $this->defaults );
        $this->assertArrayHasKey( 'num_posts', $this->defaults );
        $this->assertArrayHasKey( 'exclude_categories', $this->defaults );
    }

    function test_get_feed_posts() {
        global $uc_dailydigest_widget;
        $this->posts = $uc_dailydigest_widget->get_feed_posts($this->testfeed);
        $this->assertInternalType( 'array', $this->posts );
        $this->assertGreaterThan( 0, sizeof($this->posts) );
    }
}
?>
