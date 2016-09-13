<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Gestion des options du CMS mini POPS
 *
 * @package     cms mini POPS
 * @subpackage  query
 * @version 1
 */

/*
global $query;

// Requête passer à l'url
$query = get_url_queries();
*/

/***********************************************/
/*                Fonctions                    */
/***********************************************/

/**
 * Récuperer l'url courante
 * @param (string) $mode    base|raw|uri
 * @return string
 */
function get_current_url( $mode = 'base' ){

    $mode = (string) $mode;
    $port = (int) $_SERVER['SERVER_PORT'];
    $port = 80 !== $port && 443 !== $port && 8888 !== $port ? ( ':' . $port ) : ''; // Port 8888 mamp, easyphp
    $url  = ! empty( $GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI'] ) ? $GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI'] : ( ! empty( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' );
    $url  = 'http' . ( is_ssl() ? 's' : '' ) . '://' . $_SERVER['HTTP_HOST'] . $port . $url;

    switch ( $mode ) :
        case 'raw' :
            return $url;
        case 'uri' :
            $home = guess_url();
            $url  = explode( '?', $url, 2 );
            $url  = reset( $url );
            $url  = str_replace( $home, '', $url );
            return trim( $url, '/' );
        default :
            $url  = explode( '?', $url, 2 );
            return reset( $url );
    endswitch;

}

/**
 * Récupere les args passer à l'url courante
 * @return array retourne les arguments
 */
function get_query_vars(){

    $args = parse_url( get_current_url('raw'), PHP_URL_QUERY);

    if( false !== $args)
        return parse_args($args);
    
    return false;
}


/**
 * Récuperer la requête url si mod rewrite actif ( apache )
 * @return string retourne la requete passer par l'url
 */
function get_url_queries(){

    if ( IS_REWRITE_RULES ){

        return get_current_url('uri');

    } else {

        $args = get_query_vars();

        if( !$args)
            return '';

        $value = reset($args);
        $key   = key($args);

        if( is_same('page', $key) )
            return trim( $value , '/' );

        $query_rules = apply_filters( 'query_rules' , array() );
        $query_rules = array_merge( array('tag'), $query_rules );

        foreach ( $query_rules as $rule ) {
            if( is_same($rule, $key) )
                return trim( $rule .'/'. $value , '/' );
        }
    }

    return '';

}


/**
 * On réécrit l'url selon si mod rewrite actif ( apache ) ou pas
 * @return url
 */
function get_permalink( $slug ='' , $type ='page' ){

    $type = (string) $type;
    $slug = (string) $slug;

    if( is_same($type , 'page') && empty($slug) )
        return MP_HOME;

    if( is_same($type , 'page') &&  is_page($slug) )
        $link = ( IS_REWRITE_RULES ) ? MP_HOME .'/'. $slug : MP_HOME .'/index.php?page='.$slug;
    if( is_same($type, 'feed') && is_same($slug , 'rss') )
        $link = ( IS_REWRITE_RULES ) ? MP_HOME .'/'. 'feed' : MP_HOME .'/index.php?page='. 'feed';
    if( is_same($type , 'page') &&  is_same($slug , 'sitemap') )
        $link = ( IS_REWRITE_RULES ) ? MP_HOME .'/sitemap.xml' : MP_HOME .'/index.php?page=sitemap.xml';
    if( is_same($type , 'tag') &&  is_tag($slug) )
        $link = ( IS_REWRITE_RULES ) ? MP_HOME .'/tag/'.$slug : MP_HOME .'/index.php?tag='.$slug;

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

    if( !isset($query) )
        return false;

    if( is_same( $query , 'error') ) return true;
    if( is_home() )         return false;
    if( is_page() )         return false;
    if( is_robots() )       return false;
    if( is_feed() )         return false;
    if( is_sitemap() )      return false;
    if( is_tag() )          return false;

    else return true;
}


/**
 * Vérifie si le paramètre demandé est une page
 * @param  string Si vide on utilise la requête $query
 * @return boolean  [[Description]]
 */
function is_page( $page = '' ){

    global $query;

    $page = (string) $page;

    if ( strlen($page) >0 ) {

        $slug = $page;

    } else {

        if( !isset($query) )
            return false;

        $slug = $query;

    }

    if( !is_filename( str_replace('/', '', $slug) ) ) return false;

    $page = glob( MP_PAGES_DIR .'/'. $slug , GLOB_MARK|GLOB_ONLYDIR );

    if( empty($page) ) return false;

    $page = glob( $page[0] . basename($slug) .'.md' );

    if( empty($page) ) return false;

    return true;
}


/**
 * Vérifie si le paramètre demandé est la page d'accueil
 * @param  string Si vide on utilise la requête $query
 * @return boolean  [[Description]]
 */
function is_home(){

    global $query;

    if( !isset($query) )
        return false;
    
    return is_same( $query , '');
}

/**
 * Vérifie si la requête passé à l'url est un feed
 * @return boolean
 */
function is_feed(){

    global $query;

    if( !isset($query) )
        return false;

    return is_same( $query , 'feed' );
}

/**
 * Vérifie si la requête passé à l'url est le fichier robots.txt (seulement si apache est actif )
 * @return boolean
 */
function is_robots(){

    global $query;

    if( !isset($query) )
        return false;

    return is_same( $query , 'robots.txt');
}

/**
 * Vérifie si la requête passé à l'url est le fichier sitemap.xml
 * @return boolean
 */
function is_sitemap(){

    global $query;

    if( !isset($query) )
        return false;

    return is_same( $query , 'sitemap.xml');
}

/**
 * Vérifie si la requête passé à l'url est un tag
 * @return boolean
 */
function is_tag(){

    global $query;

    if( !isset($query) )
        return false;

    $args = explode('/', $query);

    if( $args[0] === 'tag' && !empty($args[1]) )
        return true;

    return false;
}
