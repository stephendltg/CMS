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

    /* Init "where" */
    if( empty($args['where']) ) $args['where'][] = $query;

     /* Init et validation "max" */
    $max = !empty($args['max']) && is_intgr($args['max']) ? $args['max'] : 10;

    /* Init et nettoyage "order" */
    $args['order'] = !empty($args['order']) ? strtoupper($args['order']) : 'ASC';

    /* Init et validation "orderby" */
    $args['orderby'] = !empty($args['orderby']) && is_in( $args['orderby'], array('date','name','type') ) ? $args['orderby'] : '';

    /* Init et validation "name" */
    if( !empty($args['name']) ){

        $args['name']= explode(',', $args['name']);
        $names = '';
        foreach( $args['name'] as $name )
            $names .= sanitize_file_name($name) ? sanitize_file_name($name).',' :'';
        $names = '{'. rtrim($names,',') .'}';

    } else $names = '*';

    /* Init et validation "type" */
    if( !empty($args['type']) ) {

        $args['type'] = explode( ',', strtolower($args['type']) );
        $types = '';
        foreach( $args['type'] as $type )
            $types .= is_in( $type, $extension ) ? $type.',' : '';
        $types = '{'. rtrim($types,',') .'}';

    } else $types = '*';


    /* On créer la recherche */
    $search = $names.'.'.$types;

    /* On récupère la liste des fichiers en nettoyant les fichiers sensibles */
    foreach( $args['where'] as $slug ){
        $medias = glob( str_replace( '//', '/', CONTENT .'/'. $slug .'/'. $search ) , GLOB_BRACE );
        $medias = array_diff( $medias , array( CONTENT.'/site.txt', CONTENT.'/'.$slug.'/'.basename($slug).'.txt') );
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
                $tmp = array_map( function($value){ return basename(str_replace(CONTENT.'/','',$value));} , $medias );
                break;
            default:
                return array();
                break;
        }

        $medias = array_combine( $medias , $tmp );
        if( is_same($args['order'], 'ASC') ) asort($medias);
        if( is_same($args['order'], 'DESC') ) arsort($medias);
        $medias = array_keys($medias);

    } else {

        /* On filtre par "order" uniquement */
        if( is_same($args['order'], 'ASC') ) sort($medias);
        if( is_same($args['order'], 'DESC') ) rsort($medias);

    }

    /* Mode shuffle valid uniquement sans orderby */
    if( is_same($args['order'], 'SHUFFLE') ) shuffle($medias);

    /* Limite de resultat */
    array_splice( $medias, $max );

    /* On renvoie le tableau sous forme de slug */
    return array_map( function($value){ return str_replace(CONTENT.'/','',$value);} , $medias );
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
function get_the_images( $name ='', $where = array(), $max = 10 ){

    $max    = (integer) $max;
    $name   = (string) $name;

    $types  = apply_filter('the_images_type' , 'jpg,jpeg,png,gif,svg' );
    $images = get_attached_media( array('where'=>$where, 'name'=>$name, 'type'=>$types, 'max'=>$max) );
    $images = array_map( function($image){ return CONTENT_URL.'/'.$image;} , $images );

    return $images;
}

/**
 * Recherche une image attachés
 * @param  $where           array() : Listes des slugs de pages où chercher les images sous forme de tableau
 * @param  $name            string  : Listes des noms de medias recherchés séparer par des virgules ex: drums,loops
 * @return array    retourne les résultats sous forme de tableau
 */
function get_the_image( $name ='' ){

    $name   = (string) $name;

    return implode( get_attached_media( array('name'=>$name, 'type'=>'jpg,jpeg,png,gif,svg', 'max'=> 1) ) );
}
