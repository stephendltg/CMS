<?php
/**
 *
 * @package CMS mini POPS
 */

/** On definit le repertoire racine  */
define( 'ABSPATH', dirname(__FILE__) . '/' );

// On définit le coeur du CMS
define( 'INC', 'core' );

/** On verifie que le fichier config existe  */
if ( file_exists( ABSPATH . 'config.php') ) {
	require_once( ABSPATH . 'config.php' );
}

require_once(ABSPATH . INC . '/load.php');
require_once(ABSPATH . INC . '/template_load.php');
