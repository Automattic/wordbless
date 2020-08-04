<?php

namespace WorDBless;

class Load {

	public static function load() {
		if ( ! defined( 'ABSPATH' ) ) {
			define( 'ABSPATH', __DIR__ . '/../../../../wordpress/' );
		}

		define( 'WP_REPAIRING', true ); // Will not try to install WordPress
		define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );

		require ABSPATH . '/wp-settings.php';
		Options::init();
		Posts::init();
	}

}


