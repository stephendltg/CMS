<?php defined('ABSPATH') or die('No direct script access.');
/**
 * snippet: widget-rss.php
 *
 * @package miniPops
 * @subpackage Rhythmicon
 * @version 1
 */

echo 'test';

the_args('test_1');

/*

function mp_object_cache( $func ){

	$func = (string) $func;

	static $cache = array();

	// Launch Action
    switch ($func) {

        case 'get';
	        if ( empty( $group ) ) $group = 'default';

			if ( isset( $cache[ $group ] ) && ( isset( $cache[ $group ][ $key ] ) || array_key_exists( $key, $cache[ $group ] ) ) ) {

				if ( is_object($cache[$group][$key]) )
					return clone $cache[$group][$key];
				else
					return $cache[$group][$key];
			}

			return false;

        break;

        case 'set';
	        if ( empty( $group ) )
				$group = 'default';

			if ( is_object( $data ) )
				$data = clone $data;

			$cache[$group][$key] = $data;
			return true;

        break;

        case 'delete';
	        unset( $cache[$group][$key] );
			return true;

        break;


        case 'flush';
	        $cache = array();
			return true;
        break;

        default:
            return false;
        }

}


function get_transient( $transient ){

	//return mp_object_cache( 'get', $transient, '_transient_' );

}

function set_transient( $transient, $value, $expiration = 0 ){

	return mp_object_cache( 'set', $transient, $value, '_transient_', (int) $expiration );

}

set_transient( 'background', get_the_args( 'test' ) );


the_args( 'test' );

*/
