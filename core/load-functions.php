<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction indispensable au chargement du cms mini POPS.
 *
 *
 * @package cms mini POPS
 * @subpackage load-functions
 * @version 1
 */


// On cache les erreurs php
error_reporting(0);


/***********************************************/
/*              Variables globales             */
/***********************************************/

global $is_apache, $is_mod_rewrite;

$is_apache = ( strpos( $_SERVER['SERVER_SOFTWARE'], 'Apache' ) !== false || strpos( $_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false );

$is_mod_rewrite = function_exists('apache_get_modules') ? in_array( 'mod_rewrite', apache_get_modules() ) : false ;


/***********************************************/
/*              Fonctions globales             */
/***********************************************/


/**
 * On vérifie la version de php utilisé si non compatible on fait un die
 */
function check_php_versions() {
    if ( version_compare( phpversion() , "5.4.0", "<" ) ) { // passer à 5.4 pour http_response_code() ligne 256
        $msg =  '<p>Server PHP version ' . phpversion() .
                ' .</p><p>this cms need PHP version 5.3 .</p>';
        cms_maintenance( $msg );
	}
}

/**
 * On met à l'heure le serveur selon la constante definit sinon on utilise l'heure du serveur par defaut
 */
function setting_the_time() {
    if ( defined ( 'TIME_ZONE' ) ){ date_default_timezone_set( TIME_ZONE ); }
    else { date_default_timezone_set( 'UTC' ); }
}

/**
 * Demarrage du timer.
 *
 * @return true
 */
function timer_start() {
	global $timestart;
	$timestart = microtime( true );
	return true;
}

/**
 * Timer Stop
 *
 * @return value
 */
function timer_stop( $precision = 3 ) {

	global $timestart;
    $precision   = (int) $precision;
	$timeend = microtime( true );
	$timeend = $timeend - $timestart;
	$timeend = number_format( $timeend, $precision );
	return $timeend;
}

/**
 * Active le mode debug si constante debug est true
 */
function debug_mode() {
	if ( DEBUG ) {
        ini_set('display_errors', 1);
        error_reporting( E_ALL );
    }
    else {
		error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
	}
}


/**
 * On créé les repertoires si cms non installé et on verifie les droits en ecriture.
 */
function cms_not_installed() {
    if ( !is_writable( realpath( ABSPATH ) ) )
        cms_maintenance( 'Error directory permissions !' );

    @mkdir( CONTENT_DIR , 0755 , true );
    if ( !is_writable( CONTENT_DIR ) ) cms_maintenance( 'Error directory permissions : '. str_replace( ABSPATH , "" , CONTENT_DIR ) .' !' );
    @mkdir( DATABASE_DIR , 0755 , true );
    if ( !is_writable( DATABASE_DIR ) ) cms_maintenance( 'Error directory permissions : '. str_replace( ABSPATH , "" , DATABASE_DIR ) .' !' );
    @mkdir( CONTENT , 0755 , true );
    if ( !is_writable( CONTENT ) ) cms_maintenance( 'Error directory permissions : '. str_replace( ABSPATH , "" , CONTENT ) .' !' );
}


/**
 * Active la mod rewrite si le serveur apache le supporte
 */
function mod_rewrite_rules(){

    //remove_option('mod_rewrite_rules');

    global $is_apache, $is_mod_rewrite;

    // On modifie le fichier htaccess si le mode rewrite n'est pas active et que nous sommes sur serveur apache
    if ( !option_exists('mod_rewrite_rules') ) {

        if ( $is_mod_rewrite ) {

            // On definit le repertoire root
            $root =  str_replace( 'http://' . $_SERVER['HTTP_HOST'] , "" , HOME ) ;
            if ( empty( $root ) ) $root = '/';

            $rules  = "# BEGIN miniPops\n\n";
            $rules .= "# protect the htaccess file\n";
            $rules .= "<files .htaccess>\n";
            $rules .= "order allow,deny\n";
            $rules .= "deny from all\n";
            $rules .= "</files>\n\n";
            $rules .= "# protect the config file\n";
            $rules .= "<files config.php>\n";
            $rules .= "order allow,deny\n";
            $rules .= "deny from all\n";
            $rules .= "</files>\n\n";
            $rules .= "# Set default charset utf-8\n";
            $rules .= "AddDefaultCharset UTF-8\n\n";
            $rules .= "# Format audio \n";
            $rules .= "AddType audio/ogg  .ogg\n";
            $rules .= "AddType audio/mp3  .mp3\n\n";
            $rules .= "<IfModule mod_rewrite.c>\n\t";
            $rules .= "RewriteEngine on\n\n\t";
            $rules .= "# if you homepage is ". HOME ."\n\t";
            $rules .= "# RewriteBase $root\n\n\t";
            $rules .= "# block specify files in the content folder from being accessed directly\n\t";
            $rules .= "RewriteRule ^". str_replace( ABSPATH , "" , CONTENT_DIR ) ."/(.*)\.(pl|php|php3|php4|php5|cgi|spl|scgi|fcgi|shtm|shtml|xhtm|xhtml|html|htm|xml|txt|md|mdown)$ error [R=301,L]\n\n\t";
            $rules .= "# block all files core folder from being accessed directly\n\t";
            $rules .= "RewriteRule ^core/(.*) error [R=301,L]\n\n\t";
            //$rules .= "RewriteCond %{REQUEST_FILENAME} !-f\n\t";
            //$rules .= "RewriteCond %{REQUEST_FILENAME} !-d\n\t";
            //$rules .= "RewriteRule ^panel/(.*) panel/index.php [L]\n\n\t";
            $rules .= "RewriteCond %{REQUEST_FILENAME} !-f\n\t";
            $rules .= "RewriteCond %{REQUEST_FILENAME} !-d\n\t";
            $rules .= "RewriteRule ^(.*) index.php [L]\n\n\t";
            $rules .= "# Update code bellow for SEO improvements\n\t";
            $rules .= "# Redirect 301 /index " . HOME . "/\n\n";
            $rules .= "</IfModule>\n\n";
            $rules .= "# END miniPops";

            if ( file_exists( ABSPATH . '.htaccess' ) ) {
                $rule = file_get_contents( ABSPATH . '.htaccess' );
                $marker_begin =  strpos( $rule , '# BEGIN miniPops') ;
                $marker_end =  strpos( $rule , '# END miniPops') + strlen('# END miniPops') ;
                $rules = substr_replace( $rule , $rules , $marker_begin , $marker_end );
            }
            if ( !file_put_contents( ABSPATH . '.htaccess', $rules ) ) cms_maintenance( 'Error file permissions !' );
            else add_option('mod_rewrite_rules' , true );
        }

    } else { if( !$is_apache || !$is_mod_rewrite ) remove_option('mod_rewrite_rules'); }
}

/**
 * Mise en maintenance de CMS
 *
 */
function cms_maintenance( $message = 'Service Unavailable !' , $subtitle='Service Unavailable' , $http_response_code = 503 ) {

    header( 'Content-Type: text/html; charset=utf-8' );
    http_response_code( $http_response_code );
	header( 'Retry-After: 600' );
?>
	<!DOCTYPE html>
	<html>
	<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $http_response_code ?></title>
    <style>
        body {
            background-color: #1b8caf;
            font-family: Helvetica, Arial, sans-serif;
        }
        h1, div {
            color: #fff;
            margin: auto;
            max-width: 400px;
            text-align: center;
        }
        h1 {
            padding-top: 15%;
            padding-bottom: 40px;
            text-transform: uppercase;
        }
        h2 {
            font-size: 1.2em;
        }
        hr {
            border: 1px solid white;
            width : 90%;
        }
        ul {
            list-style: none;
        }
        li {
            padding: 5px;
            text-align: left;
        }
        p {
            padding: 5px;
        }
        div {
            background-color: #242424;
            border-radius: 5px;
            padding: 20px 0;
        }
    </style>
    </head>
	<body>
        <h1><?php echo $http_response_code ?></h1>
        <div>
            <h2><?php echo $subtitle ?></h2>
            <hr>
            <br>
            <?php echo $message ?>
        </div>
	</body>
	</html>
<?php
	die();
}


/**
 * Encodage du document
 *
 * On vérifier l'encodage de la config ( voir default-constant.php )
 */
function set_internal_encoding() {
    header_remove( 'x-powered-by' );
    if ( function_exists( 'mb_language' ) ) mb_language( 'uni' );
    if ( function_exists( 'mb_regex_encoding' ) ) mb_regex_encoding( CHARSET );
	if ( function_exists( 'mb_internal_encoding' ) ) mb_internal_encoding( CHARSET );
}

/**
 * Entête du document
 *
 * On affecte le bon header au document
 */
function get_http_header() {

    header( 'Content-Type: text/html; charset='.CHARSET );
    http_response_code(200);

    if( is_robots() )
        header( 'Content-Type: text/plain; charset='.CHARSET );

    if( is_feed() || is_sitemap() )
        header( 'Content-Type: text/xml; charset='.CHARSET );

    if( is_404() )
        http_response_code(404);
}


/**
 * On supprimer anti slashes des variables $_GET, $_POST, $_COOKIE et $_REQUEST ajouter par la fonction get_magic_quote si elle est active. ( get_magic_quotes_gpc retourne false à partir de php 5.4
 *
 */

function magic_quotes() {
	if ( get_magic_quotes_gpc() ) {
        function stripslashesGPC(&$value) { $value = stripslashes( $value ); }
        array_walk_recursive($_GET, 'stripslashesGPC');
        array_walk_recursive($_POST, 'stripslashesGPC');
        array_walk_recursive($_COOKIE, 'stripslashesGPC');
        array_walk_recursive($_REQUEST, 'stripslashesGPC');
    }
}


/**
 * Detect si https.
 * thanks wordpress
 *
 * @return string The guessed URL.
 */
function is_ssl() {
    if ( isset($_SERVER['HTTPS']) ) {
        if ( 'on' == strtolower($_SERVER['HTTPS']) ) return true;
        if ( '1' == $_SERVER['HTTPS'] ) return true;
    } elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) { return true; }
    return false;
}

