<?php

namespace WorDBless;

class Load {

	public static function load() {
		if ( ! defined( 'ABSPATH') ) {
			define( 'ABSPATH', dirname( __DIR__, 4 ) . '/wordpress/' );
		}

		define( 'WP_REPAIRING', true ); // Will not try to install WordPress

		require ABSPATH . '/wp-settings.php';
		Options::init();
	}

}


