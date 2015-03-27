<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Gestion des options du CMS
 *
 * @package     cms
 * @subpackage  options
 * @version 1
 */

set_transient()
get_transient()


set-cache ( $name , $value , $group )
get-cache ($ name , $group )


/**
 * get_transient
 *
 *  <code>
 *
 *  </code>
 *
 * @return string
 */
function get_transient( $transient ) {

    global $object_cache;

    // On redefinit la variable $option
    $transient = (string) $transient;

    $option_name = xmldb('options')->select( '[name="'.$option.'"]' , null );

    return isset( $option_name['value'] ) ? $option_name['value'] : '';
}


/**
 * set_transient
 *
 *  <code>
 *
 *
 *  </code>
 *
 * @return string
 */
function set_transient( $transient , $value , $expiration =0 ) {

    global $object_cache;

    // On redefinit la variable $option
    $transient = (string) $transient;
    $expiration = (int) $expiration;


    $option_name = xmldb('options')->select( '[name="'.$option.'"]' , null );

    return isset( $option_name['value'] ) ? $option_name['value'] : '';
}
