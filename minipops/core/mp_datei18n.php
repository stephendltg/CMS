<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction date et internationalisation
 *
 *
 * @package cms mini POPS
 * @subpackage date - fonction date
 * @version 1
 */


/*
 * Liste des timezone valid
 */
function list_timezones_valid(){

    return array(
        'Pacific/Midway'        => "(GMT-11:00) Midway Island",
        'US/Samoa'              => "(GMT-11:00) Samoa",
        'US/Hawaii'             => "(GMT-10:00) Hawaii",
        'US/Alaska'             => "(GMT-09:00) Alaska",
        'US/Pacific'            => "(GMT-08:00) Pacific Time (US &amp; Canada)",
        'America/Tijuana'       => "(GMT-08:00) Tijuana",
        'US/Arizona'            => "(GMT-07:00) Arizona",
        'US/Mountain'           => "(GMT-07:00) Mountain Time (US &amp; Canada)",
        'America/Chihuahua'     => "(GMT-07:00) Chihuahua",
        'America/Mazatlan'      => "(GMT-07:00) Mazatlan",
        'America/Mexico_City'   => "(GMT-06:00) Mexico City",
        'America/Monterrey'     => "(GMT-06:00) Monterrey",
        'Canada/Saskatchewan'   => "(GMT-06:00) Saskatchewan",
        'US/Central'            => "(GMT-06:00) Central Time (US &amp; Canada)",
        'US/Eastern'            => "(GMT-05:00) Eastern Time (US &amp; Canada)",
        'US/East-Indiana'       => "(GMT-05:00) Indiana (East)",
        'America/Bogota'        => "(GMT-05:00) Bogota",
        'America/Lima'          => "(GMT-05:00) Lima",
        'America/Caracas'       => "(GMT-04:30) Caracas",
        'Canada/Atlantic'       => "(GMT-04:00) Atlantic Time (Canada)",
        'America/La_Paz'        => "(GMT-04:00) La Paz",
        'America/Santiago'      => "(GMT-04:00) Santiago",
        'Canada/Newfoundland'   => "(GMT-03:30) Newfoundland",
        'America/Buenos_Aires'  => "(GMT-03:00) Buenos Aires",
        'Greenland'             => "(GMT-03:00) Greenland",
        'Atlantic/Stanley'      => "(GMT-02:00) Stanley",
        'Atlantic/Azores'       => "(GMT-01:00) Azores",
        'Atlantic/Cape_Verde'   => "(GMT-01:00) Cape Verde Is.",
        'Africa/Casablanca'     => "(GMT) Casablanca",
        'Europe/Dublin'         => "(GMT) Dublin",
        'Europe/Lisbon'         => "(GMT) Lisbon",
        'Europe/London'         => "(GMT) London",
        'Africa/Monrovia'       => "(GMT) Monrovia",
        'Europe/Amsterdam'      => "(GMT+01:00) Amsterdam",
        'Europe/Belgrade'       => "(GMT+01:00) Belgrade",
        'Europe/Berlin'         => "(GMT+01:00) Berlin",
        'Europe/Bratislava'     => "(GMT+01:00) Bratislava",
        'Europe/Brussels'       => "(GMT+01:00) Brussels",
        'Europe/Budapest'       => "(GMT+01:00) Budapest",
        'Europe/Copenhagen'     => "(GMT+01:00) Copenhagen",
        'Europe/Ljubljana'      => "(GMT+01:00) Ljubljana",
        'Europe/Madrid'         => "(GMT+01:00) Madrid",
        'Europe/Paris'          => "(GMT+01:00) Paris",
        'Europe/Prague'         => "(GMT+01:00) Prague",
        'Europe/Rome'           => "(GMT+01:00) Rome",
        'Europe/Sarajevo'       => "(GMT+01:00) Sarajevo",
        'Europe/Skopje'         => "(GMT+01:00) Skopje",
        'Europe/Stockholm'      => "(GMT+01:00) Stockholm",
        'Europe/Vienna'         => "(GMT+01:00) Vienna",
        'Europe/Warsaw'         => "(GMT+01:00) Warsaw",
        'Europe/Zagreb'         => "(GMT+01:00) Zagreb",
        'Europe/Athens'         => "(GMT+02:00) Athens",
        'Europe/Bucharest'      => "(GMT+02:00) Bucharest",
        'Africa/Cairo'          => "(GMT+02:00) Cairo",
        'Africa/Harare'         => "(GMT+02:00) Harare",
        'Europe/Helsinki'       => "(GMT+02:00) Helsinki",
        'Europe/Istanbul'       => "(GMT+02:00) Istanbul",
        'Asia/Jerusalem'        => "(GMT+02:00) Jerusalem",
        'Europe/Kiev'           => "(GMT+02:00) Kyiv",
        'Europe/Minsk'          => "(GMT+02:00) Minsk",
        'Europe/Riga'           => "(GMT+02:00) Riga",
        'Europe/Sofia'          => "(GMT+02:00) Sofia",
        'Europe/Tallinn'        => "(GMT+02:00) Tallinn",
        'Europe/Vilnius'        => "(GMT+02:00) Vilnius",
        'Asia/Baghdad'          => "(GMT+03:00) Baghdad",
        'Asia/Kuwait'           => "(GMT+03:00) Kuwait",
        'Europe/Moscow'         => "(GMT+03:00) Moscow",
        'Africa/Nairobi'        => "(GMT+03:00) Nairobi",
        'Asia/Riyadh'           => "(GMT+03:00) Riyadh",
        'Europe/Volgograd'      => "(GMT+03:00) Volgograd",
        'Asia/Tehran'           => "(GMT+03:30) Tehran",
        'Asia/Baku'             => "(GMT+04:00) Baku",
        'Asia/Muscat'           => "(GMT+04:00) Muscat",
        'Asia/Tbilisi'          => "(GMT+04:00) Tbilisi",
        'Asia/Yerevan'          => "(GMT+04:00) Yerevan",
        'Asia/Kabul'            => "(GMT+04:30) Kabul",
        'Asia/Yekaterinburg'    => "(GMT+05:00) Ekaterinburg",
        'Asia/Karachi'          => "(GMT+05:00) Karachi",
        'Asia/Tashkent'         => "(GMT+05:00) Tashkent",
        'Asia/Kolkata'          => "(GMT+05:30) Kolkata",
        'Asia/Kathmandu'        => "(GMT+05:45) Kathmandu",
        'Asia/Almaty'           => "(GMT+06:00) Almaty",
        'Asia/Dhaka'            => "(GMT+06:00) Dhaka",
        'Asia/Novosibirsk'      => "(GMT+06:00) Novosibirsk",
        'Asia/Bangkok'          => "(GMT+07:00) Bangkok",
        'Asia/Jakarta'          => "(GMT+07:00) Jakarta",
        'Asia/Krasnoyarsk'      => "(GMT+07:00) Krasnoyarsk",
        'Asia/Chongqing'        => "(GMT+08:00) Chongqing",
        'Asia/Hong_Kong'        => "(GMT+08:00) Hong Kong",
        'Asia/Irkutsk'          => "(GMT+08:00) Irkutsk",
        'Asia/Kuala_Lumpur'     => "(GMT+08:00) Kuala Lumpur",
        'Australia/Perth'       => "(GMT+08:00) Perth",
        'Asia/Singapore'        => "(GMT+08:00) Singapore",
        'Asia/Taipei'           => "(GMT+08:00) Taipei",
        'Asia/Ulaanbaatar'      => "(GMT+08:00) Ulaan Bataar",
        'Asia/Urumqi'           => "(GMT+08:00) Urumqi",
        'Asia/Seoul'            => "(GMT+09:00) Seoul",
        'Asia/Tokyo'            => "(GMT+09:00) Tokyo",
        'Asia/Yakutsk'          => "(GMT+09:00) Yakutsk",
        'Australia/Adelaide'    => "(GMT+09:30) Adelaide",
        'Australia/Darwin'      => "(GMT+09:30) Darwin",
        'Australia/Brisbane'    => "(GMT+10:00) Brisbane",
        'Australia/Canberra'    => "(GMT+10:00) Canberra",
        'Pacific/Guam'          => "(GMT+10:00) Guam",
        'Australia/Hobart'      => "(GMT+10:00) Hobart",
        'Australia/Melbourne'   => "(GMT+10:00) Melbourne",
        'Pacific/Port_Moresby'  => "(GMT+10:00) Port Moresby",
        'Australia/Sydney'      => "(GMT+10:00) Sydney",
        'Asia/Vladivostok'      => "(GMT+10:00) Vladivostok",
        'Asia/Magadan'          => "(GMT+11:00) Magadan",
        'Pacific/Auckland'      => "(GMT+12:00) Auckland",
        'Pacific/Fiji'          => "(GMT+12:00) Fiji",
        'Asia/Kamchatka'        => "(GMT+12:00) Kamchatka"
    );
}


