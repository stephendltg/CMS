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

get_the_page('title');
//_echo( get_the_page('title'),1 );

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


function  is_all( $value, $compare){

    //_echo( $value . ':'. $compare, 1);

    return true;
}

/**
 * Boucle pages
 * @param  $args    array
 *                  'where'   array() : Listes des slugs de pages où chercher sous forme de tableau si vide recherche dans toutes les pages
 *                  'filter'  string  : Listes des champs recherchés séparer par des virgules ex: title,pubdate
 *                  'max'     integer : Nombre de résultat par défaut : 10
 *                  'order'   string  : Mode de tri "ASC" ( par défaut ), "DESC" ou "SHUFFLE"
 *                  'orderby' string  : Trier par "date" ( par défaut ), "auteur", "tag", tout champs valide dans le document
 *
 * ex: the_loop('filter[author]=denis,jean,michel&order=asc&orderby=title');
 * @return array    retourne les résultats sous forme de tableau
 */
function the_loop( $args = array() ){


    $args = parse_args( $args, array(
        'where'   => get_all_page(),
        'max'     => 10,
        'order'   => 'ASC',
        'orderby' => 'pubdate'
        ) );

    /* Nettoyage "max" */
    $max = (int) $args['max'];
    unset($args['max']);

    /* Nettoyage "order" */
    $order = strtoupper($args['order']);
    unset($args['order']);

    /* Nettoyage "orderby" */
    $orderby = is_in( $args['orderby'], array('pubdate','author','tag') ) ? $args['orderby'] : 'pubdate';
    unset($args['orderby']);

    /* Nettoyage "where" */
    $where = array_flip($args['where']);
    unset($args['where']);

    /* Table de data mit de côté*/
    $next   = array();

    /* Préparation du filtre */
    foreach ($args as $filter => $query) {

        $filter = sanitize_key($filter);

        preg_match('/^(is_.*?)\((.*?)\)/', $query, $match ); // On cherche si une requete de recherche

        // Si requête particulière ( requête sur tableau, comparaison, intervalle, etat)
        if( !empty($match[0]) && function_exists($match[1]) ){

            if( is_in( $match[1], array('is_in','is_notin') ) ){

                $args[$filter] = array( '', explode(',', sanitize_list($match[2],',') ) );

            } elseif( is_in( $match[1], array('is_same','is_match','is_different','is_low','is_max','is_size','is_sup') ) ){
                
                $args[$filter] = array( '', trim($match[2]) );

            } elseif( is_same( $match[1], 'is_between' ) ){

                $args[$filter] = explode(',', sanitize_list($match[2],',') );
                $args[$filter] = array( '', $args[$filter][0], $args[$filter][1] );

            } else {

                $args[$filter] = null;
            }

            add_action( $filter.'_search', $match[1] );  // Ajout du hook pour chaque filtre
        
        } else {

            $query = trim($query);

            if( $query === '!'){
                // Requête qui test si la valeur n'est null
                $args[$filter] = array();
                add_action( $filter.'_search', function($value){ return strlen($value) === 0 ? false:true;} );  // Ajout du hook pour chaque filtre;

            } else {
                // Requête simple 
                $args[$filter] = array('', '|'.trim($query).'|' );
                add_action( $filter.'_search', 'is_match' );  // Ajout du hook pour chaque filtre
            }

        }
    }


    /* Boucle principal de recherche */
    foreach ($where as $page => $key){


        foreach ($args as $filter => $compare) {
        
            /* On ajoute la réference au mask du filtre */
           $compare[0] = get_the_page($filter, $page);

            /* On applique le filtre */
            if( false === do_action($filter.'_search', $compare, true) )
                unset($where[$page]);
        }

        /* on prépare le trie si la table existe toujours */
        if( isset($where[$page]) ){

            /* on commence par décharger la table */
            unset($where[$page]);

            /* On filtre par "orderby" */
            $order_by = get_the_page($orderby, $page);
            if( strlen($order_by) === 0 )    $next[] = $page;
            else                             $where[$page] = $order_by;

        }
    }

  
    /* On filtre par "order" uniquement */
    if( is_same($order, 'ASC' ) ) asort($where);
    if( is_same($order, 'DESC') ) arsort($where);

    /* On supprimer les valeurs qui ont servit au trie puis on ajoute les données mit de côté*/
    $where = array_keys( $where );
    $where = array_merge( $where, $next );

    /* Limite de resultat */
    array_splice( $where, $max );

    return $where;
}
