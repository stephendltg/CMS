<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction script
 *
 *
 * @package cms mini POPS
 * @subpackage script - gestion des script
 * @version 1
 */


/***********************************************/
/*          Functions script                    */
/***********************************************/


/*
 * Register a script.
 * @param string           $handle (doit être unique)
 * @param string           $src    Full ou path de la feuille de script
 * @param array            $deps   Optional
 * @param string|bool|null $ver    Optional. Numéro de version, if it has one, which is added to the URL
 *                                 as a query string for cache busting purposes. If version is set to false, a version
 *                                 number is automatically added equal to date year month.
 *                                 If set to null, no version is added.
 * @return bool
 */
function mp_register_script( $handle, $src = false, $deps = array(), $ver = false ) {

    // On récupère la liste des handles
    $mp_register_script = mp_cache_data('mp_register_script');

    // On ajoute notre handle
    if( !isset($mp_register_script[$handle]) )
        $mp_register_script[$handle] = array('source'=>$src, 'dependencies'=>$deps, 'version'=>$ver);
    else 
        return false;

    // Mise à jour de la liste des handles
    $mp_register_script = mp_cache_data('mp_register_script', $mp_register_script);

    return true;
}


/*
 * add inline a script.
 * @param string           $handle (doit être unique)
 * @param string           $data   donnée css
 * @param array            $deps   Optional.
 *
 * @return bool
 */
function add_inline_script( $handle, $data, $deps = array() ){

    if ( false !== stripos($data, '</script>') ) {

        _doing_it_wrong( __FUNCTION__, sprintf('Do not pass %1$s tags to %2$s.', '<code>&lt;script&gt;</code>','<code>add_inline_script()</code>') );
        $data = trim( preg_replace( '#<script[^>]*>(.*)</script>#is', '$1', $data ) );
    }
    
    $deps = array_merge( $deps, array('data'=>$data) );

    return mp_register_script( $handle, false , $deps );
}


/*
 * remove a handle CSS scriptsheet.
 * @param string           $handle (doit être unique)
 *
 * @return bool
 */
function mp_deregister_script( $handle ) {

    // On récupère la liste des handles
    $mp_deregister_script = mp_cache_data('mp_register_script');

    // On supprimer le handle trouvé
    if( isset($mp_deregister_script[$handle]) ){

        // Suppression du handle
        unset($mp_deregister_script[$handle]);
        // Mise à jour de la liste des handles
        mp_cache_data('mp_register_script', $mp_deregister_script);
        return true;
    }

    return false;
}


/*
 * enqueue a handle script.
 * @param string           $handle (doit être unique)
 *
 * @return bool
 */
function mp_enqueue_script( $handle, $src = false, $deps = array(), $ver = false  ){

    // On créer le $handle si définit
    if( $src )
        mp_register_script( $handle, $src, $deps, $ver );

    // On charge les registers
    $enqueue_script = mp_cache_data('mp_register_script');

    // On check le handle
    if( !isset($enqueue_script[$handle]) )
        return;

    // On détermine si embed
    if( false === $enqueue_script[$handle]['source'] 
        && isset($enqueue_script[$handle]['dependencies']['data']) 
        && $enqueue_script[$handle]['dependencies']['data']
        ){

        $data = apply_filters('pre_script_embed_data', $enqueue_script[$handle]['dependencies']['data'], $enqueue_script[$handle]['dependencies'] );

        // On supprime le handle de la liste
        mp_deregister_script($handle);

        if( strlen($data) === 0)
            return false;

        printf("<script id='%s-inline-js' type='text/javascript'>\n%s\n</script>\n", esc_attr($handle), $data);

    } else {

        /* URL */
        $url = rel2abs( $enqueue_script[$handle]['source'], MP_TEMPLATE_URL );
        $url = apply_filters('pre_script', $url, $enqueue_script[$handle]['dependencies']);
        $url = esc_url_raw( explode('?', $url)[0] );

        if( strlen($url) === 0 ){

            // On supprime le handle de la liste
            mp_deregister_style($handle);

            _doing_it_wrong( __FUNCTION__, 'error url!' );

            return;
        }

        /* PREVENT EXTENSION */
        if( 'js' !== substr(strrchr($url,'.'), 1) ){

            // On supprime le handle de la liste
            mp_deregister_style($handle);

            _doing_it_wrong( __FUNCTION__, 'error extension file !' );

            return;
        }


        /* VERSIONING */
        if( false === $enqueue_script[$handle]['version'] )
            $version = date('Ym'); // equivaut à un cache d'un mois des naviguateurs
        else
            $version = sanitize_allspecialschars($enqueue_script[$handle]['version']);
        
        $url = $url . ( strlen($version) === 0  ? '' : '?ver=' . $version );


        /* ATTRIBUTS */
        $script_attributs = apply_filters('script_attributs', array('async', 'defer') );

        if( !isset($enqueue_script[$handle]['dependencies']['attributs']) || is_notin( $enqueue_script[$handle]['dependencies']['attributs'], $script_attributs ) )
            $attributs = '';
        else
            $attributs = $enqueue_script[$handle]['dependencies']['attributs'];


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

        if( isset($enqueue_script[$handle]['dependencies']['conditional']) ){

            $conditional = preg_replace( '/[^ltegtIE0-9 !()&|]/', '', $enqueue_script[$handle]['dependencies']['conditional'] );
            $before      = "<!--[if '. $conditional .']>\n";
            $after       = "<![endif]-->\n";
        }

        // On supprime le handle de la liste
        mp_deregister_script($handle);

        printf( "%s<script id='%s-js' type='text/javascript' href='%s'>\n</script>%s", $before, esc_attr($handle), $url, $after );

    }

}

/*
 * enqueue every script register
 *
 * @return
 */
function mp_enqueue_scripts(){

    // Action pour shunter mp_register_style
    do_action('enqueue_scripts');

    // On charge les registers
    $enqueue_registers = mp_cache_data('mp_register_script');

    if( !empty($enqueue_registers) ){

        // On charge les registers
        foreach ($enqueue_registers as $handle => $args)
            mp_enqueue_script($handle);
    }

}