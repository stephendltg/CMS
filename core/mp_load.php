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
require( ABSPATH . INC . '/mp_load_functions.php' );

// On inclus les fichier pour l'initialisation du CMS.
require( ABSPATH . INC . '/mp_default_constants.php' );

// On initialise les constantes: DEBUG, CONTENT_DIR et DATABASE_DIR.
mp_init_constants();

// On vérifie la version de PHP.
mp_check_php_versions();

// On demarre un timer.
timer_start();

// On vérifie si mode debug est actif.
mp_debug_mode();

// On definit l'encodage du header.
mp_set_internal_encoding();

// On charge les fonctions primordiales ( Hook, helper )
require( ABSPATH . INC . '/mp_hook.php' );
require( ABSPATH . INC . '/mp_helper.php' );
require( ABSPATH . INC . '/mp_constants.php' );

// On vérifier que le cms est bien installer et les droits d'écriture sur les repertoires.
cms_not_installed();

// On nettoie les requetes si version PHP < 5.4
mp_magic_quotes();

//On nettoie les données passer par GET
$_GET = array_map( 'esc_url', $_GET );

// Definit les constantes pour les répertoires de stockage du site
if ( defined('MEMORY_LIMIT') ) get_limit_memory(MEMORY_LIMIT);

// On charge les filtres par défaut
require( ABSPATH . INC . '/mp_filters.php' );

// On définit les constantes pour plugins ( + declaration de HOME )
mp_plugin_directory_constants();

// On créer les constantes de securite.
mp_secure_constants();

// On active le mod rewrite si disponible
mp_rewrite_rules();

// Mise à l'heure
mp_setting_the_time();

// Fonction d'extinction de minipops
register_shutdown_function( 'shutdown_action_hook' );

// on inclus la requête passé à l'url ainsi que les fonction tags
require( ABSPATH . INC . '/mp_query.php' );

// On charge le bon http header selon la requête
get_http_header();

require( ABSPATH . INC . '/mp_api.php' );


// Fonction pour gérer les pages
//require( ABSPATH . INC . '/cron.php' );
require( ABSPATH . INC . '/mp_pages.php' );
require( ABSPATH . INC . '/mp_attachment.php' );
require( ABSPATH . INC . '/mp_pops.php' );
require( ABSPATH . INC . '/mp_cache.php' );
require( ABSPATH . INC . '/parsedown.php' );

// On verifie l'utilisateur et son roles
// à faire

// On charge les must plugins ( plugins non désactivable ).
foreach ( glob( MU_PLUGIN_DIR .'/*.php' ) as $mu_plugin ) {
		include_once( $mu_plugin );
}
unset( $mu_plugin );

do_action( 'muplugins_loaded' );

// On charge la gestion des script et style
require( ABSPATH . INC . '/mp_enqueue.php' );
// On charge les fonctions gérant la traduction
require( ABSPATH . INC . '/mp_lang.php' );

// On charge les plugins seulement actif recuperer dans option( 'active_plugins' ) = test, memory, ...
@mkdir( PLUGIN_DIR , 0755 , true );
if( get_the_blog('plugins') ){
	foreach ( glob( PLUGIN_DIR .'/{'.get_the_blog('plugins').'}' , GLOB_BRACE | GLOB_ONLYDIR ) as $plugin ){
		if( glob( $plugin.'/'. basename($plugin).'.php' ) )
			include_once( $plugin.'/'. basename($plugin).'.php' );
	}
	unset( $plugin );
}

do_action( 'plugins_loaded' );

// Hook theme activé
do_action( 'setup_theme' );

// On definit les constantes pour le thème actif.
define( 'TEMPLATEPATH' , get_template_directory() );
define( 'TEMPLATEURL', rel2abs(str_replace(ABSPATH ,'' ,TEMPLATEPATH) ) );

// On charge le fichier fonction.php du thème actif
if ( glob( TEMPLATEPATH . '/functions.php' ) )
	include( TEMPLATEPATH . '/functions.php' );


// Hook apres chargement du theme
do_action( 'after_setup_theme' );

// on inclus les fonctions et classes pour parser page
require( ABSPATH . INC . '/mp_optimizer.php' );
require( ABSPATH . INC . '/mp_template.php' );

// Hook mini-Pops  - Core démarré
do_action( 'loaded' );
