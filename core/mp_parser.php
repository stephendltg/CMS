<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction parser
 *
 *
 * @package cms mini POPS
 * @subpackage parser
 * @version 1
 */


/***********************************************/
/*          Functions parser                   */
/***********************************************/

/**
 * Encode en base64 pour un usage embarque en data uri(css,html) si fichier sinon encodage seulement
 * @param  $data : $file(chemin absolu) ou $string
 * @return string|boolean
 */
function datauri_encode( $data ) {
    if( is_string( $data ) ){
        if ( file_exists( $data ) && !is_dir( $data ) ){
           $mime_type = mime_content_type( $data );
           return 'data:' . $mime_type . ';base64,' . base64_encode( file_get_contents( $data ) );
        }
        return 'data:' . $mime_type . ';base64,' . base64_encode( $data );
    }
    return false;
}

/**
* Extrait d'une chaine
* @param  $text     chaine à extraire
* @param  $length   longueur de l'extrait
* @param  $mode     mode characère ou mot
*/
function excerpt( $text , $length = 140 , $mode = 'chars' ) {

    $test = (string) $text;
    $mode = (string) $mode;

    $text = strip_all_tags($text);

    if( is_same( strtolower($mode) , 'words' ) ){
        if( str_word_count($text , 0) > $length ) {
          $words = str_word_count($text, 2);
          $pos   = array_keys($words);
          $text  = substr( $text , 0 , $pos[$length]) . '...';
        }
        return $text;
    } else {
        return substr( $text , 0 , $length );
    }
}

/**
* Parse une chaine markdown en html
* @param  $markdown     chaine à parser
*/
function parse_markdown( $markdown ){

    $markdown = (string) $markdown;

    # commentaires
    $markdown = str_replace(array('&#039;&#039;', "``"),
                           array('&#8220;', '&#8221;'), $markdown);

    // On parse markdown
    $Extra = new Parsedown();
    $markdown = $Extra->text( $markdown );

    // On nettoie toutes les urls lie à href
    $clean_all_url = function($array){ return 'href="'.esc_url_raw($array[2]).'"'; };
    $markdown = preg_replace_callback( '/href=([\'"])(.+?)([\'"])/i' , $clean_all_url , $markdown );

    // On remet les chevrons pour la balise code
    $markdown = str_replace( '&amp;', '&' , $markdown );

    # Traits de séparation
    $markdown = str_replace(array('---', '--'),
                           array('&#8212;', '&#8211;'), $markdown);

    # trois petits points et puis lalala
    $markdown = str_replace('...', '&#8230;', $markdown);

    return $markdown;
}

/**
* Parse une chaine text en html
* @param  $text     chaine à parser
*/
function parse_text( $text ){

    $text = (string) $text;

    # commentaires
    $text = str_replace(array('&#039;&#039;', "``"),
                           array('&#8220;', '&#8221;'), $text);

    // On nettoie toutes les urls lie à href
    $clean_all_url = function($array){
        return 'href="'.esc_url_raw($array[2]).'"';
    };

    $text = preg_replace_callback( '/href=([\'"])(.+?)([\'"])/i' , $clean_all_url , $text );

    # Traits de séparation
    $text = str_replace(array('---', '--'),
                           array('&#8212;', '&#8211;'), $text);

    # trois petits points et puis lalala
    $text = str_replace('...', '&#8230;', $text);

    return $text;
}
