<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction pages
 *
 *
 * @package cms mini POPS
 * @subpackage enqueue - gestion des scripts et styles
 * @version 1
 */


/***********************************************/
/*          Functions medias attachés          */
/***********************************************/

/*add data pour IE*/

global $mp_style, $mp_script;

$mp_style  = array();
$mp_script = array();


/*
 * Register a CSS stylesheet.
 * @param string           $handle (doit être unique)
 * @param string           $src    Full ou path de la feuille de style
 * @param array            $deps   Optional
 * @param string|bool|null $ver    Optional. Numéro de version, if it has one, which is added to the URL
 *                                 as a query string for cache busting purposes. If version is set to false, a version
 *                                 number is automatically added equal to current installed WordPress version.
 *                                 If set to null, no version is added.
 * @param string           $media  Optional
 *                                 Default 'all'. Accepts media types like 'all', 'print' and 'screen'
 * @return bool
 */
function mp_register_style( $handle, $src = false, $deps = array(), $ver = false, $media = 'all' ) {

    // On récupère la liste des style
    $mp_register_style = mp_cache_data('mp_register_style');

    // On ajoute notre style
    if( !isset($mp_register_style[$handle]) )
        $mp_register_style[$handle] = array('source'=>$src, 'dependencies'=>$deps, 'version'=>$ver, 'media'=>$media);
    else 
        return false;

    // Mise à jour de la liste des style
    $mp_register_style = mp_cache_data('mp_register_style', $mp_register_style);

    return true;
}


/*
 * add inline a CSS stylesheet.
 * @param string           $handle (doit être unique)
 * @param string           $data   donnée css
 * @param array            $deps   Optional.
 *
 * @return bool
 */
function add_inline_style( $handle, $data, $deps = array() ){

    if ( false !== stripos( $data, '</style>' ) ) {

        _doing_it_wrong( __FUNCTION__, sprintf('Do not pass %1$s tags to %2$s.', '<code>&lt;style&gt;</code>','<code>wp_add_inline_style()</code>') );
            $data = trim( preg_replace( '#<style[^>]*>(.*)</style>#is', '$1', $data ) );
    }
    
    $deps = array_merge( $deps, array('data'=>$data) );

    return mp_register_style( $handle, false , $deps );

}


/*
 * remove a handle CSS stylesheet.
 * @param string           $handle (doit être unique)
 *
 * @return bool
 */
function mp_deregister_style( $handle ) {

    // On récupère la liste des style
    $mp_deregister_style = mp_cache_data('mp_register_style');

    // On supprimer le handle trouvé
    if( isset($mp_deregister_style[$handle]) ){

        // Suppression du handle
        unset($mp_deregister_style[$handle]);
        // Mise à jour de la liste des style
        mp_cache_data('mp_register_style', $mp_deregister_style);
        return true;
    }

    return false;
}


function enqueue_style( $handle ){

    $enqueue_style = mp_cache_data('mp_register_style');

    if( !isset($enqueue_style[$handle]) )
        return false;

    // On détermine si style embed
    if( false === $enqueue_style[$handle]['source'] && !empty($enqueue_style[$handle]['dependencies']['data']) ){

        $data = apply_filters('pre_style_embed_data', $enqueue_style[$handle]['dependencies']['data']);

        if( strlen($data) === 0)
            return false;

        if( isset($enqueue_style[$handle]['dependencies']['after']) && true===$enqueue_style[$handle]['dependencies']['after'] )
            $after = true;
        else
            $after = false;

        return array('style'=>'embed', 'after'=>$after, 'data' =>'<style type="text/css">'. $data .'</style>'. PHP_EOL);

    } else {

        $source = $enqueue_style[$handle]['source'];
        $url    = esc_url_raw( rel2abs($source, MP_TEMPLATE_URL) );

        if( strlen($url) === 0 )
            return false;

        return array('style'=>'enqueue', 'after'=>true, 'source' => $url );


    }

}


/*
 * remove a handle CSS stylesheet.
 * @param string           $after filtre optional pour style dans footer ou dans header
 *
 * @return bool
 */
function wmp_enqueue_styles( $after = false ){

    $after = (bool) $after;

    // Init vars
    $inline = array();

    // On récupère la liste des style
    $enqueue_styles = mp_cache_data('mp_register_style');

    if( is_null($enqueue_styles) || !is_array($enqueue_styles) )
        return false;

    foreach ($enqueue_styles as $handle => $value) {

        $style = enqueue_style($handle);

        _echo($style,1);

    } 


}



/***********************************************/
/*       Enqueue file script or style          */
/***********************************************/

// CONDITIONNAL
            // gt pour «greater than»)
            // ≥ (mot-clé gte pour «greater than equal»)
            // < (mot-clé lt pour «less than»)
            // ≤ (mot-clé lte pour «less than equal»)
            // | "OR"
            // & 'AND"'
            // exemple: <!-- [if (lt IE 6)|(IE 8)] --> <!-- [endif] -->

