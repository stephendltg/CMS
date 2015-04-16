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
 * @param  string    $str chaine de caractères
 * @param  bool      $flag htmlspecialchars
 * @return string    Chaine de caractère nettoyer
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
 * @param  string    $file nom du fichier à charger - chemin absolu
 * @param  bool      $force Si forcer on ne test pas l'existence du fichier
 * @return array     Donnée contenu dans le fichier
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
 * @param  string    $db_name Nome de la database
 * @param  int       $chmod Mode d'accès du fichier
 * @return boolean
 */
function db_create( $db_name , $chmod = 0775 ) {

    // On redefinit la variable
    $db_name = (string) $db_name;

    if ( is_dir( ABSPATH . '/' . $db_name ) ) return false;
    return mkdir( ABSPATH . '/' . $db_name , $chmod );
}



/***********************************************/
/*                     DB Table                */
/***********************************************/

// On initialise les filtres global
global $json_file ;

if ( ! isset( $json_file ) )
	$json_file = array();


/**
 * Création d'une table dans la data base
 *
 * @param  string   $table_name Nom de la table
 * @param  array    $fields tableau des champs
 * @param  string   $db Nom de la DB
 * @return boolean
 */
function T_Create( $table_name , $fields , $db = JSONDB ) {

    // On redefinit les variables
    $table_name  = (string) $table_name;
    $db          = (string) $db;

    if (  file_exists( $db . '/' . $table_name . '.table.json' ) &&
        is_dir( dirname( $db ) ) &&
        is_writable( dirname( $db ) ) &&
        isset( $fields ) &&
        is_array( $fields ) ) {

        // Creation des champs
        $data = array( 'autoincremente' => 0 );
        $data['fields'] = $fields;

        // Creation de la nouvelle table
        return file_put_contents( $db . '/' . $table_name . '.table.json' , json_encode($data) , LOCK_EX);

    } else {

        // Something wrong... return false
        return false;
    }
}


/**
* Supprimer une table
*
*
* @param  string    $table_name Nom de la table
* @param  array     $fields tableau des champs
* @param  string    $db Nom de la DB
* @return boolean
*/
function T_Drop( $table_name , $db = JSONDB ) {

    // On redefinit les variables
    $table_name   = (string) $table_name;
    $db           = (string) $db;

    if ( file_exists( $db . '/' . $table_name . '.table.json' ) && ! is_dir( $db . '/' . $table_name . '.table.json' ) )
            return unlink( $db . '/' . $table_name . '.table.json' );
    return false;
}


/**
* Prépare la lecture d'une table
*
*
* @param  string    $table_name Nom de la table
* @param  string    $db Nom de la DB
* @return boolean
*/
function json_prepare( $table_name , $db = JSONDB ) {

    global $json_file;

    // On redefinit les variables
    $table_name = (string) $table_name;
    $db         = (string) $db;

    if ( $json_file['json_object'] = json_loadfile ( $db . '/' . $table_name . '.table.json' ) )
        $json_file['json_filename'] = $db . '/' . $table_name . '.table.json';
    return false;
}


/**
* Ferme la lecture d'une table ( libère la mémoire et bloque l'accès à la variable globale )
*
*
* @return boolean
*/
function json_close( ) {

    global $json_file;

    unset ( $json_file['json_filename'] );
    unset ( $json_file['json_object'] );
}


/**
* Lire les infos d'une table
*
*
* @return array
*/
function json_info() {

    global $json_file;

    return array(
        'table_name'        => basename(  $json_file['json_filename'] ),
        'table_size'        => filesize(  $json_file['json_filename'] ),
        'table_last_change' => filemtime( $json_file['json_filename'] ),
        'table_last_access' => fileatime( $json_file['json_filename'] ),
        'table_fields'      => $json_file['json_object']['fields'],
        'records_count'     => count( $json_file['json_object'] )-2
    );
}


/**
* Ajouté un champ à une table
*
*
* @param  string    $name Nom du champs
* @return boolean
*/
function add_field( $name ) {

    global $json_file;

    // On redefinit les variables
    $name = (string) $name;

    if ( in_array( $name , $json_file['json_object']['fields'] ) || $name == '' )
        return false;

    $json_file['json_object']['fields'][]= esc_json( $name );
    return file_put_contents( $json_file['json_filename'] , json_encode($json_file['json_object']) , LOCK_EX);
}


/**
* Supprimer un champ à une table
*
*
* @param  string    $name Nom du champs
* @return boolean
*/
function delete_field( $name ) {

    global $json_file;

    // On redefinit les variables
    $name = (string) $name;

    if ( $key = array_search( $name , $json_file['json_object']['fields'] ) + 1 ) {
        unset ( $json_file['json_object']['fields'][$key-1] ); // On supprimer le champs
        $json_file['json_object']['fields'] = array_values ( $json_file['json_object']['fields'] ); // On reaffecte les index du tableau
        return file_put_contents( $json_file['json_filename'] , json_encode($json_file['json_object']) , LOCK_EX);
    }
    return false;
}

/**
* Modifier un champ à une table
*
*
* @param  string    $name Nom du champs
* @return boolean
*/
function update_field( $oldname , $name ) {

    global $json_file;

    // On redefinit les variables
    $oldname = (string) $oldname;
    $name    = (string) $name;
    if ( $key = array_search( $name , $json_file['json_object']['fields'] ) + 1 ) {
        $json_file['json_object']['fields'][$key-1] = $name;
        return file_put_contents( $json_file['json_filename'] , json_encode($json_file['json_object']) , LOCK_EX);
    }
    return false;
}




db_create ( 'test');

//table1
T_Create( 'testing', array ( 'looser', 'encore', 'manger', 'hache', 'vert'), ABSPATH.'test' ) ;
// Table 2
T_Create( 'esteban', array ( 'fabienne', 'manger'), ABSPATH.'test' ) ;

//T_Drop( 'testing' , ABSPATH.'test' );

json_prepare( 'testing' , ABSPATH.'test' );

//update_field('looser','stephen');
//add_field('gagos');
delete_field('manger');

var_dump( $json_file );

json_close();

