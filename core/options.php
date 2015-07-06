<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Gestion des options du CMS mini POPS
 *
 * @package     cms mini POPS
 * @subpackage  options
 * @version 1
 */


// Appel d'un hook pour fermer la connection à la base de donnée
add_action('muplugins_loaded', function() { mpdb( 'options' , 'EXECUTE' ); } );


/**
 * Ajoute une option
 *
 *  <code>
 *      add_option('pages_limit', 10);
 *  </code>
 *
 */
function add_option( $option , $value = null ) {

    if ( is_array( $option ) ) {

        foreach ( $option as $k => $v ) {
            if ( !option_exists( $k ) && !is_array( $v ) ) {
                mpdb( 'options' , 'INSERT' , array ( $k => $v ) );
            }
        }

    } else {

        if ( !option_exists( $option ) && !is_array( $value ) ) {
            mpdb( 'options' , 'INSERT' , array ( $option => $value ) );
        }

    }

}

/**
 * Mise à jour de la valeur d'une option
 *
 *  <code>
 *      option_update('pages_limit', 12);
 *      option_update(array('pages_count' => 10, 'pages_default' => 'home'));
 *  </code>
 *
 */
function update_option( $option , $value = null ) {

    if ( is_array($option) ) {

        foreach ( $option as $k => $v ) {
            if ( !is_array( $v ) ) {
                mpdb( 'options' , 'UPDATE' , array ( $k => $v) );
            }
        }

    } else {
        if ( !is_array( $value ) ) {
            mpdb( 'options' , 'UPDATE' , array ( $option => $value ) );
        }
    }
}



/**
 * On récupère la valeur d'une option
 *
 *  <code>
 *      $pages_limit = get_option('pages_limit');
 *      if ($pages_limit == '10') {
 *          // do something...
 *      }
 *  </code>
 *
 * @return string
 */
function get_option( $option ) {

    // On redefinit la variable $option
    $option = (string) $option;

    return implode ( '', mpdb( 'options' , 'FIND' , $option ) );

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

    // On redefinit la variable $option
    $option = (string) $option;

    return mpdb( 'options' , 'DELETE' , $option );
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

    // On redefinit la variable $option
    $option = (string) $option;

    if ( !mpdb( 'options' , 'FIND' , $option ) ) {
        return false;
    }
    return true;

}