/**
* Fonction _date idem à date() avec mise à l'heure
* @param $format     format de sortie
* @param $timestamp  timestamp
* @param $translate  bool traduction true/false
*/
function _date( $format = 'Y-m-d H:i:s', $timestamp = false, $translate = true ){

    // On définit le timestamp
    $i = $timestamp;

    if ( false === $i )                     $i = time();
    elseif( is_string($i) && is_date($i) )  $i = strtotime($i);
    else                                    $i = (int) $i;

    // Si le format timestamp demandé on retourne timestamp
    if ( 'U' === $format )  return $i;

    // On calcul le décalage horaire
    if( is_intgr(get_option('setting->timezone')) && is_between(get_the_blog('setting->timezone'), -12, 12) )
        $gmt_offset = get_option('setting->timezone') * HOUR_IN_SECONDS;
    elseif( array_key_exists( get_option('setting->timezone', null) , list_timezones_valid() ) ){
        $dateTime     = date_create( date('r', $i) );
        $dateTimeZone = timezone_open( get_option('setting->timezone') );
        $gmt_offset   = timezone_offset_get($dateTimeZone, $dateTime);
    }
    else    $gmt_offset = 0;


    // On lance la traduction
    if ( $translate ){

        // The Weekdays
        $weekday[0] =  __('Sunday');
        $weekday[1] =  __('Monday');
        $weekday[2] =  __('Tuesday');
        $weekday[3] =  __('Wednesday');
        $weekday[4] =  __('Thursday');
        $weekday[5] =  __('Friday');
        $weekday[6] =  __('Saturday');

        // Abbreviations for each day.
        $weekday_abbrev[__('Sunday')]    =  __('Sun');
        $weekday_abbrev[__('Monday')]    =  __('Mon');
        $weekday_abbrev[__('Tuesday')]   =  __('Tue');
        $weekday_abbrev[__('Wednesday')] =  __('Wed');
        $weekday_abbrev[__('Thursday')]  =  __('Thu');
        $weekday_abbrev[__('Friday')]    =  __('Fri');
        $weekday_abbrev[__('Saturday')]  =  __('Sat');

        // The Months
        $month['01'] = __( 'January' );
        $month['02'] = __( 'February' );
        $month['03'] = __( 'March' );
        $month['04'] = __( 'April' );
        $month['05'] = __( 'May' );
        $month['06'] = __( 'June' );
        $month['07'] = __( 'July' );
        $month['08'] = __( 'August' );
        $month['09'] = __( 'September' );
        $month['10'] = __( 'October' );
        $month['11'] = __( 'November' );
        $month['12'] = __( 'December' );

        // Abbreviations for each month.
        $month_abbrev[__( 'January' )]  = __( 'Jan');
        $month_abbrev[__( 'February' )] = __( 'Feb');
        $month_abbrev[__( 'March' )]    = __( 'Mar');
        $month_abbrev[__( 'April' )]    = __( 'Apr');
        $month_abbrev[__( 'May' )]      = __( 'May');
        $month_abbrev[__( 'June' )]     = __( 'Jun');
        $month_abbrev[__( 'July' )]     = __( 'Jul');
        $month_abbrev[__( 'August' )]   = __( 'Aug');
        $month_abbrev[__( 'September' )] = __( 'Sep');
        $month_abbrev[__( 'October' )]  = __( 'Oct');
        $month_abbrev[__( 'November' )] = __( 'Nov');
        $month_abbrev[__( 'December' )] = __( 'Dec');

        // The Meridiems
        $meridiem['am'] = __('am');
        $meridiem['pm'] = __('pm');
        $meridiem['AM'] = __('AM');
        $meridiem['PM'] = __('PM');


        $datemonth              = $month[ date( 'm', $i ) ];
        $datemonth_abbrev       = $month_abbrev[ $datemonth ];
        $dateweekday            = $weekday[ date( 'w', $i ) ];
        $dateweekday_abbrev     = $weekday_abbrev[ $dateweekday ];
        $datemeridiem           = $meridiem[ date( 'a', $i ) ];
        $datemeridiem_capital   = $meridiem[ date( 'A', $i ) ];

        $format = ' '.$format;

        $format = preg_replace( "/([^\\\])D/", "\\1" . backslashit( $dateweekday_abbrev ), $format );
        $format = preg_replace( "/([^\\\])F/", "\\1" . backslashit( $datemonth ), $format );
        $format = preg_replace( "/([^\\\])l/", "\\1" . backslashit( $dateweekday ), $format );
        $format = preg_replace( "/([^\\\])M/", "\\1" . backslashit( $datemonth_abbrev ), $format );
        $format = preg_replace( "/([^\\\])a/", "\\1" . backslashit( $datemeridiem ), $format );
        $format = preg_replace( "/([^\\\])A/", "\\1" . backslashit( $datemeridiem_capital ), $format );

        $format = substr( $format, 1, strlen( $format ) -1 );
    }
    return @date($format, $i + $gmt_offset);
}


/**
* Récupère la date de publication d'une page
* @param $format     format de sortie
* @param $slug       slug de la page (si vide slug de le page appellée par l'url)
*/
/***********************************************/
/*        Internationnalisation time           */
/***********************************************/

function get_the_date( $format = '', $slug = '' ) {

    $format = (string) $format;

    $pubdate = get_the_page('pubdate', $slug);

    if ( '' == $format )
        $the_date = _date( get_option( 'setting->date_format', 'F j, Y' ), $pubdate );
    else
        $the_date = _date( $format, $pubdate );

    return apply_filter( 'get_the_date', $the_date, $format, $pubdate );
}


/**
* Récupère l'heure de publication d'une page
* @param $format     format de sortie
* @param $slug       slug de la page (si vide slug de le page appellée par l'url)
*/
function get_the_time( $format = '', $slug = '' ) {

    $format = (string) $format;

    $pubdate = get_the_page('pubdate', $slug);

    if ( strlen($pubdate) == 0 )  return;

    if ( '' == $format )
        $the_time = _date( get_option( 'setting->time_format', 'g:i a' ), $pubdate );
    else
        $the_time = _date( $format, $pubdate );

    return apply_filter( 'get_the_time', $the_time, $format, $pubdate );
}
