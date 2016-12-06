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
/*              Fonctions globales             */
/***********************************************/

/**
 * Function de compatibilité pour ancienne version de PHP
 */
if (!function_exists('http_response_code')) {

    function http_response_code($code = NULL) { 

        $prev_code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);

        if ($code === NULL)
            return $prev_code;

        switch ($code) {
            case 100: $text = 'Continue'; break;
            case 101: $text = 'Switching Protocols'; break;
            case 200: $text = 'OK'; break;
            case 201: $text = 'Created'; break;
            case 202: $text = 'Accepted'; break;
            case 203: $text = 'Non-Authoritative Information'; break;
            case 204: $text = 'No Content'; break;
            case 205: $text = 'Reset Content'; break;
            case 206: $text = 'Partial Content'; break;
            case 300: $text = 'Multiple Choices'; break;
            case 301: $text = 'Moved Permanently'; break;
            case 302: $text = 'Moved Temporarily'; break;
            case 303: $text = 'See Other'; break;
            case 304: $text = 'Not Modified'; break;
            case 305: $text = 'Use Proxy'; break;
            case 400: $text = 'Bad Request'; break;
            case 401: $text = 'Unauthorized'; break;
            case 402: $text = 'Payment Required'; break;
            case 403: $text = 'Forbidden'; break;
            case 404: $text = 'Not Found'; break;
            case 405: $text = 'Method Not Allowed'; break;
            case 406: $text = 'Not Acceptable'; break;
            case 407: $text = 'Proxy Authentication Required'; break;
            case 408: $text = 'Request Time-out'; break;
            case 409: $text = 'Conflict'; break;
            case 410: $text = 'Gone'; break;
            case 411: $text = 'Length Required'; break;
            case 412: $text = 'Precondition Failed'; break;
            case 413: $text = 'Request Entity Too Large'; break;
            case 414: $text = 'Request-URI Too Large'; break;
            case 415: $text = 'Unsupported Media Type'; break;
            case 500: $text = 'Internal Server Error'; break;
            case 501: $text = 'Not Implemented'; break;
            case 502: $text = 'Bad Gateway'; break;
            case 503: $text = 'Service Unavailable'; break;
            case 504: $text = 'Gateway Time-out'; break;
            case 505: $text = 'HTTP Version not supported'; break;
            default:
                _doing_it_wrong( __FUNCTION__, 'Unknown http status code ' . $code);
                // exit('Unknown http status code "' . htmlentities($code) . '"');
                return $prev_code;
        }

        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

        header($protocol . ' ' . $code . ' ' . $text);

        $GLOBALS['http_response_code'] = $code;

        // original function always returns the previous or current code
        return $prev_code;
    }
}


/**
 * On vérifie la version de php utilisé si non compatible on fait un die
 */
function mp_check_php_versions() {

    if ( version_compare( phpversion() , "5.2.0", "<" ) )
        cms_maintenance( '<p>Server PHP version ' . phpversion() . ' .</p><p>this cms need PHP version 5.2 .</p>' );
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
            @ini_set( 'error_log', MP_CONTENT_DIR . '/debug.log' );
        }
    } else {
        error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
    }
    if ( defined( 'API_REST' ) )
        @ini_set( 'display_errors', 0 );
}

/**
 * Fonction de tracing error pour mode debug
 */
function _echo( $var, $var_dump = 0 ){

    if (!DEBUG) return null;

    echo '<pre>';
    if($var_dump) var_dump($var);
    else print_r($var);
    echo '<pre>';

}

/* Afficahge d'inforamtions si une erreur survient (mode debug doit être actif
* __FILE__, __DIR__, __FUNCTION__, __CLASS__, __METHOD__, __LINE__, __NAMESPACE__, __TRAIT__
* @param string $function The function that was called.
* @param string $message  A message explaining what has been done incorrectly.
*/
function _doing_it_wrong( $function, $message ) {
 
    if ( DEBUG ) {

        if ( function_exists( '__' ) ) {
            trigger_error( sprintf( __( '%1$s was called <strong>incorrectly</strong>. %2$s' ), $function, $message ) );
        } else {
            trigger_error( sprintf( '%1$s was called <strong>incorrectly</strong>. %2$s', $function, $message ) );
        }
    }
}

/**
 * On créé les repertoires si cms non installé et on verifie les droits en ecriture.
 */
