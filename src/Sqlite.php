<?php

namespace WorDBless;

use WP_User;

/**
 * SQLite integration class
 */
class Sqlite {

	/**
	 * Initialize SQLite database and install tables if needed
	 *
	 * @return void
	 */
	public static function init() {
		global $wpdb;

		// Check if tables exist
		$db_dir = ABSPATH . 'wp-content/database';
		if ( ! file_exists( $db_dir ) ) {
			mkdir( $db_dir, 0755, true );
		}
		if ( ! is_writable( $db_dir ) ) {
			chmod( $db_dir, 0755 );
		}

		// Define required SQLite constants
		if ( ! defined( 'FQDBDIR' ) ) {
			define( 'FQDBDIR', $db_dir . '/' );
		}
		if ( ! defined( 'FQDB' ) ) {
			define( 'FQDB', FQDBDIR . '.ht.sqlite' );
		}

		// Load SQLite integration plugin
		require_once ABSPATH . 'wp-content/plugins/wp-sqlite-integration/constants.php';
		require_once ABSPATH . 'wp-content/plugins/wp-sqlite-integration/wp-includes/sqlite/db.php';

		// Check if tables exist
		$table = $wpdb->get_var( "SELECT name FROM sqlite_master WHERE type='table' AND name='{$wpdb->prefix}options'" );
		if ( ! $table ) {
			require_once ABSPATH . 'wp-admin/includes/schema.php';
			require_once ABSPATH . 'wp-includes/option.php';
			require_once ABSPATH . 'wp-includes/capabilities.php';
			require_once ABSPATH . 'wp-content/plugins/wp-sqlite-integration/wp-includes/sqlite/install-functions.php';
			sqlite_make_db_sqlite();
		}

		if ( ! get_option( 'blogname' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			update_option( 'blogname', 'WorDBless SQLite' );
			update_option( 'admin_email', 'admin@example.com' );
			update_option( 'blog_public', 0 );
			update_option( 'WPLANG', 'en_US' );
			update_option( 'timezone_string', 'America/New_York' );
			update_option( 'date_format', 'Y-m-d' );
			update_option( 'time_format', 'H:i:s' );
			update_option( 'siteurl', 'http://anything.example' );
			update_option( 'home', 'http://anything.example' );
			$user_id = wp_create_user( 'admin', 'password', 'admin@example.com' );
			$user    = new WP_User( $user_id );
			$user->set_role( 'administrator' );
			wp_install_defaults( $user_id );
			wp_install_maybe_enable_pretty_permalinks();
			flush_rewrite_rules();
		}
	}
}
