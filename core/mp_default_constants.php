<?php defined('ABSPATH') or die('No direct script access.');

/**
 * CHARGEMENT DES CONSTANTES DU CMS mini POPS
 *
 * Definitions des constantes sinon renseigné sur le fichier config.php
 *
 * @package cms mini POPS
 * @subpackage default-constant
 * @version 1
 */


/**
 *
 * On définit les constantes primordiale à l'initialisation
 *
 */
function mp_init_constants() {

    // Definit les constantes pour les répertoires de stockage du site
	if ( !defined('CONTENT_DIR') )
        define( 'CONTENT_DIR', ABSPATH . 'content' );

	define( 'CONTENT', CONTENT_DIR . '/home' );
	define( 'THEMES_DIR', CONTENT_DIR . '/themes' );

    // Definit les constantes debug
	if ( !defined('DEBUG') )
		define( 'DEBUG', false );

	if ( !defined('DEBUG_DISPLAY') )
		define( 'DEBUG_DISPLAY', true );

	if ( !defined('DEBUG_LOG') )
		define( 'DEBUG_LOG', false );

    // Definit l'encodage des documents.
    define( 'CHARSET', 'UTF-8' );

	// Constantes de temps
	define( 'MINUTE_IN_SECONDS', 60 );
	define( 'HOUR_IN_SECONDS',   60 * MINUTE_IN_SECONDS );
	define( 'DAY_IN_SECONDS',    24 * HOUR_IN_SECONDS   );
	define( 'WEEK_IN_SECONDS',    7 * DAY_IN_SECONDS    );
	define( 'YEAR_IN_SECONDS',  365 * DAY_IN_SECONDS    );

	// On fixe l'heure par défaut ( mise à l'heure un peu plus tard selon la configuration )
	date_default_timezone_set( 'UTC' );

}


/**
 *
 * On definit les constantes pour les plugins
 *
 */
function mp_plugin_directory_constants() {

    // Definit la constante HOME : url du site de référence
    if ( !defined('HOME') )
		define( 'HOME', guess_url() );

    // Definit les constantes pour l'utilisation des plugins répertoire et url des répertoires
	define( 'CONTENT_URL', rel2abs(str_replace(ABSPATH ,'' ,CONTENT) ) );
	define( 'PLUGIN_DIR', CONTENT_DIR . '/plugins' );
	define( 'PLUGIN_URL', rel2abs(str_replace(ABSPATH ,'' ,PLUGIN_DIR) ) );
	define( 'MU_PLUGIN_DIR', CONTENT_DIR . '/mu-plugins' );
	define( 'MU_PLUGIN_URL', rel2abs(str_replace(ABSPATH ,'' ,MU_PLUGIN_DIR) ) );
}

/**
 *
 * On definit les constantes de securites
 *
 */
function mp_secure_constants() {
    $CP='QxhO%n(HVBl(R!$P4wT)wmYnj$eKTV8p';$KP='(&4$k3B5kM41CXxna&mwj@Kt4O3EqSTo';$MK= CONTENT_DIR.date('Ym').HOME;$CP.=$KP;$MK.=$KP;$U='_';$KS = array('KEY','SALT');$KZ = array('AUTH','SECURE_AUTH','LOGGED_IN','NONCE','SECRET');foreach($KS as $_KS)foreach($KZ as $_KZ) define( $_KZ.$U.$_KS , md5('MPOPS'.$_KZ.$_KS.md5( $MK ) . $MK)  .md5( $_KZ.$_KS.$MK) );define('COOKIEHASH',md5('MPOPSCOOKIEHASH'.md5($MK.$CP).$MK.$CP).md5('MPOPSCOOKIEHASH'.$MK.$CP));unset($U,$MK,$_KZ,$_KS,$KZ,$KS,$CP,$KP);
}