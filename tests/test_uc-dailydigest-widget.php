<?php
class WP_Test_UC_DailyDigest_Widget extends WP_UnitTestCase {

    function test_tests() {
        $this->assertTrue( true );
    }

    function test_is_plugin_active() {
        $this->assertTrue( is_plugin_active( 'uc-dailydigest-widget/uconn-daily-digest-widget.php' ) );
    }

    function test_init_hook_was_added() {
        $this->assertGreaterThan( 0, has_filter( 'init', 'widget_textdomain');
    }

    function test_admin_styles_registered() {
        $this->assertTrue( wp_style_is( 'register_admin_styles', 'registered'));
    }

    function test_admin_styles_enqueued() {
        $this->assertTrue( wp_style_is( 'register_admin_styles', 'enqueued'));
    }

    function test_widget_styles_registered() {
        $this->assertTrue( wp_style_is( 'register_widget_styles', 'registered'));
    }

    function test_widget_styles_enqueued() {
        $this->assertTrue( wp_style_is( 'register_widget_styles', 'enqueued'));
    }

    function test_get_widget_slug() {
        global $wp_widget_factory;
        $this->assertEquals( 'uconn-daily-digest-widget', $wp_widget_factory->widgets['UConn_Daily_Digest_Widget'] );
    }
}
?>
