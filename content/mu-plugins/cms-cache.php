<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Gestion du cache du CMS ( Stock le cache du CMS un fichier : optimisation accès disque )
 *
 * @package     cms
 * @subpackage  cms_cache
 * @version 1
 */


define ('CMS_FILE_CACHED', CONTENT_DIR . '/cache_' . md5( HOME ) );

add_action ( 'muplugins_loaded' , 'cms_load_cache' , 1 );
add_action ( 'muplugins_loaded' , 'cms_save_cache' , 99 );

/** function cms_load_cache **/
function cms_load_cache() {
    if ( file_exists( CMS_FILE_CACHED ) && is_file( CMS_FILE_CACHED ) ){
        if ( ( time() - filemtime ( CMS_FILE_CACHED ) ) < DAY_IN_SECONDS ) {
            $object_cache = json_decode( file_get_contents ( CMS_FILE_CACHED ), true );
            $object_cache['autoincremente'] = 0;
        }else {
            unlink ( CMS_FILE_CACHED );
        }
    }
}

/** function cms_save_cache **/
function cms_save_cache() {

    global $object_cache;

    if ( $object_cache['autoincremente'] > 0 ){
        $file = fopen( CMS_FILE_CACHED , 'w+');
        fwrite ( $file, json_encode ( $object_cache ) );
        fclose( $file );
    }
}
