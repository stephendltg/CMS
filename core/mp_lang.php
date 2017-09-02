<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction Traduction et internationalisation
 *
 *
 * @package cms mini POPS
 * @subpackage lang - fonction de traduction
 * @version 1
 */



/**
 * Récupèrer la langue d'usage
 * @return boolean
 */
function get_the_lang()
{

    $lang = get_the_blog('lang');

    if ( !$lang || !is_readable(MP_TEMPLATE_DIR .'/lang/en_'. strtolower($lang) .'.yml') )
        $lang = 'en';

    return strtolower($lang);
}


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

    $local_lang   = get_the_lang();

    // On charge la traductions du snippets ( fichiers traduction temporaire et valide le temps du snippets )
    if( is_same( $domain, 'snippets') ){

        $domain = mp_cache_data('__args')['lang'] ? mp_cache_data('__args')['lang'] : null;

    } else { // On charge la traductions des domaines thème ou plugin

        if( is_same($domain, null) ){
            // Traduction du thème
            $path   = MP_TEMPLATE_DIR . '/lang/en_'. $local_lang .'.yml';
            $name = basename(MP_TEMPLATE_DIR);

        } else {

            // Traduction d'un plugins
            if( glob(MP_PLUGIN_DIR.'/'.$domain.'/lang/en_'.$local_lang.'.yml')
                && is_in($domain, get_option('active_plugins'))
            ) $path = MP_PLUGIN_DIR.'/'.$domain.'/lang/en_'. $local_lang .'.yml';

            else
                return $text;

            $name = $domain;
        }

        // On vérifie que la lang n'est pas déjà chargé dans la variable static
        if( !empty( $set_domain[$name] ) ){

            $domain = $set_domain[$name];

        } elseif( $temp = yaml_parse_file($path) ){

            //mp_transient_data('lang', 'yaml_parse_file', 5, array($path) )

            $domain = !empty($temp['lang']) ? $temp['lang'] : array(null); // array(null): on évite de reboucle à chaque requete
            $set_domain[$name] = $domain;

        } else return $text;

    }

    // On affecte la traduction
    if( !empty($domain[$local_lang][$text]) && is_string($domain[$local_lang][$text]) )
        return $domain[$local_lang][$text];

    elseif( !empty($domain['en'])
          && is_array($domain['en'])
          && ( $key = array_search($text, $domain['en'], true) ) !== false
          && !empty($domain[$local_lang][$key])
          && is_string($domain[$local_lang][$key])
          )
          return $domain[$local_lang][$key];

    else return $text;

}


function _e( $text, $domain = null ){
    echo __( $text, $domain );
}


function esc_attr__( $text , $domain = null ){
    return sanitize_allspecialschars( __( $text, $domain ) );
}


function esc_attr_e( $text , $domain = null ){
    echo esc_attr_( $text, $domain );
}


function esc_html__( $text , $domain = null ){
    return esc_html( __( $text, $domain ) );
}


function esc_html_e( $text , $domain = null ){
    echo esc_html_( $text, $domain );
}


function _n( $singular, $plural, $number, $domain = null ){
    $number = (int) $number;
    return is_same($number, 1) ? __( $singular, $domain) : __( $plural, $domain );
}
