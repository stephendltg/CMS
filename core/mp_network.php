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

            if ( is_ip($ip) )           return $ip;
        }
    }

    return '0.0.0.0';
}



/**
 * Recupère la localisation de l'adresse ip du client
 * @return string
 *
 * 'status' => 'success',
 * 'country' => 'COUNTRY',
 * 'countryCode' => 'COUNTRY CODE',
 * 'region' => 'REGION CODE',
 * 'regionName' => 'REGION NAME',
 * 'city' => 'CITY',
 * 'zip' => ZIP CODE,
 * 'lat' => LATITUDE,
 * 'lon' => LONGITUDE,
 * 'timezone' => 'TIME ZONE',
 * 'isp' => 'ISP NAME',
 * 'org' => 'ORGANIZATION NAME',
 * 'as' => 'AS NUMBER / NAME',
 * 'query' => 'IP ADDRESS USED FOR QUERY'
 *
 * Si erreur:
 *  'status' => 'fail',
 *  'message' => 'ERROR MESSAGE',
 *  'query' => 'IP ADDRESS USED FOR QUERY'
 */
function geo_ip( $args = array() ){

    $args = parse_args( $args, array(
        'ip'   => get_ip_client(),  // $ip = '90.93.196.19';
        'mode' => null
        ) );

    // On peut geo-localiser une url
    if( is_url($args['ip']) )
        $$args['ip'] = parse_url($args['ip'])['host'];
    else
        if( !is_ip($args['ip']) ) return false;

    // Api de géolocalisation 
    $geo_api = apply_filters('geo_api_service', 'http://ip-api.com/php/');
    $geo_api = esc_url_raw($geo_api);

    // Fields de retour
    $fields = 262143; // http://ip-api.com/docs/api:returned_values#field_generator

    //connection au serveur de ip-api.com et recuperation des données 
    if(is_callable('curl_init')) {

        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $geo_api.$args['ip'].'?fields='.$fields);
        curl_setopt($c, CURLOPT_HEADER, false);
        curl_setopt($c, CURLOPT_TIMEOUT, 10);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        $query = @unserialize(curl_exec($c));
        curl_close($c);

    } else {
        $query = @unserialize(file_get_contents($geo_api.$args['ip'].'?fields='.$fields));
    }

    if($query && $query['status'] == 'success') { 

        if( !empty($args['mode']) )
            return isset($query[$args['mode']]) ? $query[$args['mode']] : false;
        else
            return $query;
    }

    return false;
}

/***********************************************/
/*                    url                      */
/***********************************************/

/**
 * Recupère le code l'url ciblé, voir http_response_code pour les codes de retour
 * @param  string    url à vérifier
 * @return integer   code de retour de l'url 200: ok, etc ...
 */
function get_http_response_code( $url, $timeout = 5 ) {

    if ( function_exists('curl_version') ){

        $ch = curl_init();
        $opts = array( CURLOPT_RETURNTRANSFER => true, CURLOPT_URL => $url, CURLOPT_NOBODY => true, CURLOPT_TIMEOUT => $timeout );
        curl_setopt_array($ch, $opts);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode;
    }

    ini_set("default_socket_timeout","05");
    set_time_limit(5);

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

    ini_set("default_socket_timeout","05");
    set_time_limit(5);

    $content = file_get_content($url);

    if( !$code )
        return $content;

    if( $content === false )
        return intval( substr($http_response_header[0], 9, 3) );
}



/***********************************************/
/*                 FTP CLIENT                  */
/***********************************************/


function remote_ftp(){

if (isset($_POST['Submit'])) {
 if (!empty($_FILES['upload']['name'])) {
    $ch = curl_init();
    $localfile = $_FILES['upload']['tmp_name'];
    $fp = fopen($localfile, 'r');
    curl_setopt($ch, CURLOPT_URL, 'ftp://ftp_login:password@ftp.domain.com/'.$_FILES['upload']['name']);
    curl_setopt($ch, CURLOPT_UPLOAD, 1);
    curl_setopt($ch, CURLOPT_INFILE, $fp);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localfile));
    curl_exec ($ch);
    $error_no = curl_errno($ch);
    curl_close ($ch);
        if ($error_no == 0) {
            $error = 'File uploaded succesfully.';
        } else {
            $error = 'File upload error.';
        }
 } else {
        $error = 'Please select a file.';
 }
}

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