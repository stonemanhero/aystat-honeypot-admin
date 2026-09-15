<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Aystat_Honeypot_Admin_DB {

	public static function get_table_name() {
		global $wpdb;
		// Updated to match the new plugin prefix used in uninstall.php
		return $wpdb->prefix . 'aystat_honeypot_logs';
	}

	public static function create_table() {
		global $wpdb;
		$table_name = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            ip_address varchar(100) NOT NULL,
            user_agent varchar(255) NOT NULL,
            username varchar(255) NOT NULL,
            password varchar(255) NOT NULL,
            PRIMARY KEY  (id),
            KEY ip_address (ip_address),
            KEY time (time)
        ) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option( 'aystat_honeypot_admin_db_version', AYSTAT_HONEYPOT_ADMIN_VERSION );
	}

	public static function insert_log( $ip, $ua, $user, $pass ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->insert(
			self::get_table_name(),
			[
				'time'       => gmdate( 'Y-m-d H:i:s' ),
				'ip_address' => sanitize_text_field( $ip ),
				'user_agent' => sanitize_text_field( $ua ),
				'username'   => sanitize_text_field( $user ),
				'password'   => sanitize_text_field( $pass )
			]
		);
	}
}