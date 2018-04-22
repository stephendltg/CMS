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

	define('MP_VERSION', '1.0.0');

    // Definit les constantes pour les répertoires de stockage du site
	if ( !defined('MP_CONTENT_DIR') )
		define( 'MP_CONTENT_DIR', ABSPATH . 'content' );

	if ( !defined('MP_SQLITE_ENCRYPT') )
		define( 'MP_SQLITE_ENCRYPT', substr( md5( __FILE__ ), 0, 8 ) );

	if ( !defined('FORCE_RELOCATE') )
		define( 'FORCE_RELOCATE', false );

	define( 'MP_PAGES_DIR',   MP_CONTENT_DIR . '/pages'   );
	define( 'MP_THUMBS_DIR',  MP_CONTENT_DIR . '/thumbs'   );
	define( 'MP_THEMES_DIR',  MP_CONTENT_DIR . '/themes'  );
	define( 'MP_SQLITE_DIR',  MP_CONTENT_DIR . '/sqlite'  );
	define( 'MP_CONFIG_DIR',  MP_CONTENT_DIR . '/config'  );
	define( 'MP_CACHE_DIR',   MP_CONTENT_DIR . '/cache'  );

    // Definit les constantes debug
	if ( !defined('DEBUG') )
		define( 'DEBUG', true );

	if ( !defined('DEBUG_DISPLAY') )
		define( 'DEBUG_DISPLAY', true );

	if ( !defined('DEBUG_LOG') )
		define( 'DEBUG_LOG', false );

    // Definit l'encodage des documents.
    define( 'CHARSET', 'UTF-8' );

    // Definit open shared sqlite3
    define( 'SQLITE3_OPEN_SHAREDCACHE' , 0x00020000 );

	// Constantes de temps
	define( 'MINUTE_IN_SECONDS', 60 );
	define( 'HOUR_IN_SECONDS',   60 * MINUTE_IN_SECONDS );
	define( 'DAY_IN_SECONDS',    24 * HOUR_IN_SECONDS   );
	define( 'WEEK_IN_SECONDS',    7 * DAY_IN_SECONDS    );
	define( 'YEAR_IN_SECONDS',  365 * DAY_IN_SECONDS    );

	// On fixe l'heure en UTC
	date_default_timezone_set('UTC');

}


/**
 *
 * On definit les constantes pour les plugins
 *
 */
function mp_plugin_directory_constants() {

    // Definit la constante MP_HOME : url du site de référence
    if ( !defined('MP_HOME') )
		define( 'MP_HOME', get_option('setting.home', guess_url() ) );

	// A l'heure actuel MP_CONTENT_URL n'accepte pas les sous domaines
	if ( !defined('MP_CONTENT_URL') )
		define( 'MP_CONTENT_URL', guess_url() . '/mp-content');

    // Definit les constantes pour l'utilisation des plugins répertoire et url des répertoires
	define( 'MP_PAGES_URL',   MP_CONTENT_URL  . '/pages' );
	define( 'MP_THUMBS_URL',  MP_CONTENT_URL . '/thumbs'   );
	define( 'MP_PLUGIN_DIR',  MP_CONTENT_DIR  . '/plugins' );
	define( 'MP_PLUGIN_URL',  MP_CONTENT_URL  . '/plugins' );
	define( 'MU_PLUGIN_DIR',  MP_CONTENT_DIR  . '/mu-plugins' );
	define( 'MU_PLUGIN_URL',  MP_CONTENT_URL  . '/mu-plugins' );
}


/**
 *
 * On definit les constantes de securites
 * http://www.sethcardoza.com/api/rest/tools/random_password_generator/length:32
 */
function mp_secure_constants() {
    $CP=get_option('setting.api_key', 'QxhO%n(HVBl(R!$P4wT)wmYnj$eKTV8p');$KP=get_option('setting.api_salt','(&4$k3B5kM41CXxna&mwj@Kt4O3EqSTo');$MK= MP_CONTENT_DIR.date('Ym').MP_HOME;$CP.=$KP;$MK.=$KP;$U='_';$KS = array('KEY','SALT');$KZ = array('AUTH','NONCE','SECRET');foreach($KS as $_KS)foreach($KZ as $_KZ) define( $_KZ.$U.$_KS , md5('MPOPS'.$_KZ.$_KS.md5( $MK ) . $MK)  .md5( $_KZ.$_KS.$MK) );define('COOKIEHASH',md5('MPOPSCOOKIEHASH'.md5($MK.$CP).$MK.$CP).md5('MPOPSCOOKIEHASH'.$MK.$CP));unset($U,$MK,$_KZ,$_KS,$KZ,$KS,$CP,$KP);
}

/**
 *
 * On definit les constantes des cookies
 */
function mp_cookies_constants() {

	define('AUTH_COOKIE', 'minipops_auth_' . COOKIEHASH);

	if ( !defined('COOKIEPATH') )
		define('COOKIEPATH', preg_replace('|https?://[^/]+|i', '', guess_url() . '/' ) );

	if ( !defined('COOKIE_DOMAIN') )
		define('COOKIE_DOMAIN', false);

	// Spécifie la durée de vie du cookie en secondes. La valeur de 0 signifie : "Jusqu'à ce que le navigateur soit éteint".
    if ( !defined('SESSION_COOKIE_LIFE') )
        define('SESSION_COOKIE_LIFE', 0);

    define('SESSION_COOKIE', 'minipops_' . COOKIEHASH);

    // Modifie les paramètres du cookie de session
    $secure = is_ssl() && 'https' === parse_url( guess_url(), PHP_URL_SCHEME );
    session_set_cookie_params ( SESSION_COOKIE_LIFE, COOKIEPATH, COOKIE_DOMAIN, $secure, true);
    session_name(SESSION_COOKIE);
}

