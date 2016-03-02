<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction Options
 *
 *
 * @package cms mini POPS
 * @subpackage options - fonction des options plugins/ thème
 * @version 1
 */


/**
 * Ajoute une option
 * -> trim($option)
 *  <code>
 *      add_option('pages_limit', 10);
 *      add_option(array('pages_count' => 10, 'pages_default' => 'home'));
 *  </code>
 *
 */
function add_the_option( $option , $value = null ) {

    if ( is_array( $option ) ) {
        $flag = false;
        foreach ( $option as $k => $v ) {
            if ( !option_exists( $k ) && !is_array( $v ) ) {
                if ( mpdb( 'options' , 'INSERT' , array ( $k => $v ) ) ) $flag = true;
            }
        }
        return $flag;
    } else {
        if ( !option_exists( $option ) && !is_array( $value ) )
            return mpdb( 'options' , 'INSERT' , array ( $option => $value ) );
    }
}

/**
 * Mise à jour de la valeur d'une option
 *
 *  <code>
 *      update_option('pages_limit', 12);
 *      update_option(array('pages_count' => 10, 'pages_default' => 'home'));
 *  </code>
 *
 */
function update_the_option( $option , $value = null ) {

    if ( is_array($option) ) {
        $flag = false;
        foreach ( $option as $k => $v ) {
            if ( !is_array( $v ) ) {
                if ( mpdb( 'options' , 'UPDATE' , array ( $k => $v) ) ) $flag = true;
            }
        }
        return $flag;
    } else {
        if ( !is_array( $value ) ) return mpdb( 'options' , 'UPDATE' , array ( $option => $value ) );
    }
}



/**
 * On récupère la valeur d'une option
 *
 *  <code>
 *      $pages_limit = get_option('pages_limit');
 *  </code>
 *
 * @return string
 */
function get_the_option( $option ) {

    $option = (string) $option;

    global $__args;

    // On récupère le champ ciblé
    $params = explode('->', $option);
    $size = size($params);

    $args = '';

    // On recherche le champ ciblé dans la table
    if( !empty( $__args[ $params[0] ] ) ){

        if( is_same( $size, 1 ) )
            $args = $__args[ $params[0] ];

        elseif( is_same( $size, 2 ) ){
            if( !empty( $__args[ $params[0] ][ $params[1] ] ) )
                $args = $__args[ $params[0] ][ $params[1] ];
        }

        elseif( is_same( $size, 3 ) ){
            if( !empty( $__args[ $params[0] ][ $params[1] ] ) && !empty( $__args[ $params[0] ][ $params[1] ][ $params[2] ] ) )
                $args = $__args[ $params[0] ][ $params[1] ][ $params[2] ];
        }

    }

    // Si la valeur du champ ciblé est une chaine on vérifie que c'est une variable yaml
    // et si c'est le cas on recherche sa valeur associé dans la table.
    if( is_string($args)  && is_different( strpos($args,'&'), false) )
        $args = preg_replace_callback('/&([\w->]+)/i', function($matches){
            $matches = get_the_args($matches[1]);
            return is_array( $matches ) ? '' : $matches ;
        } , $args);

    return $args;
}


/**
 * Supprimer une option
 *
 *  <code>
 *      remove_option('pages_limit');
 *  </code>
 *
 * @return boolean
 */
function remove_the_option( $option ) {

    $option = (string) $option;

    return mpdb( 'options' , 'REMOVE' , $option );
}


/**
 * Vérification de l'existence d'une option
 *
 *  <code>
 *      if (option_exists('pages_limit')) {
 *          // do something...
 *      }
 *  </code>
 *
 * @return boolean
 */
function option_exists( $option ) {

    $option = (string) $option;

    if ( !mpdb( 'options' , 'FIND' , $option ) ) return false;

    return true;
}

