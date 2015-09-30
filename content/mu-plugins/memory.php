<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Affichage de la mémoire du CMS et optimisation de celle-ci
 *
 * @package     cms
 * @subpackage  memory
 * @version 1
 */

add_action ( 'mpops_footer' , 'dashboard' , 9999 );


function dashboard(){

    global $hook_actions, $hook_filter;

    echo '<div style="background : #000; color:#fff; padding: 5px; margin: 0">';

    // Données globales
    echo '<p>url: '. HOME .' | chemin: '. ABSPATH .'</p>';

    echo '<p>Requetes passer par l\'url : '. get_url_queries() .'</p>';

    echo '<p>Memory: '. get_cms_memory() .' | Time (Exec) : '. timer_stop(3) . ' | Hook filters (nbr appel) : '.count($hook_filter).' | Hook actions (nbr appel) : '.count($hook_actions).' </p><hr>';

    echo '<p>HOOKS:</p>';
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

    //DATABASE
    echo '<p>DATA BASE:</p>';

    //Listing query
    echo '<p>Liste des tables:</p><ul>';
    foreach ( mpdb('*' , 'INFO' ) as $query_file ) {
        echo '<li>'. basename($query_file) . '</li>';
    }
    echo '</ul>';

    //Requetes query
    foreach( mpdb('*','STATISTIC') as $query_name => $funcs )  {
        echo '<div style="display: inline-block; width:50%;vertical-align: top;">';
        echo '<p>' . $query_name . '</p><ul>';
        foreach( $funcs as $func => $request ){
            ( is_array($request) ) ? $list_request = implode( ' | ', $request ) : $list_request = $request;
            echo '<li>' . $func . ' : ' . count( $request ) . ' requetes ('. $list_request. ')</li>';
        }
        echo '</ul></div>';
    }
    echo '<hr>';

    echo '<p style="text-align: right">Author plugins: stephen deletang</p></div>';

}
