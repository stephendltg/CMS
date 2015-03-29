<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Gestion du cache du CMS (limiter les requetes xml)
 *
 * @package     cms
 * @subpackage  cache
 * @version 1
 */


// On initialise les transcients global
global $object_cache, $object_merge ;

if ( ! isset( $object_cache ) )
	$object_cache = array();

if ( ! isset( $object_merge ) )
	$object_merge = array();


/**
 * Chargement du cache temporaire ( durée de vie 1 jour, objectif limiter l'accès disque dur )
 *
 */

define ('FILE_CACHED', CONTENT_DIR . '/cache_' . md5( HOME ) );

if ( file_exists( FILE_CACHED ) ){
    if ( ( time() - filemtime ( FILE_CACHED ) ) < DAY_IN_SECONDS ) {
        $object_merge = $object_cache = json_decode( file_get_contents ( FILE_CACHED ), true );
    }else {
        unlink ( FILE_CACHED );
    }
}
add_action ( 'muplugins_loaded' , 'save_cache' );



/**
 * function save_cache
 *
 */
function save_cache() {

    global $object_cache, $object_merge ;

    $prepare_object_cache = json_encode ( $object_cache );

    if ( $prepare_object_cache != json_encode ( $object_merge ) ){
        $file = fopen( FILE_CACHED , 'w+');
        fwrite ( $file, $prepare_object_cache );
        fclose( $file );
    }
}


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
        // On stocke le cache par groupe puis par clé
        $object_cache[$group][$key] = $data;
        // On trie le cache
        ksort( $object_cache[$group] );
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

    unset ( $object_cache[$group][$key] );
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
