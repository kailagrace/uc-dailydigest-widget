<?php
class WP_Test_UC_DailyDigest_Widget extends WP_UnitTestCase {
    protected $widget_slug = 'uconn-daily-digest-widget';

    function setUp() {
        global $uc_dailydigest_widget;

        parent::setUp();

        include_once( plugin_dir_path( dirname( __FILE__ ) ) . '/uconn-daily-digest-widget.php' );

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
}
?>
