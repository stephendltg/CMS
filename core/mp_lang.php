<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction lang
 *
 *
 * @package cms mini POPS
 * @subpackage lang - fonction de traduction
 * @version 1
 */


/**
 * Gestion des traductions
 *
 * Fichier traduction lang:
 *
 *
 * lang :
 *
 *    en :
 *        item : do you like the canada ?
 *    fr :
 *        item: aimes-tu le canada ?
 * ----
 *
 *
 * @return string
 */
function __( $text, $domain = null ){

    $text   = (string) $text;

    static $set_domain = array();

    $default_lang = apply_filter('mp_default_lang', 'en');
    $local_lang   = apply_filter('mp_local_lang', get_the_blog('lang') );

    // On charge la traductions du snippets ( fichiers traduction temporaire et valide le temps du snippets )
    if( is_same( $domain, 'snippet') ){

        $domain = !empty($GLOBALS['__args']['lang']) ? $GLOBALS['__args']['lang'] : null ;

    } else {

        if( is_same($domain, null) ){

            // Traduction du thème
            $path   = TEMPLATEPATH . '/lang/'. $default_lang .'_'. $local_lang .'.lang';
            $name = basename(TEMPLATEPATH);

        } else {

            $active_plugins = explode( ',', get_the_blog('plugins') );

            // Traduction d'un plugins
            if( glob( PLUGIN_DIR .'/'. $domain .'/'. $domain.'.lang' ) && is_in($domain, $active_plugins) )
                $path = PLUGIN_DIR.'/'.$domain.'/lang'. $default_lang .'_'. $local_lang .'.lang';
            else
                return $text;

            $name = $domain;
        }

        // On vérifie que la lang n'est pas déjà chargé dans la variable static
        if( !empty( $set_domain[$name] ) ){

            $domain = $set_domain[$name];

        } elseif( glob($path) ){

            $temp = file_get_yaml( $path, true );
            $domain = !empty($temp['lang']) ? $temp['lang'] : array(null); // array(null): on évite de reboucle à chaque requete
            $set_domain[$name] = $domain;

        } else {

            return $text;

        }

    }

    // On affecte la traduction
    if( !empty($domain[$default_lang]) && is_array($domain[$default_lang]) ){

        $key = array_search( $text, $domain[$default_lang], true );

        return !empty( $domain[$local_lang][$key] ) ? $domain[$local_lang][$key] : $text;

    } else {

        return $text;

    }
}


function __e( $text, $domain = null ){
    echo __( $text, $domain );
}


function esc_attr__( $text , $domain = null ){
    return sanitize_allspecialschars( __( $text, $domain ) );
}


function esc_attr__e( $text , $domain = null ){
    echo esc_attr__( $text, $domain );
}


function esc_html__( $text , $domain = null ){
    return esc_html( __( $text, $domain ) );
}


function esc_html__e( $text , $domain = null ){
    echo esc_html__( $text, $domain );
}
