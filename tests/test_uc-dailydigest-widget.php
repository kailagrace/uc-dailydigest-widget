<?php
class WP_Test_UC_DailyDigest_Widget extends WP_UnitTestCase {
    protected $widget_slug = 'uconn-daily-digest-widget';
    protected $xml_transient_name = 'daily_digest_xml';
    protected $posts_xpath = '/rss/news';

    private $defaults;
    private $test_feed;
    private $simpleXML_test_feed;

    function setUp() {
        global $uc_dailydigest_widget;

        parent::setUp();

        include_once( plugin_dir_path( dirname( __FILE__ ) ) . '/uconn-daily-digest-widget.php' );

        $this->test_feed = plugin_dir_path( dirname( __FILE__ ) ) . 'tests/test_feed.xml';
        $this->test_xml = file_get_contents( $this->test_feed );
        $daily_digest_feed = simplexml_load_string($this->test_xml);
        $this->simpleXML_test_feed = $daily_digest_feed->xpath($this->posts_xpath);

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
        $this->assertInternalType( 'string', $daily_digest_xml );
        $this->assertGreaterThan( 0, strlen($daily_digest_xml) );
    }

    function test_feed_delete_transient() {
        global $uc_dailydigest_widget;

        $uc_dailydigest_widget->delete_feed_cache();
        $daily_digest_xml = get_transient( $this->xml_transient_name );
        $this->assertFalse( $daily_digest_xml );
    }

    function test_filter_simpleXML_posts() {
        global $uc_dailydigest_widget;

        $private_filter_simpleXML_posts = new ReflectionMethod('UConn_Daily_Digest_Widget', 'filter_simpleXML_posts');
        $private_filter_simpleXML_posts->setAccessible(true);

        $pre_filter_length = sizeof($this->simpleXML_test_feed);

        $private_filter_simpleXML_posts->invokeArgs(new UConn_Daily_Digest_Widget, array( &$this->simpleXML_test_feed, "UConn Today") );

        $post_filter_length = sizeof($this->simpleXML_test_feed);

        $this->assertGreaterThan($post_filter_length, $pre_filter_length);
    }

    function test_get_feed_posts_filter() {
        global $uc_dailydigest_widget;

        $original = $uc_dailydigest_widget->get_feed_posts($this->test_feed);
        $pre_filter_length = sizeof($original);

        $filtered1 = $uc_dailydigest_widget->get_feed_posts($this->test_feed, "UConn Today");
        $one_filter_length = sizeof($filtered1);

        $this->assertGreaterThan( $one_filter_length, $pre_filter_length );

        $filtered2 = $uc_dailydigest_widget->get_feed_posts($this->test_feed, "UConn Today; Research, Funding, and Awards");

        $two_filter_length = sizeof($filtered2);

        $this->assertGreaterThan( $two_filter_length, $one_filter_length );
    }

    function test_get_feed_posts_limit() {
        global $uc_dailydigest_widget;

        $limited1 = $uc_dailydigest_widget->get_feed_posts($this->test_feed, "", 0);
        $limit1_length = sizeof($limited1);

        $this->assertEquals( 0, $limit1_length );

        $limited2 = $uc_dailydigest_widget->get_feed_posts($this->test_feed, "", 1);
        $limit2_length = sizeof($limited2);

        $this->assertEquals( 1, $limit2_length );

        $limited3 = $uc_dailydigest_widget->get_feed_posts($this->test_feed, "", -1);
        $limit3_length = sizeof($limited3);

        $this->assertEquals( 1, $limit3_length );

    }

    function test_flush_widget_cache() {
        global $uc_dailydigest_widget;

        $uc_dailydigest_widget->flush_widget_cache();

        $this->assertFalse(wp_cache_get($this->widget_slug, 'widget'));
    }

    function test_widget_admin_form() {
        global $uc_dailydigest_widget;

        ob_start();
        $uc_dailydigest_widget->form( $uc_dailydigest_widget->get_widget_defaults() );
        $widget_form = ob_get_flush();

        $html = new DOMDocument();
        $html->loadHTML($widget_form);
        $this->assertInstanceOf( 'DOMElement', $html->getElementById( $uc_dailydigest_widget->get_field_id( 'feed_url' ) ) );
        $this->assertInstanceOf( 'DOMElement', $html->getElementById( $uc_dailydigest_widget->get_field_id( 'num_posts' ) ) );
        $this->assertInstanceOf( 'DOMElement', $html->getElementById( $uc_dailydigest_widget->get_field_id( 'exclude_categories' ) ) );
    }

    function test_widget() {
        global $uc_dailydigest_widget;

        ob_start();
        $uc_dailydigest_widget->widget( array(),  $uc_dailydigest_widget->get_widget_defaults() );
        $widget_form = ob_get_flush();

        $html = new DOMDocument();
        $html->loadHTML($widget_form);
        $this->assertInstanceOf( 'DOMNodeList', $html->getElementsByTagName( 'li' ) );
        
    }

    // TODO: test updating widget options

    function test_deactivate() {
        global $uc_dailydigest_widget;

        $posts = $uc_dailydigest_widget->get_feed_posts($this->test_feed);
        $daily_digest_xml = get_transient( $this->xml_transient_name );
        $this->assertInternalType( 'string', $daily_digest_xml );
        $this->assertGreaterThan( 0, strlen($daily_digest_xml) );

        $uc_dailydigest_widget->deactivate(null);
        $daily_digest_xml = get_transient( $this->xml_transient_name );
        $this->assertFalse( $daily_digest_xml );
    }

}
?>
