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
	        mkdir( 'wordpress/wp-content' );
        }

        copy( 'src/dbless-wpdb.php', 'wordpress/wp-content/db.php' );
    }
}
