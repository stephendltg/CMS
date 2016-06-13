<?php defined('ABSPATH') or die('No direct script access.');
/**
 *
 *
 * @package cms mini POPS
 * @subpackage Gestion d'un cache statique
 * @version 1
 */



/***********************************************/
/*       Gestion du cache des pages            */
/***********************************************/



global $is_mod_rewrite, $query;

if( !DEBUG
    && $is_mod_rewrite
    && get_option('optimize->cache')
    && $_SERVER['REQUEST_METHOD'] == 'GET'
    && empty($_GET)
    && isset($_SERVER['HTTP_USER_AGENT'])
    && !preg_match( '/(mp_logged_in_|mp-postpass_|comment_author_|comment_author_email_)/', var_export( $_COOKIE , true ) )
    ){

    if( file_exists($_SERVER['DOCUMENT_ROOT'].'/cache/'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/index.html') ){
        readfile($_SERVER['DOCUMENT_ROOT'].'/cache/'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/index.html');
        die();
    }

    if( (is_page()||is_home()) && is_notin($query, get_option('optimize->pages_no_cache', array())) ){
        add_action('TEMPLATE_REDIRECT', function(){ ob_start('mp_cache_pages'); } );
    }

}



// On vide le cache si changement du thème
if( get_the_blog('theme') !== get_option('optimize->cache_theme') ){
    mp_clear_cache_all_pages();
    update_option('optimize->cache_theme', get_the_blog('theme') );
}


// On supprime le cache si une page est renommé
add_action('do_before_rename_the_page', 'mp_clear_cache_page');

// On supprime le cache si une page est caché
add_action('do_before_hide_the_page', 'mp_clear_cache_page');

// On supprime le cache si une page a été modifiée
add_action('do_before_edit_the_page', 'mp_clear_cache_page');

// On supprime le cache si une page a été suprrimée
add_action('do_before_delete_the_page', 'mp_clear_cache_page');


/***********************************************/
/*       Function cache pages                  */
/***********************************************/

function mp_cache_pages( $html ){

    @mkdir( $_SERVER['DOCUMENT_ROOT'].'/cache/'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], 0755, true );
    file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/cache/'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/index.html', $html );
    return $html;
}

function mp_clear_cache_all_pages(){
    if( is_dir($_SERVER['DOCUMENT_ROOT'].'/cache/') )
        rrmdir($_SERVER['DOCUMENT_ROOT'].'/cache/');
}

function mp_clear_cache_page( $slug ){
    if( is_dir($_SERVER['DOCUMENT_ROOT'].'/cache/'.$slug.'/') )
        rrmdir($_SERVER['DOCUMENT_ROOT'].'/cache/'.$slug.'/');
}
