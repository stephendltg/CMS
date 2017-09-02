<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction Validator
 *
 *
 * @package cms mini POPS
 * @subpackage Validator
 * @version 1
 */


global $is_lynx, $is_gecko, $is_winIE, $is_macIE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone, $is_IE, $is_edge,
       $is_apache, $is_IIS, $is_iis7, $is_nginx,
       $is_mod_rewrite;



/***********************************************/
/*                Browser detection            */
/***********************************************/

$is_lynx = $is_gecko = $is_winIE = $is_macIE = $is_opera = $is_NS4 = $is_safari = $is_chrome = $is_iphone = $is_edge = false;
  
if( isset($_SERVER['HTTP_USER_AGENT']) ) {

    if( strpos($_SERVER['HTTP_USER_AGENT'], 'Lynx') !== false )
        $is_lynx = true;

    elseif( strpos( $_SERVER['HTTP_USER_AGENT'], 'Edge' ) !== false )
        $is_edge = true;

    elseif( stripos($_SERVER['HTTP_USER_AGENT'], 'chrome') !== false )
        $is_chrome = true;

    elseif( stripos($_SERVER['HTTP_USER_AGENT'], 'safari') !== false )
        $is_safari = true;

    elseif( ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false ) && strpos($_SERVER['HTTP_USER_AGENT'], 'Win') !== false )
        $is_winIE = true;

    elseif( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Mac') !== false )
        $is_macIE = true;

    elseif( strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko') !== false )
        $is_gecko = true;

    elseif( strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== false )
        $is_opera = true;

    elseif( strpos($_SERVER['HTTP_USER_AGENT'], 'Nav') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla/4.') !== false )
        $is_NS4 = true;
}
  
if ( $is_safari && stripos($_SERVER['HTTP_USER_AGENT'], 'mobile') !== false )
    $is_iphone = true;  

$is_IE = ( $is_macIE || $is_winIE );



/***********************************************/
/*                Serveur detection            */
/***********************************************/

$is_apache = ( strpos( $_SERVER['SERVER_SOFTWARE'], 'Apache' ) !== false || strpos( $_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false );

$is_nginx = ( strpos( $_SERVER['SERVER_SOFTWARE'], 'nginx' ) !== false );

$is_IIS = !$is_apache && (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false || strpos($_SERVER['SERVER_SOFTWARE'], 'ExpressionDevServer') !== false);

$is_iis7 = $is_IIS && intval( substr( $_SERVER['SERVER_SOFTWARE'], strpos( $_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS/' ) + 14 ) ) >= 7;



/***********************************************/
/*          module apache detection            */
/***********************************************/


if (function_exists('apache_get_modules'))
  $is_mod_rewrite = in_array('mod_rewrite', apache_get_modules() );
else
  $is_mod_rewrite =  getenv('HTTP_MOD_REWRITE')=='On' ? true : false;




/***********************************************/
/*                 mobile detection            */
/***********************************************/

// Mobile detection
function is_mobile() {

    if( empty($_SERVER['HTTP_USER_AGENT']) ) {
        $is_mobile = false;

    } elseif( strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile')  !== false // many mobile devices (all iPhone, iPad, etc.)
           || strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
           || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/')   !== false
           || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle')  !== false
           || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
           || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
           || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false 
    ){
        $is_mobile = true;

    } else {
        $is_mobile = false;
    }
 
    return $is_mobile;
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

function is( $value ){
    return !empty($value);
}

function isnot( $value ){
    return !isset( $value );
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
    return is_match( $value , '/^[a-z0-9@._-]+$/i' ) and is_min( $value , 3 );
}

function is_validate_file( $file ) {

    if ( false !== strpos( $file, '..' ) )
        return 1;

    if ( false !== strpos( $file, './' ) )
        return 1;

    if ( false !== strpos( $file, '@' ) )
        return 1;

    if (':' == substr( $file, 1, 1 ) )
        return 2;

    if ( !is_filename($file) )
        return 3;

    return 0;
}

function is_in( $value , $in ){
    return in_array( $value , $in , true );
}

function is_intgr( $value ){
    return filter_var ( $value , FILTER_VALIDATE_INT ) !== false;
}

function is_ip( $value ){
    $value = trim($value);
    return filter_var( $value , FILTER_VALIDATE_IP ) !== false;
}

function is_low( $value , $low ){
    return size($value) < $low;
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

function is_sup( $value , $is_sup ){
    return size($value) > $is_sup;
}

function is_url( $value ){
    $regex = '_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iu';
    return is_match( $value , $regex );
}


function is_serialized($value, &$result = null) {

    // Bit of a give away this one
    if ( !is_string($value) || empty($value) )
        return false;

    // Serialized false, return true. unserialize() returns false on an
    // invalid string or it could return false if the string is serialized
    // false, eliminate that possibility.
    if ($value === 'b:0;') {
        $result = false;
        return true;
    }

    $length = strlen($value);
    $end    = '';

    switch ($value[0]) 
    {
        case 's':
            if ($value[$length - 2] !== '"')
                return false;
        case 'b':
        case 'i':
        case 'd':
            // This looks odd but it is quicker than isset()ing
            $end .= ';';
        case 'a':
        case 'O':
            $end .= '}';

            if ($value[1] !== ':')
                return false;

            switch ($value[2]) {
                case 0:
                case 1:
                case 2:
                case 3:
                case 4:
                case 5:
                case 6:
                case 7:
                case 8:
                case 9:
                break;

                default:
                    return false;
            }

        case 'N':
            $end .= ';';

            if ($value[$length - 1] !== $end[0])
                return false;
        break;

        default:
            return false;
    }

    if (($result = @unserialize($value)) === false){
        $result = null;
        return false;
    }
    return true;
}