/**
 * Guess the URL for the site.
 * thanks wordpress
 *
 * @return string The guessed URL.
 */
function guess_url() {

    global $is_mod_rewrite;

	if ( defined('HOME') && '' != HOME ) { $url = HOME; }
    else {
		$abspath_fix = str_replace( '\\', '/', ABSPATH );
		$script_filename_dir = dirname( $_SERVER['SCRIPT_FILENAME'] );

		if ( $script_filename_dir . '/' == $abspath_fix ) {
			$path = preg_replace( '#/[^/]*$#i', '', $_SERVER['PHP_SELF'] );
		} else {
			if ( false !== strpos( $_SERVER['SCRIPT_FILENAME'], $abspath_fix ) ) {
				$directory = str_replace( ABSPATH, '', $script_filename_dir );
				$path = preg_replace( '#/' . preg_quote( $directory, '#' ) . '/[^/]*$#i', '' , $_SERVER['REQUEST_URI'] );
			} elseif ( false !== strpos( $abspath_fix, $script_filename_dir ) ) {
				$subdirectory = substr( $abspath_fix, strpos( $abspath_fix, $script_filename_dir ) + strlen( $script_filename_dir ) );
				$path = preg_replace( '#/[^/]*$#i', '' , $_SERVER['REQUEST_URI'] ) . $subdirectory;
			} else {
				$path = $_SERVER['REQUEST_URI'];
			}
		}
        if ( !$is_mod_rewrite ) { $path = $_SERVER['PHP_SELF']; }

        $schema = is_ssl() ? 'https://' : 'http://';
		$url = $schema . $_SERVER['HTTP_HOST'] . $path;
	}

	return rtrim($url, '/');
}


