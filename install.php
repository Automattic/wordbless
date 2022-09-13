<?php

if ( ! is_dir('wordpress/wp-content' ) ) {
	mkdir( 'wordpress/wp-content' );
}

copy('src/dbless-wpdb.php', 'wordpress/wp-content/db.php');
