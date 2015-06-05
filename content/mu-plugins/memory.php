<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Affichage de la mémoire du CMS et optimisation de celle-ci
 *
 * @package     cms
 * @subpackage  memory
 * @version 1
 */

add_action ( 'muplugins_loaded' , 'get_cms_memory' , 99 );


/**
 * convertisseur pour mémoire
 * http://php.net/manual/fr/function.memory-get-usage.php
 *
 * Argument $size ( valeur en octet )
 *
 * @return string
 */
function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}

/**
 * get_cms_peak_memory
 *
 * Retourne la mémoire maximale alloué par php
 *
 * @return string
 */
function get_cms_peak_memory( $flag = false ) {

    return convert( memory_get_peak_usage() );
}


/**
 * get_cms_memory
 *
 * Retourne la mémoire alloué par php
 * Argument $flag = true pour obtenir la mémoire allouée par le système
 *
 * @return string
 */
function get_cms_memory( $flag = false ) {

    // On redefinit les variables
    $flag   = (bool) $flag;

    if ( $flag )
        return convert( memory_get_usage(true) );

    return convert( memory_get_usage() );
}


echo '<p>memoire: '. get_cms_memory() .' | Temps : '. timer_stop(3) . ' | hook filter : '.count($hook_filter).' | hook action : '.count($hook_actions).' </p>';
