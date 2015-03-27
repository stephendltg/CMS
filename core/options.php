<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Gestion des options du CMS
 *
 * @package     cms
 * @subpackage  options
 * @version 1
 */




/**
 * Ajoute une option
 *
 *  <code>
 *      add_option('pages_limit', 10);
 *      add_option(array('pages_count' => 10, 'pages_default' => 'home'));
 *  </code>
 *
 * @return boolean
 */
function add_option( $option , $value = null ) {

    if ( is_array( $option ) ) {

        foreach ( $option as $k => $v ) {
            $_option = xmldb('options')->select( '[name="'.$k.'"]', null );
            if ( count($_option) == 0 ) {
                xmldb('options')->insert( array('name' => $k, 'value' => $v) );
            }
        }

    } else {

        $_option = $options->select('[name="'.$option.'"]', null);
        if (count($_option) == 0) {
            return xmldb('options')->insert(array('name' => $option, 'value' => $value));
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
 * @return boolean
 */
function update_option( $option, $value = null ) {

    if ( is_array($option) ) {

        foreach ( $option as $k => $v ) {
            xmldb('options')->updateWhere( '[name="'.$k.'"]' , array('value' => $v) );
        }

    } else {
        return xmldb('options')->updateWhere( '[name="'.$option.'"]' , array('value' => $value) );
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
function get_option($option) {

    // On redefinit la variable $option
    $option = (string) $option;

    $option_name = xmldb('options')->select( '[name="'.$option.'"]' , null );

    return isset( $option_name['value'] ) ? $option_name['value'] : '';
}


/**
 * Supprime une option
 *
 *  <code>
 *      remove_option('pages_limit');
 *  </code>
 *
 * @return boolean
 */
function remove_option($option) {

    // On redefinit la variable $option
    $option = (string) $option;

    return xmldb('options')->deleteWhere( '[name="'.$option.'"]' );
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
function option_exists($option) {

    // On redefinit la variable $option
    $option = (string) $option;

    return ( count( xmldb('options')->select('[name="'.$option.'"]', null ) ) > 0) ? true : false;
}


