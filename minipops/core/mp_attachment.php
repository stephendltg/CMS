<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction pages
 *
 *
 * @package cms mini POPS
 * @subpackage attachment - gestion des fichiers associé aux pages
 * @version 1
 */


/***********************************************/
/*          Functions medias attachés          */
/***********************************************/

/**
 * Recherche medias attachés
 * @param  $args    array
 *                  'where'   array() : Listes des slugs de pages où chercher les images sous forme de tableau si vide recherche selon la requete $query
 *                  'type'    string  : Listes des extensions recherchés séparer par des virgules ex: json,jpeg
 *                  'name'    string  : Listes des noms de medias recherchés séparer par des virgules ex: drums,loops
 *                  'max'     integer : Nombre de résultat par défaut : 10
 *                  'order'   string  : Mode de tri "ASC" ( par défaut ), "DESC" ou "SHUFFLE"
 *                  'orderby' string  : Trier par "date" ( date du fichier, par défaut ), "name" ( nom du fichier) ou par "type" ( extension de fichier )
 * @return array    retourne les résultats sous forme de tableau
 */
function get_attached_media( $args = array() ) {

    global $query;

    $args = parse_args( $args, array(
            'where' => $query,
            'max'   => 10,
            'order' => 'ASC',
            'orderby' => '',
            'name'    => '*',
            'type'    => '*'
        ));


    /* Table d'extension valid */
    $extension = array(
        'jpg','jpeg','png','gif','svg','bmp','tiff',
        'mp3','ogg','wma','m4a','wav','aiff',
        'mp4','mov','m4v','swf','flv',
        'mpeg','avi','wmv',
        'doc','docx',
        'xls','xlt','xlm','xld','xla','xlc','xlw','xll',
        'ppt','pps',
        'rtf','txt',
        'pdf',
        'zip','gz','tar',
        'css',
        'js',
        'json',
        'xml',
        'html','htm'
    );


    /* validation "where" */
    $args['where'] = sanitize_list( $args['where'], ',');
    $args['where'] = explode( ',', $args['where'] );

    /* Init et nettoyage "order" */
    $args['order'] = strtoupper($args['order']);

    /* Validation "orderby" */
    $args['orderby'] = is_in( $args['orderby'], array('date','name','type') ) ? $args['orderby'] : '';

    /* Validation "type" */
    $args['type'] = strtolower( sanitize_list($args['type'], ',') );
    $args['type'] = explode( ',', $args['type'] );
    $types = '';
    foreach( $args['type'] as $type )
        $types .= is_in( $type, $extension ) ? $type.',' : '';
    $types = '{'. rtrim($types,',') .'}';

    /* On prépare la liste des nom recherché */
    $names = '{'. sanitize_list( $args['name'], ',' ) .'}';

    /* On créer la recherche */
    $search = $names.'.'.$types;

    /* On récupère la liste des fichiers en nettoyant les fichiers sensibles */
    foreach( $args['where'] as $slug ){

        $medias = glob( MP_PAGES_DIR .'/'. $slug .'/'. $search, GLOB_BRACE );
        $medias = array_diff( $medias , array( MP_PAGES_DIR.'/site.yml', MP_PAGES_DIR.'/'.$slug.'/'.basename($slug).'.md') );
    }

    /* On filtre par "orderby" et "order" */
    if( !empty($args['orderby']) ){

        switch ($args['orderby']) {
            case 'date':
                $tmp = array_map( function($value){ return filemtime( $value );} , $medias );
                break;
            case 'type':
                $tmp = array_map( function($value){ return substr(strrchr($value,'.'),1);} , $medias );
                break;
            case 'name':
                $tmp = array_map( function($value){ return basename(str_replace(MP_PAGES_DIR.'/','',$value));} , $medias );
                break;
            default:
                return array();
                break;
        }

        $medias = array_combine( $medias , $tmp );
        if( is_same($args['order'], 'ASC') )  asort($medias);
        if( is_same($args['order'], 'DESC') ) arsort($medias);
        $medias = array_keys($medias);

    } else {

        /* On filtre par "order" uniquement */
        if( is_same($args['order'], 'ASC') )  sort($medias);
        if( is_same($args['order'], 'DESC') ) rsort($medias);

    }

    /* Mode shuffle valid uniquement sans orderby */
    if( is_same($args['order'], 'SHUFFLE') ) shuffle($medias);

    /* Limite de resultat */
    array_splice( $medias, intval($args['max']) );

    /* On renvoie le tableau sous forme de slug */
    return array_map( function($value){ return ltrim( str_replace(MP_PAGES_DIR.'/','',$value), '/');} , $medias );
}


/***********************************************/
/*          Functions images attachées         */
/***********************************************/

/**
 * Recherche images attachés
 * @param  $where           array() : Listes des slugs de pages où chercher les images sous forme de tableau
 * @param  $name            string  : Listes des noms de medias recherchés séparer par des virgules ex: drums,loops
 * @param  $max             integer : Nombre de résultat par défaut : 10
 * @return array    retourne les résultats sous forme de tableau
 */
function get_the_images( $name = '*', $where = null, $max = 10 ){

    $max    = (integer) $max;
    $name   = (string) $name;

    do_action('do_before_get_the_images', $name, $where );

    $types  = apply_filters('the_images_type' , 'jpg,jpeg,png,gif,svg' );

    $images = get_attached_media( array('where'=>$where, 'name'=>$name, 'type'=>$types, 'max'=>$max) );
    $images = array_map( function($image){ return MP_PAGES_URL.'/'.$image;} , $images );

    do_action('do_after_get_the_images', $name, $where );

    return $images;
}

/**
 * Recherche une image attachés
 * @param  $name            string  : Listes des noms de medias recherchés séparer par des virgules ex: drums,loops
 * @return array    retourne les résultats sous forme de tableau
 */
function get_the_image( $name = '*', $url = true ){

    $name   = (string) $name;

    do_action('do_before_get_the_image', $name );

    $types  = apply_filters('the_image_type' , 'jpg,jpeg,png,gif,svg' );

    $image = implode( get_attached_media( array('name' => $name, 'type' => $types, 'max'=> 1) ) );

    do_action('do_after_get_the_image', $name );

    if( !$image ) return;

    return $url ? MP_PAGES_URL.'/'.$image : MP_PAGES_DIR.'/'.$image;
}


/***********************************************/
/*          Utilitaire image                   */
/***********************************************/

/**
* Lecture info image
* @param  $image     info de l'image à récupérer
*/
function image_args( $image, $args = 'all' ){

    $image   = (string) $image;
    $args    = (string) $args;

    try {
        $img = new abeautifulsite\SimpleImage($image);
        $info = $img->get_original_info();
        if( $args !== 'all')
            $info = isset($info[$args]) ? $info[$args] : false;
    } catch(Exception $e) {
        _doing_it_wrong(__FUNCTION__, $e->getMessage() );
        return false;
    }
    return $info;
}


/**
* Compresseur d'image
* @param  $image     image à compresser
*/
function imagify( $image, $quality = 80 ){

    $image   = (string) $image;
    $quality = (int) $quality;

    try {
        $img = new abeautifulsite\SimpleImage($image);
        $img->flip('x')->save($image, $quality);
    } catch(Exception $e) {
        _doing_it_wrong(__FUNCTION__, $e->getMessage() );
        return false;
    }
}