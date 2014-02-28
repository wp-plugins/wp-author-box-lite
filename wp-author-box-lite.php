<?php

/*
Plugin Name: WP Author Box Lite
Plugin URI: http://wordpress.org/extend/plugins/wp-author-box-lite/
Description: Awesome Author box that you'll fall inlove with. Adds Author box after every post, pages and custom post types. If you decide to upgrade to <a href="http://codecanyon.net/item/wp-author-box/6815678?ref=phpbits"><strong>WP Author Box Pro</strong></a>, please deactivate WP Author Box Lite first.
Version: 1.0.2
Author: phpbits
Author URI: http://codecanyon.net/user/phpbits?ref=phpbits
License: GPL2
*/

//avoid direct calls to this file
if (!function_exists('add_action')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

/*##################################
	REQUIRE
################################## */

//Add Redux Framework
if ( !class_exists( 'ReduxFramework' ) && file_exists( dirname( __FILE__ ) . '/includes/ReduxFramework/ReduxCore/framework.php' ) ) {
	require_once( dirname( __FILE__ ) . '/includes/ReduxFramework/ReduxCore/framework.php' );
}

require_once( dirname( __FILE__ ) . '/includes/socials.php');
require_once( dirname( __FILE__ ) . '/admin/functions-config.php');
require_once( dirname( __FILE__ ) . '/core/functions.user-setting.php');
require_once( dirname( __FILE__ ) . '/core/functions.display.php');
?>