<?php defined('ABSPATH') or die('No direct script access.');

/*
Plugin Name: MiniPops Website Monitoring
Plugin URI:
Description: Tools for developper
Version: 1.0
Author: Stephen Deletang
Author URI:
*/

define( 'MEMORY_VERSION'    , '1' );
define( 'MEMORY_NAME'       , 'Website Monitoring' );
define( 'MEMORY_AUTHOR'     , 'Stephen Deletang' );


if( DEBUG ) add_action ( 'mp_footer' , 'dashboard' , PHP_INT_MAX );


function dashboard(){

    global $mp_hook_actions, $mp_hook_filter;

    echo '<div style="background : #000; color:#fff; padding: 5px; margin: 0">';

    echo '<p><big>'.MEMORY_NAME.' ( v'.MEMORY_VERSION.' ) | Author: '.MEMORY_AUTHOR.'</big></p>';

    echo '<p>----</p>';

    // Données globales
    echo '<p>Url: '. HOME .' | Path: '. ABSPATH .'</p>';

    echo '<p>----</p>';

    echo '<p>Requetes passer par l\'url : '. get_url_queries() .'</p>';

    echo '<p>----</p>';

    echo '<p>Memory limit: '. get_limit_memory() .' | Memory upload : '. get_upload_memory() . ' | Memory Post: '.get_upload_memory().' | Time limit execution : '.get_max_time_execution().'s </p>';

    echo '<p>----</p>';

    echo '<p>Memory: '. get_cms_memory() .' | Time (Exec) : '. timer_stop(3) . '</p>';

    echo '<p>----</p>';

    echo '<p>HOOKS:</p>';

    echo '<p> Hook filters (nbr appel) : '.count($mp_hook_filter).' | Hook actions (nbr appel) : '.count($mp_hook_actions).' </p><br>';

    // Listes hooks actions:
    echo '<div style="display: inline-block; width:49%; vertical-align: top;"><p>Hook actions (nbr actions) :</p><ol>';
    foreach( $mp_hook_actions as $hook_name => $actions ){
        echo '<li>'.$hook_name.' ( '.count($actions).' )</li>';
    }
    echo '</ol></div>';

    // Listes hooks filter:
    echo '<div style="display: inline-block; vertical-align: top;"><p>Hooks filters (nbr filters) :</p><ol>';
    foreach( $mp_hook_filter as $hook_name => $filters ){
        echo '<li>'.$hook_name.' ( '.count($filters).' )</li>';
    }
    echo '</ol></div>';

    echo '<p>----</p></div>';

}
