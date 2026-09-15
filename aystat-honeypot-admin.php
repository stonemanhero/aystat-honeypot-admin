<?php
/**
 * Plugin Name: Aystat Honeypot Admin
 * Description: A secure honeypot to trap bots and malicious actors by faking an admin login page.
 * Version:     1.0.0
 * Author:      stonemanhero
 * Text Domain: aystat-honeypot-admin
 * License:     GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AYSTAT_HONEYPOT_ADMIN_VERSION', '1.0.0' );
define( 'AYSTAT_HONEYPOT_ADMIN_PATH', plugin_dir_path( __FILE__ ) );

require_once AYSTAT_HONEYPOT_ADMIN_PATH . 'includes/class-aystat-honeypot-admin-db.php';
require_once AYSTAT_HONEYPOT_ADMIN_PATH . 'includes/class-aystat-honeypot-admin-settings.php';
require_once AYSTAT_HONEYPOT_ADMIN_PATH . 'includes/class-aystat-honeypot-admin-interceptor.php';

register_activation_hook( __FILE__, array( 'Aystat_Honeypot_Admin_DB', 'create_table' ) );
add_action( 'plugins_loaded', 'aystat_honeypot_admin_init' );

function aystat_honeypot_admin_init() {
	new Aystat_Honeypot_Admin_Settings();
	new Aystat_Honeypot_Admin_Interceptor();
}