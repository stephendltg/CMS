<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction sanitialize
 *
 *
 * @package cms mini POPS
 * @subpackage sanitialize
 * @version 1
 */


/***********************************************/
/*                fonctions sanitialize        */
/***********************************************/


/**
* Ajoute des slash dans une chaine
* @param $string     chaine
*/
function backslashit( $string ) {

    if ( isset( $string[0] ) && $string[0] >= '0' && $string[0] <= '9' )
        $string = '\\\\' . $string;
    return addcslashes( $string, 'A..Za..z' );
}


/**
* Supprime les antislashs d'une chaîne et uniquement
* @param $string     chaine
*/
function stripslashes_str( $value ) {
    return is_string( $value ) ? stripslashes( $value ) : $value;
}


/**
 * Supprime toutes les balises ( style et script y comprit ).
 */
function strip_all_tags( $string ) {

    $string = (string) $string;

    $string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
    return trim( strip_tags($string) );
}

/**
 * Enlève tous les accents
 */
function remove_accent( $string ){

    $string       = (string) $string;

    $string = encode_utf8( $string );
    $char_not_clean = array('/@/','/À/','/Á/','/Â/','/Ã/','/Ä/','/Å/','/Ç/','/È/','/É/','/Ê/','/Ë/','/Ì/','/Í/','/Î/','/Ï/','/Ò/','/Ó/','/Ô/','/Õ/','/Ö/','/Ù/','/Ú/','/Û/','/Ü/','/Ý/','/à/','/á/','/â/','/ã/','/ä/','/å/','/ç/','/è/','/é/','/ê/','/ë/','/ì/','/í/','/î/','/ï/','/ð/','/ò/','/ó/','/ô/','/õ/','/ö/','/ù/','/ú/','/û/','/ü/','/ý/','/ÿ/', '/©/');
    $clean = array('a','a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','y','a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','o','o','o','o','o','o','u','u','u','u','y','y','copy');
    $string = preg_replace( $char_not_clean , $clean , $string );
    $string = utf8_decode($string);
    $string = preg_replace('/\?/', '', $string);
    $string = strtolower($string);
    return $string;
}


/**
 * Enlève tous les caractères spéciaux
 */
function sanitize_allspecialschars( $string ) {

    $string       = (string) $string;

    $special_chars = array( "[", "]", "/", "\\", "<", ">", "\"", "{", "}", chr(0) );
    $special_chars = apply_filters( 'sanitize_allspecialschars_char' , $special_chars );
    $special_chars = preg_replace( "#\x{00a0}#siu", ' ', $special_chars );
    $string = str_replace( $special_chars, '', $string );
    $string = str_replace( '%20', ' ', $string );
    $string = preg_replace( '/[\r\n\t]+/', ' ', $string );
    return $string;
}

/**
 * Nettoie une liste d'éléments
 */
function sanitize_list( $list, $separator = ', ' ) {

    if ( empty( $list ) )
        return '';

    $trimed_sep = trim( $separator );
    $double_sep = $trimed_sep . $trimed_sep;
    $list = preg_replace( '/\s*' . $trimed_sep . '\s*/', $trimed_sep, $list );
    $list = trim( $list, $trimed_sep . ' ' );

    while ( false !== strpos( $list, $double_sep ) )
        $list = str_replace( $double_sep, $trimed_sep, $list );

    return str_replace( $trimed_sep, $separator, $list );
}


/**
 * Apply `array_unique()` and `natcasesort()` on a list.
 *
 * @param (string|array) $list      liste de donnée à trier dont les valeurs et clés seront unique
 * @param (string|bool)  $separator Le séparateur. If not false, the function will explode and implode the list.
 *
 * @return (string|array) The list.
 */
function unique_sorted_list( $list, $separator = false ) {
    
    if ( array() === $list || '' === $list )
        return $list;

    if ( false !== $separator )
        $list = explode( $separator, $list );

    $list = array_flip( array_flip( $list ) );
    natcasesort( $list );

    if ( false !== $separator )
        $list = implode( $separator, $list );

    return $list;
}


