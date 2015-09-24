<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction helper
 *
 *
 * @package cms mini POPS
 * @subpackage helper - extend function php
 * @version 1
 */

/***********************************************/
/*               Fonctions                      */
/***********************************************/

function detect_encoding( $string ) {
    $string       = (string) $string;
    if ( function_exists( 'mb_internal_encoding' ) ) {
      return strtolower ( mb_detect_encoding( $string , 'UTF-8, ISO-8859-1, windows-1251') );
    } else {
      foreach( array('utf-8', 'iso-8859-1', 'windows-1251') as $item )
        if( md5( iconv( $item , $item , $string ) ) == md5( $string ) ) return $item;
      return false;
    }
}

function encode_utf8( $string ){
    $string       = (string) $string;
    $encoding = detect_encoding( $string );
    if( is_same( $encoding , 'utf-8') ) return $string;
    return iconv( $encoding , 'utf-8' , $string );
}

function lang(){
    $lang = explode(',' , $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    return substr($lang[0],0,2);
}


/***********************************************/
/*                Fonctions memory             */
/***********************************************/

/**
 * convertisseur pour mémoire
 * http://php.net/manual/fr/function.memory-get-usage.php
 * Argument $size ( valeur en octet )
 * @return string
 */
function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}

/**
 * get_cms_peak_memory
 * Retourne la mémoire maximale alloué par php
* Argument $real_usage = true pour obtenir la mémoire allouée par le système
 * @return string
 */
function get_cms_peak_memory( $real_usage = false ) {
    return convert( memory_get_peak_usage( $real_usage ) );
}

/**
 * get_cms_memory
 * Retourne la mémoire alloué par php
 * Argument $real_usage = true pour obtenir la mémoire allouée par le système
 * @return string
 */
function get_cms_memory( $real_usage = false ) {
    return convert( memory_get_usage( $real_usage ) );
}


/***********************************************/
/*                Fonctions file               */
/***********************************************/

/**
* Supprimer un fichier
* @param  string    $filename     Nom du fichier - chemin absolu
*/
function delete( $file ) {
    $file       = (string) $file;
    if ( file_exists( $file ) && !is_dir( $file ) ) return unlink( $file );
    return false;
}

/**
 * Encode en base64 pour un usage embarque en data uri(css,html) si fichier sinon encodage seulement
 * @param  $data : $file(chemin absolu) ou $string
 * @return string|boolean
 */
function datauri_encode( $data ) {
    if( is_string( $data ) ){
        if ( file_exists( $data ) && !is_dir( $data ) ){
	       $mime_type = mime_content_type( $data );
	       return 'data:' . $mime_type . ';base64,' . base64_encode( file_get_contents( $data ) );
        }
        return 'data:' . $mime_type . ';base64,' . base64_encode( $data );
    }
    return false;
}

/***********************************************/
/*                Fonctions esc                */
/***********************************************/

/**
 * Enlèves les caractères dangereux et Encode les signes < > " ' en valeur html pur.
 * Usage: stockage dans base json
 */
function esc_attr( $value ) {
    if ( is_string( $value ) ) {
        // On supprime les caractères invisibles
        $char = array('/%0[0-8bcef]/', '/%1[0-9a-f]/', '/[\x00-\x08]/', '/\x0b/', '/\x0c/', '/[\x0e-\x1f]/');
        do {
            $cleaned = $value;
            $value = preg_replace( $char , '' , $value );
        } while ( $cleaned != $value );

        $value = esc_html( $value );
    }
    if ( is_array( $value ) ) array_walk( $value , function( &$v ){ $v = esc_attr( $v ); } );
    return $value;
}


/**
 * Encode les signes < > " ' en valeur html.
 */
function esc_html( $string ) {
    $string = (string) $string;
    $flags = ENT_QUOTES;
    if( defined('ENT_SUBSTITUTE') ) $flags |= ENT_SUBSTITUTE;
    return htmlspecialchars( $string , $flags , CHARSET );
}

