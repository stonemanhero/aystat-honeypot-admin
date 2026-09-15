<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// FIXED: Prefixed the global-scoped variable and explicitly escaped it for Plugin Check
$aystat_honeypot_table_name = esc_sql( $wpdb->prefix . 'aystat_honeypot_logs' );

// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
$wpdb->query( "DROP TABLE IF EXISTS {$aystat_honeypot_table_name}" );

// Updated to delete the renamed options
delete_option( 'aystat_honeypot_admin_settings' );
delete_option( 'aystat_honeypot_admin_db_version' );

if ( function_exists( 'wp_cache_flush' ) ) {
	wp_cache_flush();
}