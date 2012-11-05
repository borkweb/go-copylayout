<?php
/*
Plugin Name: GO Copy Layout
Plugin URI: http://
Description: Copy widgets and widget settings
Version: 2.0
Author: GigaOM
Author URI: http://gigaom.com
License: GPL2

Notes: Based on the version developed by Adam Backstrom of Plymouth State University (http://www.plymouth.edu)
*/

require_once __DIR__ . '/components/class-go-copylayout.php';

$go_copylayout = new GO_CopyLayout;

add_action('admin_menu', array( $go_copylayout, 'admin_menu' ) );
