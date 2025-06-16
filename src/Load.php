<?php

namespace WorDBless;

use PDO;
use PDOException;
use WP_User;

/**
 * Loads WorDBless
 */
class Load {

	/**
	 * Loads WorDBless and initializes its modules
	 *
	 * @return void
	 */
	public static function load( $db_engine = 'dbless', $persist = false ) {
		if ( ! defined( 'ABSPATH' ) ) {
			define( 'ABSPATH', __DIR__ . '/../../../../wordpress/' );
		}
		if ( ! $persist ) {
			// Clean up any existing SQLite database files.
			Sqlite::cleanup();
		}

		define( 'WP_REPAIRING', true ); // Will not try to install WordPress
		define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
		if ( ! defined( 'UPLOADS' ) ) {
			( defined( 'dbless_UPLOADS' ) )
			? define( 'UPLOADS', 'wp-content/' . constant( '\dbless_UPLOADS' ) )
			: define( 'UPLOADS', 'wp-content/uploads' );
		}

		if ( ! defined( 'DB_ENGINE' ) ) {
			// normalize to one of the following: 'dbless', 'mysql', 'sqlite'
			$db_engine = strtolower( $db_engine );
			if ( ! in_array( $db_engine, array( 'dbless', 'mysql', 'sqlite' ), true ) ) {
				$db_engine = 'dbless';
			}
			define( 'DB_ENGINE', $db_engine );
		}
		$_SERVER['SERVER_NAME'] = 'anything.example';
		$_SERVER['HTTP_HOST']   = 'anything.example';

		global $table_prefix;
		$table_prefix = 'wp_';

		require ABSPATH . '/wp-settings.php';
		require_once ABSPATH . 'wp-admin/includes/admin.php';
		// UPLOADS is defined by the time we get here, via a bootstrap.php or the code block above.
		if ( ! file_exists( ABSPATH . UPLOADS ) ) { // @phpstan-ignore constant.notFound
			mkdir( ABSPATH . UPLOADS ); // @phpstan-ignore constant.notFound
		}

		if ( DB_ENGINE === 'dbless' ) {
			Options::init();
			Posts::init();
			PostMeta::init();
			Users::init();
			UserMeta::init();
			WpDie::init();
		} elseif ( DB_ENGINE === 'sqlite' ) {
			Sqlite::init();
		}
	}
}
