<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Gestion du cache du CMS (limiter les requêtes xml donc l'accès disque)
 *
 * @package     cms
 * @subpackage  cache
 * @version 1
 */


// On initialise le cache global
global $object_cache;

if ( ! isset( $object_cache ) )
	$object_cache = array( 'autoincremente' => 0 );


/**
 * get_cache
 *
 *  <code>
 *     get_cache ( 'link' , 'links' );
 *  </code>
 *
 * @return value si ok sinon false
 */
function get_cache( $key , $group = 'default' ) {

    global $object_cache;

    // On redefinit les variables
    $key = (string) $key;
    $group = (string) $group;

    if ( !array_key_exists( $group , $object_cache )  )
        return false;

    if ( !array_key_exists( $key , $object_cache[$group] )  )
        return false;

    return $object_cache[$group][$key];
}


/**
 * set_cache
 *
 *  <code>
 *     set_cache ( 'link' , 'http://google.com' , 'links' );
 *
 *  </code>
 *
 * @return boolean
 */
function set_cache( $key , $data = null , $group ='default' ) {

    global $object_cache;

    // On redefinit les variables
    $key = (string) $key;
    $group = (string) $group;

    if ( isset($data) ) {
        $object_cache['autoincremente'] += 1; // Nombre d'operation sur cache
        $object_cache[$group][$key] = $data; // On stocke le cache par groupe puis par clé
        ksort( $object_cache[$group] ); // On trie le cache
        return $data;
    }
    return false;
}


/**
 * remove_cache
 *
 *  <code>
 *     remove_cache ( 'link' , 'links' );
 *  </code>
 *
 * @return boolean
 */
function remove_cache( $key , $group = 'default' ) {

    global $object_cache;

    // On redefinit les variables
    $key = (string) $key;
    $group = (string) $group;

    if ( !array_key_exists( $group , $object_cache )  )
        return false;

    if ( !array_key_exists( $key , $object_cache[$group] )  )
        return false;

    $object_cache['autoincremente'] += 1; // Nombre d'operation sur cache
    unset ( $object_cache[$group][$key] ); // Suppression du cache sélectionner
    return true;
}

/**
 * reset_cache
 *
 *  <code>
 *     reset_cache ();
 *  </code>
 *
 * @return boolean
 */
function reset_cache() {

    global $object_cache;

    foreach( $object_cache as $group => $value){
        unset ($object_cache[$group]);
    }
    return true;
}