function cms_not_installed() {


    static $one_shot = false;if($one_shot) return;else $one_shot = true; // FUNCTION SECURE

    if ( !is_writable( ABSPATH ) ) 
        cms_maintenance( 'Error directory permissions !' );

    if( isset($GLOBALS['mp_config']) ){

        $GLOBALS['mp_config'] = "<?php
/**
 * La configuration de votre cms.
 *
 * @package CMS mini POPS
 * @subpackage config
 * @version 1
 */


/** Definit le mode de debuggage pour développement. */
define ( 'DEBUG' , false );

/** Definit l'url du site. */
define( 'MP_HOME', '". guess_url(). "' );";

    file_put_content( ABSPATH . 'mp-config.php', $GLOBALS['mp_config'] );
    unset($GLOBALS['mp_config']);
    }

    @mkdir( MP_CONTENT_DIR , 0755 , true );
    if ( !is_writable( MP_CONTENT_DIR ) ) 
        cms_maintenance( 'Error directory permissions : '.  MP_CONTENT_DIR .' !' );

    @mkdir( MP_PAGES_DIR , 0755 , true );
    if ( !is_writable( MP_PAGES_DIR ) ) 
        cms_maintenance( 'Error directory permissions : '. MP_PAGES_DIR .' !' );

    @mkdir( MP_THEMES_DIR , 0755 , true );
}


/**
 * Active la mod rewrite si le serveur apache le supporte
 */
