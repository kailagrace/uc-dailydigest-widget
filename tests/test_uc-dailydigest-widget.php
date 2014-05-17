<?php
class WP_Test_UC_DailyDigest_Widget extends WP_UnitTestCase {
    protected $widget_slug = 'uconn-daily-digest-widget';
    protected $xml_transient_name = 'daily_digest_xml';

    private $defaults;
    private $test_feed;
    private $simpleXML_test_feed;

    function setUp() {
        global $uc_dailydigest_widget;

        parent::setUp();

        include_once( plugin_dir_path( dirname( __FILE__ ) ) . '/uconn-daily-digest-widget.php' );

        $this->test_feed = plugin_dir_path( dirname( __FILE__ ) ) . 'tests/test_feed.xml';
        $this->test_xml = file_get_contents( $this->test_feed );
        $this->simpleXML_test_feed = simplexml_load_string($this->test_xml);

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

        $this->assertEquals( $this->widget_slug, $uc_dailydigest_widget->get_widget_slug() );
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

    function test_feed_transient_undefined() {
        $daily_digest_xml = get_transient( $this->xml_transient_name );
        $this->assertFalse( $daily_digest_xml );
    }

    function test_get_feed_posts() {
        global $uc_dailydigest_widget;

        $posts = $uc_dailydigest_widget->get_feed_posts($this->test_feed);
        $this->assertInternalType( 'array', $posts );
        $this->assertGreaterThan( 0, sizeof($posts) );
    }

    function test_feed_transient_exists() {
        global $uc_dailydigest_widget;

        $posts = $uc_dailydigest_widget->get_feed_posts($this->test_feed);
        $daily_digest_xml = get_transient( $this->xml_transient_name );
        $this->assertTrue( $daily_digest_xml );
        $this->assertInternalType( 'array', $daily_digest_xml );
        $this->assertGreaterThan( 0, sizeof($daily_digest_xml) );
    }

    function test_feed_delete_transient() {
        global $uc_dailydigest_widget;

        $uc_dailydigest_widget->delete_feed_cache();
        $daily_digest_xml = get_transient( $this->xml_transient_name );
        $this->assertFalse( $daily_digest_xml );
    }

    function test_filter_simpleXML_posts() {
        global $uc_dailydigest_widget;

        $pre_filter_length = sizeof($this->simpleXML_test_feed);

        $uc_dailydigest_widget->filter_simpleXML_posts( $this->simpleXML_test_feed, "UConn Today" );

        $post_filter_length = sizeof($this->simpleXML_test_feed);

        $this->assertGreaterThan($post_filter_length, $pre_filter_length);
    }

    function test_get_feed_posts_filter() {
        global $uc_dailydigest_widget;

        $original = $uc_dailydigest_widget->get_feed_posts($this->test_feed);
        $pre_filter_length = sizeof($original);

        $filtered1 = $uc_dailydigest_widget->get_feed_posts($this->test_feed, "UConn Today");
        $one_filter_length = sizeof($filtered1);

        $this->assertGreaterThan( $post_filter_length, $one_filter_length );

        $filtered2 = $uc_dailydigest_widget->get_feed_posts($this->test_feed, "UConn Today; Research, Funding, and Awards");

        $two_filter_length = sizeof($filtered2);

        $this->assertGreaterThan( $one_filter_length, $two_filter_length );
    }

    function test_get_feed_posts_limit() {
        global $uc_dailydigest_widget;

        $limited1 = $uc_dailydigest_widget->get_feed_posts($this->test_feed, "", 0);
        $limit1_length = sizeof($limited1);

        $this->assertEqual( 0, $limit1_length );

        $limited2 = $uc_dailydigest_widget->get_feed_posts($this->test_feed, "", 1);
        $limit2_length = sizeof($limited2);

        $this->assertEqual( 0, $limit2_length );

        $limited3 = $uc_dailydigest_widget->get_feed_posts($this->test_feed, "", -1);
        $limit3_length = sizeof($limited3);

        $this->assertEqual( 1, $limit3_length );

    }

    function test_deactivate() {
        global $uc_dailydigest_widget;

        $posts = $uc_dailydigest_widget->get_feed_posts($this->test_feed);
        $daily_digest_xml = get_transient( $this->xml_transient_name );
        $this->assertTrue( $daily_digest_xml );

        $uc_dailydigest_widget->deactivate();
        $daily_digest_xml = get_transient( $this->xml_transient_name );
        $this->assertFalse( $daily_digest_xml );
    }

}
?>
