<?php defined('ABSPATH') or die('No direct script access.');
/**
 *
 *
 * @package cms mini POPS
 * @subpackage Gestion d'un cache statique
 * @version 1
 */







/***********************************************/
/*       Déclaration des données de cache      */
/***********************************************/
        $rules .= "<IfModule mod_rewrite.c>\n\n\t";
        $rules .= "RewriteEngine on\n\n\t";
        $rules .= "# if you homepage is ". HOME ."\n\t";
        $rules .= "# RewriteBase $root\n\n\t";
        $rules .= "# block specify the cache\n\t";
        $rules .= "RewriteCond %{REQUEST_METHOD} GET\n\t";
        $rules .= "RewriteCond %{QUERY_STRING} !.*=.*\n\t";
        $rules .= "RewriteCond %{HTTP:Cookie} !^.*(minipops_auth|comment_author_|comment_author_email_).*$\n\t";
        $rules .= "RewriteCond %{HTTPS} off\n\t";
        $rules .= "RewriteCond %{DOCUMENT_ROOT}/cache/%{HTTP_HOST}%{REQUEST_URI}/index.html -f\n\t";
        $rules .= "RewriteRule ^(.*) cache/%{HTTP_HOST}%{REQUEST_URI}/index.html [L]\n\n\t";
        $rules .= "</IfModule>\n\n";


        case 'site_optimize_cache_page_no_cache':
            if( !is_array($value) )
                $value = null;
            break;

        case 'site_optimize_cache_cached':
        case 'site_optimize_lazyload_images':
        case 'site_optimize_files_html':
        case 'site_optimize_files_css':
        case 'site_optimize_files_js':
            if( !is_bool($value) ) $value = null;
            break;

add_option('optimize->cache->cached', false);
    add_option('optimize->cache->pages_no_cache', '~');
    add_option('optimize->cache->theme', '~');

/***********************************************/
/*       Gestion du cache des pages            */
/***********************************************/



global $is_rewrite_rules, $query;

if( !DEBUG
    && $is_rewrite_rules
    && get_option('optimize->cache->cached')
    && $_SERVER['REQUEST_METHOD'] == 'GET'
    && empty($_GET)
    && isset($_SERVER['HTTP_USER_AGENT'])
    && !preg_match( '/(minipops_auth|comment_author_|comment_author_email_)/', var_export( $_COOKIE , true ) )
    ){

    if( file_exists($_SERVER['DOCUMENT_ROOT'].'/cache/'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/index.html') ){
        readfile($_SERVER['DOCUMENT_ROOT'].'/cache/'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/index.html');
        die();
    }

    if( (is_page()||is_home()) && is_notin($query, get_option('optimize->cache->pages_no_cache', array())) ){
        add_action('TEMPLATE_REDIRECT', function(){ ob_start('mp_cache_pages'); } );
    }

}


// On vide le cache si changement du thème
if( get_the_blog('theme') !== get_option('optimize->cache->theme') ){
    mp_clear_cache_all_pages();
    update_option('optimize->cache->theme', get_the_blog('theme') );
}


if( get_option('optimize->cache->cached') ){

    // On supprime le cache si une page est renommé
    add_action('do_before_rename_the_page', 'mp_clear_cache_page');

    // On supprime le cache si une page est caché
    add_action('do_before_hide_the_page', 'mp_clear_cache_page');

    // On supprime le cache si une page a été modifiée
    add_action('do_before_edit_the_page', 'mp_clear_cache_page');

    // On supprime le cache si une page a été suprrimée
    add_action('do_before_delete_the_page', 'mp_clear_cache_page');

}


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
