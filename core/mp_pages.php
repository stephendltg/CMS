<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction pages
 *
 *
 * @package cms mini POPS
 * @subpackage pages - parser pages
 * @version 1
 */


/***********************************************/
/*          Functions page   			       */
/***********************************************/

/**
 * Charge un champ d'une page
 * @param  $field   nom du champ recherché
 * @param  $slug    nom du repertoire de la page type blog ou blog/post ( identique au résultat de  get_url_queries )
 * @return array    Données contenu dans le champs
 */
function get_the_page( $field, $slug ='' ){

    global $query;

    static $page = array();

    if( empty($slug) ) {

        $slug = $query;

        if( is_home() )
            $slug = is_page('home') ? 'home' : '';

        if( is_404() )
            $slug = is_page('error') ? 'error' : '';
    }

    if( !isset($page[$slug]) && !empty($slug) ){

        $filemtime_page = filemtime( CONTENT .'/'. $slug .'/'. basename($slug) .'.txt' );

        // On affecte la valeurs title au cas ou non renseigné
        $page[$slug]['title']  = basename($slug);

        // On lit le fichier
        $page[$slug] = file_get_yaml( CONTENT .'/'. $slug .'/'. basename($slug) .'.txt');

        // On affecte les données importante!
        $page[$slug]['edit_date'] = $filemtime_page;
        $page[$slug]['slug']      = $slug;

        if( is_same($slug,'home') )         $page[$slug]['url'] = HOME;
        elseif( is_same($slug,'error') )    $page[$slug]['url'] = null;
        else $page[$slug]['url'] = get_permalink( $slug );

    }

    return !empty($page[$slug][$field]) ? apply_filter( 'get_the_'.$field, $page[$slug][$field], $slug ) : '';
}



/**
 * Sauvegarder une page
 * @param  $filename    nom du fichier à sauvegarder tel que get_url_queries : blog/post
 * @param  $array   	Données à sauvegarder sous format de tableau (array)
 * @return boolean
 */
function file_put_page( $filename , $array ) {

    $filename = (string) $filename;

    if( is_array($array) && !empty($array) && is_filename(basename($filename)) ){
        $slugs = explode( '/', $filename );
        if( is_sup($slugs, 1) ) {
            unset($slugs[size($slugs)-1]);
            foreach( $slugs as $slug )
                if(!is_page($slug)) return false;
        }
        $dir = CONTENT .'/'. $filename;
        @mkdir( $dir , 0755 , true );
        if ( !file_put_yaml( $dir .'/'. basename($filename).'.txt' , $array ) ) return false;
        @chmod( $dir .'/'. basename($filename).'.txt' , 0644 );
        return true;
    }
    return false;
}


/**
 * Cacher une page
 * @param  $slug    slug de la page
 * @return boolean
 */
function hide_page( $slug ) {
    $slug = (string) $slug;
    if( file_exists(CONTENT .'/'. $slug .'/@'.$slug.'.txt') ) return true;
    if( !is_page($slug) ) return false;
    do_action('mp_page_rename', array($slug) );
    return rename( CONTENT .'/'. $slug .'/'.$slug.'.txt' , CONTENT .'/'. $slug .'/@'.$slug.'.txt' );
}


/**
 *  Rendre une page visible
 * @param  $slug    slug de la page
 * @return boolean
 */
function visible_page( $slug ) {
    $slug = (string) $slug;
    if( is_page($slug) ) return true;
    do_action('mp_page_rename', array($slug) );
    return rename( CONTENT .'/'. $slug .'/@'.$slug.'.txt' , CONTENT .'/'. $slug .'/'.$slug.'.txt' );
}


/**
 * renomer slug d'une page
 * @param  $slug        slug ancienne page
 * @param  $new_slug    slug nouvelle page
 * @return boolean
 */
function rename_slug( $slug , $new_slug ) {
    $slug = (string) $slug;
    $new_slug = (string) $new_slug;
    $new_slug = sanitize_file_name($new_slug);
    if( is_same($slug, $new_slug) ) return false;
    if( is_page($slug) ){ $slug_file = $slug; $new_slug_file = $new_slug; }
    elseif( hide_page($slug) ){ $slug_file = '@'.$slug; $new_slug_file = '@'.$new_slug;}
    else return false;
    do_action('mp_page_rename', array($slug) );
    if( rename( CONTENT .'/'. $slug .'/'.$slug_file.'.txt' , CONTENT .'/'. $slug .'/'.$new_slug_file.'.txt' ) )
        return rename( CONTENT .'/'. $slug , CONTENT .'/'. $new_slug );
}


/***********************************************/
/*          Functions listing pages            */
/***********************************************/

/**
 * Récupère l'ensemble des page
 * @return
 */
function get_all_page(){
    static $dir = CONTENT;
    static $all_pages = array();
    $dirs = glob($dir . '/*', GLOB_ONLYDIR);
    if( count($dirs)>0){
        foreach ($dirs as $d) {
            $d = str_replace( CONTENT.'/' , '' , $d);
            if( is_page($d) ) $all_pages[] = $d;
        }
    }
    foreach ($dirs as $dir) get_all_page($dir);
    return array_diff( $all_pages, array('home','error') );
}


/**
 * Récupère le slug parent
 * @param  $slug    slug de la page enfant
 * @return
 */
function get_parent_page( $slug = '' ) {
    return rtrim( str_replace( basename($slug), '', $slug ) , '/' );
}


/**
 * Récupère les slug enfants
 * @param  $slug    slug de la page parents
 * @return
 */
function get_childs_page ( $slug = '' ) {
    $childs = glob( str_replace( '//','/', CONTENT .'/'.$slug.'/*' ) , GLOB_ONLYDIR );
    if( count($childs)>0){
        foreach( $childs as $key => $child ){
            $child = str_replace( CONTENT.'/' , '' , $child);
            if( !is_page($child) ) unset($childs[$key]);
            else $childs[$key] = trim($child,'/');
        }
    }
    return array_diff( $childs, array('home','error') );
}


/**
 * Récupère les slug adjacents
 * @param  $slug    slug de la page adjacente
 * @return
 */
function get_adjacent_page( $slug = '' ) {
    return array_diff( get_childs_page(get_parent_page($slug)) , array($slug) );
}


/***********************************************/
/*          Function select                    */
/***********************************************/

function mpops( $args = array('where'=>'', 'max'=>10, 'order'=>'none') ){

    global $query;

    $queries = array();

    $meta_compare = array(
        'is_between',
        'is_different',
        'is_in',
        'is_max',
        'is_min',
        'is_notin',
        'is_same',
        'is_size',
        'is_match',
        'is_sup',
        'is_low'
    );

    $meta_type = array(
        'is_alpha',
        'is_alphanum',
        'is_date',
        'is_email',
        'is_filename',
        'is_intgr',
        'is_ip',
        'is_num',
        'is_url',
        'is_string',
    );

    $relation = array(
        'OR',
        'AND',
    ); //Defaut AND

}
