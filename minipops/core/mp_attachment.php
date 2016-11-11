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
 *                  'file'    string  : Recherche par nom de fichier ex:mon-image.jpg
 *                   'slug'    string  : Ou on recherche les fichiers, un seul slug autorisé
 * @return array    retourne les résultats sous forme de tableau
 */
function get_attached_media( $args = array(), $mode = 'path' ) {

    $args = parse_args( $args, array(
            'where'   => '',
            'max'     => 10,
            'order'   => 'ASC',
            'orderby' => '',
            'name'    => '*',
            'type'    => '*',
            'file'    => false,  // Nom des fichiers (doivent être dans le même repertoire)
            'slug'    => null    // Repertoire ou se trouve les fichiers
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


    /* valeur de sortie */
    $medias = array();

    /* Init et nettoyage "order" */
    $args['order'] = strtoupper($args['order']);

    /* Validation "orderby" */
    $args['orderby'] = is_in( $args['orderby'], array('date','name','type') ) ? $args['orderby'] : '';

    /* Validation "type" */
    $args['type'] = strtolower( unique_sorted_list( sanitize_list($args['type'], ','), ',' ) );
    $args['type'] = explode( ',', $args['type'] );
    $types = '';
    foreach( $args['type'] as $type )
        $types .= is_in( $type, $extension ) ? $type.',' : '';


    /* On lance la recherche s'il dagit d'une recherche par nom ou par fichier */
    if( $args['file'] === false ){

        /* validation "where" */
        $args['where'] = unique_sorted_list( sanitize_list( $args['where'], ','), ',');
        $args['where'] = explode( ',', $args['where'] );

        /* On formate les types */
        $types = '{'. rtrim($types,',') .'}';

        /* On prépare la liste des nom recherché */
        $names = '{'. unique_sorted_list( sanitize_list( $args['name'], ',' ), ',') .'}';

        /* On créer la recherche */
        $search = $names.'.'.$types;

        /* On récupère la liste des fichiers en nettoyant les fichiers sensibles */
        foreach( $args['where'] as $slug ){

            /* slug ou se trouve les fichiers */
            $slug   = strlen($slug) == 0 ? '/': '/'. trim($slug,'/') .'/';
            $medias = glob( MP_PAGES_DIR . $slug . $search, GLOB_BRACE );
            $medias = array_diff( $medias , array( MP_PAGES_DIR.'/site.yml', MP_PAGES_DIR.'/'.$slug.'/'.basename($slug).'.md') );
        
        }

        /* Gestion des max dans le cas de plusieurs fichier */
        if($args['max'] === 'auto')
            $args['max'] = count($medias);

    } else {


        /* Validation "file" */
        $files = unique_sorted_list( sanitize_list($args['file'], ','), ',' );
        $files = explode(',', $files);

        /* Gestion des max dans le cas de plusieurs fichier */
        if($args['max'] === 'auto')
            $args['max'] = count($files);

        /* slug ou se trouve les fichiers */
        $slug  = strlen($args['slug']) == 0 ? '/': '/'. trim($args['slug'],'/') .'/';

        /* On formate les types */
        $types = explode(',', rtrim($types,',') );

        /* On test tous les fichiers */
        foreach ($files as $file) {
            
            $file  = ltrim($file, '/');
            $type  = substr(strrchr($file,'.'), 1);

            if( is_in($type, $types) && file_exists( MP_PAGES_DIR . $slug . $file) )
                $medias[] = MP_PAGES_DIR . $slug . $file;
        }

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
                $tmp = array_map( 
                            function($value){ return basename(str_replace(MP_PAGES_DIR.'/','',$value)); }
                       , $medias );
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

    /*  mode de sortie */
    switch ($mode) {

        case null:
            return array_map( 'basename', $medias);
        case 'path':
            return $medias;
        case 'uri':
            return array_map( 
                        function($value){ return esc_url_raw( str_replace(MP_PAGES_DIR,MP_PAGES_URL,$value) ); }
                   , $medias );
        default:
            return array_map( 
                        function($value){ return ltrim( str_replace(MP_PAGES_DIR,'',$value) ,'/'); }
                   , $medias);
    }

}



/**
 * Recherche une image attachés
 * @param  $name            string  : Listes des noms de medias recherchés séparer par des virgules ex: drums,loops
 * @return array    retourne les résultats sous forme de tableau
 */
function get_the_image( $args, $mode = 'scheme' ){

    $args = parse_args( $args, array(
            'max'    => 1,
            'size'   => null,
        ));

    // on prepare la size
    $size = $args['size'];
    unset($args['size']);

    // init la sortie
    $images = array();

    // Requête
    $req = http_build_query($args);

    // On récupère la cache
    if( mp_cache_data('get_the_image_'.$req) ){
        
        $images = mp_cache_data('get_the_image_'.$req);

    } else {

        // Liste des images valident
        $types  = '&type='. apply_filters('the_image_type', 'jpg,jpeg,png,gif,svg' );

        // On lance la recherche
        $images = get_attached_media( $req.$types, 'path' );

        // Mise en cache
        mp_cache_data('get_the_image_'.$req, $images);

        // On sort si la table est vide
        if( empty($images) )   return;  // get_the_image sert également à get_the_page('thumbnail')
    }  

    if( $mode === 'uri' ){

        if( IMAGIFY ){

            switch ($size) {

                case 'small':
                    $images = array_map( function($image){ return imagify( $image,'width=320'); }, $images );
                    break;
                case 'medium':
                    $images = array_map( function($image){ return imagify( $image,'width=800'); }, $images );
                    break;
                case 'large':
                    $images = array_map( function($image){ return imagify( $image,'width=1024'); }, $images );
                    break;
                case 'thumbnail':
                    $images = array_map( function($image){ return imagify( $image,'width=480&height=480'); }, $images );
                    break;
                case '16/9':
                    //get_the_image('width=640&height='. ceil(640/(16/9)) .'&file='.$value, 'uri');
                    $images = array_map( function($image){ return imagify( $image,'keep=top&width=800&height='. ceil(600/(16/9)) ); }, $images );
                    break;
                default:
                    $images = array_map( function($image) use ($args) { return imagify( $image, $args ); }, $images );
                    break;
            }
        }

        $images = array_map( function($image){ return esc_url_raw( str_replace(MP_PAGES_DIR,MP_PAGES_URL,$image) ); } , $images );
    }

    return $args['max'] == 1 ? $images[0] : $images;
}



/**
* resize image
* @param  $image     image à redimenssionner
*/
function imagify( $image, $args = null){

    $image   = (string) $image;

    $args = parse_args($args, array(

        'width'   => false,    // Largeur image
        'height'  => false,    // Hauteur image
        'quality' => 75,       // Qualité compression image
        'rotate'  => 0,        // rotation de l'image (angle en degres)
        'flip'    => false,    // x, y inversion image
        'keep'    => 'top',   // center, top, right, bottom, left, top left, top right, bottom left, bottom right
        'grid'    => false
        ));

    // On check que l'image n'a pas déjà été traité
    if( strpos($image, '@') )
        return $image;

    // on récupère l'extension du fichier
    $extension  = strrchr($image,'.');

    // Paramètre image
    $params = $args['width']. ( !$args['height'] ? '' : 'x'.$args['height'] );

    // Nouveau nom d'image 
    $new_image = str_replace($extension, '@'.$params.$extension, $image);

    if( !file_exists($new_image) ){

        if( $extension !== '.svg' ){

            try {

                $img = new abeautifulsite\SimpleImage($image);

                // effet mirroir
                if( $args['flip'] === 'x' )
                    $img->flip('x');
                elseif( $args['flip'] === 'y' )
                    $img->flip('y');

                // rotation
                if( $args['rotate'] !== 0 )
                    $img->rotate( intval($args['rotate']) );

                // Resize image
                if( !$args['width'] && $args['height'] ){

                    if($args['grid']){

                        $width = ceil( $args['grid']/3 );

                        if( 'portrait' == $img->get_orientation() )
                            $img->thumbnail($width, $args['height'], $args['keep'] );
                        else
                            $img->thumbnail($width*2, $args['height'], $args['keep'] );
                    
                    } else {
                        $img->fit_to_height($args['height']);
                    }
                }

                elseif( $args['width'] && !$args['height'] )
                    $img->fit_to_width($args['width']);

                elseif( $args['width'] && $args['height'] )
                    $img->thumbnail($args['width'], $args['height'], $args['keep'] );

                // Save the image
                $img->save($new_image, intval($args['quality']) );

            } catch(Exception $e) {
                _doing_it_wrong(__FUNCTION__, $e->getMessage() );
                return $image;
            }

        } else {

            if( !$svg = file_get_content( $image ) )
                return $image;

            $svg = sanitize_svg($svg);

            if( !file_put_contents( $new_image, $svg ) )
                return $image;
        }
    }
    return $new_image;
}