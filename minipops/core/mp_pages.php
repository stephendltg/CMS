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
        case 'keywords':
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
        case 'image':
            $value = esc_html($value);
            $value = mp_pops($value, $slug);
            break;
            
        case 'excerpt':
            $value = esc_html($value);
            $value = excerpt( $value, 140, 'words' );
            if( '' === $value )
                excerpt( get_the_page('content', $slug ), 140, 'words' );
            break;
        default:
            if( empty( $GLOBALS['mp_hook_filter']['get_the_page_'.$field] ) )
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
    }

    if( !isset($page[$slug]) && !empty($slug) ){

        do_action('do_before_get_the_page', array($field, $slug) );

        // Date d'edition de la page corresponds à la date de modification du fichier
        $filemtime_page = filemtime( CONTENT .'/'. $slug .'/'. basename($slug) .'.md' );

        // On affecte la valeurs title au cas ou non renseigné
        $page[$slug]['title']  = basename($slug);

        // On lit le fichier
        $page[$slug] = file_get_page( CONTENT .'/'. $slug .'/'. basename($slug) .'.md');

        // On affecte les données importante!
        $page[$slug]['edit_date']   = gmdate( 'Y-m-d H:i:s', $filemtime_page );
        $page[$slug]['pubdate']     = isset($page[$slug]['pubdate']) && is_date($page[$slug]['pubdate']) ? gmdate( 'Y-m-d H:i:s', strtotime($page[$slug]['pubdate']) ) : $page[$slug]['edit_date'];
        $page[$slug]['slug']        = $slug;

        if( is_same($slug,'home') )         $page[$slug]['url'] = HOME;
        elseif( is_same($slug,'error') )    $page[$slug]['url'] = null;
        else                                $page[$slug]['url'] = get_permalink( $slug );

        do_action('do_after_get_the_page', array($field, $slug) );
    }

    if( !empty($page[$slug][$field]) ){

        if( $field === 'slug' || $field === 'url' )
            return $page[$slug][$field];
        else
            return apply_filter('get_the_page_'.$field, sanitize_page($field,$page[$slug][$field],$slug), $slug);
    }
    else
        return apply_filter( 'default_page_'. $field, '', $field, $slug );

}



/**
 * Sauvegarder une page
 * @param  $filename    nom du fichier à sauvegarder tel que get_url_queries : blog/post
 * @param  $args    	Données à sauvegarder sous format de tableau (array)
 * @return boolean
 */
function mp_set_the_page( $slug , $args = array() ) {

    $slug = (string) $slug;

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

        $dir = CONTENT .'/'. $slug;
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

    rrmdir(CONTENT .'/'. $slug);

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

    if( file_exists(CONTENT .'/'. $slug .'/@'.$slug.'.yml') ) return true;

    if( !is_page($slug) ) return false;

    do_action('do_before_hide_the_page', array($slug) );

    $hide_the_page = rename( CONTENT .'/'. $slug .'/'.$slug.'.txt' , CONTENT .'/'. $slug .'/@'.$slug.'.yml' );

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

    do_action('do_before_visible_the_page', array($slug) );

    $visible_the_page = rename( CONTENT .'/'. $slug .'/@'.$slug.'.yml' , CONTENT .'/'. $slug .'/'.$slug.'.yml' );

    do_action('do_after_visible_the_page', array($slug) );

    return $visible_the_page;
}


/**
 * renomer slug d'une page
 * @param  $slug        slug ancienne page
 * @param  $new_slug    slug nouvelle page
 * @return boolean
 */
function mp_rename_the_page( $slug , $new_slug ) {

    $slug = (string) $slug;
    $new_slug = (string) $new_slug;
    $new_slug = sanitize_file_name($new_slug);


    if( is_same($slug, $new_slug) ) return false;

    if( is_page($slug) ){

        $slug_file = $slug;
        $new_slug_file = $new_slug;

    } elseif( mp_hide_the_page($slug) ){

        $slug_file = '@'.$slug;
        $new_slug_file = '@'.$new_slug;

    } else
        return false;

    do_action('do_before_rename_the_page', array($slug, $newslug) );

    if( rename( CONTENT .'/'. $slug .'/'.$slug_file.'.yml' , CONTENT .'/'. $slug .'/'.$new_slug_file.'.yml' ) )
        $rename_the_page = rename( CONTENT .'/'. $slug , CONTENT .'/'. $new_slug );

    do_action('do_after_rename_the_page', array($slug, $newslug) );

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
function get_childs_page( $slug = '' ) {

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


/**
 * Boucle pages
 * @param  $args    array
 *                  'where'   array() : Listes des slugs de pages où chercher sous forme de tableau si vide recherche dans toutes les pages
 *                  'filter'  string  : Listes des champs recherchés séparer par des virgules ex: title,pubdate
 *                  'max'     integer : Nombre de résultat par défaut : 10
 *                  'order'   string  : Mode de tri "ASC" ( par défaut ), "DESC" ou "SHUFFLE"
 *                  'orderby' string  : Trier par "date" ( par défaut ), "auteur", "tag", tout champs valide dans le document
 * @return array    retourne les résultats sous forme de tableau
 */
function the_loop( $args = array() ){


    $args = parse_args( $args, array(
        'where' => get_all_page(),
        'max' => 10,
        'order' => 'ASC',
        'orderby' => 'pubdate'
        ) );

    /* Validation "max" */
    $args['max'] = (int) $args['max'];

    /* Nettoyage "order" */
    $args['order'] = strtoupper($args['order']);

    /* Validation "orderby" */
    $args['orderby'] = is_in( $args['orderby'], array('pubdate','author','tag') ) ? $args['orderby'] : 'pubdate';


    /* On filtre par filter */
    if( !empty($args['filter']) ){

        foreach ($args['where'] as $key => $page){

            $filter = get_the_page($args['filter'], $page);

            // On applique le filtre
            if( strlen($filter) == 0 ) 
                unset($args['where'][$key]);

            // On applique la valeur au filtre
            if( !empty($args['value']) ){

                $args['value'] = is_array($args['value']) ? $args['value'] : array($args['value']);

                // var toogle
                $i = 0;

                foreach ($args['value'] as $value)
                    if( strstr($filter, $value) )  $i = -1;

                if( !$i )
                    unset($args['where'][$key]);
            }
        }
    }


    /* On filtre par "orderby" et "order" */
    if( !empty($args['orderby']) ){

        foreach ($args['where'] as $key => $page){

            $order_by = get_the_page($args['orderby'], $page);

            if( strlen($order_by) == 0 ) 
                unset($args['where'][$key]);
            else
                $tmp[] = $order_by;
        }

       // _echo ($tmp);


        if( empty($args['where']) || empty($tmp) )
            return array();


        /* On filtre par "order" uniquement */
        $args['where'] = array_combine( $args['where'] , $tmp );
        if( is_same($args['order'], 'ASC') ) asort($args['where']);
        if( is_same($args['order'], 'DESC') ) arsort($args['where']);
        $args['where'] = array_keys($args['where']);

    } else {

        /* On filtre par "order" uniquement */
        if( is_same($args['order'], 'ASC') ) sort($args['where']);
        if( is_same($args['order'], 'DESC') ) rsort($args['where']);

    }

    /* Limite de resultat */
    array_splice( $args['where'], $max );


    return $args['where'];

}
