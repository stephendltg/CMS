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
if ( file_exists( ABSPATH . 'mp_config.php') ) {
	require_once( ABSPATH . 'mp_config.php' );
}
require_once(ABSPATH . INC . '/mp_load.php');
require_once(ABSPATH . INC . '/mp_template_load.php');
