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


function enqueue_style( $handled, $src , $array = array(), $media = null , $ver = null, $footer = false, $type ='style' ){

    if( $footer )   $where = 'footer';
    else            $where = 'header';

    $source   = apply_filter('mp_enqueue_'.$type.'_src', esc_url_raw($src) );
    $handled  = apply_filter('mp_enqueue_'.$type.'_handled', sanitize_file_name($handled) );
    $path     = $_SERVER['DOCUMENT_ROOT'].parse_url($source, PHP_URL_PATH);

    if( glob($path)
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

        $medias_types = apply_filter('mp_medias_types', array('all', 'screen', 'handheld', 'print','braille','embossed','projection','screen','speech','tty','tv') );

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

function enqueue_script( $handled, $src , $ver, $footer = false ){
    enqueue_style( $handled, $src , null , null , $ver, $footer, $type ='script' );
}


/***********************************************/
/*       enqueue inline script or style        */
/***********************************************/


function add_inline_style( $handled, $data, $footer = false, $type = 'style' ){

    if( $footer )   $where = 'footer';
    else            $where = 'header';

    $handled  = apply_filter('mp_inline_'.$type.'_handled', sanitize_file_name($handled) );
    $data     = apply_filter('mp_inline_'.$type.'_data', strip_all_tags($data) );

    if( is_sup($data,0)
        && is_sup($handled,0)
        && !isset($GLOBALS['mp_'.$type][$where]['enqueue'][$handled])
        && !isset($GLOBALS['mp_'.$type][$where]['inline'][$handled])
    ) {
        $GLOBALS['mp_'.$type][$where]['inline'][$handled] = $data;
        ksort( $GLOBALS['mp_'.$type][$where]['inline'] );
    }
}


function add_inline_script( $handled, $data, $footer = false ){
    inline_style( $handled, $data, $footer, 'script' );
}


/***********************************************/
/*       dequeue handled script or style       */
/***********************************************/

function dequeue_style( $handled , $footer = false , $type = 'style' ){

    if( $footer )   $where = 'footer';
    else            $where = 'header';

    if( !empty($GLOBALS['mp_'.$type][$where]['enqueue'][$handled]) )
        unset( $GLOBALS['mp_'.$type][$where]['enqueue'][$handled] );
    elseif( !empty($GLOBALS['mp_'.$type][$where]['iniline'][$handled]) )
        unset( $GLOBALS['mp_'.$type][$where]['inline'][$handled] );

}


function dequeue_script( $handled , $footer = false ){
    dequeue_style( $handled , $footer , 'script' );
}


/***********************************************/
/*       echo enqueue styles or scripts         */
/***********************************************/

function enqueue_styles( $footer = false, $type ='style' ){

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
                $scheme    = apply_filter('mp_enqueue_style_scheme', $scheme);
                $link      = $before.sprintf( $scheme, $handled, $source .'?ver='.substr( md5($version), -12, 10 ), $media ).$after;
                $enqueue   = array_merge( $enqueue, apply_filter('mp_enqueue_style_link', array($handled=>$link), $data) );
            }
            else{
                $scheme    = '<link rel="javascript" type="text/javascript" id="%1$s" href="%2$s">'."\n";
                $scheme    = apply_filter('mp_enqueue_script_scheme', $scheme);
                $link      = $before.sprintf( $scheme, $handled, $source .'?ver='.substr( md5($version), -12, 10 ) ).$after;
                $enqueue   = array_merge( $enqueue, apply_filter('mp_enqueue_script_link', array($handled=>$link), $data) );
            }
        }

    }

    $enqueue = apply_filter('mp_enqueue_'.$type.'s', $enqueue, $footer );

    // On récupère les éléments en ligne
    if ( array_key_exists( 'inline' , $GLOBALS['mp_'.$type][$where] ) ) {
        $inline_enqueue = '';
        // On concate les éléments en ligne
        foreach ($GLOBALS['mp_'.$type][$where]['inline'] as $handled => $data )
            $inline_enqueue = $data;

        $inline_enqueue = apply_filter('mp_inline_'.$type.'s', $inline_enqueue );
        if( strlen($inline_enqueue) > 0 ){
            if(is_same($type,'style'))
                $inline[$handled] = '<style type="text/css">'. $inline_enqueue .'</style>'. "\n";
            else
                $inline[$handled] = '<script type="text/javascript">'. $inline_enqueue .'</script>'. "\n";
        }
    }

    $inline = apply_filter('mp_inline_'.$type.'s', $inline, $footer );

    $mp_enqueue = array_merge($enqueue, $inline);

    if ( is_size($mp_enqueue,0) ) return;

    echo implode($mp_enqueue);
}

function enqueue_scripts( $footer = false ){
    return enqueue_styles( $footer, $type ='script');
}