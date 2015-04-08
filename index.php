<?php
/**
 *
 * @package CMS
 */

/** On definit le repertoire racine  */
define( 'ABSPATH', dirname(__FILE__) . '/' );

// On définit le coeur de CMS
define( 'INC', 'core' );

/** On inclut les fonctions primordiales  */
require( ABSPATH . INC . '/load-functions.php' );

/** On definit les parametres de retour d'erreur de php  */
error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );

/** On verifie que le fichier config existe  */
if ( file_exists( ABSPATH . 'config.php') ) {

	require_once( ABSPATH . 'config.php' );

} else {

    //On tente de reconstruire le fichier config
    if ( file_exists( ABSPATH . INC . '/setup-config.php' ) ) {
        $path = guess_url() . '/core/setup-config.php';
        header ('Location: '.$path );
        die();
    }
    else {
        cms_maintenance( '503' , 'HTTP Error 503: Service indisponible' , 'La configuration de votre site est absente !' );
    }
}
