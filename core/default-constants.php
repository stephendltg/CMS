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
function init_constants() {

	if ( !defined('CONTENT_DIR') )
        define('CONTENT_DIR', ABSPATH . 'content');

	if ( !defined('DEBUG') )
		define( 'DEBUG', false );

    // Definit l'encodage des documents.
    if ( !defined('CHARSET') )
        define('CHARSET', 'UTF-8');

    // Definit le stockage de la bd
    if ( !defined('JSONDB') )
		define ('JSONDB', ABSPATH . 'test');

	// Constantes de temps
	define( 'MINUTE_IN_SECONDS', 60 );
	define( 'HOUR_IN_SECONDS',   60 * MINUTE_IN_SECONDS );
	define( 'DAY_IN_SECONDS',    24 * HOUR_IN_SECONDS   );
	define( 'WEEK_IN_SECONDS',    7 * DAY_IN_SECONDS    );
	define( 'YEAR_IN_SECONDS',  365 * DAY_IN_SECONDS    );
}


/**
 *
 * On definit les constantes pour les plugins
 *
 */
function plugin_directory_constants() {

    if ( !defined('HOME') )
		define( 'HOME', get_option('siteurl') );

	if ( !defined('PLUGIN_DIR') )
		define( 'PLUGIN_DIR', CONTENT_DIR . '/plugins' );

	if ( !defined('PLUGIN_URL') )
		define( 'PLUGIN_URL', HOME . '/plugins' );

	if ( !defined('MU_PLUGIN_DIR') )
		define( 'MU_PLUGIN_DIR', CONTENT_DIR . '/mu-plugins' );

	if ( !defined('MU_PLUGIN_URL') )
		define( 'MU_PLUGIN_URL', HOME . '/mu-plugins' );
}

/**
 *
 * On definit les constantes pour le thème
 *
 */
function theme_directory_constants() {

    /** On Definit le repertoire des themes. */
    if ( !defined('THEMES_DIR') )
        define('THEMES_DIR', CONTENT_DIR . '/themes');

}


/**
 *
 * On définit les constantes pour les cookies
 *
 */
function MP_cookie_constants() {

	if ( !defined( 'COOKIEHASH' ) ) {
        define( 'COOKIEHASH', md5( HOME ) );
	}

	if ( !defined('USER_COOKIE') )
		define('USER_COOKIE', 'cmsuser_' . COOKIEHASH);

	if ( !defined('PASS_COOKIE') )
		define('PASS_COOKIE', 'cmsspass_' . COOKIEHASH);

	if ( !defined('AUTH_COOKIE') )
		define('AUTH_COOKIE', 'cms_' . COOKIEHASH);

	if ( !defined('SECURE_AUTH_COOKIE') )
		define('SECURE_AUTH_COOKIE', 'cms_sec_' . COOKIEHASH);

	if ( !defined('LOGGED_IN_COOKIE') )
		define('LOGGED_IN_COOKIE', 'cms_logged_in_' . COOKIEHASH);

	if ( !defined('TEST_COOKIE') )
		define('TEST_COOKIE', 'cms_test_cookie');

	if ( !defined('SITECOOKIEPATH') )
		define('SITECOOKIEPATH', preg_replace('|https?://[^/]+|i', '', HOME . '/' ) );

	if ( !defined('ADMIN_COOKIE_PATH') )
		define( 'ADMIN_COOKIE_PATH', SITECOOKIEPATH . 'wp-admin' );

	if ( !defined('PLUGINS_COOKIE_PATH') )
		define( 'PLUGINS_COOKIE_PATH', preg_replace('|https?://[^/]+|i', '', PLUGIN_URL)  );

	if ( !defined('COOKIE_DOMAIN') )
		define('COOKIE_DOMAIN', false);
}



/**
 *
 * On definit les constantes pour certaines fonctionnalité
 *
 */
function functionality_constants() {



}


