<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Gestion des options du CMS mini POPS
 *
 * @package     cms mini POPS
 * @subpackage  query
 * @version 1
 */


global $query;

// Requête passer à l'url
$query = get_url_queries();

/***********************************************/
/*                Fonctions                    */
/***********************************************/

/**
 * Récuperer la requête url si mod rewrite actif ( apache )
 * @return string retourne la requete passer par l'url
 */

function get_url_queries(){

    global $is_mod_rewrite;

    $url = '';
    if ( $is_mod_rewrite ){

        $request_url = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '';
        $script_url  = (isset($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : '';

        if ( $request_url != $script_url ) $url = trim(preg_replace('/'. str_replace('/', '\/', str_replace('index.php', '', $script_url)) .'/', '', $request_url, 1), '/');
        $url = preg_replace('/\?.*/', '', $url);
    } else {
        $first_get_query = key($_GET);
        $query_rules = array ('p','tag');
        $query_rules = apply_filter( 'query_rules' , $query_rules );
        foreach ($query_rules as $rule) {
            if( is_same( $rule , $first_get_query )  && !empty($_GET[$first_get_query]) )
                $url = ( is_same('p',$rule) ) ? $_GET[$first_get_query] : $rule.'/'.$_GET[$first_get_query];
        }
    }
    // On supprime le dernier '/' pour certaine requête ( www.local.fr/test/?lkklk ou www.local.fr/index.php?p=test/ ) et rediriger ce type de reqete vers la page demande la plus proche donc limiter les erreurs 404.
    $url = rtrim( $url , '/' );
    return $url;
}


/**
 * On réécrit l'url selon si mod rewrite actif ( apache ) ou pas
 * @return url
 */
function get_permalink( $slug ='' , $type ='page' ){

    $type = (string) $type;
    $slug = (string) $slug;

    global $is_mod_rewrite;

    if( is_same($type , 'page') &&  empty($slug) )
        return HOME;

    if( is_same($type , 'page') &&  is_page($slug) )
        $link = ( $is_mod_rewrite ) ? HOME .'/'. $slug : HOME .'/index.php?p='.$slug;
    if( is_same($type, 'feed') && is_same($slug , 'rss') )
        $link = ( $is_mod_rewrite ) ? HOME .'/'. 'feed' : HOME .'/index.php?p='. 'feed';
    if( is_same($type , 'page') &&  is_same($slug , 'sitemap') )
        $link = ( $is_mod_rewrite ) ? HOME .'/sitemap.xml' : HOME .'/index.php?p=sitemap.xml';
    if( is_same($type , 'tag') &&  is_tag($slug) )
        $link = ( $is_mod_rewrite ) ? HOME .'/tag/'.$slug : HOME .'/index.php?tag='.$slug;

    if(!empty($link) ) return $link;
    else return false;
}



/***********************************************/
/*         Fonctions de validation query       */
/***********************************************/

/**
 * Vérifier si on est sur une erreur 404
 * @return boolean
 */
function is_404(){

    global $query;

    if( is_same( $query , 'error') ) return true;
    if( is_home() )         return false;
    if( is_page() )         return false;
    //if( !is_author() )       return false;
    //if( !is_tag() )          return false;
    else return true;
}


/**
 * Vérifie si le paramètre demandé est un mot clé
 * @param  string Si vide on utilise la requête $query
 * @return boolean
 */
function is_tag( $tag ='' ){

    global $query;

    $url = $query;
    if ( !empty($tag) ) $url = $tag;

    // on cherche dans la base tag si le mot clé tag existe sinon false
    // non construit pour le moment

    return false;
}


/**
 * Vérifie si le paramètre demandé est une page
 * @param  string Si vide on utilise la requête $query
 * @return boolean  [[Description]]
 */
function is_page( $page ='' ){

    global $query;

    $url = $query;
    if ( !empty($page) ) $url = $page;
    if( !is_filename( str_replace('/','',$url) ) ) return false;
    $page = glob( CONTENT .'/'. $url , GLOB_MARK|GLOB_ONLYDIR );
    if( empty($page)) return false;
    $page = glob( $page[0] . basename($url) .'.txt' );
    if( empty($page)) return false;
    return true;
}

/**
 * Vérifie si le paramètre demandé est la page d'accueil
 * @param  string Si vide on utilise la requête $query
 * @return boolean  [[Description]]
 */
function is_home( $page ='' ){

    global $query;

    $url = ( !empty($query) ) ? $query : 'index.php';
    if( !empty($page) ) $url = $page;
    return is_same( $url , 'index.php');
}

/**
 * Vérifie si la requête passé à l'url est un feed
 * @return boolean
 */
function is_feed(){

    global $query;

    return is_same( $query , 'feed' );
}

/**
 * Vérifie si la requête passé à l'url est le fichier robots.txt (seulement si apache est actif )
 * @return boolean
 */
function is_robots(){

    global $query;

    return is_same( $query , 'robots.txt');
}

/**
 * Vérifie si la requête passé à l'url est le fichier sitemap.xml
 * @return boolean
 */
function is_sitemap(){

    global $query;

    return is_same( $query , 'sitemap.xml');
}

/**
 * Vérifie si la requête passé à l'url est le fichier favicon.ico (seulement si apache est actif )
 * @return boolean
 */
function is_favicon(){

    global $query;

    return is_same( $query , 'favicon.ico');
}
