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
@ini_set( 'display_errors', 0 ); // A voir selon la philosophie

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
function mp_check_php_versions() {
    if ( version_compare( phpversion() , "5.2.0", "<" ) ) { // passer à 5.4 pour http_response_code() ligne 337
        $msg =  '<p>Server PHP version ' . phpversion() .
                ' .</p><p>this cms need PHP version 5.4 .</p>';
        cms_maintenance( $msg );
	}
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
function mp_debug_mode() {

    if ( DEBUG ) {

        function _echo( $var, $var_dump = 0 ){
            echo '<pre>';
            if($var_dump) var_dump($var);
            else print_r($var);
            echo '<pre>';
        }

        error_reporting( E_ALL );

        if ( DEBUG_DISPLAY ){
            @ini_set( 'display_errors', 1 );
            @ini_set('error_prepend_string','<div style="background-color:#eee;padding:10px">');
            @ini_set('error_append_string','<br/></div>');
        }
        elseif ( null !== DEBUG_DISPLAY )
            @ini_set( 'display_errors', 0 );

        if ( DEBUG_LOG ) {
            @ini_set( 'log_errors', 1 );
            @ini_set( 'error_log', CONTENT_DIR . '/debug.log' );
        }
    } else {
        error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
    }
    if ( defined( 'API_REST' ) )
        @ini_set( 'display_errors', 0 );
}


/**
 * On créé les repertoires si cms non installé et on verifie les droits en ecriture.
 */
function cms_not_installed() {

    static $one_shot = false;if($one_shot) return;else $one_shot = true; // FUNCTION SECURE

    if ( !is_writable( realpath( ABSPATH ) ) ) cms_maintenance( 'Error directory permissions !' );

    @mkdir( CONTENT_DIR , 0755 , true );

    if ( !is_writable( CONTENT_DIR ) ) cms_maintenance( 'Error directory permissions : '. str_replace( ABSPATH , "" , CONTENT_DIR ) .' !' );

    @mkdir( CONTENT , 0755 , true );
    if ( !is_writable( CONTENT ) ) cms_maintenance( 'Error directory permissions : '. str_replace( ABSPATH , "" , CONTENT ) .' !' );

    @mkdir( THEMES_DIR , 0755 , true );
}


/**
 * Active la mod rewrite si le serveur apache le supporte
 */
function mp_rewrite_rules(){

    static $one_shot = false;if($one_shot) return;else $one_shot = true; // FUNCTION SECURE

    global $is_apache, $is_mod_rewrite;

    //$configuration = array();

    $rewrite = get_option('setting->urlrewrite', true);

    if ($rewrite === 'enable')
        return;

    if($rewrite === 'disable'){
        $is_mod_rewrite = false;
        return;
    }

    if( !$is_apache || !$is_mod_rewrite )
        $rewrite = false;

    // On modifie le fichier htaccess si le mode rewrite n'est pas active et que nous sommes sur serveur apache
    if ( $rewrite === true ) {

        $rewrite = 'enable';

        // On definit le repertoire root
        $root =  str_replace( 'http://' . $_SERVER['HTTP_HOST'] , "" , HOME ) ;
        if ( empty( $root ) ) $root = '/';

        $rules  = "# BEGIN miniPops\n\n";
        $rules .= "# protect the htaccess file\n";
        $rules .= "<files .htaccess>\n\t";
        $rules .= "order allow,deny\n\t";
        $rules .= "deny from all\n";
        $rules .= "</files>\n\n";
        $rules .= "# Disable directory listing\n";
        $rules .= "Options -Indexes\n\n";
        $rules .= "# XSS Protection & iFrame Protection & Mime Security\n";
        $rules .= "<IfModule mod_headers.c>\n\t";
        $rules .= 'Header set X-XSS-Protection "1; mode=block"'."\n\t";
        $rules .= "Header always append X-Frame-Options SAMEORIGIN\n\t"; // DENY, SAMEORIGIN
        $rules .= "Header set X-Content-Type-Options nosniff\n";
        $rules .= "</IfModule>\n\n";
        $rules .= "# Set default charset utf-8\n";
        $rules .= "AddDefaultCharset UTF-8\n\n";
        $rules .= "# Format audio \n";
        $rules .= "AddType audio/ogg  .ogg\n";
        $rules .= "AddType audio/mp3  .mp3\n\n";
        $rules .= "<IfModule mod_rewrite.c>\n\n\t";
        $rules .= "RewriteEngine on\n\n\t";
        $rules .= "# HTTP trace method\n\t";
        $rules .= "RewriteCond %{REQUEST_METHOD} ^TRACE\n\t";
        $rules .= "RewriteRule .* - [F]\n\n\t";
        $rules .= "# if you homepage is ". HOME ."\n\t";
        $rules .= "# RewriteBase $root\n\n\t";
        $rules .= "# block specify the cache\n\t";
        $rules .= "RewriteCond %{REQUEST_METHOD} GET\n\t";
        $rules .= "RewriteCond %{QUERY_STRING} !.*=.*\n\t";
        $rules .= "RewriteCond %{HTTP:Cookie} !^.*(mpops_logged_in_|mpops-postpass_|comment_author_|comment_author_email_).*$\n\t";
        $rules .= "RewriteCond %{HTTPS} off\n\t";
        $rules .= "RewriteCond %{DOCUMENT_ROOT}/cache/%{HTTP_HOST}%{REQUEST_URI}/index.html -f\n\t";
        $rules .= "RewriteRule ^(.*) cache/%{HTTP_HOST}%{REQUEST_URI}/index.html [L]\n\n\t";
        $rules .= "# block specify files in the cache folder from being accessed directly\n\t";
        $rules .= "RewriteRule ^". str_replace( ABSPATH , "" , CONTENT_DIR ) ."/(.*)\.(pl|php|php3|php4|php5|cgi|spl|scgi|fcgi|shtm|shtml|xhtm|xhtml|htm|xml|yml|yaml|md|mdown|gz)$ error [R=301,L]\n\n\t";
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

    } else {

        $rewrite = 'disable';
        $is_mod_rewrite = false;
        $rules = "# BEGIN miniPops\n# END miniPops";
    }

    if ( file_exists( ABSPATH . '.htaccess' ) ) {
        $rule = file_get_contents( ABSPATH . '.htaccess' );
        $marker_begin =  strpos( $rule , '# BEGIN miniPops') ;
        $marker_end =  strpos( $rule , '# END miniPops') + strlen('# END miniPops') ;
        $rules = substr_replace( $rule , $rules , $marker_begin , $marker_end );
    }

    if ( !file_put_contents( ABSPATH . '.htaccess', $rules ) ) cms_maintenance( 'Error file permissions !' );

    update_option('setting->urlrewrite', $rewrite);

}

/**
 * Mise en maintenance de CMS
 *
 */
function cms_maintenance( $message = 'Service Unavailable !' , $subtitle='Service Unavailable' , $http_response_code = 503 ) {
    //ini_set( 'display_errors', 0 );
    header( 'Content-Type: text/html; charset=utf-8' );
    if( function_exists('http_response_code'))
        http_response_code($http_response_code);
    else header( $_SERVER['SERVER_PROTOCOL']." 503 Service Unavailable", true, 503 );
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
function mp_set_internal_encoding() {
    static $one_shot = false;if($one_shot) return;else $one_shot = true; // FUNCTION SECURE
    header_remove( 'x-powered-by' );
    if ( function_exists( 'mb_language' ) ) mb_language( 'uni' );
    if ( function_exists( 'mb_regex_encoding' ) ) mb_regex_encoding( CHARSET );
	if ( function_exists( 'mb_internal_encoding' ) ) mb_internal_encoding( CHARSET );
}


/**
 * Runs just before PHP shuts down execution.
 *
 * @since 1.2.0
 * @access private
 */
function shutdown_action_hook() {

    do_action( 'shutdown' );
}


/**
 * Entête du document
 *
 * On affecte le bon header au document
 */
function get_http_header() {

    header( 'Content-Type: text/html; charset='.CHARSET );
    //http_response_code(200);

    if( is_robots() || is_feed() )
        header( 'Content-Type: text/plain; charset='.CHARSET );

    if( is_sitemap() )
        header( 'Content-Type: text/xml; charset='.CHARSET );

    // Empêcher le crawl des mauvais robots du sitempa.xml
    if( is_sitemap() || is_robots() )
        header("X-Robots-Tag: noindex", true);

    if( is_404() )
        http_response_code(404);
}


/**
 * On supprimer anti slashes des variables $_GET, $_POST, $_COOKIE et $_REQUEST ajouter par la fonction get_magic_quote si elle est active. ( get_magic_quotes_gpc retourne false à partir de php 5.4
 *
 */

function mp_magic_quotes() {
    static $one_shot = false;if($one_shot) return;else $one_shot = true; // FUNCTION SECURE
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
        if ( !$is_mod_rewrite ) { $path = str_replace( array('index.php','index'),'', $_SERVER['PHP_SELF']); }

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

    static $one_shot = false;if($one_shot) return;else $one_shot = true; // FUNCTION SECURE

    if( get_the_blog('theme') ){
        // On liste les thèmes présents dans le repertoire
        $themes = glob( THEMES_DIR .'/', GLOB_MARK|GLOB_ONLYDIR );
        if( is_sup($themes, 0) ){
            foreach( $themes as $theme )
                if( is_same( $theme, THEMES_DIR . '/' . get_the_blog('theme') ) )
                    return $theme;
        }
    }

    return ABSPATH . INC . '/theme';
}


/**
 * Récuperer un champs de configuration du site
 * @return string valeur du champ
 */
function get_the_blog( $field, $default = false ){

    $field = (string) $field;

    $field = strtolower(trim($field));

    switch ($field) {

        case 'copyright':
            $value = get_option('blog->'.$field);
            if( null === $value ) return $default;
            $value = parse_text($value);
            break;
        case 'home':
            $value = esc_url_raw( get_permalink() );
            break;
        case 'rss':
            $value = esc_url_raw( get_permalink('rss', 'feed') );
            break;
        case 'template_url':
            $value = esc_url_raw( TEMPLATEURL );
            break;
        case 'charset':
            $value = CHARSET;
            break;
        case 'version':
            $value = MP_VERSION;
            break;
        case 'language':
            $value = get_the_lang();
            break;
        default:
            $value = get_option('blog->'.$field, $default);
            break;
    }
    return apply_filter( 'get_the_blog_'. $field, $value, $field );
}


/**
 * On init le blog
 */
function init_the_blog(){

    $blog = array(
        'title'=>'miniPops',
        'subtitle'=>'Un site sous miniPops',
        'description'=>'Un site sous miniPops',
        'keywords'=>'minipops, cms, minipopscms',
        'author'=>'stephen deletang',
        'copyright'=>'@2015 -  Propulsé par miniPops',
        'lang'=> lang(),
        'theme'=>'default' );

    $setting = array(
        'urlrewrite'=> true,
        'timezone'=> 'Europe/London',
        'date_format' => 'F j, Y',
        'time_format' => 'g:i a',
        'api_key'=>random_salt(32),
        'api_keysalt'=>random_salt(32) );

    add_option('blog', $blog);
    add_option('setting', $setting);

    add_option('optimize->cache', true);
    add_option('optimize->pages_no_cache', '~');
    add_option('optimize->cache_theme', '~');

    add_option('customize->primary_menu', '~');
}
