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

/*
 * Une page est constitué d'un dossier comportant un fichier texte gérant le contenu et les medias associés de la page.
*/

/**
 * Nettoyer une valeur d'une page
 * @param  $field   nom du champ
 * @param  $value   valeur à nettoyer
 * @param  $slug    slug de la page
 */
function sanitize_page($field, $value, $slug){

    switch ($field) {

        case 'title':
        case 'description':
            $value = esc_html($value);
            break;
        case 'tag':
        case 'robots':
            $value = remove_accent($value);
            $value = sanitize_words($value);
            $value = str_replace(' ', ',', $value);
            break;
        case 'author':
            $value = sanitize_user($value);
            break;
        case 'template':
            $value = sanitize_file_name($value);
            break;
        case 'content':
            $value = esc_html($value);
            $value = mp_pops($value, $slug);
            $value = parse_markdown( $value);
            break;
        case 'thumbnail':
            $value = esc_html($value);
            $value = http_build_query( array('image'=>$value, 'slug'=>$slug, 'alt'=>get_the_page('description'), 'class'=>'thumbnail' ) );
            $value = pops_image($value);
            break;
        case 'excerpt':
            $value = esc_html($value);
            $value = excerpt( $value, 140, 'words' );
            break;
        default:
            if( empty( mp_cache_data('mp_hook_filters')['get_the_page_'.$field] ) )
                $value = sanitize_allspecialschars($value);
            break;
    }
    return $value;
}

/**
 * Charge un champ d'une page
 * @param  $field   nom du champ recherché
 * @param  $slug    nom du repertoire de la page type blog ou blog/post ( identique au résultat de  get_url_queries )
 * @return array    Données contenu dans le champs
 */
function get_the_page( $field, $slug = '' ){

    global $query;

    static $page = array();

    $field = (string) $field;
    $slug  = (string) $slug;

    if( empty($slug) ) {

        $slug = $query;

        if( is_home() )  $slug = is_page('home') ? 'home' : '';

        if( is_404() )   $slug = is_page('error') ? 'error' : '';

        if(!is_page() )  return;

    }

    if( !isset($page[$slug]) && !empty($slug) ){

        do_action('do_before_get_the_page', array($field, $slug) );

        // Date d'edition de la page corresponds à la date de modification du fichier
        $filemtime_page = filemtime( MP_PAGES_DIR .'/'. $slug .'/'. basename($slug) .'.md' );

        // On affecte la valeurs title au cas ou non renseigné
        $page[$slug]['title']  = basename($slug);

        // On lit le fichier
        $page[$slug] = file_get_page( MP_PAGES_DIR .'/'. $slug .'/'. basename($slug) .'.md');

        // On affecte les données importante!
        $page[$slug]['edit_date']   = gmdate( 'Y-m-d H:i:s', $filemtime_page );
        $page[$slug]['pubdate']     = isset($page[$slug]['pubdate']) && is_date($page[$slug]['pubdate']) ? gmdate( 'Y-m-d H:i:s', strtotime($page[$slug]['pubdate']) ) : $page[$slug]['edit_date'];
        $page[$slug]['slug']        = $slug;

        if( is_same($slug,'home') )         $page[$slug]['url'] = MP_HOME;
        elseif( is_same($slug,'error') )    $page[$slug]['url'] = null;
        else                                $page[$slug]['url'] = get_permalink( $slug );

        do_action('do_after_get_the_page', array($field, $slug) );
    }

    if( !empty($page[$slug][$field]) ){

        if( $field === 'slug' || $field === 'url' )
            return $page[$slug][$field];
        else
            return apply_filters('get_the_page_'.$field, sanitize_page($field,$page[$slug][$field],$slug), $slug);
    }
    else
        return apply_filters( 'default_page_'. $field, '', $field, $slug );

}



/**
 * Sauvegarder une page
 * @param  $filename    nom du fichier à sauvegarder tel que get_url_queries : blog/post
 * @param  $args    	Données à sauvegarder sous format de tableau (array)
 * @return boolean
 */
function mp_set_the_page( $slug , $args = array() ) {

    $slug = (string) $slug;

    $slug = trim($slug, '/');

    $args = parse_args( $args, array(
        'title' => '',
        'author' => '',
        'tag' => '',
        'description' => '',
        'content' => ''
        ));

    if( is_filename(basename($slug)) ){

        $slugs = explode( '/', $slug );

        // On vérifie que les pages parents existes si plusieurs élements dans le slug
        if( is_sup($slugs, 1) ) {
            unset($slugs[size($slugs)-1]);
            foreach( $slugs as $page )
                if(!is_page($page)) return false;
        }

        do_action('do_before_edit_the_page', array($slug) );

        $dir = MP_PAGES_DIR .'/'. $slug;
        @mkdir( $dir , 0755 , true );
        if ( !file_put_page( $dir .'/'. basename($slug).'.md' , $args ) ) return false;
        @chmod( $dir .'/'. basename($slug).'.md' , 0644 );

        do_action('do_after_edit_the_page', array($slug) );

        return true;
    }
    return false;
}

