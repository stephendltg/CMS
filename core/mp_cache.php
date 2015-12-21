<?php defined('ABSPATH') or die('No direct script access.');
/**
 *
 *
 * @package cms mini POPS
 * @subpackage cache
 * @version 1
 */


/***********************************************/
/*       Gestion du cache des pages            */
/***********************************************/

// On gérer le cache si page ou home ( seulement si DEBUG is OFF et que nous sommes sur un serveur apache )
if( is_page() ) add_action('get_header', function(){ ob_start('mp_cache_pages'); } );
if( is_home() ) add_action('get_header', function(){ ob_start('mp_cache_pages'); } );

// On supprime le cache si une page est renommé
add_action('mp_page_rename', 'mp_clear_cache_page');


/***********************************************/
/*       Function cache pages                  */
/***********************************************/

function mp_cache_pages( $html ){

    global $query, $is_mod_rewrite;

    if( !DEBUG
    && $is_mod_rewrite
    && $_SERVER['REQUEST_METHOD'] == 'GET'
    && is_notin( $query, apply_filter('nocache_pages', array() ) )
    && empty( $_GET )
    && isset( $_SERVER['HTTP_USER_AGENT'] )
    && !preg_match( '/(mpops_logged_in_|mpops-postpass_|comment_author_|comment_author_email_)/', var_export( $_COOKIE , true ) ) // Check if looged in to WP
    && !file_exists( $_SERVER['DOCUMENT_ROOT'].'/cache/'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '/index.html' )
    ) {
        @mkdir( $_SERVER['DOCUMENT_ROOT'].'/cache/' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 0755, true );
        file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/cache/' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '/index.html', $html );
    }
    return $html;
}

function mp_clear_cache_all_pages(){
    if( is_dir($_SERVER['DOCUMENT_ROOT'].'/cache/') )
        rmdir_recursive($_SERVER['DOCUMENT_ROOT'].'/cache/');
}

function mp_clear_cache_page( $slug ){
    if( is_dir($_SERVER['DOCUMENT_ROOT'].'/cache/'.$slug.'/') )
        rmdir_recursive($_SERVER['DOCUMENT_ROOT'].'/cache/'.$slug.'/');
}
