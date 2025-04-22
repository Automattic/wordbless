<?php
/**
 * This is the installation script to copy the db dropin plugin into WordPress.
 *
 * @package Automattic/wordbless
 */

namespace WorDBless\Composer;

class InstallDropin {
	public static function copy() {
		if ( ! is_dir( 'wordpress/wp-content' ) ) {
			mkdir( 'wordpress/wp-content', 0777, true );
		}
		if ( ! is_dir( 'wordpress/wp-content/themes' ) ) {
			mkdir( 'wordpress/wp-content/themes', 0777, true );
		}

		// Copy the dbless-wpdb.php file
		copy( dirname( __DIR__ ) . '/dbless-wpdb.php', 'wordpress/wp-content/db.php' );

		// Copy the SQLite database integration plugin
		$sqlite_plugin_dir = 'wordpress/wp-content/plugins/wp-sqlite-integration';
		if ( ! is_dir( $sqlite_plugin_dir ) ) {
			mkdir( $sqlite_plugin_dir, 0777, true );
		}

		// Copy the plugin files
		$source_dir = dirname( __DIR__, 2 ) . '/third-party/sqlite-database-integration';
		if ( is_dir( $source_dir ) ) {
			self::recursive_copy( $source_dir, $sqlite_plugin_dir );
		}
	}

	/**
	 * Recursively copy a directory
	 *
	 * @param string $src Source directory
	 * @param string $dst Destination directory
	 * @return void
	 */
	private static function recursive_copy( $src, $dst ) {
		$dir = opendir( $src );
		@mkdir( $dst );
		while ( false !== ( $file = readdir( $dir ) ) ) { // phpcs:ignore
			if ( ( '.' !== $file ) && ( '..' !== $file ) ) {
				if ( is_dir( $src . '/' . $file ) ) {
					self::recursive_copy( $src . '/' . $file, $dst . '/' . $file );
				} else {
					copy( $src . '/' . $file, $dst . '/' . $file );
				}
			}
		}
		closedir( $dir );
	}
}
