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

// On initialise les constantes: DEBUG, MP_CONTENT_DIR et DATABASE_DIR.
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
require( ABSPATH . INC . '/mp_helper.php' );
require( ABSPATH . INC . '/mp_hook.php' );
require( ABSPATH . INC . '/mp_validator.php' );
require( ABSPATH . INC . '/mp_sanitize.php' );
require( ABSPATH . INC . '/mp_escape.php' );
require( ABSPATH . INC . '/mp_network.php' );
require( ABSPATH . INC . '/mp_files.php' );

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

// On définit les constantes pour plugins ( + declaration de MP_HOME )
mp_plugin_directory_constants();

// On charge la gestion des fichier yaml
require( ABSPATH . INC . '/mp_yaml.php' );
// On charge les fonctions gérant les options des plugins et du thème
require( ABSPATH . INC . '/mp_options.php' );

// On init le blog
init_the_blog();

// On créer les constantes de securite.
mp_secure_constants();

// On active le mod rewrite si disponible
mp_rewrite_rules();

// Fonction d'extinction de minipops
register_shutdown_function( 'shutdown_action_hook' );

// Fonction pour gérer les pages
//require( ABSPATH . INC . '/cron.php' );
require( ABSPATH . INC . '/mp_query.php' );
require( ABSPATH . INC . '/mp_pages.php' );
require( ABSPATH . INC . '/mp_attachment.php' );
require( ABSPATH . INC . '/mp_pops.php' );
require( ABSPATH . INC . '/parsedown.php' );
// On charge les fonctions gérant la date
require( ABSPATH . INC . '/mp_datei18n.php' );

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

// On charge les plugins seulement actif recuperer dans option( 'active_plugins' ) = [test, memory, ...]
@mkdir( MP_PLUGIN_DIR , 0755 , true );

$plugins = get_option('plugins->active_plugins');
if( !empty($plugins) ){
	foreach( glob(MP_PLUGIN_DIR .'/{'.implode(',', $plugins).'}', GLOB_BRACE|GLOB_ONLYDIR) as $plugin ){
		if( glob( $plugin.'/'. basename($plugin).'.php' ) )
			include_once( $plugin.'/'. basename($plugin).'.php' );
	}
	unset( $plugin );
}
unset( $plugins );

do_action( 'plugins_loaded' );

// On créer les constantes pour les cookies
mp_cookies_constants();

// On verifie l'utilisateur et son roles
require( ABSPATH . INC . '/mp_auth.php' );

// Hook theme activé
do_action( 'setup_theme' );

// On definit le chemin pour le thème actif.
define( 'MP_TEMPLATE_PATH', get_template_directory() );

// On définit les constantes si un thème valide sinon on charge le thème du core
if( !defined('MP_TEMPLATE_DIR') ){
	define( 'MP_TEMPLATE_DIR' , MP_THEMES_DIR . '/'. MP_TEMPLATE_PATH );
	define( 'MP_TEMPLATE_URL' , rel2abs( MP_TEMPLATE_PATH, MP_CONTENT_URL.'/themes' ) );
}

// On charge le fichier fonction.php du thème actif
if ( glob( MP_TEMPLATE_DIR . '/functions.php' ) )
	include( MP_TEMPLATE_DIR . '/functions.php' );

// Hook apres chargement du theme
do_action( 'after_setup_theme' );

// Requête passer à l'url
global $query;
$query = get_url_queries();

// On charge le bon http header selon la requête
get_http_header();

// on inclus les fonctions d'optimisation et des templates
require( ABSPATH . INC . '/mp_template.php' );

// Hook mini-Pops  - Core démarré
do_action( 'loaded' );