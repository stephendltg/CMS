<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction Network
 *
 *
 * @package cms mini POPS
 * @subpackage Network
 * @version 1
 */


/***********************************************/
/*               Fonctions email               */
/***********************************************/


/**
 * Envoie d'email à partir d'un tableau passer en paramètre
 * @param  array    parametres de l'email
 * @mode  $string   Type d'email ( html ou plain )
 * @return boolean
 */
function email( $params = null , $mode = 'plain' ){

    $params = parse_args( $params );

    if( is_array( $params ) and !empty( $params ) ) {

        if( isset($params['to']) )                                      $to      = $params['to'];
        if( isset($params['from'])    && is_email($params['from']) )    $from    = $params['from'];
        if( isset($params['replyTo']) && is_email($params['replyTo']) ) $replyTo = $params['replyTo'];
        if( isset($params['subject']) )                                 $subject = esc_attr($params['subject']);
        if( isset( $params['body'] ) )                                  $body    = esc_attr($params['body']);

        foreach ( explode( ',', trim($to) ) as $addr_mail )
            if( !is_email($addr_mail) ) return false;

        if( empty($to) || empty($subject) || empty($body) ) 
            return false;

        if( empty($from) ) {

            $sitename = strtolower($_SERVER['SERVER_NAME']);

            if ( substr( $sitename, 0, 4 ) == 'www.' )
                $sitename = substr( $sitename, 4 );

            $from = 'miniPOPS@' . $sitename;
        }

        if( empty($replyTo) ) 
            $replyTo = $from;

        if( is_notin( $mode , array('plain','html') ) ) 
            return false;

        $headers = array(
            'From: ' . $from,
            'Reply-To: ' . $replyTo,
            'Return-Path: ' . $replyTo,
            'Message-ID: <' . time() . '-' . $from . '>',
            'X-Mailer: PHP v' . phpversion(),
            'Content-Type: text/'. $mode .'; charset=utf-8',
            'Content-Transfer-Encoding: 8bit',
        );

        return mail( $to , encode_utf8( $subject ) , encode_utf8( $body ) , implode( PHP_EOL , $headers ) );
    }
    return false;
}


/***********************************************/
/*               Fonctions IP                  */
/***********************************************/

/**
 * Recupère l'adresse ip du client
 * @return string
 */
function get_ip_client() {

    // Find the best order ////.
    $keys = array(
        'HTTP_CF_CONNECTING_IP', // CF = CloudFlare.
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_X_REAL_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR',
    );

    foreach ( $keys as $key ) {

        if ( array_key_exists( $key, $_SERVER ) ) {
            
            $ip = explode( ',', $_SERVER[ $key ] );
            $ip = end( $ip );

            if ( is_ip($ip) )
                return $ip;
        }
    }

    return '0.0.0.0';
}


/***********************************************/
/*                    url                      */
/***********************************************/

/**
 * Recupère le code l'url ciblé, voir http_response_code pour les codes de retour
 * @param  string    url à vérifier
 * @return integer   code de retour de l'url 200: ok, etc ...
 */
function get_http_response_code( $url ) {

    $headers = get_headers($url);
    return intval( substr($headers[0], 9, 3) );
}

/**
 * Recupère le contenu d'une url
 * @param  string    url
 * @param  bool      code = true : retourne le code erreur générer par l'url
 * @return integer   code de retour de l'url 200: ok, etc ...
 */
function url_get_content($url, $code = false) {

    $content = file_get_content($url);

    if( !$code )
        return $content;

    if( $content === false )
        return intval( substr($http_response_header[0], 9, 3) );
}


/***********************************************/
/*                 API-REST CLIENT             */
/***********************************************/

/**
 * Requete api rest
 * @param  string    url
 * @param  string    token
 * @param  string    method
 * @param  array     options
 * @return string    json
 */
function mp_remote( $url, $token, $method ='GET', $options = array() ){


    $url = esc_url_raw($url);

    if( empty($url) || is_notin( $method, array('GET', 'POST', 'PUT', 'PATCH', 'DELETE') ) || is_same(strlen($token), 0 ) )
        return false;

    $context = array( 'http' => array('ignore_errors' => true, 'method' => 'GET' , 'header' => array('authorization: '.$token) ) );

    if( is_in( $method, array('POST', 'PUT', 'PATCH', 'DELETE') ) )
        $context['http']['method'] = 'POST';

    if( is_in( $method, array('PUT', 'PATCH') ) ){
        
        if( !empty($options) && is_array($options) ) 
            $context['http']['content'] = $options;
        else return false;
    }

    $context  = stream_context_create( $context );

    if( !file_get_contents( $url, false, $context ) ) 
        return false;

    return json_decode( $response );
}