/**
 * supprimer une page
 * @param  $slug    nom du fichier à supprimer tel que get_url_queries : blog/post
 * @return boolean
 */
function mp_delete_the_page( $slug ) {

    $slug = (string) $slug;

    if( !get_childs_page($slug) ) return false;

    do_action('do_before_delete_the_page', array($slug) );

    rrmdir(MP_PAGES_DIR .'/'. $slug);

    do_action('do_after_delete_the_page', array($slug) );

    return true;
}


/**
 * Cacher une page ( on renomme que le fichier texte du coup is_page() n'est plus valide )
 * @param  $slug    slug de la page
 * @return boolean
 */
function mp_hide_the_page( $slug ) {

    $slug = (string) $slug;

    $page = basename($slug);

    if( file_exists(MP_PAGES_DIR .'/'. $slug .'/@'.$page.'.md') ) return true;

    if( !is_page($slug) ) return false;

    do_action('do_before_hide_the_page', array($slug) );

    $hide_the_page = rename( MP_PAGES_DIR .'/'. $slug .'/'.$page.'.md' , MP_PAGES_DIR .'/'. $slug .'/@'.$page.'.md' );

    do_action('do_after_hide_the_page', array($slug) );

    return $hide_the_page;
}


/**
 *  Rendre une page visible
 * @param  $slug    slug de la page
 * @return boolean
 */
function mp_visible_the_page( $slug ) {

    $slug = (string) $slug;

    if( is_page($slug) ) return true;

    $page = basename($slug);

    do_action('do_before_visible_the_page', array($slug) );

    $visible_the_page = rename( MP_PAGES_DIR .'/'. $slug .'/@'.$page.'.md' , MP_PAGES_DIR .'/'. $slug .'/'.$page.'.md' );

    do_action('do_after_visible_the_page', array($slug) );

    return $visible_the_page;
}


/**
 * renomer slug d'une page
 * @param  $slug        slug de l'ancienne page
 * @param  $new_name    nom de la nouvelle page
 * @return boolean
 */
function mp_rename_the_page( $slug , $new_name ) {

    $slug = (string) $slug;
    $new_name = (string) $new_name;

    $slug = trim($slug, '/');
    $new_name = sanitize_file_name($new_name);

    // On récupère le dernier argument du slug
    $new_slug = explode('/', $slug);
    $slug_filename = end($new_slug);

    if( is_same($slug_filename, $new_name) ) return false;

    if( !is_page($slug) )  return false;

    // On reconstruit le nouveau slug
    $new_slug[key($new_slug)] = $new_name;
    $new_slug  = join('/', $new_slug);

    do_action('do_before_rename_the_page', array($slug, $new_name) );

    if( rename( MP_PAGES_DIR .'/'. $slug .'/'.$slug_filename.'.md' , MP_PAGES_DIR .'/'. $slug .'/'.$new_name.'.md' ) )
        $rename_the_page = rename( MP_PAGES_DIR .'/'. $slug , MP_PAGES_DIR .'/'. $new_slug );

    do_action('do_after_rename_the_page', array($slug, $new_name) );

    return $rename_the_page;
}


/***********************************************/
/*          Functions listing pages            */
/***********************************************/

/**
 * Récupère l'ensemble des page
 * @return
 */
function get_all_page(){

    static $dir = MP_PAGES_DIR;
    static $all_pages = array();

    $dirs = glob($dir . '/*', GLOB_ONLYDIR);

    if( count($dirs)>0){
        foreach ($dirs as $d) {
            $d = ltrim( str_replace( MP_PAGES_DIR, '' , $d), '/');
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

    $slug = (string) $slug;

    $slug = trim($slug, '/');

    $slug = explode('/', $slug);
    unset($slug[size($slug)-1]);
    $slug = join('/', $slug);

    return is_page($slug);

}


/**
 * Récupère les slug enfants
 * @param  $slug    slug de la page parents
 * @return
 */
function get_childs_page( $slug = '' ) {

    $slug = (string) $slug;

    $slug = trim($slug, '/');

    $childs = glob( MP_PAGES_DIR .'/'.$slug.'/*' , GLOB_ONLYDIR );

    if( count($childs)>0){
        foreach( $childs as $key => $child ){
            $child = ltrim( str_replace( MP_PAGES_DIR, '' , $child), '/');
            if( !is_page($child) ) unset($childs[$key]);
            else $childs[$key] = $child;
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