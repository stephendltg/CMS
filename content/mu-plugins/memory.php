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


echo '<div style="background : #000; color:#fff; padding: 5px; margin: 0">';

// Données globales
echo '<p>Memory: '. get_cms_memory() .' | Time (Exec) : '. timer_stop(3) . ' | Hook filters (nbr appel) : '.count($hook_filter).' | Hook actions (nbr appel) : '.count($hook_actions).' </p><hr>';

// Listes hooks actions:
echo '<div style="display: inline-block; width:49%; vertical-align: top;"><p>Hook actions (nbr actions) :</p><ol>';
foreach( $hook_actions as $hook_name => $actions ){
    echo '<li>'.$hook_name.' ( '.count($actions).' )</li>';
}
echo '</ol></div>';

// Listes hooks filter:
echo '<div style="display: inline-block; vertical-align: top;"><p>Hooks filters (nbr filters) :</p><ol>';
foreach( $hook_filter as $hook_name => $filters ){
    echo '<li>'.$hook_name.' ( '.count($filters).' )</li>';
}
echo '</ol></div><hr>';

//Requetes query
echo '<div style="display: inline-block; vertical-align: top;">';
foreach( mpdb('*','STATISTIC') as $query_name => $funcs )  {
    echo '<p>Table : ' . $query_name . '</p><ul>';
    foreach( $funcs as $func => $request ){
        ( is_array($request) ) ? $list_request = implode( ' | ', $request ) : $list_request = $request;
        echo '<li>' . $func . ' : ' . count( $request ) . ' requetes ('. $list_request. ')</li>';
    }
    echo '</ul>';
}
echo '</div><hr>';

echo '<p style="text-align: right">Author plugins: stephen deletang</p></div>';
