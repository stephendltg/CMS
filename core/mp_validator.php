<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction Validator
 *
 *
 * @package cms mini POPS
 * @subpackage Validator
 * @version 1
 */

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


