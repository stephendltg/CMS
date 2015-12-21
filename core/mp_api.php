<?php defined('ABSPATH') or die('No direct script access.');
/**
 *
 *
 * @package cms mini POPS
 * @subpackage API REST
 * @version 1
 */


/***********************************************/
/*                 API-REST CLIENT             */
/***********************************************/

/* Accès api-rest */
function mp_remote( $url, $token, $method ='GET', $options = array() ){


    $url = esc_url_raw($url);

    if( empty($url) || is_notin( $method, array('GET', 'POST', 'PUT', 'PATCH', 'DELETE') ) || is_same(strlen($token), 0 ) )
        return false;

    $context = array( 'http' => array('ignore_errors' => true, 'method' => 'GET' , 'header' => array('authorization: '.$token) ) );

    if( is_in( $method, array('POST', 'PUT', 'PATCH', 'DELETE') ) )
        $context['http']['method'] = 'POST';

    if( is_in( $method, array('PUT', 'PATCH') ) ){
        if( !empty($options) && is_array($options) ) $context['http']['content'] = $options;
        else return false;
    }

    return $context;
    $context  = stream_context_create( $context );
    if( !file_get_contents( $url, false, $context ) ) return false;
    return json_decode( $response );
}

/***********************************************/
/*                 SECURE                      */
/***********************************************/

function mp_nonce( $action = -1 ){
    $user = get_user_current() ?: 'public';
    return substr( hash_hmac( 'md5', ( DAY_IN_SECONDS . '|' . $action . '|' . $user . '|' . NONCE_KEY ) , NONCE_SALT ) , -12, 10 );
}

function mp_verify_nonce( $nonce, $action = -1 ){
    $user = get_user_current() ?: 'public';
    $nonce_compare = substr( hash_hmac( 'md5', ( DAY_IN_SECONDS . '|' . $action . '|' . $user . '|' . NONCE_KEY ) , NONCE_SALT ) , -12, 10 );
    return hash_equals( $nonce_compare, $nonce );
}

// Because hash_equals require php >=5.6
if(!function_exists('hash_equals')) {
    function hash_equals($a, $b) {
        return substr_count($a ^ $b, "\0") * 2 === strlen($a . $b);
    }
}

/***********************************************/
/*   RECUPERE l'utilisateur connecté           */
/***********************************************/

function get_user_current(){
    return 'admin';
}
