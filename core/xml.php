<?php defined('ABSPATH') or die('No direct script access.');

/**
 * GESTION DES FICHIERS XML
 *
 * @package     CMS
 * @subpackage  xml
 *
 */



/***********************************************/
/*                     XML                     */
/***********************************************/

/**
 * Create safe xml data. Removes dangerous characters for string.
 *
 *  <code>
 *      $xml_safe = XML_safe($xml_unsafe);
 *  </code>
 *
 * @return string
 */
function XML_safe($str, $flag = true) {

    // Redefine vars
    $str  = (string) $str;
    $flag = (bool) $flag;

    // Remove invisible chars
    $non_displayables = array('/%0[0-8bcef]/', '/%1[0-9a-f]/', '/[\x00-\x08]/', '/\x0b/', '/\x0c/', '/[\x0e-\x1f]/');
    do {
        $cleaned = $str;
        $str = preg_replace($non_displayables, '', $str);
    } while ($cleaned != $str);

    // htmlspecialchars
    if ($flag) $str = htmlspecialchars($str, ENT_QUOTES, 'utf-8');

    // Return safe string
    return $str;
}

/**
 * Get XML file
 *
 *  <code>
 *      $xml_file = XML_loadFile('path/to/file.xml');
 *  </code>
 *
 * @return array
 */
function XML_loadFile($file, $force = false) {

    // Redefine vars
    $file  = (string) $file;
    $force = (bool) $force;

    // For CMS API XML file force method
    if ($force) {
        $xml = file_get_contents($file);
        $data = simplexml_load_string($xml);

        return $data;
    } else {
        if (file_exists($file) && is_file($file)) {
            $xml = file_get_contents($file);
            $data = simplexml_load_string($xml);

            return $data;
        } else {
            return false;
        }
    }
}



/***********************************************/
/*                     DB                      */
/***********************************************/

/**
 * Création d'une data base
 *
 * @return boolean
 */
function create( $db_name, $chmod = 0775 ) {

    // On redefinit la variable
    $db_name = (string) $db_name;

    if ( is_dir( ABASTH . '/' . $db_name ) ) return false;
    return mkdir( ABASTH . '/' . $db_name, $chmod );
}



/***********************************************/
/*                     New Table               */
/***********************************************/


 /*
 * Déclaration d'une nouvelle classe 'table'
 *
 * @return new table
 */
function xmldb($table_name){
    return new Table($table_name);
}