// Un handled doit être unique selon son placement ( dans le header ou le footer ) qu'il soit inline ou enqueue

/**
 * On ajouter une feuille de style en file d'attente
 * @param  $handled  : nom de la feuille de style
 * @param  $src      : url de la feuille de style
 * @param  $array    : Paramètre  tel que "conditionnal" et "cache"
 * @param  $media    : Type de media css "all", "screen", etc ...
 * @param  $ver      : version feuille de style
 * @param  $footer   : boolean, true pour placer la feuille de style dans le footer de la page sinon dans le header
 * @param  $type     : "style" ou "script"
 * @return array    retourne les résultats sous forme de tableau
 */
function mp_enqueue_style( $handled, $src , $array = array(), $media = null , $ver = null, $footer = false, $type ='style' ){

    if( $footer )   $where = 'footer';
    else            $where = 'header';

    $source   = apply_filters('mp_enqueue_'.$type.'_src', esc_url_raw($src) );
    $handled  = apply_filters('mp_enqueue_'.$type.'_handled', sanitize_file_name($handled) );
    $path     = $_SERVER['DOCUMENT_ROOT'].parse_url($source, PHP_URL_PATH);

    // Extension valid
    $type_extension = ($type === 'script') ? 'js' : 'css';

    if( pathinfo($path, PATHINFO_EXTENSION) === $type_extension
        && glob($path)
        && is_sup($handled,0)
        && !isset($GLOBALS['mp_'.$type][$where]['enqueue'][$handled])
        && !isset($GLOBALS['mp_'.$type][$where]['inline'][$handled])
    ) {

        $media = array();
        $filetime = filemtime($path);
        $version  = !empty($ver) && is_num($ver) ? $ver : date( 'Ymjhi',filemtime($path) );
        $cache    = isset( $array['cache'] ) && is_bool( $array['cache'] )? $array['cache'] : true;
        $before   = !empty( $array['conditional'] ) ? '<!--[if '.strip_all_tags( $array['conditional'] ).']>' : '';
        $after    = !empty( $before ) ? '<![endif]-->' : '';

        $medias_types = apply_filters('mp_medias_types', array('all', 'screen', 'handheld', 'print','braille','embossed','projection','screen','speech','tty','tv') );

        if( is_same($type, 'style') )
            $media = !empty($media) && is_in( $media, $medias_types ) ? array('media'=>$media) : array('media'=>'all');

        $GLOBALS['mp_'.$type][$where]['enqueue'][$handled] = array(
                                                            'source'   => $source,
                                                            'handled'  => $handled,
                                                            'path'     => $path,
                                                            'filetime' => $filetime,
                                                            'version'  => $version,
                                                            'before'   => $before,
                                                            'after'    => $after,
                                                            'cache'    => $cache,
                                                            );
        $GLOBALS['mp_'.$type][$where]['enqueue'][$handled] = array_merge($GLOBALS['mp_'.$type][$where]['enqueue'][$handled], $media);
        ksort( $GLOBALS['mp_'.$type][$where]['enqueue'] );
    }

}


/**
 * On ajouter un fichier script en file d'attente
 * @param  $handled  : nom de la feuille de style
 * @param  $src      : url de la feuille de style
 * @param  $media    : Type de media css "all", "screen", etc ...
 * @param  $ver      : version feuille de style
 * @param  $footer   : boolean, true pour placer la feuille de style dans le footer de la page sinon dans le header
 * @return array    retourne les résultats sous forme de tableau
 */
function mp_enqueue_script( $handled, $src , $ver = null, $footer = false ){
    mp_enqueue_style( $handled, $src , null , null , $ver, $footer, $type ='script' );
}


/***********************************************/
/*       enqueue inline script or style        */
/***********************************************/

/**
 * On ajouter un style en file d'attente
 * @param  $handled  : nom du style
 * @param  $data     : Donnée Style
 * @param  $footer   : boolean, true pour placer la feuille de style dans le footer de la page sinon dans le header
 */
function mp_add_inline_style( $handled, $data, $footer = false, $type = 'style' ){

    if( $footer )   $where = 'footer';
    else            $where = 'header';

    $handled  = apply_filters('mp_inline_'.$type.'_handled', sanitize_file_name($handled) );
    $data     = apply_filters('mp_inline_'.$type.'_data', $data );

    if( is_sup($data,0)
        && is_sup($handled,0)
        && !isset($GLOBALS['mp_'.$type][$where]['enqueue'][$handled])
        && !isset($GLOBALS['mp_'.$type][$where]['inline'][$handled])
    ) {
        $GLOBALS['mp_'.$type][$where]['inline'][$handled] = $data;
        ksort( $GLOBALS['mp_'.$type][$where]['inline'] );
    }

}

/**
 * On ajouter un script en file d'attente
 * @param  $handled  : nom du script
 * @param  $data     : Donnée script
 * @param  $footer   : boolean, true pour placer la feuille de style dans le footer de la page sinon dans le header
 */
