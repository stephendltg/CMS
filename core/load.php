<?php defined('ABSPATH') or die('No direct script access.');
/**
 * CHARGEMENT DU CMS mini POPS
 *
 *
 * @package CMS mini POPS
 * @subpackage load
 * @version 1
 */

/** On inclut les fonctions primordiales  */
require( ABSPATH . INC . '/load-functions.php' );

// On inclus les fichier pour l'initialisation du CMS.
require( ABSPATH . INC . '/default-constants.php' );

// On initialise les constantes: DEBUG, CONTENT_DIR et DATABASE_DIR.
init_constants();

// On vérifie la version de PHP.
check_php_versions();

// Mise à l'heure
setting_the_time();

// On demarre un timer.
timer_start();

// On vérifie si mode debug est actif.
debug_mode();

// On créer les constantes de securite.
secure_constants();

// On definit l'encodage du header.
set_internal_encoding();

// On charge les fonctions primordiales ( Hook, helper et database )
require( ABSPATH . INC . '/hook.php' );
require( ABSPATH . INC . '/helper.php' );
require( ABSPATH . INC . '/mpdb.php' );

// On vérifier que le cms est bien installer et les droits d'écriture sur les repertoires.
cms_not_installed();

// On charge la table option et ses fonctions
require( ABSPATH . INC . '/options.php' );

// Load most of miniPOPS.
//require( ABSPATH . INC . '/post.php' );
//require( ABSPATH . INC . '/cron.php' );
//require( ABSPATH . INC . '/admin-bar.php' );

// On définit les constantes pour plugins ( declaration de HOME )
plugin_directory_constants();

// On active le mod rewrite si disponible
mod_rewrite_rules();

// On nettoie les requetes si version PHP < 5.4
magic_quotes();

//On nettoie les données passer par GET
$_GET = array_map( 'esc_url', $_GET );

// on inclus la requête passé à l'url ainsi que les fonction tags
require( ABSPATH . INC . '/query.php' );
require( ABSPATH . INC . '/pops.php' );

// on charge le config du site dans la base option
file_get_config();

// Hook : chargement des paramètres du site
do_action( 'config_loaded' );


// On verifie l'utilisateur et son roles
// à faire

// On charge les must plugins ( plugins non désactivable ).
foreach ( glob( MU_PLUGIN_DIR .'/*.php' ) as $mu_plugin ) {
	include_once( $mu_plugin );
}
unset( $mu_plugin );

do_action( 'muplugins_loaded' );

// On charge les plugins seulement actif recuperer dans option( 'active_plugins' ) = test, memory, ...
@mkdir( PLUGIN_DIR , 0755 , true );
foreach ( glob( PLUGIN_DIR .'/{'.get_option( 'active_plugins' ).'}.php' , GLOB_BRACE ) as $plugin ) {
	include_once( $plugin );
}
unset( $plugin );

do_action( 'plugins_loaded' );

// Hook theme activé
do_action( 'setup_theme' );

// On definit les constantes pour le thème actif.
define( 'TEMPLATEPATH' , get_template_directory() );

// On charge le fichier fonction.php du thème actif
if ( file_exists( TEMPLATEPATH . '/functions.php' ) )
	include( TEMPLATEPATH . '/functions.php' );

// Hook apres chargement du theme
do_action( 'after_setup_theme' );

// on inclus les fonctions et classes pour parser page
require( ABSPATH . INC . '/pages.php' );
require( ABSPATH . INC . '/parsedown.php' );
require( ABSPATH . INC . '/template.php' );

// On charge le http header
get_http_header();

// Hook mini-Pops  - Core démarré
do_action( 'loaded' );
