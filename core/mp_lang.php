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
function get_the_lang(){

    $lang = get_the_blog('lang');

    if( !$lang || !is_readable(TEMPLATEPATH .'/lang/en_'. strtolower($lang) .'.lang') )
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
    if( is_same( $domain, 'snippet') ){

        $domain = !empty($GLOBALS['__args']['lang']) ? $GLOBALS['__args']['lang'] : null ;

    } else {

        if( is_same($domain, null) ){

            // Traduction du thème
            $path   = TEMPLATEPATH . '/lang/en_'. $local_lang .'.lang';
            $name = basename(TEMPLATEPATH);

        } else {

            $active_plugins = explode( ',', get_the_blog('plugins', null) );

            // Traduction d'un plugins
            if( glob( PLUGIN_DIR.'/'.$domain.'/lang/en'.$local_lang.'.lang' ) && is_in($domain, $active_plugins) )
                $path = PLUGIN_DIR.'/'.$domain.'/lang/en_'. $local_lang .'.lang';
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
    if( !empty($domain['en']) && is_array($domain['en']) ){

        $key = array_search( $text, $domain['en'], true );

        return !empty( $domain[$local_lang][$key] ) ? $domain[$local_lang][$key] : $text;

    } else {

        return $text;

    }
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


function esc_html_( $text , $domain = null ){
    return esc_html( __( $text, $domain ) );
}


function esc_html_e( $text , $domain = null ){
    echo esc_html_( $text, $domain );
}


function _n( $singular, $plural, $number, $domain = null ){
    $number = (int) $number;
    return is_same($number, 1) ? __( $singular, $domain) : __( $plural, $domain );
}


/***********************************************/
/*        Internationnalisation time           */
/***********************************************/
/**
 * $date = date selon gmt greenwich
 */
function get_the_date( $date, $d = '' ) {

    if( !is_date($date) )
        return false;

    if ( '' == $d )
        $the_date = mysql2date( get_option( 'date_format' ), $date );
    else
        $the_date = mysql2date( $d, $date );

    return apply_filters( 'get_the_date', $the_date, $d, $date );
}



/**
 * $date = date selon gmt greenwich
 */
function mysql2date( $format, $date, $translate = true ) {

    if ( empty( $date ) )
        return false;

    // On applique le décalage selon le type de mise à l'heure ( UTC ou Pays )
    if( is_intgr( get_the_blog('timezone') ) && is_between( get_the_blog('timezone'), -12, 12 ) )
        $gmt_offset = get_the_blog('timezone');
    else
        $gmt_offset = date('Z');

    // On applique le décalage horaire
    $i = strtotime( $date ) + $gmt_offset;

    if ( 'U' == $format )
        return $i;

    if ( $translate )
        return date_i18n( $format, $i );
    else
        return date( $format, $i );
}


function current_time( $type ) {

    $type = (string) $type;

    if( is_intgr( get_the_blog('timezone') ) && is_between( get_the_blog('timezone'), -12, 12 ) )
        $gmt = 0;
    else
        $gmt = 1;

    switch ( $type ) {
        case 'mysql':
            return gmdate( 'Y-m-d H:i:s' );
        case 'timestamp':
            return ($gmt) ? time() : time() + ( get_the_blog('timezone') * HOUR_IN_SECONDS );
        default:
            return ($gmt) ? date( $type ) : date( $type, time() + ( get_the_blog('timezone') * HOUR_IN_SECONDS ) );
    }
}


function _date($format="r", $timestamp=false, $timezone=false)
{
    $userTimezone = new DateTimeZone(!empty($timezone) ? $timezone : 'GMT');
    $gmtTimezone = new DateTimeZone('GMT');
    $myDateTime = new DateTime(($timestamp!=false?date("r",(int)$timestamp):date("r")), $gmtTimezone);
    $offset = $userTimezone->getOffset($myDateTime);
    return date($format, ($timestamp!=false?(int)$timestamp:$myDateTime->format('U')) + $offset);
}


function date_i18n( $dateformatstring, $unixtimestamp = false ){

    $dateformatstring = (string) $dateformatstring;
    $gmt = (bool) $gmt;

    $i = $unixtimestamp;

    if ( false === $i )
        $i = current_time( 'timestamp' );

    /*
     * Store original value for language with untypical grammars.
     * See https://core.trac.wordpress.org/ticket/9396
     */
    $req_format = $dateformatstring;

    if ( ( !empty( $wp_locale->month ) ) && ( !empty( $wp_locale->weekday ) ) ) {

        $datemonth = $wp_locale->get_month( date( 'm', $i ) );
        $datemonth_abbrev = $wp_locale->get_month_abbrev( $datemonth );
        $dateweekday = $wp_locale->get_weekday( date( 'w', $i ) );
        $dateweekday_abbrev = $wp_locale->get_weekday_abbrev( $dateweekday );
        $datemeridiem = $wp_locale->get_meridiem( date( 'a', $i ) );
        $datemeridiem_capital = $wp_locale->get_meridiem( date( 'A', $i ) );
        $dateformatstring = ' '.$dateformatstring;
        $dateformatstring = preg_replace( "/([^\\\])D/", "\\1" . backslashit( $dateweekday_abbrev ), $dateformatstring );
        $dateformatstring = preg_replace( "/([^\\\])F/", "\\1" . backslashit( $datemonth ), $dateformatstring );
        $dateformatstring = preg_replace( "/([^\\\])l/", "\\1" . backslashit( $dateweekday ), $dateformatstring );
        $dateformatstring = preg_replace( "/([^\\\])M/", "\\1" . backslashit( $datemonth_abbrev ), $dateformatstring );
        $dateformatstring = preg_replace( "/([^\\\])a/", "\\1" . backslashit( $datemeridiem ), $dateformatstring );
        $dateformatstring = preg_replace( "/([^\\\])A/", "\\1" . backslashit( $datemeridiem_capital ), $dateformatstring );

        $dateformatstring = substr( $dateformatstring, 1, strlen( $dateformatstring ) -1 );
    }


    $j = @date( $dateformatstring, $i );

    return $j;

}