function mp_rewrite_rules(){

    static $one_shot = false;if($one_shot) return;else $one_shot = true; // FUNCTION SECURE

    global $is_apache, $is_mod_rewrite;

    // Constante pour reforcer la réécriture d'url
    if( FORCE_RELOCATE )
        update_option('setting->urlrewrite', true);

    // On récupère la variable dans option
    $rewrite = get_option('setting->urlrewrite', $is_mod_rewrite);

    // Si mod rewrite déjà activé on arrête la fonction et on définit la constante IS_REWRITE_RULES
    if ( $rewrite === 'enable' ){
        define('IS_REWRITE_RULES', true);
        return;
    }

    // Si mod rewrite a déjà été testé invalide désactive le toggle de réécriture et on arrête la fonction. on passe la constante IS_REWRITE_RULES
    if($rewrite === 'disable'){
        define('IS_REWRITE_RULES', false);
        return;
    }

    // Si pas un serveur apache et pas de mod rewrite actif on affecte la variable stocker dans option à false
    // if( !$is_apache || !$is_mod_rewrite )
    //    $rewrite = false;

    // Entête à tout document htaccess

    /**********************
        Protection 
    ***********************/

    $header  = '# SECURITY:[FILES]'. PHP_EOL;
    $header .= '# htaccess protect'. PHP_EOL;
    $header .= '<Files .htaccess>'. PHP_EOL;
    $header .= '    order allow,deny'. PHP_EOL;
    $header .= '    deny from all'. PHP_EOL;
    $header .= '</Files>'. PHP_EOL;
    $header .= '# block listing files'. PHP_EOL;
    $header .= 'Options All -Indexes'. PHP_EOL;
    $header .= '# Force index.php the others are blocked' . PHP_EOL;
    $header .= 'DirectoryIndex index.php'. PHP_EOL;
    $header .= '# block all files begin by index' . PHP_EOL;
    $header .= '<Files ~ "^(index)\.(p?s?x?htm?|txt|aspx?|cfml?|cgi|pl|php[3-9]|jsp|xml)$">'. PHP_EOL;
    $header .= ' order allow,deny'. PHP_EOL;
    $header .= ' deny from all'. PHP_EOL;
    $header .= '</Files>'. PHP_EOL. PHP_EOL;

    $header .= '# SECURITY:[SERVEUR]'. PHP_EOL;
    $header .= '# Masquer les informations du serveur'. PHP_EOL;
    $header .= 'ServerSignature Off'. PHP_EOL;
    $header .= '# Activation du suivi des liens symboliques'. PHP_EOL;
    $header .= 'Options +FollowSymLinks'. PHP_EOL . PHP_EOL;

    $header .= '# SECURITY:[XSS Protection]'. PHP_EOL;
    $header .= '# XSS Protection & iFrame Protection & Mime Security'. PHP_EOL;
    $header .= '<IfModule mod_headers.c>'. PHP_EOL;
    $header .= '    Header set X-XSS-Protection "1; mode=block"'. PHP_EOL;
    $header .= '    Header always append X-Frame-Options DENY'. PHP_EOL; /* DENY, SAMEORIGIN */
    $header .= '    Header set X-Content-Type-Options nosniff'. PHP_EOL;
    $header .= '    Header unset X-Powered-By'. PHP_EOL;
    $header .= '</IfModule>'. PHP_EOL . PHP_EOL;

    /**********************
        Firewall 
    ***********************/
    $header .= '# 6G FIREWALL/BLACKLIST'. PHP_EOL;
    $header .= '# @ https://perishablepress.com/6g/'. PHP_EOL . PHP_EOL;

    /*
        6G:[QUERY STRINGS]
        Cette partie vérifie que l'URL demandée par le client n'a pas été faite pour profiter de failles sur votre serveur web ou votre code PHP. 
        Si c'est le cas, il va interdire l'accès à la page grace à (RewriteRule .* - [F]), ou le [F] signifie que l'accès n'est pas autorisé.
    */
    $header .= '# 6G:[QUERY STRINGS]'. PHP_EOL;
    $header .= '<IfModule mod_rewrite.c>'. PHP_EOL;
    $header .= '    RewriteEngine On'. PHP_EOL;
    $header .= '    RewriteCond %{QUERY_STRING} (eval\() [NC,OR]'. PHP_EOL;
    $header .= '    RewriteCond %{QUERY_STRING} (127\.0\.0\.1) [NC,OR]'. PHP_EOL;
    $header .= '    RewriteCond %{QUERY_STRING} ([a-z0-9]{2000}) [NC,OR]'. PHP_EOL;
    $header .= '    RewriteCond %{QUERY_STRING} (javascript:)(.*)(;) [NC,OR]'. PHP_EOL;
    $header .= '    RewriteCond %{QUERY_STRING} (base64_encode)(.*)(\() [NC,OR]'. PHP_EOL;
    $header .= '    RewriteCond %{QUERY_STRING} (GLOBALS|REQUEST)(=|\[|%) [NC,OR]'. PHP_EOL;
    $header .= '    RewriteCond %{QUERY_STRING} (<|%3C)(.*)script(.*)(>|%3) [NC,OR]'. PHP_EOL;
    $header .= '    RewriteCond %{QUERY_STRING} (\\|\.\.\.|\.\./|~|`|<|>|\|) [NC,OR]'. PHP_EOL;
    $header .= '    RewriteCond %{QUERY_STRING} (boot\.ini|etc/passwd|self/environ) [NC,OR]'. PHP_EOL;
    $header .= '    RewriteCond %{QUERY_STRING} (thumbs?(_editor|open)?|tim(thumb)?)\.php [NC,OR]'. PHP_EOL;
    $header .= '    RewriteCond %{QUERY_STRING} (\'|\")(.*)(drop|insert|md5|select|union) [NC]'. PHP_EOL;
    $header .= '    RewriteRule .* - [F]'. PHP_EOL;
    $header .= '</IfModule>'. PHP_EOL . PHP_EOL;

    /*
        6G: [REQUEST METHOD]
        Cette partie teste les méthodes HTTP envoyées. 
        Les navigateurs ne prenant en charge que GET et POST, toutes les autres se retrouvent bloquées avec la même méthode que le bloc précédent.
    */
    $header .= '# 6G:[REQUEST METHOD]'. PHP_EOL;
    $header .= '<IfModule mod_rewrite.c>'. PHP_EOL;
    $header .= '    RewriteCond %{REQUEST_METHOD} ^(connect|debug|delete|move|put|trace|track) [NC]'. PHP_EOL;
    $header .= '    RewriteRule .* - [F]'. PHP_EOL;
    $header .= '</IfModule>'. PHP_EOL . PHP_EOL;

    /*
        6G:[REFERRERS]
        Ce bloc est là pour bloquer le trafique provenant de certains referers (c'est-à-dire les referents, les sites d'où proviennent les visiteurs).
        Si vos êtes soumis a dû référer spam, c'est à cet endroit que vous pourrez lister les adresses de spammeur.
    */
    // On va cherche la liste des adresse ip à bannir
    $bad_referrers = file_get_content_array( INC . '/data/bad-referrers.data');

    if( !empty($bad_referrers) ){

        $header .= '# 6G:[REFERRERS]'. PHP_EOL;
        $header .= '<IfModule mod_rewrite.c>'. PHP_EOL;
        $header .= '    RewriteCond %{HTTP_REFERER} ([a-z0-9]{2000}) [NC,OR]'. PHP_EOL;
        $header .= '    RewriteCond %{HTTP_REFERER} ('. join('|', $bad_referrers) .') [NC]'. PHP_EOL;
        $header .= '    RewriteRule .* - [F]'. PHP_EOL;
        $header .= '</IfModule>'. PHP_EOL . PHP_EOL;
    }
    unset($bad_referrers);

    /*
        6G:[REQUEST STRINGS]
        Ce bloc est là pour bloquer les appels les plus fréquents fait pas des Bots essayant de déterminer le type de site que vous possédez.
    */
    $header .= '# 6G:[REQUEST STRINGS]'. PHP_EOL;
    $header .= '<IfModule mod_alias.c>'. PHP_EOL;
    $header .= '    RedirectMatch 403 (?i)([a-z0-9]{2000})'. PHP_EOL;
    $header .= '    RedirectMatch 403 (?i)(https?|ftp|php):/'. PHP_EOL;
    $header .= '    RedirectMatch 403 (?i)(base64_encode)(.*)(\()'. PHP_EOL;
    $header .= '    RedirectMatch 403 (?i)(=\\\'|=\\%27|/\\\'/?)\.'. PHP_EOL;
    $header .= '    RedirectMatch 403 (?i)/(\$(\&)?|\*|\"|\.|,|&|&amp;?)/?$'. PHP_EOL;
    $header .= '    RedirectMatch 403 (?i)(\{0\}|\(/\(|\.\.\.|\+\+\+|\\\"\\\")'. PHP_EOL;
    $header .= '    RedirectMatch 403 (?i)(~|`|<|>|:|;|,|%|\\|\s|\{|\}|\[|\]|\|)'. PHP_EOL;
    $header .= '    RedirectMatch 403 (?i)/(=|\$&|_mm|cgi-|etc/passwd|muieblack)'. PHP_EOL;
    $header .= '    RedirectMatch 403 (?i)(&pws=0|_vti_|\(null\)|\{\$itemURL\}|echo(.*)kae|etc/passwd|eval\(|self/environ)'. PHP_EOL;
    $header .= '    RedirectMatch 403 (?i)\.(aspx?|bash|bak?|cfg|cgi|dll|exe|git|hg|ini|jsp|log|mdb|out|sql|svn|swp|tar|rar|rdf)$'. PHP_EOL;
    $header .= '    RedirectMatch 403 (?i)/(^$|(mp-)?config|mobiquo|phpinfo|shell|sqlpatch|thumb|thumb_editor|thumbopen|timthumb|webshell)\.php'. PHP_EOL;
    $header .= '</IfModule>'. PHP_EOL . PHP_EOL;

    /*
        6G:[USER AGENTS]
        Cette ligne bloque les bots dont le nom est dans la grande liste que vous pouvez voir.
        Notez la présence de archive.org en tout premier. 
        J'ai personnellement autorise ce bot, car je trouve que la présence d'une copie de son site sur la waybackmachine n'est pas un mal, au contraire.
    */
    // On va cherche la liste des adresse ip à bannir
    $bad_bots = file_get_content_array( INC . '/data/bad-bots.data');

    if( !empty($bad_bots) ){

        $header .= '# 6G:[USER AGENTS]'. PHP_EOL;
        $header .= '<IfModule mod_setenvif.c>'. PHP_EOL;
        $header .= '    SetEnvIfNoCase User-Agent ([a-z0-9]{2000}) bad_bot'. PHP_EOL;
        $header .= '    SetEnvIfNoCase User-Agent ('. join('|', $bad_bots )  .') bad_bot'. PHP_EOL;
        $header .= '    # Apache < 2.3'. PHP_EOL;
        $header .= '    <IfModule !mod_authz_core.c>'. PHP_EOL;
        $header .= '        Order Allow,Deny'. PHP_EOL;
        $header .= '        Allow from All'. PHP_EOL;
        $header .= '        Deny from env=bad_bot'. PHP_EOL;
        $header .= '    </IfModule>'. PHP_EOL;
        $header .= '    # Apache >= 2.3'. PHP_EOL;
        $header .= '    <IfModule mod_authz_core.c>'. PHP_EOL;
        $header .= '        <RequireAll>'. PHP_EOL;
        $header .= '            Require all Granted'. PHP_EOL;
        $header .= '            Require not env bad_bot'. PHP_EOL;
        $header .= '        </RequireAll>'. PHP_EOL;
        $header .= '    </IfModule>'. PHP_EOL;
        $header .= '</IfModule>'. PHP_EOL . PHP_EOL;
    }
    unset($bad_bots);

    /*
        6G:[BAD IPS]
        Enfin, ce bloc actuellement vide vous permet de bloquer des ips spécifiques.
        Si vous subissez les assauts d'un bot ou une tentative de DDOS depuis une IP, c'est à cet endroit qu'il faudra l'inserer !
    */

    // On va cherche la liste des adresse ip à bannir
    $bad_ips = file_get_content_array( INC . '/data/bad-ips.data');

    if( !empty($bad_ips) ){

        $header .= '# 6G:[BAD IPS]'. PHP_EOL;
        $header .= '<Limit GET HEAD OPTIONS POST PUT>'. PHP_EOL;
        $header .= '    Order Allow,Deny'. PHP_EOL;
        foreach( $bad_ips as $ip)
            $header .= '    Deny from '. $ip . PHP_EOL;
        $header .= '    Allow from All'. PHP_EOL;
        $header .= '</Limit>'. PHP_EOL . PHP_EOL;
    }
    unset($bad_ips);


    /**********************
        Hotlinking
    ***********************/
    $header .= '# SECURITY:[hotlinking]'. PHP_EOL;
    $header .= '# Désactiver le hotlinking de vos images'. PHP_EOL;
    $header .= 'RewriteEngine On'. PHP_EOL;
    $header .= 'RewriteCond %{HTTP_REFERER} !^$'. PHP_EOL;
    $header .= 'RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?'. $_SERVER['HTTP_HOST'] .' [NC]'. PHP_EOL;

    // On va cherche la liste des domain autorise à faire des liens sur les images
    $hotlinking_whitelist = file_get_content_array( INC . '/data/hotlinking-whitelist.data');

    if( !empty($hotlinking_whitelist) ){
        foreach ($hotlinking_whitelist as $domain)
            $header .= 'RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?'. $domain .' [NC]'. PHP_EOL;
    }
    unset($hotlinking_whitelist);

    $header .= 'RewriteRule \.(jpg|jpeg|png|gif)$ http://fakeimg.pl/400x200/?text=:-)_Oups_! [NC,R,L]'. PHP_EOL . PHP_EOL;


    /**********************
        Force charset
    ***********************/
    $header .= '# CHARSET:[Set default charset utf-8]'. PHP_EOL;
    $header .= 'AddDefaultCharset UTF-8'. PHP_EOL . PHP_EOL;

    /**********************
        Addtype
    ***********************/
    $header .= '# TYPE:[Format file]'. PHP_EOL;
    $header .= 'AddType audio/ogg  .ogg'. PHP_EOL;
    $header .= 'AddType audio/mp3  .mp3'. PHP_EOL;
    $header .= 'AddEncoding gzip svgz'. PHP_EOL;
    $header .= 'AddType image/svg+xml svg svgz'. PHP_EOL . PHP_EOL;

    /**********************
        Mise en cache
    ***********************/
    $header .= '# CACHE:[Expires headers (for better cache control)]'. PHP_EOL;
    $header .= '<IfModule mod_expires.c>'. PHP_EOL;
    $header .= '    ExpiresActive on'. PHP_EOL;

    $header .= '    # Perhaps better to whitelist expires rules? Perhaps.'. PHP_EOL;
    $header .= '    ExpiresDefault "access plus 1 month "'. PHP_EOL;

    $header .= '    # cache.appcache needs re-requests in FF 3.6 (thanks Remy ~Introducing HTML5)'. PHP_EOL;
    $header .= '    ExpiresByType text/cache-manifest "access plus 0 seconds "'. PHP_EOL;

    $header .= '    # Your document html'. PHP_EOL;
    $header .= '    ExpiresByType text/html  "access plus 0 seconds "'. PHP_EOL;

    $header .= '    # Data'. PHP_EOL;
    $header .= '    ExpiresByType text/xml  "access plus 0 seconds"'. PHP_EOL;
    $header .= '    ExpiresByType application/xml  "access plus 0 seconds"'. PHP_EOL;
    $header .= '    ExpiresByType application/json  "access plus 0 seconds"'. PHP_EOL;

    $header .= '    # Feed'. PHP_EOL;
    $header .= '    ExpiresByType application/rss+xml "access plus 1 hour"'. PHP_EOL;
    $header .= '    ExpiresByType application/atom+xml "access plus 1 hour"'. PHP_EOL;

    $header .= '    # Favicon (cannot be renamed)'. PHP_EOL;
    $header .= '    ExpiresByType image/x-icon "access plus 1 week"'. PHP_EOL;

    $header .= '    # Media: images, video, audio'. PHP_EOL;
    $header .= '    ExpiresByType image/gif "access plus 1 month "'. PHP_EOL;
    $header .= '    ExpiresByType image/png "access plus 1 month "'. PHP_EOL;
    $header .= '    ExpiresByType image/jpg "access plus 1 month "'. PHP_EOL;
    $header .= '    ExpiresByType image/jpeg "access plus 1 month "'. PHP_EOL;
    $header .= '    ExpiresByType video/ogg "access plus 1 month "'. PHP_EOL;
    $header .= '    ExpiresByType audio/ogg "access plus 1 month "'. PHP_EOL;
    $header .= '    ExpiresByType video/mp4 "access plus 1 month "'. PHP_EOL;
    $header .= '    ExpiresByType video/webm "access plus 1 month "'. PHP_EOL;

    $header .= '    # HTC files (css3pie)'. PHP_EOL;
    $header .= '    ExpiresByType text/x-component "access plus 1 month "'. PHP_EOL;

    $header .= '    # Webfonts'. PHP_EOL;
    $header .= '    ExpiresByType application/x-font-ttf "access plus 1 month"'. PHP_EOL;
    $header .= '    ExpiresByType font/opentype "access plus 1 month"'. PHP_EOL;
    $header .= '    ExpiresByType application/x-font-woff "access plus 1 month"'. PHP_EOL;
    $header .= '    ExpiresByType application/x-font-woff2 "access plus 1 month"'. PHP_EOL;
    $header .= '    ExpiresByType image/svg+xml "access plus 1 month"'. PHP_EOL;
    $header .= '    ExpiresByType application/vnd.ms-fontobject "access plus 1 month"'. PHP_EOL;

    $header .= '    # CSS and JavaScript'. PHP_EOL;
    $header .= '    ExpiresByType text/css "access plus 1 year "'. PHP_EOL;
    $header .= '    ExpiresByType application/javascript "access plus 1 year "'. PHP_EOL;

    $header .= '</IfModule>'. PHP_EOL . PHP_EOL;
    $header .= '<IfModule mod_headers.c>'. PHP_EOL;
    $header .= '    <filesMatch "\.(ico|jpe?g|png|gif|swf)$">'. PHP_EOL;
    $header .= '        Header set Cache-Control "public"'. PHP_EOL;
    $header .= '    </filesMatch>'. PHP_EOL;
    $header .= '    <filesMatch "\.(css)$">'. PHP_EOL;
    $header .= '        Header set Cache-Control "public"'. PHP_EOL;
    $header .= '    </filesMatch>'. PHP_EOL;
    $header .= '    <filesMatch "\.(js)$">'. PHP_EOL;
    $header .= '        Header set Cache-Control "private"'. PHP_EOL;
    $header .= '    </filesMatch>'. PHP_EOL;
    $header .= '    <filesMatch "\.(x?html?|php)$">'. PHP_EOL;
    $header .= '        Header set Cache-Control "private, must-revalidate"'. PHP_EOL;
    $header .= '    </filesMatch>'. PHP_EOL;
    $header .= '</IfModule>'. PHP_EOL . PHP_EOL;

    /**********************
        Compression des fichiers
    ***********************/
    $header .= '# COMPRESS:[Compression static file]'. PHP_EOL;
    $header .= '<IfModule mod_deflate.c>'. PHP_EOL;
    // $header .= '    DeflateCompressionLevel 9'. PHP_EOL . PHP_EOL; // Ne fonctionne pas sur macos
    $header .= '    # On force deflate sur certains mauvais headers'. PHP_EOL;
    $header .= '    # # developer.yahoo.com/blogs/ydn/posts/2010/12/pushing-beyond-gzipping/'. PHP_EOL;
    $header .= '    <IfModule mod_setenvif.c>'. PHP_EOL;
    $header .= '        <IfModule mod_headers.c>'. PHP_EOL;
    $header .= '            SetEnvIfNoCase ^(Accept-EncodXng|X-cept-Encoding|X{15}|~{15}|-{15})$ ^((gzip|deflate)\s*,?\s*)+|[X~-]{4,13}$ HAVE_Accept-Encoding'. PHP_EOL;
    $header .= '            RequestHeader append Accept-Encoding "gzip,deflate" env=HAVE_Accept-Encoding'. PHP_EOL;
    $header .= '        </IfModule>'. PHP_EOL;
    $header .= '    </IfModule>'. PHP_EOL . PHP_EOL;

    $header .= '    # On supprime les Etags'. PHP_EOL;
    $header .= '    <IfModule mod_headers.c>'. PHP_EOL;
    $header .= '        Header unset ETag'. PHP_EOL;
    $header .= '    </IfModule>'. PHP_EOL;
    $header .= '    FileETag None'. PHP_EOL . PHP_EOL;

    $header .= '    AddOutputFilterByType DEFLATE application/atom_xml'. PHP_EOL;
    $header .= '    AddOutputFilterByType DEFLATE application/rss+xml'. PHP_EOL;
    $header .= '    AddOutputFilterByType DEFLATE application/xml'. PHP_EOL;
    $header .= '    AddOutputFilterByType DEFLATE application/x-httpd-php'. PHP_EOL;
    $header .= '    AddOutputFilterByType DEFLATE application/x-httpd-fastphp'. PHP_EOL;
    $header .= '    AddOutputFilterByType DEFLATE application/x-httpd-eruby'. PHP_EOL;
    $header .= '    AddOutputFilterByType DEFLATE application/vnd.google-earth.kml+xml'. PHP_EOL;
    $header .= '    AddOutputFilterByType DEFLATE application/javascript'. PHP_EOL;
    $header .= '    AddOutputFilterByType DEFLATE application/x-javascript'. PHP_EOL;
    $header .= '    AddOutputFilterByType DEFLATE application/xhtml+xml'. PHP_EOL;
    $header .= '    AddOutputFilterByType DEFLATE application/json'. PHP_EOL;
    $header .= '    AddOutputFilterByType DEFLATE text/html'. PHP_EOL;
    $header .= '    AddOutputFilterByType DEFLATE text/xml'. PHP_EOL;
    $header .= '    AddOutputFilterByType DEFLATE text/css'. PHP_EOL;
    $header .= '    AddOutputFilterByType DEFLATE text/plain'. PHP_EOL;
    $header .= '    AddOutputFilterByType DEFLATE image/svg+xml svg svgz'. PHP_EOL;
    $header .= '    AddOutputFilterByType DEFLATE text/javascript'. PHP_EOL;
    $header .= '    AddOutputFilterByType DEFLATE font/opentype'. PHP_EOL . PHP_EOL;

    $header .= '    # On s\'assure que certains types de fichiers ne sont pas pris en compte'. PHP_EOL;
    $header .= '    SetEnvIfNoCase Request_URI \.(?:exe|t?gz|zip|bz2|sit|rar|gif|jpe?g|svg|png|avi|mpg|swf|flv|mov|mp3|ogv|mp4|pdf|webm|ogg|rm)$ no-gzip dont-vary'. PHP_EOL . PHP_EOL;

    $header .= '    BrowserMatch ^Mozilla/4 gzip-only-text/html'. PHP_EOL;
    $header .= '    BrowserMatch ^Mozilla/4\.0[678] no-gzip'. PHP_EOL . PHP_EOL;

    $header .= '    # A décommenter car empêche flowplayer de se charger sur Internet Explorer'. PHP_EOL;
    $header .= '    BrowserMatch \bMSIE !no-gzip !gzip-only-text/html'. PHP_EOL . PHP_EOL;

    $header .= '    # On s\'assure que les proxies envoient le bon contenu'. PHP_EOL;
    $header .= '    Header append Vary User-Agent env=!dont-vary'. PHP_EOL;
    $header .= '</IfModule>'. PHP_EOL . PHP_EOL;

    /**********************
        Gestion si fichiers sur serveur differents tel que police et css
    ***********************/
    $header .= '<IfModule mod_headers.c>'. PHP_EOL;
    $header .= '<FilesMatch "\.(ttf|ttc|otf|eot|woff|woff2|font.css|css)$">'. PHP_EOL;
    $header .= 'Header set Access-Control-Allow-Origin "*"'. PHP_EOL;
    $header .= '</FilesMatch>'. PHP_EOL;
    $header .= '</IfModule>'. PHP_EOL . PHP_EOL;


    // On affecte le header au règle apache pour le domaine
    $rules   = apply_filters('mp_apache_rules_header', $header);

    // On modifie le fichier htaccess si le mode rewrite n'est pas active et que nous sommes sur serveur apache
    if ( $rewrite === true ) {

        // On affecte la variable qui sera stocker dans la table option
        define('IS_REWRITE_RULES', !$is_apache ? false : true );

        // On definit le repertoire root
        $root =  str_replace( 'http://' . $_SERVER['HTTP_HOST'] , "" , guess_url() ) ;
        if ( empty( $root ) ) $root = '/';


        /**********************
            REWRITE RULE
        ***********************/
        $rules .= '# MINIPOPS:[Rewrite url]' . PHP_EOL;
        $rules .= '<IfModule mod_rewrite.c>'. PHP_EOL;
        $rules .= ' RewriteEngine on'. PHP_EOL . PHP_EOL;
        $rules .= ' # if you homepage is '. MP_HOME . PHP_EOL;
        $rules .= ' # RewriteBase '. $root . PHP_EOL. PHP_EOL;

        // On récupère les extensions à bloquer
        $bad_exts = file_get_content_array( INC . '/data/bad-exts.data');

        if( 0 === strpos( MP_CONTENT_DIR, ABSPATH) ){

            $rules .= ' # SECURITY:[FILES]'. PHP_EOL;
            if( !empty($bad_exts) )
                $rules .= 'RewriteRule ^'. str_replace( ABSPATH , '' , MP_CONTENT_DIR ) .'/(.*)\.('. join('|', $bad_exts) .')$ error [R=301,L]'. PHP_EOL . PHP_EOL;

        } else {
            
            // Règles apache si MP_CONTENT_DIR est en dehors du répertoire ABSPATH
            $rules_content_dir  = $header;
            $rules_content_dir .= '# SECURITY:[SERVEUR]'. PHP_EOL;
            $rules_content_dir .= '# disable ExecCGI'. PHP_EOL;
            $rules_content_dir .= 'OPTIONS -ExecCGI  -Indexes'. PHP_EOL . PHP_EOL;

            if( !empty($bad_exts) ){
                $rules_content_dir .= '# SECURITY:[FILES]'. PHP_EOL;
                $rules_content_dir .= '<Files ~ "\.('. join('|', $bad_exts) .')$">'. PHP_EOL;
                $rules_content_dir .= ' Deny from all'. PHP_EOL;
                $rules_content_dir .= '</Files>'. PHP_EOL;
            }

            // On tente de proteger les fichiers
            @file_marker_contents( MP_CONTENT_DIR . '/.htaccess', $rules_content_dir);
        }

        unset($bad_exts);

        // Suite du bloc principale
        $rules .= ' # SECURITY:[FILES-CORE]'. PHP_EOL;
        $rules .= ' RewriteRule ^core/(.*) error [R=301,L]'. PHP_EOL;
        //$rules .= "RewriteCond %{REQUEST_FILENAME} !-f\n\t";
        //$rules .= "RewriteCond %{REQUEST_FILENAME} !-d\n\t";
        //$rules .= "RewriteRule ^panel/(.*) panel/index.php [L]\n\n\t";
        $rules .= ' RewriteCond %{REQUEST_FILENAME} !-f'. PHP_EOL;
        $rules .= ' RewriteCond %{REQUEST_FILENAME} !-d'. PHP_EOL;
        $rules .= ' RewriteRule ^(.*) index.php [L]'. PHP_EOL . PHP_EOL;
        $rules .= ' # Update code bellow for SEO improvements'. PHP_EOL;
        $rules .= ' # Redirect 301 /index ' . MP_HOME . '/' . PHP_EOL. PHP_EOL;
        $rules .= '</IfModule>';



    } else {

        define('IS_REWRITE_RULES', false);
    }

    if( !$is_apache )
        $rules = '';

    // On tent d'écrire les règles principale 
    if( !file_marker_contents(ABSPATH . '.htaccess', $rules) )
         _doing_it_wrong( __FUNCTION__, 'Error file permission .htaccess.' );

    if( file_exists( ABSPATH . 'php.ini' ) )
        file_marker_contents( ABSPATH . 'php.ini', 'expose_php = on');
    
    // On stock la valeur de réécriture dans option
    update_option('setting->urlrewrite', IS_REWRITE_RULES ? 'enable' : 'disable' );

}


