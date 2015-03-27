<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction indispensable au chargement du cms.
 *
 *
 * @package cms
 * @subpackage load-functions
 * @version 1
 */


// On cache les erreurs php
//error_reporting(0);

/**
 * On vérifie la version de php utilisé si non compatible on fait un die
 */
function check_php_versions() {

	$php_version = phpversion();

    if ( version_compare( $php_version, "5.3.0", "<" ) ) {

        $msg =  '<p>Votre serveur utilise la version '.
                $php_version.
                ' de PHP.</p><p>Ce cms a besoin au minimum de la version 5.3 .</p>';

        // Appel page maintenance
        maintenance ( 'error PHP', 'details error:', $msg);
	}
}


/**
 * On met à l'heure le serveur selon la constante definit sinon on utilise l'heure du serveur par defaut
 */
function setting_the_time() {
    if ( defined ( 'TIME_ZONE' ) ){
        date_default_timezone_set( TIME_ZONE );
    } else {
        date_default_timezone_set( 'UTC' );
    }
}


/**
 * Demarrage du timer.
 *
 * @access private
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
function timer_stop( $display = 0, $precision = 3 ) {
	global $timestart, $timeend;
	$timeend = microtime( true );
	$timetotal = $timeend - $timestart;
	$r = number_format( $timetotal, $precision );
	if ( $display )
		echo $r;
	return $r;
}

/**
 * Active le mode debug si constante debug est true
 *
 * @access private
 */
function debug_mode() {
	if ( DEBUG ) {
		error_reporting( E_ALL );
	} else {
		error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
	}
}

/**
 * On redirige si le cms n'est pas installé.
 *
 * @access private
 */
function cms_not_installed() {

    if ( !defined ('XMLDB') || !file_exists( XMLDB .'/options.table.xml') ) {

        // Tableau d'erreurs
        $errors = array();

        // Repertoire à analyser
        $dir_array = array('content');

        // On vérife que le mod rewrite existe
        if ( function_exists('apache_get_modules') ) {
            if ( ! in_array( 'mod_rewrite', apache_get_modules() ) ) {
                $errors['mod_rewrite'] = ' n\'est pas installé !';
            }
        }
        else {
            $errors['apache'] = ' est absent!';
        }

        // On vérifie la persmission du repertoire du cms
        if ( ! is_writable(ABSPATH) ) {
            $errors[ ABSPATH ] = ' n\'est pas accessible en écriture, il sera impossible de créer un fichier config !';
        }


        // On vérifie la persmission du fichier config
        if ( file_exists ( ABSPATH . 'config.php' ) && !is_writable('config.php') ) {
            $errors['config.php'] = ' n\'est pas accessible en écriture !';
        }

        // On vérifie la permission du fichier htaccess
        if ( file_exists('.htacess') && !is_writable('.htaccess') ) {
        $errors['htaccess'] = ' n\'est pas accessible en écriture !';
        }

        // On vérifier les permissions des repertoire à analyser
        foreach ( $dir_array as $dir ) {
            if ( !is_writable($dir.'/') ) {
                $errors[$dir] = ' n\'est pas accessible en écriture !';
            }
        }

        // Si le serveur est bien configurer on peut installer le CMS sinon on informe l'utilisateur
        if ( empty($errors) ) {
            if ( file_exists('core/setup-config.php') ) {
            $host  = $_SERVER['HTTP_HOST'];
            $uri   = rtrim( dirname( $_SERVER['PHP_SELF'] ) , '/\\' );
            header("Location: http://$host$uri/core/setup-config.php");
            die();
            } else {
                maintenance ('Service indisponible', 'Details error :', 'Votre site est corrompu !');
            }

        }

        // Si le serveur n'est pas bien configurer on met le site en maintenance avec les infos adéquat
        $msg ='<ul>';
        foreach( $errors as $k=>$v){
            $msg .= "<li>$k : $v</li>";
        }
        $msg .='</ul>';

        // Appel page maintenance
        maintenance ( 'Installation abort', 'Details error :', $msg );
    }
}


/**
 * Listing de must plugins dans le répertoire MU_PLUGIN_DIR ( a créer)
 *
 * @return array trié alaphabétiquement des mu-plugins
 */
function get_mu_plugins() {
	$mu_plugins = array();
	if ( !is_dir( MU_PLUGIN_DIR ) )
		return $mu_plugins;
	if ( ! $dh = opendir( MU_PLUGIN_DIR ) )
		return $mu_plugins;
	while ( ( $plugin = readdir( $dh ) ) !== false ) {
		if ( substr( $plugin, -4 ) == '.php' )
			$mu_plugins[] = MU_PLUGIN_DIR . '/' . $plugin;
	}
	closedir( $dh );
	sort( $mu_plugins );

	return $mu_plugins;
}


/**
 * Mise en maintenance de CMS
 *
 */
function maintenance( $title = 'maintenance' , $subtitle='Site en maintenance', $message = 'Oups, Nous sommes désolé !' ) {

    $protocol = $_SERVER["SERVER_PROTOCOL"];
	if ( 'HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol )
        $protocol = 'HTTP/1.0';
    header( "$protocol 503 Service Unavailable", true, 503 );
    header( 'Content-Type: text/html; charset=utf-8' );
	header( 'Retry-After: 600' );
?>
	<!DOCTYPE html>
	<html>
	<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?></title>
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
        <h1><?php echo $title ?></h1>
        <div>
            <h2><?php echo $subtitle ?></h2>
            <?php echo $message ?>
        </div>
		<?php $message ?>
	</body>
	</html>
<?php
	die();
}



/**
 * Retrieve an array of active and valid plugin files.
 *
 * While upgrading or installing WordPress, no plugins are returned.
 *
 * The default directory is wp-content/plugins. To change the default
 * directory manually, define `WP_PLUGIN_DIR` and `WP_PLUGIN_URL`
 * in wp-config.php.
 *
 * @since 3.0.0
 * @access private
 *
 * @return array Files.
 */
function get_active_and_valid_plugins() {
	$plugins = array();
	$active_plugins = get_option( 'active_plugins'  );

	if ( empty( $active_plugins ) )
		return $plugins;

	foreach ( $active_plugins as $plugin ) {
		if ( ! validate_file( $plugin ) // $plugin must validate as file
			&& '.php' == substr( $plugin, -4 ) // $plugin must end with '.php'
			&& file_exists( PLUGIN_DIR . '/' . $plugin ) // $plugin must exist
			)
		$plugins[] = PLUGIN_DIR . '/' . $plugin;
	}
	return $plugins;
}

/**
 * Encodage du document
 *
 * On vérifier l'encodage de la config si non précisé on encode en utf-8 ( voir default-constant.php )
 * @access private
 */
function set_internal_encoding() {
    header('Content-Type: text/html; charset='.CHARSET);
	if ( function_exists( 'mb_internal_encoding' ) ) {
        mb_internal_encoding( CHARSET );
	}
}

/**
 * On supprimer anti slashes des variables $_GET, $_POST, $_COOKIE et $_REQUEST ajouter par la fonction get_magic_quote si elle est active. ( get_magic_quotes_gpc retourne false à partir de php 5.4
 *
 * @access private
 */

function magic_quotes() {
	if (get_magic_quotes_gpc()) {
        function stripslashesGPC(&$value) { $value = stripslashes($value); }
        array_walk_recursive($_GET, 'stripslashesGPC');
        array_walk_recursive($_POST, 'stripslashesGPC');
        array_walk_recursive($_COOKIE, 'stripslashesGPC');
        array_walk_recursive($_REQUEST, 'stripslashesGPC');
    }
}
