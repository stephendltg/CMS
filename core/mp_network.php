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

    if( is_array( $params ) and !empty( $params ) ) {

        if( isset( $params['to'] ) )                                        $to      = $params['to'];
        if( isset( $params['from'] )    && is_email( $params['from'] ) )    $from    = $params['from'];
        if( isset( $params['replyTo'] ) && is_email( $params['replyTo'] ) ) $replyTo = $params['replyTo'];
        if( isset( $params['subject'] ) )                                   $subject = esc_attr( $params['subject'] );
        if( isset( $params['body'] ) )                                      $body    = esc_attr( $params['body'] );

        foreach ( explode( ',' , trim( $to ) ) as $addr_mail )
            if( !is_email( $addr_mail ) ) return false;

        if( empty( $to ) || empty( $subject ) || empty( $body ) ) return false;

        if( empty( $from ) ) {
            $sitename = strtolower( $_SERVER['SERVER_NAME'] );
            if ( substr( $sitename, 0, 4 ) == 'www.' )
             $sitename = substr( $sitename, 4 );
            $from = 'miniPOPS@' . $sitename;
        }

        if( empty( $replyTo ) ) $replyTo = $from;

        if( is_notin( $mode , array('plain','html') ) ) return false;

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

    // IP si internet partagé
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
       return $_SERVER['HTTP_CLIENT_IP'];
    }
    // IP derrière un proxy
    elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
       return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    // Sinon : IP normale
    else {
       return (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
    }
}