/**
 * Mise en maintenance de CMS
 *
 */
function cms_maintenance( $message = 'Service Unavailable !' , $subtitle='Service Unavailable' , $http_response_code = 503 ) {

    //ini_set( 'display_errors', 0 );
    header( 'Content-Type: text/html; charset=utf-8' );
    http_response_code($http_response_code);
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
    if ( function_exists( 'mb_language' ) )             mb_language( 'uni' );
    if ( function_exists( 'mb_regex_encoding' ) )       mb_regex_encoding( CHARSET );
	if ( function_exists( 'mb_internal_encoding' ) )    mb_internal_encoding( CHARSET );
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

    if( is_404() )
        http_response_code(404);
    else
        http_response_code(200);
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
        if ( '1' == $_SERVER['HTTPS'] )              return true;

    } elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {

        return true; 
    }

    return false;
}

/**
 * Guess the URL for the site.
 * thanks wordpress
 *
 * @return string The guessed URL.
 */
function guess_url() {


	if ( defined('MP_HOME') && '' != MP_HOME ) { 
    
        $url = MP_HOME; 
    
    } else {

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
function get_template_directory( $mode = 'directory' ){

    static $path = 1;

    if( get_the_blog('theme') && $path === 1 ){
        
        // On liste les thèmes présents dans le repertoire
        $themes = glob( MP_THEMES_DIR .'/*', GLOB_ONLYDIR );

        if( is_sup($themes, 0) ){

            foreach( $themes as $theme ){

                if( is_same( $theme, MP_THEMES_DIR . '/' . get_the_blog('theme') ) )
                    $path = get_the_blog('theme');
            }
        }
    }

    // On évite de boucler sur la recherche du thème
    if( $path === 1 )
        $path = false;

    // On charge le thème du core par défaut
    switch ($mode) {

        case 'url':
            return !$path ? MP_HOME . '/'. INC . '/theme' : MP_CONTENT_URL .'/themes/'. $path;
            break;
        case 'path':
            return !$path ? INC . '/theme' : $path;
            break;
        default:
            return !$path ? ABSPATH . INC . '/theme' : MP_THEMES_DIR . '/'. $path;
            break;
    }

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
            $value = esc_url_raw( MP_TEMPLATE_URL );
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
        case 'logo':
            $value = get_the_image('name=logo&orderby=type&max=5&order=desc', 'uri');
            break;   
        default:
            $value = get_option('blog->'.$field, $default);
            break;
    }
    
    return apply_filters( 'get_the_blog_'. $field, $value, $field );
}


/**
 * On init le blog
 */
function init_the_blog(){

    global $is_mod_rewrite;

    $blog = array(
        'title'=>'miniPops',
        'subtitle'=>'Un site sous miniPops',
        'description'=>'Un site sous miniPops',
        'keywords'=>'minipops, cms, minipopscms',
        'author'=>'stephen deletang',
        'author_email'=>'~',
        'copyright'=>'@2015 -  Propulsé par miniPops',
        'lang'=> lang(),
        'theme'=>'default',
        'robots'=>'index' );

    $setting = array(
        'urlrewrite'=> $is_mod_rewrite,
        'timezone'=> 'Europe/London',
        'date_format' => 'F j, Y',
        'time_format' => 'g:i a',
        'api_key'=>random_salt(32),
        'api_keysalt'=>random_salt(32) );

    $plugins = array(
        'active_plugins'=>'~');

    add_option('blog', $blog);
    add_option('setting', $setting);
    add_option('plugins', $plugins);
    add_option('customize->primary_menu', '~');

    // Execution de tâche journalière
    do_event( time(), 'daily', 'callback');
}