function sanitize_key( $key ) {

    $key = (string) $key;

    $key = strtolower( $key );
    $key = preg_replace( '/[^a-z0-9\/_-]/', '', $key );
    return $key;
}


function sanitize_tag( $tag_name ) {

    $tag_name = (string) $tag_name;

    $tag_name = strtolower( preg_replace('/[^a-zA-Z0-9_]/', '', $tag_name) );
    return $tag_name;
}

function sanitize_html_class( $class ) {

    $class = (string) $class;

    $class = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', $class );
    $class = preg_replace( '/[^A-Za-z0-9_-]/', '', $class );
    return $class;
}


function sanitize_email( $email ){

    $email = (string) $email;

    if( strlen($email ) < 3 ) return '';

    if( strpos($email, '@', 1 ) === false ) return '';

    list( $local, $domain ) = explode('@', $email, 2);

    $local = preg_replace( '/[^a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]/', '', $local );
    if ( '' === $local ) return '';

    $domain = preg_replace( '/\.{2,}/', '', $domain );
    if ( '' === $domain ) return '';

    $domain = trim( $domain, " \t\n\r\0\x0B." );
    if ( '' === $domain ) return '';

    // Split the domain into subs
    $subs = explode( '.', $domain );
    if ( 2 > count( $subs ) ) return '';

    // Create an array that will contain valid subs
    $new_subs = array();
    foreach ( $subs as $sub ) {
        $sub = trim( $sub, " \t\n\r\0\x0B-" );
        $sub = preg_replace( '/[^a-z0-9-]+/i', '', $sub );
        if ( '' !== $sub )
            $new_subs[] = $sub;
    }
    if ( 2 > count( $new_subs ) ) return '';

    $domain = join( '.', $new_subs );
    $email = $local . '@' . $domain;

    return $email;
}


function sanitize_user( $username ){

    $username = (string) $username;

    $username = strip_all_tags( $username );
    $username = remove_accent( $username );
    $username = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '', $username );
    $username = preg_replace( '/&.+?;/', '', $username );
    $username = preg_replace( '|[^a-z0-9 _.\-@]|i', '', $username );
    $username = trim( $username );
    $username = preg_replace( '|\s+|', ' ', $username );
    return $username;
}

/**
 * Nettoie un nom de fichier
 */
function sanitize_file_name( $filename ) {

    $filename       = (string) $filename;

    //thanks wordpress
    $special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", chr(0) );
    $filename = apply_filters( 'sanitize_file_name_char' , $filename );
    $filename = preg_replace( "#\x{00a0}#siu", ' ', $filename );
    $filename = str_replace( $special_chars, '', $filename );
    $filename = str_replace( array( '%20', '+' ), '-', $filename );
    $filename = preg_replace( '/[\r\n\t -]+/', '-', $filename );
    $filename = trim( $filename, '.-_' );
    $filename = remove_accent( $filename );
    return $filename;
}


/**
 * Nettoie une color et sortie en rgb ou rgba
 */
function sanitize_color( $color ){

    $color = (string) $color;

    $special_chars = array("?", "%", "[", "]", "/", "\\", "=", "<", ">", ":", ";", "'", "\"", "&", "$", "*", "|", "~", "`", "!", "{", "}", chr(0) );
    $color = preg_replace( "#\x{00a0}#siu", ' ', $color );
    $color = str_replace( $special_chars, '', $color );
    $color = str_replace( array( '%20', '+' ), ' ', $color );
    $color = preg_replace( '/[\r\n\t ]+/', '', $color );

    $pattern = "/^(#[0-9a-f]{3}|#(?:[0-9a-f]{2}){2,4}|(rgb)a?\((-?\d+%?[,\s]+){2,3}\s*[\d\.]+%?\))$/";
    
    if( $result = is_match( $color , $pattern ) ){
        if (substr(trim($color), 0, 1) === '#')
            return 'rgb('. join(',', array_map( function($v){return is_null($v)?0:$v;}, sscanf($color, "#%02x%02x%02x") ) ) .')';
        return $color;
    }
    return null;
}