function mp_add_inline_script( $handled, $data, $footer = false ){
    mp_add_inline_style( $handled, $data, $footer, 'script' );
}


/***********************************************/
/*       dequeue handled script or style       */
/***********************************************/

/**
 * On supprime une feuille de style ou un style en ligne
 * @param  $handled  : nom de la feuille de style ou du style en ligne
 * @param  $footer   : boolean, true pour supprimer une feuille de style dans le footer ou sinon dans le header
 * @param  $type     : "style" ou "script"
 */
function mp_dequeue_style( $handled , $footer = false , $type = 'style' ){

    if( $footer )   $where = 'footer';
    else            $where = 'header';

    if( !empty($GLOBALS['mp_'.$type][$where]['enqueue'][$handled]) )
        unset( $GLOBALS['mp_'.$type][$where]['enqueue'][$handled] );
    elseif( !empty($GLOBALS['mp_'.$type][$where]['inline'][$handled]) )
        unset( $GLOBALS['mp_'.$type][$where]['inline'][$handled] );

}

/**
 * On supprime une feuille de style ou un style en ligne
 * @param  $handled  : nom de la feuille de style ou du style en ligne
 * @param  $footer   : boolean, true pour supprimer une feuille de style dans le footer ou sinon dans le header
 */
function mp_dequeue_script( $handled , $footer = false ){
    mp_dequeue_style( $handled , $footer , 'script' );
}


/***********************************************/
/*       echo enqueue styles or scripts         */
/***********************************************/

/**
 * On liste les fichiers style en file d'attente
 * @param  $footer   : boolean, true pour supprimer une feuille de style dans le footer ou sinon dans le header
 * @param  $type     : "style" ou "script"
 * @return string    retourne les liens ou le contenu
 */
function mp_enqueue_styles( $footer = false, $type ='style' ){

    $enqueue = array();
    $inline  = array();

    // Toogle: où on place enqueue_styles
    if( $footer )       $where = 'footer';
    else                $where = 'header';

    // On vérifie que la table $where existe
    if( !array_key_exists( $where , $GLOBALS['mp_'.$type] ) ) return;

    // On récupère les éléments link
    if ( array_key_exists( 'enqueue' , $GLOBALS['mp_'.$type][$where] ) ){

        foreach ($GLOBALS['mp_'.$type][$where]['enqueue'] as $handled => $data) {

            extract($data);
            if(is_same($type,'style')){
                $scheme    = '<link rel="stylesheet" type="text/css" id="%1$s" href="%2$s" media="%3$s">'."\n";
                $scheme    = apply_filters('mp_enqueue_style_scheme', $scheme);
                $link      = $before.sprintf( $scheme, $handled, $source .'?ver='.substr( md5($version), -12, 10 ), $media ).$after;
                $enqueue   = array_merge( $enqueue, apply_filters('mp_enqueue_style_link', array($handled=>$link), $data) );
            }
            else{
                $scheme    = '<link rel="javascript" type="text/javascript" id="%1$s" href="%2$s">'."\n";
                $scheme    = apply_filters('mp_enqueue_script_scheme', $scheme);
                $link      = $before.sprintf( $scheme, $handled, $source .'?ver='.substr( md5($version), -12, 10 ) ).$after;
                $enqueue   = array_merge( $enqueue, apply_filters('mp_enqueue_script_link', array($handled=>$link), $data) );
            }
        }

    }

    $enqueue = apply_filters('mp_enqueue_'.$type.'s', $enqueue, $footer );

    // On récupère les éléments en ligne
    if ( array_key_exists( 'inline' , $GLOBALS['mp_'.$type][$where] ) ) {
        $inline_enqueue = '';
        // On concate les éléments en ligne
        foreach ($GLOBALS['mp_'.$type][$where]['inline'] as $handled => $data )
            $inline_enqueue .= $data;

        $inline_enqueue = apply_filters('mp_inline_'.$type.'s', $inline_enqueue, $footer );

        if( strlen($inline_enqueue) > 0 ){
            if(is_same($type,'style'))
                $inline[] = '<style type="text/css">'. $inline_enqueue .'</style>'. "\n";
            else
                $inline[] = '<script type="text/javascript">'. $inline_enqueue .'</script>'. "\n";
        }

    }

    if( !empty($enqueue) )
        $mp_enqueue = array_merge($enqueue, $inline);
    else
        $mp_enqueue = $inline;

    if ( is_size($mp_enqueue,0) ) return;

    unset($GLOBALS['mp_'.$type][$where]); // on libère la variable global

    echo implode($mp_enqueue);
}


/**
 * On liste les fichiers script en file d'attente
 * @param  $footer   : boolean, true pour supprimer une feuille de style dans le footer ou sinon dans le header
 * @return string    retourne les liens ou le contenu
 */
function mp_enqueue_scripts( $footer = false ){
    return mp_enqueue_styles( $footer, $type ='script');
}