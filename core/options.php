<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Gestion des options du CMS mini POPS
 *
 * @package     cms mini POPS
 * @subpackage  options
 * @version 1
 */

// Creation de la base si non créer
mpdb( 'options' , 'CREATE_TABLE' , array( 'active_plugins' => '', 'active_theme' => '' ) );

// On ouvre la connection à la base de donnée options
mpdb( 'options' , 'PREPARE' );

// Appel d'un hook pour fermer la connection à la base de donnée options
add_action('config_loaded', function() { mpdb( 'options' , 'EXECUTE', 'PRESERVE_READ' ); } );


/**
 * Ajoute une option
 *
 *  <code>
 *      add_option('pages_limit', 10);
        add_option(array('pages_count' => 10, 'pages_default' => 'home'));
 *  </code>
 *
 */
function add_option( $option , $value = null ) {

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
function update_option( $option , $value = null ) {

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
function get_option( $option ) {

    $option = (string) $option;
    $get_result = mpdb( 'options' , 'FIND' , $option );
    if ( !$get_result ) return false;
    return implode ( '', $get_result );
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
function remove_option( $option ) {

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
