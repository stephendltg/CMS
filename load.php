<?php defined('ABSPATH') or die('No direct script access.');
/**
 * CHARGEMENT DU CMS
 *
 *
 * @package CMS
 * @subpackage load
 * @version 1
 */

// On inclus les fichier pour l'initialisation du CMS.
require( ABSPATH . INC . '/default-constants.php' );

// On initialise les constantes: WP_DEBUG, WP_CONTENT_DIR.
init_constants();

// On vérifie la version de PHP.
check_php_versions();

// Mise à l'heure
setting_the_time();

// On demarre un timer.
timer_start();

// On vérifie si mode debug est actif.
debug_mode();


//echo wp_guess_url();

// On charge les fonctions primordiales ( Hook, cache et DataBase )
require( ABSPATH . INC . '/hook.php' );
require( ABSPATH . INC . '/cache.php' );
require( ABSPATH . INC . '/xmldb.php' );
require( ABSPATH . INC . '/jsondb.php' );
require( ABSPATH . INC . '/options.php' );

// Run the installer if WordPress is not installed.
//cms_not_installed();

// Load most of WordPress.
//require( ABSPATH . INC . '/post.php' );
//require( ABSPATH . INC . '/cron.php' );
//require( ABSPATH . INC . '/admin-bar.php' );


// On définit les constantes pour plugins
plugin_directory_constants();

// On charge les must plugins ( plugins non désactivable ).
foreach ( get_mu_plugins() as $mu_plugin ) {
	include_once( $mu_plugin );
}
unset( $mu_plugin );

do_action( 'muplugins_loaded' );


// Define constants cookies
//cookie_constants();

// Variables globales commune.


// On active le thème définit
register_theme_directory( get_theme_root() );

// On charge les plugins
foreach ( get_active_and_valid_plugins() as $plugin ) {
	include_once( $plugin );
}
unset( $plugin );

do_action( 'plugins_loaded' );

// On definit l'encodage du header.
set_internal_encoding();

// On definit les constantes de fonctionnalités supplémentaires.
functionality_constants();

// On nettoie les requetes si version PHP < 5.4
magic_quotes();

/**
 * Fires when comment cookies are sanitized.
 */
do_action( 'sanitize_comment_cookies' );


/**
 * Fires before the theme is loaded.
 *
 * @since 2.6.0
 */

// On definit les constantes pour les thèmes.
theme_directory_constants();

do_action( 'setup_theme' );


// Define the template related constants.
wp_templating_constants(  );


// Load the functions for the active theme, for both parent and child theme if applicable.
if ( ! defined( 'WP_INSTALLING' ) || 'wp-activate.php' === $pagenow ) {
	if ( TEMPLATEPATH !== STYLESHEETPATH && file_exists( STYLESHEETPATH . '/functions.php' ) )
		include( STYLESHEETPATH . '/functions.php' );
	if ( file_exists( TEMPLATEPATH . '/functions.php' ) )
		include( TEMPLATEPATH . '/functions.php' );
}

/**
 * Fires after the theme is loaded.
 */
do_action( 'after_setup_theme' );


/**
 * Fires after WordPress has finished loading but before any headers are sent.
 *
 * Most of WP is loaded at this stage, and the user is authenticated. WP continues
 * to load on the init hook that follows (e.g. widgets), and many plugins instantiate
 * themselves on it for all sorts of reasons (e.g. they need a user, a taxonomy, etc.).
 *
 * If you wish to plug an action once WP is loaded, use the wp_loaded hook below.
 *
 * @since 1.5.0
 */
do_action( 'init' );


/**
 * This hook is fired once WP, all plugins, and the theme are fully loaded and instantiated.
 */
do_action( 'loaded' );
