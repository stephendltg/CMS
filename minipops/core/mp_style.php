<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction style
 *
 *
 * @package cms mini POPS
 * @subpackage style - gestion des styles
 * @version 1
 */


/***********************************************/
/*          Functions style                    */
/***********************************************/


/*
 * Register a CSS stylesheet.
 * @param string           $handle (doit être unique)
 * @param string           $src    Full ou path de la feuille de style
 * @param array            $deps   Optional
 * @param string|bool|null $ver    Optional. Numéro de version, if it has one, which is added to the URL
 *                                 as a query string for cache busting purposes. If version is set to false, a version
 *                                 number is automatically added equal to date year month.
 *                                 If set to null, no version is added.
 * @param string           $media  Optional
 *                                 Default 'all'. Accepts media types like 'all', 'screen', 'handheld', 'print','braille','embossed','projection','screen','speech','tty','tv'
 * @return bool
 */
function mp_register_style( $handle, $src = false, $deps = array(), $ver = false, $media = 'all' ) {

    // On récupère la liste des handles
    $mp_register_style = mp_cache_data('mp_register_style');

    // On ajoute notre handle
    if( !isset($mp_register_style[$handle]) )
        $mp_register_style[$handle] = array('source'=>$src, 'dependencies'=>$deps, 'version'=>$ver, 'media'=>$media);
    else 
        return false;

    // Mise à jour de la liste des handles
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

    if ( false !== stripos($data, '</style>') ) {

        _doing_it_wrong( __FUNCTION__, sprintf('Do not pass %1$s tags to %2$s.', '<code>&lt;style&gt;</code>','<code>add_inline_style()</code>') );
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

    // On récupère la liste des handles
    $mp_deregister_style = mp_cache_data('mp_register_style');

    // On supprimer le handle trouvé
    if( isset($mp_deregister_style[$handle]) ){

        // Suppression du handle
        unset($mp_deregister_style[$handle]);
        // Mise à jour de la liste des handles
        mp_cache_data('mp_register_style', $mp_deregister_style);
        return true;
    }

    return false;
}


/*
 * enqueue a handle CSS stylesheet.
 * @param string           $handle (doit être unique)
 *
 * @return bool
 */
function mp_enqueue_style( $handle , $src = false, $deps = array(), $ver = false, $media = 'all'){

    // On créer le $handle si définit
    if( $src )
        mp_register_style( $handle, $src, $deps, $ver, $media );

    // On charge les registers
    $enqueue_style = mp_cache_data('mp_register_style');

    // On check le handle
    if( !isset($enqueue_style[$handle]) )
        return;

    // On détermine si embed
    if( false === $enqueue_style[$handle]['source'] 
        && isset($enqueue_style[$handle]['dependencies']['data'])
        && $enqueue_style[$handle]['dependencies']['data']
        ){

        $data = apply_filters('pre_style_embed_data', $enqueue_style[$handle]['dependencies']['data'], $enqueue_style[$handle]['dependencies'] );

        if( strlen($data) === 0)
            return false;

        // On supprime le handle de la liste
        mp_deregister_style($handle);

        printf("<style id='%s-inline-css' type='text/css'>\n%s\n</style>\n", esc_attr($handle), $data);

    } else {

        /* URL */
        $url = rel2abs( $enqueue_style[$handle]['source'], MP_TEMPLATE_URL );
        $url = apply_filters('pre_style', $url, $enqueue_style[$handle]['dependencies']);
        $url = esc_url_raw( explode('?', $url)[0] );

        if( strlen($url) === 0 )
            return;

        // Prevent css extension.
        if( 'css' !== substr(strrchr($url,'.'), 1) )
            return;

        /* VERSIONING */
        if( false === $enqueue_style[$handle]['version'] )
            $version = date('Ym');
        else
            $version = sanitize_allspecialschars($enqueue_style[$handle]['version']);
        
        $url = $url . ( strlen($version) === 0  ? '' : '?' . $version );


        /* MEDIA */
        $medias_types = apply_filters('medias_types', array('all', 'screen', 'handheld', 'print','braille','embossed','projection','screen','speech','tty','tv') );

        $media = $enqueue_style[$handle]['media'];

        if( is_notin( $media, $medias_types ) ){
            _doing_it_wrong( __FUNCTION__, sprintf('Error of media type : only this '. implode(', ', $medias_types) ) );
            return;
        }


        /* REL */
        $rel = isset($enqueue_style[$handle]['dependencies']['alt']) && $enqueue_style[$handle]['dependencies']['alt'] ? 'alternate stylesheet' : 'stylesheet';
        $title = isset($enqueue_style[$handle]['dependencies']['title']) ? 'title ="'. esc_attr($enqueue_style[$handle]['dependencies']['title']) . '"' : '';

        /* CONDITIONNAL
        *
        *   gt pour «greater than»
        *   gte pour «greater than equal»
        *   lt pour «less than»
        *   lte pour «less than equal»
        *   | "OR"
        *   & 'AND"'
        *   exemple: <!-- [if (lt IE 6)|(IE 8)] --> <!-- [endif] -->
        */
        $before = '';
        $after  = '';

        if( isset($enqueue_style[$handle]['dependencies']['conditional']) ){

            $conditional = preg_replace( '/[^ltegtIE0-9 !()&|]/', '', $enqueue_style[$handle]['dependencies']['conditional'] );
            $before      = '<!--[if '. $conditional .']>' . PHP_EOL;
            $after       = PHP_EOL .'<![endif]-->';
        }

        // On supprime le handle de la liste
        mp_deregister_style($handle);

        printf( "%s<link rel='%s' %s id='%s-css' type='text/css' href='%s' media='%s'>\n%s", $before, $rel, $title, esc_attr($handle), $url, $media, $after );

    }

}