<?php
/**
 * Initialize the testing environment.
 *
 * @package automattic/wordbless
 */

/**
 * Load the composer autoloader.
 */
require_once __DIR__ . '/../vendor/autoload.php';

define( 'ABSPATH', __DIR__ . '/../wordpress/' );
define( 'TESTSPATH', __DIR__ );
define( 'WP_DEBUG', true );

\WorDBless\Load::load();
