<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction Escape
 *
 *
 * @package cms mini POPS
 * @subpackage escape
 * @version 1
 */

/***********************************************/
/*                Fonctions esc                */
/***********************************************/

/**
 * Enlèves les caractères dangereux et Encode les signes < > " ' en valeur html pur.
 * Usage: stockage dans base json
 */
function esc_attr( $value ) {

    if ( is_string( $value ) ) {
        // On supprime les caractères invisibles
        $char = array('/%0[0-8bcef]/', '/%1[0-9a-f]/', '/[\x00-\x08]/', '/\x0b/', '/\x0c/', '/[\x0e-\x1f]/');
        do {
            $cleaned = $value;
            $value = preg_replace( $char , '' , $value );
        } while ( $cleaned != $value );
    }

    if ( is_array( $value ) ) 
        array_walk( $value , function( &$v ){ $v = esc_attr( $v ); } );

    return $value;
}


/**
 * Encode les signes < > " ' en valeur html.
 */
function esc_html( $string ) {

    $string = (string) $string;

    $flags = ENT_QUOTES;
    if( defined('ENT_SUBSTITUTE') ) $flags |= ENT_SUBSTITUTE;

    return htmlspecialchars( $string , $flags , CHARSET );
}

/**
 * DECODE esc_html.
 */
function html( $string ) {

    $string = (string) $string;
    $flags = ENT_QUOTES;

    return htmlspecialchars_decode( $string , $flags );
}


/**
 * Sanitialize url to prevent XSS - Cross-site scripting
 * $_GET = array_map('esc_url', $_GET);
 */
function esc_url( $url ){

    $url = (string) $url;

    $url = trim($url);
    $url = rawurldecode($url);
    $url = str_replace(array('--','&quot;','!','@','#','$','%','^','*','(',')','+','{','}','|',':','"','<','>',
                                  '[',']','\\',';',"'",',','*','+','~','`','laquo','raquo',']>','&#8216;','&#8217;','&#8220;','&#8221;','&#8211;','&#8212;'),
                            array('-','-','','','','','','','','','','','','','','','','','','','','','','','','','','',''),
                            $url);
    $url = str_replace('--', '-', $url);
    $url = rtrim($url, "-");
    $url = str_replace('..', '', $url);
    $url = str_replace('//', '', $url);
    $url = preg_replace('/^\//', '', $url);
    $url = preg_replace('/^\./', '', $url);

    return $url;
}

/**
 * Encode les signes < > " ' en valeur xml.
 */
function esc_xml( $string ) {

    $string = (string) $string;

    if( defined('ENT_XML1') ) return htmlspecialchars( $string , ENT_QUOTES | ENT_XML1, CHARSET );

    return str_replace( '&#039;', '&apos;', htmlspecialchars( $string , ENT_QUOTES , CHARSET ) );
}


/**
 * Nettoyer les urls.
 */

function esc_url_raw( $url ) {

    $good_protocol = false;
    
    $protocols = array( 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp', 'webcal' );

    if( '' == $url ) return $url;

    $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url );

    if( 0 !== stripos( $url, 'mailto:' ) ){

        $strip = array('%0d', '%0a', '%0D', '%0A');
        $url = str_replace($strip, '', $url);
    }

    $url = str_replace(';//', '://', $url);

    if ( strpos($url, ':') === false
        && ! in_array( $url[0], array( '/', '#', '?' ) )
        && ! preg_match('/^[a-z0-9-]+?\.php/i', $url)
       )
        $url = 'http://' . $url;

    if( '/' === $url[0] )
        $good_protocol = false;
    else
        foreach ($protocols as $protocol) if ( 0 === stripos( $url, $protocol ) ) $good_protocol = true;

    return ($good_protocol) ? $url : '';
}