/**
 * DECODE esc_html.
 */
function html( $string ) {
    $string = (string) $string;
    $flags = ENT_QUOTES;
    return htmlspecialchars_decode( $string , $flags );
}


/**
 * Sanitialize url to prevent XSS - Cross-site scripting
 * $_GET = array_map('esc_url', $_GET);
 */
function esc_url( $url ){
    $url = (string) $url;
    $url = trim($url);
    $url = rawurldecode($url);
    $url = str_replace(array('--','&quot;','!','@','#','$','%','^','*','(',')','+','{','}','|',':','"','<','>',
                                  '[',']','\\',';',"'",',','*','+','~','`','laquo','raquo',']>','&#8216;','&#8217;','&#8220;','&#8221;','&#8211;','&#8212;'),
                            array('-','-','','','','','','','','','','','','','','','','','','','','','','','','','','',''),
                            $url);
    $url = str_replace('--', '-', $url);
    $url = rtrim($url, "-");
    $url = str_replace('..', '', $url);
    $url = str_replace('//', '', $url);
    $url = preg_replace('/^\//', '', $url);
    $url = preg_replace('/^\./', '', $url);
    return $url;
}

/**
 * Encode les signes < > " ' en valeur xml.
 */
function esc_xml( $string ) {
    $string = (string) $string;
    if( defined('ENT_XML1') ) return htmlspecialchars( $string , ENT_QUOTES | ENT_XML1, CHARSET );
    return str_replace( '&#039;', '&apos;', htmlspecialchars( $string , ENT_QUOTES , CHARSET ) );
}


/**
 * Nettoyer les urls.
 */

function esc_url_raw( $url ) {
            $good_protocol = false;
            $protocols = array( 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp', 'webcal' );
            if ( '' == $url ) return $url;
            $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url );
            if ( 0 !== stripos( $url, 'mailto:' ) ) {
                    $strip = array('%0d', '%0a', '%0D', '%0A');
                    $url = str_replace($strip, '', $url);
            }
            $url = str_replace(';//', '://', $url);

            if ( strpos($url, ':') === false &&
                ! in_array( $url[0], array( '/', '#', '?' ) ) &&
                ! preg_match('/^[a-z0-9-]+?\.php/i', $url) )
                    $url = 'http://' . $url;
            if ( '/' === $url[0] ) {
                 $good_protocol = false;
            } else {
                foreach ($protocols as $protocol) {
                    if ( 0 === stripos( $url, $protocol ) ) $good_protocol = true;
                }
            }
            return ($good_protocol) ? $url : '';
}


/**
 * Supprime toutes les balises ( style et script y comprit ).
 */
function strip_all_tags( $string ) {
    $string = (string) $string;
    $string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
    return trim( strip_tags($string) );
}


/***********************************************/
/*               Fonctions network             */
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

        foreach ( explode( ',' , trim( $to ) ) as $addr_mail ) if( !is_email( $addr_mail ) ) return false;
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


/***********************************************/
/*                fonctions validation         */
/***********************************************/

function size( $value ) {
  if( is_numeric($value) )  return $value;
  if( is_string($value) )   return strlen( trim($value) );
  if( is_array($value) )    return count($value);
  if( file_exists($value) ) return filesize($value) / 1024;
}

function is_match( $value , $regex ) {
    return preg_match( $regex , $value ) == true;
}

function is_alpha( $value ){
    return is_match( $value , '/^([a-z])+$/i' );
}

function is_alphanum( $value ){
    return is_match( $value , '/^[a-z0-9]+$/i' );
}

function is_between( $value , $min , $max) {
    return is_min( $value , $min ) and is_max( $value , $max );
}

function is_date( $value ){
    $time = strtotime( $value );
    if(!$time) return false;
    $year  = date('Y', $time);
    $month = date('m', $time);
    $day   = date('d', $time);
    return checkdate( $month , $day , $year );
}

function is_different( $value , $other ){
    return $value !== $other;
}

