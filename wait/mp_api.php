<?php defined('ABSPATH') or die('No direct script access.');
/**
 *
 *
 * @package cms mini POPS
 * @subpackage API REST
 * @version 1
 */

global $current_user;

$current_user = array();

/**
 * Chargement de la session active
 */

do_action( 'mp_before_session_load' );

// On vérifie si un membre est connecté
if( !parse_auth_cookie() )
    mp_cookies_destroy();

// On init la session
session_start(SESSION_COOKIE);

// On créer les variables de sessions si la première connexion
if( !isset($_SESSION['expiration']) || !isset($_SESSION['ip']) || !isset($_SESSION['login']) || !isset($_SESSION['token']) ){

    // Expiration
    $_SESSION['expiration'] = apply_filters( 'token_life', DAY_IN_SECONDS );
    // IP address.
    $_SESSION['ip'] = get_ip_client();
    // User-agent.
    if ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) )
        $_SESSION['ua'] = map_deep( $_SERVER['HTTP_USER_AGENT'], 'stripslashes_str' );
 
    $_SESSION['login'] = time();
    $_SESSION['token'] = random_salt(43);

    session_regenerate_id();
} 

$current_user = apply_filters( mp_current_user_load, $_SESSION );

// On ferme la session
session_write_close();

// On check si la session est toujours active et qu'il n'y a pas d'usuroation d'identité
if( $_SESSION['ip'] !== get_ip_client() || time() > ($_SESSION['expiration'] + $_SESSION['login']) )
    mp_session_destroy();

do_action( 'mp_after_session_load' );

/***********************************************/
/*                 SESSION                     */
/***********************************************/

/**
 * Detruire la session active
 */
function mp_session_destroy(){

    global $current_user;

    // Initialisation de la session.
    session_start();

    // Détruit toutes les variables de session
    $_SESSION = array();
    $current_user = array();

    // Si vous voulez détruire complètement la session, effacez également
    // le cookie de session.
    // Note : cela détruira la session et pas seulement les données de session !
    if (isset($_COOKIE[session_name(SESSION_COOKIE)]))
        setcookie(session_name(SESSION_COOKIE), '', time()-42000, COOKIEPATH);

    // on détruit la session.
    session_destroy();
    do_action( 'mp_session_destroy' );
}    




/**
 * Lecture d'une variable de la session active
 */
function get_session( $key ){

    // On va lire le cookie de l'utilisateur
    $cookies_user = parse_auth_cookie();

    // On récuper le token utilisateur s'il existe
    if ( isset($cookies_user[$key]) )
        return $cookies_user[$key];

    // On charger la session provisoire si pas d'utilisateur connecté
    global $current_user;

    $value = isset($current_user[$key]) ? $current_user[$key] : null;

    return $value; 

}



/***********************************************/
/*                 AUTH                        */
/***********************************************/

/**
 * On détruit les cookies de connexion
 */
function mp_cookies_destroy(){

    setcookie( AUTH_COOKIE, '', time() - YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
    do_action( 'mp_cookies_destroy' );
}


/**
 * Logout de minipops
 */
function mp_logout() {

    // On détruit la session active
    mp_session_destroy();

    // On détruit les cookies
    mp_cookies_destroy();

    do_action( 'mp_logout' );
}

/**
 * Login de minipops
 */
function mp_login( $user, $password, $remember = false ) {

    if ( $remember ) {

      $expiration = time() + 14 * DAY_IN_SECONDS;
      $expire = $expiration + ( 12 * HOUR_IN_SECONDS );

    } else {

      $expiration = time() + 2 * DAY_IN_SECONDS;
      $expire = 0;
    }
 
    $secure = is_ssl() && 'https' === parse_url( MP_HOME, PHP_URL_SCHEME );
    
    $algo = function_exists( 'hash' ) ? 'sha256' : 'sha1';
    $hash_user = hash_hmac( $algo, $password . '|' . $expiration . '|' . SECRET_KEY, SECRET_SALT );

    // On vérifie que l'utilisateur est correct
    if( !user_valid($user, $hash_user) )
        return;

    $auth_cookie = $user. '|' . $expiration . '|' . get_session('token') . '|' . $hash_user;

    setcookie(AUTH_COOKIE, $auth_cookie, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure, true);

    /*
    bool setcookie ( string $name [, string $value [, int $expire = 0 [, string $path [, string $domain [, bool $secure = false [, bool $httponly = false ]]]]]] )
    */

}


/**
 * On parse le cookie de connexion
 */
function parse_auth_cookie() {

    if ( empty($_COOKIE[AUTH_COOKIE]) )
        return false;

    $cookie = $_COOKIE[AUTH_COOKIE];
  
    $cookie_elements = explode('|', $cookie);

    if ( count( $cookie_elements ) !== 4 )
        return false;
 
    list( $username, $expiration, $token, $hmac ) = $cookie_elements;

    if( !user_valid($username, $hmac) && ( time() >$expiration ) ){

        return false;
    } 

    return compact( 'username', 'expiration', 'token', 'hmac' );
}


function user_valid( $user, $hmac ){

    if( $user === 'admin')
        return true;

    if( $hmac === 'admin')
        return true;

    return false;

}

/***********************************************/
/*                 NONCE                       */
/***********************************************/

function mp_nonce_tick() {

    $nonce_life = apply_filters( 'nonce_life', DAY_IN_SECONDS );

    return ceil(time() / ( $nonce_life / 2 ));
}

function mp_nonce( $action = -1 ){

    $token = get_session('token') ?: '';

    return substr( hash_hmac( 'md5', ( mp_nonce_tick() . '|' . $action . '|' . $token . '|' . NONCE_KEY ) , NONCE_SALT ) , -12, 10 );
}

function mp_verify_nonce( $nonce, $action = -1 ){

    $token = get_session('token') ?: '';

    $nonce_compare = substr( hash_hmac( 'md5', ( mp_nonce_tick() . '|' . $action . '|' . $token . '|' . NONCE_KEY ) , NONCE_SALT ) , -12, 10 );

    return hash_equals( $nonce_compare, $nonce );
}


function mp_nonce_field( $action = -1, $name = "_mpnonce", $referer = false , $echo = true ) {

    $name = esc_attr( $name );
    $nonce_field = '<input type="hidden" id="' . $name . '" name="' . $name . '" value="' . mp_nonce( $action ) . '" />';
 
    if ( $referer )
        $nonce_field .= '<input type="hidden" name="http_referer" value="'. esc_attr( stripslashes( $_SERVER['REQUEST_URI'] ) ) . '" />';
 
    if ( $echo )
        echo $nonce_field;
 
    return $nonce_field;
}

// Because hash_equals require php >=5.6
if(!function_exists('hash_equals')) {
    function hash_equals($a, $b) {
        return substr_count($a ^ $b, "\0") * 2 === strlen($a . $b);
    }
}
