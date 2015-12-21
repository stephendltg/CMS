<?php defined('ABSPATH') or die('No direct script access.');
/**
 *
 *
 * @package cms mini POPS
 * @subpackage cache
 * @version 1
 */


// Add filter optimisation HTML
add_action('get_header', function(){ ob_start('mp_minify_html'); } , 1000 );

// Add filter pour enqueue inline style
add_filter('mp_inline_styles', 'mp_easy_minify');

// Add filter pour preparer la concetanation des fichiers css
add_filter('mp_enqueue_style_link', 'mp_prepare_concatenate', 10, 2 );

// Add filter pour la concetanation des fichiers css
add_filter('mp_enqueue_styles', 'mp_concatenate_css', 10, 2 );

// Add filter pour preparer la concetanation des fichiers js
add_filter('mp_enqueue_script_link', 'mp_prepare_concatenate', 10, 2 );

// Add filter pour la concetanation des fichiers js
add_filter('mp_enqueue_scripts', 'mp_concatenate_js', 10, 2 );

//On minifie le fichier de ccombination
add_filter('mp_before_concatenate', 'mp_easy_minify');


/***********************************************/
/*                  js/css Minify              */
/***********************************************/
// Minifie js et css simplement
function mp_easy_minify( $str ){
    // On enlève les commentaires
    $str = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $str );
    /* remove tabs, spaces, newlines, etc. */
    return str_replace( array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $str );
}


/***********************************************/
/*                  html Minify                */
/***********************************************/
function mp_minify_html($html){

    preg_match_all( '/<(?<tag>[\/\w.:-]*)(?:".*?"|\'.*?\'|[^\'">]+)*>|(?<text>((<[^!\/\w.:-])?[^<]*)+)|/si', $html, $matches, PREG_SET_ORDER );
    $raw_tag = false;
    $html = '';
    foreach( $matches as $token ){
        $tag     = isset( $token['tag'] ) ? strtolower( $token['tag'] ) : null;
        $content = $token[0];
        if( is_null($tag) ){
            if($raw_tag != 'textarea')
                $content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content);
        }
        else {
            if( $tag == 'pre' || $tag == 'textarea' )   $raw_tag = $tag;
            else if( $tag == '/pre' || $tag == '/textarea' )  $raw_tag = false;
            else {
                if ( $raw_tag )  $strip = false;
                else {
                    $strip   = true;
                    $content = preg_replace('/(\s+)(\w++(?<!\baction|\balt|\bcontent|\bsrc)="")/', '$1', $content);
                    $content = str_replace(' />', '/>', $content);
                }
            }
        }
        if ($strip) {
            $content = str_replace(array("\r\n", "\r", "\n", "\t"), '', $content);
            while ( stristr($content, '  ') )
                $content = str_replace('  ', ' ', $content);
        }
        $html .= $content;
    }
    return $html;
}


/***********************************************/
/*          Compress image                     */
/***********************************************/
/**
 * Compression d'image
 * @param  $src      Source image
 * @param  $quality  normal, hard, ultra
 * @return boolean
 */

function mp_image_compress( $src, $mode = 'normal' ) {

    if( function_exists('imagecreatefrompng') // On vérifie que GD est présent
    && file_exists($src)                      // On vérifie l'existence du fichier
    && is_writable($src)                      // On vérifie les permissions
    && is_in( exif_imagetype($src), array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG) )  // C'est bien une image !
    ){

        $image_size = getimagesize($src);
        $file_mime  = end( @explode('/', $image_size['mime']) );

        // Gestion mémoire
        $m_img     = round(($image_size[0] * $image_size[1] * $image_size['bits'] * $image_size['channels'] / 8 + pow(2, 16)) * 1.65);
        $m_need    = $m_img + memory_get_usage();
        $m_need    = round($m_need / pow(1024,2),2);
        $m_limit   = (int) get_limit_memory();
        $m_alloc   = $m_need - $m_limit;
        // Si pas assez de mémoire on stop
        if( is_min($m_alloc, 0) ) return false;

        /* On créer la ressource mémoire pour l'image */
        switch ($file_mime) {
            case 'jpeg':
                $image   = imagecreatefromjpeg($src);
                $quality = apply_filter('mode_jpeg_compress', array('normal'=>85, 'hard'=>80, 'ultra'=>75) );
                $quality = array_key_exists( strtolower($mode), $quality) ? $quality[$mode] : 85;
                $created = imagejpeg( $image, $src , $quality );
                break;
            case 'png':
                $image   = imagecreatefrompng($src);
                $quality = apply_filter('mode_png_compress', array('normal'=>1, 'hard'=>2, 'ultra'=>3) );
                $quality = array_key_exists( strtolower($mode), $quality) ? $quality[$mode] : 1;
                $created = imagepng($image, $src , $quality );
                break;
            case 'gif':
                $image   = imagecreatefromgif($src);
                $created = imagegif($image, $src );
                break;
            default:
                return false;
                break;
        }
        imagedestroy($image);
        return $created;

    } else {
        return false;
    }
}

