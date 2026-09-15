<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Aystat_Honeypot_Admin_Interceptor {

	public function __construct() {
		add_action( 'init', array( $this, 'intercept' ) );
	}

	public function intercept() {
		if ( is_admin() || wp_doing_ajax() || wp_is_json_request() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		$options = get_option( 'aystat_honeypot_admin_settings', array() );

		if ( empty( $options['is_active'] ) ) {
			return;
		}

		$path         = isset( $options['admin_path'] ) ? '/' . ltrim( $options['admin_path'], '/' ) : '/admin';
		$request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$current_path = wp_parse_url( $request_uri, PHP_URL_PATH );

		if ( untrailingslashit( $current_path ) !== untrailingslashit( $path ) ) {
			return;
		}

		$this->process_request( $options );
	}

	private function process_request( $options ) {
		global $wpdb;
		$ip             = $this->get_ip();
		$user_agent     = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : 'Unknown';
		$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';

		// FIXED: Explicitly escape the table name for the Plugin Check scanner
		$table_name     = esc_sql( Aystat_Honeypot_Admin_DB::get_table_name() );
		$time_threshold = gmdate( 'Y-m-d H:i:s', strtotime( '-24 hours', current_time( 'timestamp' ) ) );

		$attempts = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(id) FROM {$table_name} WHERE ip_address = %s AND time >= %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$ip,
			$time_threshold
		) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( ! headers_sent() ) {
			header( 'X-Frame-Options: SAMEORIGIN' );
			header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
			header( 'X-Content-Type-Options: nosniff' );

			if ( empty( $_COOKIE['wordpress_test_cookie'] ) ) {
				setcookie( 'wordpress_test_cookie', 'WP Cookie check', 0, '/' );
			}
		}

		$is_locked  = ( ! empty( $options['enable_limitation'] ) && $attempts >= 3 );
		$show_error = false;

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		// We intentionally disable nonce verification here because this is a honeypot endpoint capturing malicious bot traffic.
		if ( ! $is_locked && $request_method === 'POST' && isset( $_POST['username'] ) ) {
			usleep( wp_rand( 400000, 1200000 ) );

			$username = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
			$password = isset( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '';

			Aystat_Honeypot_Admin_DB::insert_log( $ip, $user_agent, $username, $password );

			$attempts++;

			if ( ! empty( $options['enable_limitation'] ) && $attempts >= 3 ) {
				$is_locked = true;
			} else {
				$show_error = true;
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( $is_locked ) {
			header( 'HTTP/1.1 403 Forbidden' );
		}

		require_once AYSTAT_HONEYPOT_ADMIN_PATH . 'includes/class-aystat-honeypot-admin-login-view.php';
		Aystat_Honeypot_Admin_Login_View::render( $options, $show_error, $is_locked );
		exit;
	}

	private function get_ip() {
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		}

		if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$forwarded_for = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
			$ips = explode( ',', $forwarded_for );
			return trim( $ips[0] );
		}

		if ( isset( $_SERVER['REMOTE_ADDR'] ) && ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return '127.0.0.1';
	}
}