/**
 * On active le theme definit sinon on charge le theme par defaut
 *
 * @access private
 */
function get_template_directory(){

    // On récupère le thème actif dans la base options
    $active_theme = get_option( 'active_theme' );

    // On créer le répertoire des thèmes si n'existe pas
    @mkdir( THEMES_DIR , 0755 , true );

    if( !empty( $active_theme ) // On vérifie qu'un theme est defini
        && is_dir( THEMES_DIR . '/' . $active_theme ) // On verifie que le repertoire du thème existe
        && file_exists( THEMES_DIR . $active_theme . '/readme.txt' ) // On verifie l'existence de readme.txt comportant les informations sur le thème
    )
        return THEMES_DIR . '/' . $active_theme;

    else return ABSPATH . INC . '/asset';
}

/**
 * Récuperer la configuration du site
 * @return configuration sauvegarder dans base option
 */
function file_get_config() {

    // table des champs utilisé dans le fichier site.txt avec leur valeur par défaut
    $fields = array('title'=>'minipops','subtitle'=>'Un CMS SUPER','description'=>'Un site sous miniPops', 'keywords'=>'minipops, cms, minipopscms', 'lang'=>lang() ,'author'=>'stephen deletang','copyright'=>'@2015 - Stephen DELETANG');

    // On vérifie si le fichier site.txt existe sinon on va le créer avec les valeurs par défaut
    if( file_exists( CONTENT .'/site.txt' ) && is_file( CONTENT .'/site.txt' ) ) {

        // On vérifie si le fichier site.txt a été modifier
        $last_config = filemtime ( CONTENT .'/site.txt' );
        if ( is_different($last_config , (int) get_option('last-config-modified')) ){

            // On vérifie que l'on peut lire le fichier de configuration
            if( !is_readable(CONTENT .'/site.txt') ) cms_maintenance('Error file read permissions : site configuration !');

            //filtre de nettoyage des champs du fichier site.txt
            add_filter('site_title' , function($title){
                return sanitize_allspecialschars($title); } );

            add_filter('site_author' , function($author){
                return sanitize_allspecialschars($author); } );

            add_filter('site_keywords' , function($keywords){
                return str_replace(' ',', ',sanitize_words($keywords)); } );

            add_filter('site_lang' , function($lang){
                return (!empty($lang)) ? strtolower($lang).'_'.strtoupper($lang) : ''; } );

            add_filter('site_homepage' , function($homepage){
                return sanitize_file_name($homepage); } );

            //add_filter('site_copyright' , function($copyright){  return pops( $copyright , CONTENT_URL ); } );

            // On récupère le fichier site.txt et on l'encode en utf-8
            $text = encode_utf8( file_get_contents(CONTENT .'/site.txt') );

            // On récupère les champs du fichier site.txt
            foreach( $fields as $field => $value ) {
                if( preg_match('/^[ \t]*' . $field . '[ \t]*:(.*)$/mi', $text , $match ) && $match[1] ) {
                    // On nettoie les champs
                    $tmp = esc_attr( strip_all_tags( trim( $match[1] ) ) );
                    // On applique le filtre au champ associé
                    $tmp = apply_filter( 'site_'.$field , $tmp );
                    // Si le champ n'est pas vide on va le stocker dans la base option
                    if ( !empty($tmp) ) $fields[$field] = $tmp;
                }
                // On stocker dans la base option le champ nettoyer et filter dans la base option
                ( option_exists('site-'.$field) ) ? update_option('site-'.$field , $fields[$field] ) : add_option('site-'.$field , $fields[$field] );
            }
            // On modifie la date de modification du fichier site.txt dans la base option
            ( option_exists('last-config-modified') ) ? update_option('last-config-modified' , $last_config ) : add_option('last-config-modified' , $last_config );
        }
    }
    else {
        /* On créer le document site.txt */

        // Entête du document site.txt
        $text = '# Copyright: © 2015 - miniPops'. PHP_EOL;

        foreach( $fields as $field => $value ){
            // si la valeur n'est pas vide on l'insère dans le fichier site.txt
            if( !empty($value) )
                $text .= PHP_EOL .'----' . PHP_EOL . PHP_EOL . strtolower($field) . ': ' . $value . PHP_EOL;
        }

        // On stock le fichier site.txt
        $config_write = file_put_contents( CONTENT .'/site.txt' , $text , LOCK_EX );

        // Si une erreur survient on bloque l'accès au site
        if ( !$config_write ) return cms_maintenance('Error file write permissions : site configuration !');

        // On change la permission du fichier
        @chmod( CONTENT .'/site.txt' , 0644 );
    }
}