/***********************************************/
/*        Concate file                         */
/***********************************************/

function mp_prepare_concatenate( $link, $data ){

    global $concatenate;

    //$concatenate = array();

    extract($data);

    // On force $media pour les script
    if(!isset($media))
        $media ='all';

    // On créer la table à combiner selon l'emplacement
    if( $cache
        && is_size($before, 0)
    ){
        $concatenate[$handled] = array('path' => $path, 'source' => $source, 'filetime' => $filetime, 'media' => $media );
    }
    return $link;
}


/***********************************************/
/*        Concate css                          */
/***********************************************/

function mp_concatenate_css($enqueue, $footer, $type = 'css'){

    global $concatenate;

    // On créer un repertoire pour les fichiers combination soit dans le footer soit dans le header
    $footer_hash = substr( base64_encode(CONTENT_DIR).(string)$footer, -12, 10 );

    // On cherche si un fichier de combination a été créé
    $files = glob( CONTENT_DIR.'/cache/'.$footer_hash.'/*.'.$type );

    // Si un fichier combination créé on affecte le fichier au html
    if( is_size($files,1) )
        $file_concatenate = $files[0];

    // Si plusieurs fichiers de combination existe, on les supprime tous afin de surcharger le disque de fichiers inutile
    if( is_sup($files, 1) )
        foreach ($files as $file) @unlink($file);

    // On definit le schema selon le type de combination
    if(is_same($type,'css'))
        $scheme   = '<link rel="stylesheet" type="text/css" href="%1$s">'."\n";
    else
        $scheme   = '<link rel="javascript" type="text/javascript" href="%1$s">'."\n";

    // Les handles combinés qui servirons à la création du nom du fichier de combination
    $handleds = '';


    if( isset($file_concatenate) ){

        $lasttime_concatenate = filemtime($file_concatenate);
        $raw_file = false;
        $valid_handleds = '';

        foreach ($concatenate as $handled => $data){
            // On check si un fichier a été modifié
            if( $data['filetime'] > $lasttime_concatenate )
                $raw_file = true;
            // On vérifie si la liste des handleds ont été modifiés
            $valid_handleds .= $handled ;
        }

        // On hash les handles valid qui serve de nom au fichier de combination
        $valid_handleds = substr( md5($valid_handleds), -12, 10 );

        if(!$raw_file && is_same( $valid_handleds.'.css', basename($file_concatenate) ) ){
            // On dequeue les handleds combinés
            foreach ($concatenate as $handled => $data)
                unset($enqueue[$handled]);
            // On purge la table concatenate pour éviter doublon
            $concatenate = array();
            // On récupère l'url du fichier de combination
            $file_concatenate_url = rel2abs(str_replace(ABSPATH ,'' ,$file_concatenate) );
            // On enqueue le fichier de combination
            $enqueue[] = sprintf( $scheme, $file_concatenate_url );
            return $enqueue;
        }

    }

    // On supprime le fichier de combination s'il existe
    @unlink($file_concatenate);

    // Contenu du fichier de combination
    $file_content = '';

    foreach ($concatenate as $handled => $data){
        // On liste les fichiers handleds pour le nom du fichier de combination
        $handleds  .= $handled;
        // On traite le contenu du fichier de combination
        $content    = file_get_contents( $data['path'] );
        // On remplace les url relative en absolu
        $content    = preg_replace_callback(
                '#url\s*\(\s*[\'"]?([^\'"\)]+)[\'"]\s*\)#',
                function($matches) use ($data) { return 'url("'. rel2abs( $matches [1], $data['source'] ) . '")'; },
                $content
               );
        // On traite les medias css particuliers
        $before        = is_different($data['media'],'all') ? '@media '.$data['media'].'{'."\n" : '';
        $after         = !empty($before) ? "\n".'}' : '';
        $content       = $before.$content.$after;
        $file_content .= $content;
    }

    // Nom du fichier de combination
    $handleds = substr( md5($handleds), -12, 10 );

    @mkdir( CONTENT_DIR.'/cache/'.$footer_hash, 0755, true );

    $file_content = apply_filter('mp_before_concatenate', $file_content);

    // Si le fichier est bien créer on l'enqueue
    if( file_put_contents( CONTENT_DIR.'/cache/'.$footer_hash.'/'.$handleds.'.css', $file_content ) ){
        unset($file);
        // On dequeue les handleds combinés
        foreach ($concatenate as $handled => $data)
            unset($enqueue[$handled]);
        // On récupère l'url du fichier de combination
        $file_concatenate_url = rel2abs(str_replace(ABSPATH ,'' ,CONTENT_DIR.'/cache/'.$footer_hash.'/'.$handleds.'.'.$type) );
        // On enqueue le fichier de combination
        $enqueue[] = sprintf( $scheme, $file_concatenate_url );
    }
    // On purge la table concatenate pour éviter les doublons
    $concatenate = array();
    return $enqueue;
}

/***********************************************/
/*        Concate js                           */
/***********************************************/

function mp_concatenate_js($enqueue, $footer){
    mp_concatenate_css($enqueue, 'js');
}