/**
 * Nettoie un mot de tout caractères
 */
function sanitize_words( $words ) {

    $words       = (string) $words;

    //thanks wordpress
    $special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", ".", "_", "-", chr(0) );
    $words = apply_filters( 'sanitize_words_char' , $words );
    $words = preg_replace( "#\x{00a0}#siu", ' ', $words );
    $words = str_replace( $special_chars, '', $words );
    $words = str_replace( array( '%20', '+' ), ' ', $words );
    $words = preg_replace( '/[\r\n\t ]+/', ' ', $words );
    return $words;
}

/**
 * Nettoie des données svg
 */
function sanitize_svg( $svg ){

    $svg       = (string) $svg;

    // Table des éléments présent normalement dans un fichier svg
    $whitelist = array(
        "a"=>array("class", "clip-path", "clip-rule", "fill", "fill-opacity", "fill-rule", "filter", "id", "mask", "opacity", "stroke", "stroke-dasharray", "stroke-dashoffset", "stroke-linecap", "stroke-linejoin", "stroke-miterlimit", "stroke-opacity", "stroke-width", "style", "systemLanguage", "transform", "href", "xlink:href", "xlink:title"),
        "circle"=>array("class", "clip-path", "clip-rule", "cx", "cy", "fill", "fill-opacity", "fill-rule", "filter", "id", "mask", "opacity", "r", "requiredFeatures", "stroke", "stroke-dasharray", "stroke-dashoffset", "stroke-linecap", "stroke-linejoin", "stroke-miterlimit", "stroke-opacity", "stroke-width", "style", "systemLanguage", "transform"),
        "clipPath"=>array("class", "clipPathUnits", "id"),
        "defs"=>array(),
        "style" =>array("type"),
        "desc"=>array(),
        "ellipse"=>array("class", "clip-path", "clip-rule", "cx", "cy", "fill", "fill-opacity", "fill-rule", "filter", "id", "mask", "opacity", "requiredFeatures", "rx", "ry", "stroke", "stroke-dasharray", "stroke-dashoffset", "stroke-linecap", "stroke-linejoin", "stroke-miterlimit", "stroke-opacity", "stroke-width", "style", "systemLanguage", "transform"),
        "feGaussianBlur"=>array("class", "color-interpolation-filters", "id", "requiredFeatures", "stdDeviation"),
        "filter"=>array("class", "color-interpolation-filters", "filterRes", "filterUnits", "height", "id", "primitiveUnits", "requiredFeatures", "width", "x", "xlink:href", "y"),
        "foreignObject"=>array("class", "font-size", "height", "id", "opacity", "requiredFeatures", "style", "transform", "width", "x", "y"),
        "g"=>array("class", "clip-path", "clip-rule", "id", "display", "fill", "fill-opacity", "fill-rule", "filter", "mask", "opacity", "requiredFeatures", "stroke", "stroke-dasharray", "stroke-dashoffset", "stroke-linecap", "stroke-linejoin", "stroke-miterlimit", "stroke-opacity", "stroke-width", "style", "systemLanguage", "transform", "font-family", "font-size", "font-style", "font-weight", "text-anchor"),
        "image"=>array("class", "clip-path", "clip-rule", "filter", "height", "id", "mask", "opacity", "requiredFeatures", "style", "systemLanguage", "transform", "width", "x", "xlink:href", "xlink:title", "y"),
        "line"=>array("class", "clip-path", "clip-rule", "fill", "fill-opacity", "fill-rule", "filter", "id", "marker-end", "marker-mid", "marker-start", "mask", "opacity", "requiredFeatures", "stroke", "stroke-dasharray", "stroke-dashoffset", "stroke-linecap", "stroke-linejoin", "stroke-miterlimit", "stroke-opacity", "stroke-width", "style", "systemLanguage", "transform", "x1", "x2", "y1", "y2"),
        "linearGradient"=>array("class", "id", "gradientTransform", "gradientUnits", "requiredFeatures", "spreadMethod", "systemLanguage", "x1", "x2", "xlink:href", "y1", "y2"),
        "marker"=>array("id", "class", "markerHeight", "markerUnits", "markerWidth", "orient", "preserveAspectRatio", "refX", "refY", "systemLanguage", "viewBox"),
        "mask"=>array("class", "height", "id", "maskContentUnits", "maskUnits", "width", "x", "y"),
        "metadata"=>array("class", "id"),
        "path"=>array("class", "clip-path", "clip-rule", "d", "fill", "fill-opacity", "fill-rule", "filter", "id", "marker-end", "marker-mid", "marker-start", "mask", "opacity", "requiredFeatures", "stroke", "stroke-dasharray", "stroke-dashoffset", "stroke-linecap", "stroke-linejoin", "stroke-miterlimit", "stroke-opacity", "stroke-width", "style", "systemLanguage", "transform"),
        "pattern"=>array("class", "height", "id", "patternContentUnits", "patternTransform", "patternUnits", "requiredFeatures", "style", "systemLanguage", "viewBox", "width", "x", "xlink:href", "y"),
        "polygon"=>array("class", "clip-path", "clip-rule", "id", "fill", "fill-opacity", "fill-rule", "filter", "id", "class", "marker-end", "marker-mid", "marker-start", "mask", "opacity", "points", "requiredFeatures", "stroke", "stroke-dasharray", "stroke-dashoffset", "stroke-linecap", "stroke-linejoin", "stroke-miterlimit", "stroke-opacity", "stroke-width", "style", "systemLanguage", "transform"),
        "polyline"=>array("class", "clip-path", "clip-rule", "id", "fill", "fill-opacity", "fill-rule", "filter", "marker-end", "marker-mid", "marker-start", "mask", "opacity", "points", "requiredFeatures", "stroke", "stroke-dasharray", "stroke-dashoffset", "stroke-linecap", "stroke-linejoin", "stroke-miterlimit", "stroke-opacity", "stroke-width", "style", "systemLanguage", "transform"),
        "radialGradient"=>array("class", "cx", "cy", "fx", "fy", "gradientTransform", "gradientUnits", "id", "r", "requiredFeatures", "spreadMethod", "systemLanguage", "xlink:href"),
        "rect"=>array("class", "clip-path", "clip-rule", "fill", "fill-opacity", "fill-rule", "filter", "height", "id", "mask", "opacity", "requiredFeatures", "rx", "ry", "stroke", "stroke-dasharray", "stroke-dashoffset", "stroke-linecap", "stroke-linejoin", "stroke-miterlimit", "stroke-opacity", "stroke-width", "style", "systemLanguage", "transform", "width", "x", "y"),
        "stop"=>array("class", "id", "offset", "requiredFeatures", "stop-color", "stop-opacity", "style", "systemLanguage"),
        "svg"=>array("class", "clip-path", "clip-rule", "filter", "id", "height", "mask", "preserveAspectRatio", "requiredFeatures", "style", "systemLanguage", "viewBox", "width", "x", "xmlns", "xmlns:se", "xmlns:xlink", "y"),
        "switch"=>array("class", "id", "requiredFeatures", "systemLanguage"),
        "symbol"=>array("class", "fill", "fill-opacity", "fill-rule", "filter", "font-family", "font-size", "font-style", "font-weight", "id", "opacity", "preserveAspectRatio", "requiredFeatures", "stroke", "stroke-dasharray", "stroke-dashoffset", "stroke-linecap", "stroke-linejoin", "stroke-miterlimit", "stroke-opacity", "stroke-width", "style", "systemLanguage", "transform", "viewBox"),
        "text"=>array("class", "clip-path", "clip-rule", "fill", "fill-opacity", "fill-rule", "filter", "font-family", "font-size", "font-style", "font-weight", "id", "mask", "opacity", "requiredFeatures", "stroke", "stroke-dasharray", "stroke-dashoffset", "stroke-linecap", "stroke-linejoin", "stroke-miterlimit", "stroke-opacity", "stroke-width", "style", "systemLanguage", "text-anchor", "transform", "x", "xml:space", "y"),
        "textPath"=>array("class", "id", "method", "requiredFeatures", "spacing", "startOffset", "style", "systemLanguage", "transform", "xlink:href"),
        "title"=>array(),
        "tspan"=>array("class", "clip-path", "clip-rule", "dx", "dy", "fill", "fill-opacity", "fill-rule", "filter", "font-family", "font-size", "font-style", "font-weight", "id", "mask", "opacity", "requiredFeatures", "rotate", "stroke", "stroke-dasharray", "stroke-dashoffset", "stroke-linecap", "stroke-linejoin", "stroke-miterlimit", "stroke-opacity", "stroke-width", "style", "systemLanguage", "text-anchor", "textLength", "transform", "x", "xml:space", "y"),
        "use"=>array("class", "clip-path", "clip-rule", "fill", "fill-opacity", "fill-rule", "filter", "height", "id", "mask", "stroke", "stroke-dasharray", "stroke-dashoffset", "stroke-linecap", "stroke-linejoin", "stroke-miterlimit", "stroke-opacity", "stroke-width", "style", "transform", "width", "x", "xlink:href", "y"),
    );

    // On recherche tous les tag ainsi que leur contenu ( tag => <p style="color:red">, text => "mon texte", tag => </p> )
    preg_match_all( '/<(?<tag>[\/\w.:-]*)(?:".*?"|\'.*?\'|[^\'">]+)*>|(?<text>((<[^!\/\w.:-])?[^<]*)+)|/si', $svg, $matches, PREG_SET_ORDER );
    // On init le toogle tag
    $raw_tag = null;
    // On init le résultat
    $svg    = '';

    // On boucle sur tous les éléments tag ou text trouvés
    foreach( $matches as $token ){

        // On normalise la variable tag
        $tag     = !empty( $token['tag'] ) ? strtolower( $token['tag'] ) : null;
        // On associe le contenu
        $content = $token[0];

        if( array_key_exists($tag, $whitelist) ){

            // toogle pour valider un texte associé au tag
            $raw_tag = $tag;
            // On extrait les attributs
            preg_match_all('#([^\s=]+)\s*=\s*(\'[^<\']*\'|"[^<"]*")#', $content, $matches, PREG_SET_ORDER);
            // On vérifie que chaque attributs est valid
            foreach ($matches as $attribute)
                if( is_notin($attribute[1], $whitelist[$tag]) )
                    $content = str_replace($attribute[0], '', $content);

        } elseif( array_key_exists(trim($tag,'/'), $whitelist) ){

            // On réecrit un tag de fermeture valid
            $content = '<'.$tag.'>';

        } else{

            if( !array_key_exists($raw_tag, $whitelist) ) $content = ''; // si tag non valid on ne récupere pas le texte et on ne valid pas le tag
            else $raw_tag = null; // On reinit le toogle

        }

        $svg .= $content;
    }

    if( apply_filters( 'do_optimize_svg', true ) ){

        // On supprime les espaces vide, tabulation, retour chariot, ...
        $svg = str_replace(' />', '/>', $svg);
        $svg = str_replace(array("\r\n", "\r", "\n", "\t"), '', $svg);
            while ( stristr($svg, '  ') )
                $svg = str_replace('  ', ' ', $svg);

    }

    return $svg;
}
