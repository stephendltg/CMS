<?php defined('ABSPATH') or die('No direct script access.');

/**
 * AJOUT DES HOOK DO_ACTION, ADD_ACTION, APPLY_FILTER ET ADD_FILTRE
 *
 *
 * @package cms
 * @subpackage Hook
 * @version 1
 */


// On initialise les filtres global
global $hook_filter, $hook_actions ;

if ( ! isset( $hook_filter ) )
	$hook_filter = array();

if ( ! isset( $wp_actions ) )
	$hook_actions = array();



/**
 *  add_action
 *
 *  <code easy>
 *      // Hooks une fonction "newLink" à l'action "footer".
 *      add_action('footer', 'newLink', 10);
 *
 *      Function newLink() {
 *          echo '<a href="#">My link</a>';
 *      }
 *  </code>
 *
 *  <code args>
 *      do_action ('footer' );
 *
 *      add_action('footer', 'newLink', 10, array ('link', 'google') );
 *
 *      Function newLink( $a, $b ){
 *          echo '<a href="'.$a.'">'.$b.'</a>';
 *  </code>
 *
 *
 *  <code args>
 *      do_action ('footer', array('link', 'google' )); // Arguments do_action prioritaire sur add_action
 *
 *      add_action('footer', 'newLink', 10 );
 *
 *      Function newLink( $a, $b ){
 *          echo '<a href="'.$a.'">'.$b.'</a>';
 *  </code>
 *
 *
 * @param string  $action_name    Nom de l'action déclarer avec do_action
 * @param mixed   $added_function Fonction à ajouter à l'action
 * @param integer $priority       Prioritée. Default : 10
 * @param array   $args           Arguments à passer à la fonction ajouté, si : do_action ('action_name' , null )
 */

function add_action( $action_name, $added_function = null, $priority = 10, $args = array() ) {

    global $hook_actions;

    // On redefini les arguments
    $action_name     = (string) $action_name;
    $priority        = (int) $priority;

    // On affecte l'action seulement si la fonction appellée existe
    if ( !empty($added_function) ) {
        // On stocke le hook par nom d'action puis par priorité
        $hook_actions[$action_name][$priority][] = array('function' => $added_function , 'args' => $args);
        // On trie les hook par priorité ( 1 à ... )
        ksort( $hook_actions[$action_name] );
    }
}



/**
 * do_action
 *
 *  <code>
 *      // Run functions hooked on a "footer" action hook.
 *      do_action('footer');
 *  </code>
 *
 * @param  string  $action_name Nom de l'action
 * @param  array   $args        Arguments, envoi à la fonction du hook(add_action) si do_action ('action_name', array ('param1', 'param2')
 * @param  boolean $return      Retourne les données ou non. Default: false
 * @return mixed
 */

function do_action( $action_name, $args = null , $return = false ) {

    global $hook_actions;

    // On redefini les arguments
    $action_name = (string) $action_name;
    $return      = (bool) $return;

    if ( !array_key_exists( $action_name , $hook_actions ) ) {
        return false;
    }

    // On boucle pour ressortir les hooks à actionner
    foreach ( $hook_actions[$action_name] as $priority=>$actions ) {
        foreach ( $actions as $actions=>$action ){
            if ( isset($args) ) {

                if ( $return ) { return call_user_func_array( $action['function'], $args ); }
                else { call_user_func_array( $action['function'], $args ); }

            } else {
                    if ( $return ) { return call_user_func_array( $action['function'], $action['args'] ); }
                    else { call_user_func_array( $action['function'], $action['args'] ); }
            }
        }
    }

    return true;
}



/**
 * apply_filter
 *
 *  <code>
 *      apply_filter('content', $content, $arg );
 *  </code>
 *
 * @access  public
 * @param  string $filter_name Nom du filtre à hooker.
 * @param  mixed  $value       Valeurs à filtrer passé à la fonction.
 * @return mixed
 */
function apply_filter( $filter_name, $value ) {

    global $hook_filter;

    // On redefini les arguments
    $filter_name = (string) $filter_name;

    $args = array_slice(func_get_args(), 2);

    if ( ! isset( $hook_filter[$filter_name] ) ) {
        return $value;
    }

    foreach ( $hook_filter[$filter_name] as $priority => $functions ) {

        if ( ! is_null($functions) ) {
            foreach ( $functions as $function ) {

                $all_args = array_merge( array($value), $args );
                $function_name = $function['function'];
                $accepted_args = $function['accepted_args'];

                if ( $accepted_args == 1 ) { $the_args = array($value);}
                elseif ( $accepted_args > 1 ) { $the_args = array_slice($all_args, 0, $accepted_args); }
                elseif ( $accepted_args == 0 ) { $the_args = null; }
                else { $the_args = $all_args; }

                $value = call_user_func_array($function_name, $the_args);
            }
        }
    }

    return $value;
}



/**
 * add_filter
 *
 *  <code>
 *      add_filter('content', 'replacer', 2);
 *
 *      Function replacer($content, $arg) {
 *          return $content.$arg;
 *      }
 *  </code>
 *
 * @access  public
 * @param  string  $filter_name     Nom du filtre.
 * @param  string  $function_to_add Fonction ajouté au filtre.
 * @param  integer $priority        Prioritée - default: 10.
 * @param  integer $accepted_args   Nombre d'arguments passés à la fonction.
 * @return boolean
 */

function add_filter( $filter_name, $function_to_add, $priority = 10, $accepted_args = 1 ) {

    global $hook_filter;

    // On redefini les arguments
    $filter_name     = (string) $filter_name;
    $function_to_add = $function_to_add;
    $priority        = (int) $priority;
    $accepted_args   = (int) $accepted_args;

    // On vérifie qu'il n'y a pas le même filtre avec la même priorité. Thanks to WP :)
    if ( isset ( $hook_filter[$filter_name]["$priority"] ) ) {

        foreach ( $hook_filter[$filter_name]["$priority"] as $filter ) {

            if ( $filter['function'] == $function_to_add ) { return true; }
        }
    }

    // On stocke le hook par nom d'action puis par priorité
    $hook_filter[$filter_name]["$priority"][] = array( 'function' => $function_to_add, 'accepted_args' => $accepted_args );

    // On trie les hook par priorité ( 1 à ... )
    ksort( $hook_filter[$filter_name]["$priority"] );

    return true;
}