function is_email( $value ){
    return filter_var( $value , FILTER_VALIDATE_EMAIL ) !== false;
}

function is_filename( $value ){
    return is_match( $value , '/^[a-z0-9@._-]+$/i' ) and is_min( $value , 2 );
}

function is_in( $value , $in ){
    return in_array( $value , $in , true );
}

function is_intgr( $value ){
    return filter_var ( $value , FILTER_VALIDATE_INT ) !== false;
}

function is_ip( $value ){
    return filter_var( $value , FILTER_VALIDATE_IP ) !== false;
}

function is_max( $value , $max ){
    return size( $value ) <= $max;
}

function is_min( $value , $min ){
    return size( $value ) >= $min;
}

function is_num( $value ){
    return is_numeric( $value );
}

function is_notin( $value , $notin ){
    return !is_in( $value , $notin );
}

function is_same( $value , $other ){
    return $value === $other;
}

function is_size( $value , $size_to_compare ){
    return size($value) == $size_to_compare;
}

function is_url( $value ){
    $regex = '_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iu';
    return is_match( $value , $regex );
}



/***********************************************/
/*                fonctions sanitialize        */
/***********************************************/

function remove_accent( $string ){
    $string       = (string) $string;
    $string = encode_utf8( $string );
    $char_not_clean = array('/@/','/À/','/Á/','/Â/','/Ã/','/Ä/','/Å/','/Ç/','/È/','/É/','/Ê/','/Ë/','/Ì/','/Í/','/Î/','/Ï/','/Ò/','/Ó/','/Ô/','/Õ/','/Ö/','/Ù/','/Ú/','/Û/','/Ü/','/Ý/','/à/','/á/','/â/','/ã/','/ä/','/å/','/ç/','/è/','/é/','/ê/','/ë/','/ì/','/í/','/î/','/ï/','/ð/','/ò/','/ó/','/ô/','/õ/','/ö/','/ù/','/ú/','/û/','/ü/','/ý/','/ÿ/', '/©/');
    $clean = array('a','a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','y','a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','o','o','o','o','o','o','u','u','u','u','y','y','copy');
    $string = preg_replace( $char_not_clean , $clean , $string );
    $string = utf8_decode($string);
    $string = preg_replace('/\?/', '', $string);
    $string = strtolower($string);
    return $string;
}


function sanitize_allspecialschars( $string ) {
    $string       = (string) $string;
    $special_chars = array( "[", "]", "/", "\\", "<", ">", "\"", "{", "}", chr(0) );
    $special_chars = apply_filter( 'sanitize_allspecialschars_char' , $special_chars );
    $special_chars = preg_replace( "#\x{00a0}#siu", ' ', $special_chars );
    $string = str_replace( $special_chars, '', $string );
    $string = str_replace( '%20', ' ', $string );
    $string = preg_replace( '/[\r\n\t]+/', ' ', $string );
    return $string;
}

function sanitize_file_name( $filename ) {
    $filename       = (string) $filename;
    //thanks wordpress
	$special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", chr(0) );
	$filename = apply_filter( 'sanitize_file_name_char' , $filename );
    $filename = preg_replace( "#\x{00a0}#siu", ' ', $filename );
	$filename = str_replace( $special_chars, '', $filename );
	$filename = str_replace( array( '%20', '+' ), '-', $filename );
	$filename = preg_replace( '/[\r\n\t -]+/', '-', $filename );
	$filename = trim( $filename, '.-_' );
    $filename = remove_accent( $filename );
    return $filename;
}

function sanitize_words( $words ) {
    $words       = (string) $words;
    //thanks wordpress
    $special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", ".", "_", "-", chr(0) );
    $words = apply_filter( 'sanitize_words_char' , $words );
    $words = preg_replace( "#\x{00a0}#siu", ' ', $words );
    $words = str_replace( $special_chars, '', $words );
    $words = str_replace( array( '%20', '+' ), ' ', $words );
    $words = preg_replace( '/[\r\n\t ]+/', ' ', $words );
    return $words;
}
