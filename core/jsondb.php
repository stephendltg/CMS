<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction base de donnée json.
 *
 *
 * @package cms
 * @subpackage jsondb
 * @version 1
 */

/***********************************************/
/*                     JSON-functions          */
/***********************************************/

/**
 * Préparer donnée pour stockage json. Enlèves les caractères dangereux.
 *
 *  <code>
 *      $xml_safe = esc_json($xml_unsafe);
 *  </code>
 *
 * @return string
 */
function esc_json( $str , $flag = true ) {

    // On redefini les variables
    $str  = (string) $str;
    $flag = (bool) $flag;

    // On supprime les caractères invisibles
    $non_displayables = array('/%0[0-8bcef]/', '/%1[0-9a-f]/', '/[\x00-\x08]/', '/\x0b/', '/\x0c/', '/[\x0e-\x1f]/');
    do {
        $cleaned = $str;
        $str = preg_replace( $non_displayables , '' , $str );
    } while ( $cleaned != $str );

    // htmlspecialchars
    if ($flag) $str = htmlspecialchars( $str , ENT_QUOTES , 'utf-8' );

    return $str;
}


/**
 * Chargé un fichier JSON
 *
 *  <code>
 *      $json = json_loadfile('path/to/file.json');
 *  </code>
 *
 * @return array
 */
function json_loadfile( $file , $force = false ) {

    // On redefini les variables
    $file  = (string) $file;
    $force = (bool) $force;

    // For CMS API XML file force method
    if ( $force ) {
        return json_decode( file_get_contents ( $file ), true );
    } else {
        if ( file_exists( $file ) && is_file( $file ) ) {
            return json_decode( file_get_contents ( $file ), true );
        } else {
            return false;
        }
    }
}



/***********************************************/
/*                      DB                     */
/***********************************************/

/**
 * Création d'une data base ( on créer un répertoire qui regroupe les tables )
 *
 * @return boolean
 */
function create( $db_name , $chmod = 0775 ) {

    // On redefinit la variable
    $db_name = (string) $db_name;

    if ( is_dir( ABASTH . '/' . $db_name ) ) return false;
    return mkdir( ABASTH . '/' . $db_name , $chmod );
}



/***********************************************/
/*                     DB Table                */
/***********************************************/

/**
 * Création d'une table dans la data base
 *
 * @return boolean
 */
function Table_create( $table_name , $fields , $db = JSONDB ) {

    // On redefinit les variables
    $table_name  = (string) $table_name;
    $db          = (string) $db;

    if ( ! file_exists( $db . '/' . $table_name . '.table.json' ) &&
        is_dir( dirname( $db ) ) &&
        is_writable( dirname( $db ) ) &&
        isset( $fields ) &&
        is_array( $fields ) ) {

        // Create table fields
        $_fields = '<fields>';
        foreach ($fields as $field) $_fields .= "<$field/>";
        $_fields .= '</fields>';

        // Create new table
        $file = fopen( $db . '/' . $table_name . '.table.json' , 'w+');
        fwrite ( $file, json_encode ( $data ) );
        fclose( $file );

        return file_put_contents( $db . '/' . $table_name . '.table.json' , $data , LOCK_EX);

    } else {

        // Something wrong... return false
        return false;
    }

}
