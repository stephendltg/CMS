<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction base de donnée json.
 *
 *
 * @package cms mini POPS
 * @subpackage jsondb
 * @version 1
 */


define ( 'JSONHASH' , md5( date('Ymj').HOME ) );


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
function json_create_DB( $db_name , $chmod = 0775 ) {

    // On redefinit la variable
    $db_name = (string) $db_name;

    if ( is_dir( ABSPATH . '/' . $db_name ) ) return false;
    return mkdir( ABSPATH . '/' . $db_name , $chmod );
}


/***********************************************/
/*                     DB Table                */
/***********************************************/


/**
 * Création d'une table dans la data base
 *
 * @param  string   $table_name Nom de la table
 * @param  string   $db Nom de la DB
 * @return boolean
 */
function json_create_table( $table_name , $db = JSONDB ) {

    // On redefinit les variables
    $table_name  = (string) $table_name;
    $db          = (string) $db;

    if ( ! file_exists( $db . '/' . $table_name . '.table.json' ) &&
        is_dir( dirname( $db ) ) &&
        is_writable( dirname( $db ) )
       ){

        // Creation de la nouvelle table
        return file_put_contents( $db . '/' . $table_name . '.table.json' , json_encode($data) , LOCK_EX);

    }

    return false;
}


/**
* Supprimer une table
*
*
* @param  string    $table_name Nom de la table
* @param  string    $db Nom de la DB
* @return boolean
*/
function json_drop_table( $table_name , $db = JSONDB ) {

    // On redefinit les variables
    $table_name   = (string) $table_name;
    $db           = (string) $db;

    if ( file_exists( $db . '/' . $table_name . '.table.json' ) && ! is_dir( $db . '/' . $table_name . '.table.json' ) )
            return unlink( $db . '/' . $table_name . '.table.json' );
    return false;
}



/***********************************************/
/*                     TABLE CONNECT           */
/***********************************************/

/**
* Prépare la lecture d'une table
*
*
* @param  string    $table_name Nom de la table
* @param  string    $db Nom de la DB
* @return array     Donnée d'une table json
*/
function json_connect( $table_name , $db = JSONDB ) {

    $json_data = array();

    // On redefinit les variables
    $table_name = (string) $table_name;
    $db         = (string) $db;

    if ( $json_data = json_loadfile ( $db . '/' . $table_name . '.table.json' ) ) {
        return array_merge ( $json_data , array ( JSONHASH => base64_encode ( $db . '/' . $table_name . '.table.json' ) ) );
    }
    return false;
}


/**
* Ferme la lecture d'une table ( libère la mémoire et bloque l'accès à la variable globale )
*
*
* @param  string    $json_data Donnée d'une table json
* @return boolean
*/
function json_close( &$json_data ) {

    if ( count ( find( $json_data , JSONHASH ) ) > 0 ) {

        $filename = base64_decode ( $json_data[ JSONHASH ] );

        $json_data = array_diff_key( $json_data , array ( JSONHASH => null) );

        file_put_contents( $filename , json_encode( $json_data ) , LOCK_EX );

    }

    $json_data = null;

    return true;
}


/**
* Lire les infos d'une table
*
*
* @param  string    $json_data Donnée d'une table json
* @return array
*/
function json_info( &$json_data ) {

    $filename = base64_decode ( $json_data[ JSONHASH ] );

    return array(
        'table_name'        => basename( $filename ),
        'table_size'        => filesize( $filename ),
        'table_last_change' => filemtime( $filename ),
        'table_last_access' => fileatime( $filename ),
    );
}


/***********************************************/
/*                     CRUD                    */
/***********************************************/


/**
* VARIABLE GLOABALE TEMPORAIRE CRUD
*/

$JSON_DB_TMP = array();


/**
 * SEARCH ( callback function find() )
 *
 * @param  string   $value  Valeur de la table passée en paramètre
 * @param           $key    Clé de la table passée en paramètre
 * @param  string   $what   le "quoi" : on recherche quoi
 */
function search( $value , $key , $what ){

    global $JSON_DB_TMP;

    if ( is_array ( $value) ){
        array_walk ( $value , 'search' , $what );
    }
    if ( $key === $what ) {
        if ( is_array ( $value ) ) {
            $value = serialize( $value );
        }
        array_push ( $JSON_DB_TMP , $value );
    }
}


/**
 * FIND
 *
 * @param  array            $json_array    nom de la base: peut etre de type $table['nom de la collection']
 * @param  string, array    $field         string ou array : si array la recherche est de type resultat = seulement si champ1 & champ2 sont dans la même collection
 * @param  int              $row_count     Nombre de resultat:
 * @param  string           $order         Ordre de traitement : DESC (descendant), ASC ( ASCENDANT)
 * @return array            $data          Retourne un tableau contenant le ou les résultats même si vide
 */
function find( &$json_array , $field ) {

    // Appel variable super globale
    global $JSON_DB_TMP;

    // On redefinit les variables
    $field = (string) $field;

    if ( array_walk ( $json_array , 'search' , $field ) ){
        $data = $JSON_DB_TMP;   // On réaffecte le resultat si pas d'erreur.
    }

    $JSON_DB_TMP = array();     // On purge les données de la variable superglobal $JSON_DB_TMP

    return $data;
}


/**
 * INSERT ( on insere une donnée s'il elle existe alors on ajoute la donnée à la suite )
 *
 * @param  array            $json_array    nom de la base: peut etre de type $table['nom de la collection']
 * @param  string, array    $field         Tableau de donnée à mettre à jour
 * @return bool             Retourne true si tout ce passe bien
 */
function insert( &$json_array , array $fields = null ) {

    if ( count($fields) > 0 ) {
        $json_array = array_merge_recursive ( $json_array , $fields );
    }
    return $json_array;
}


/**
 * UPDATE ( on met à jour une donnée )
 *
 * @param  array            $json_array    nom de la base: peut etre de type $table['nom de la collection']
 * @param  string, array    $field         Tableau de donnée à mettre à jour
 * @return bool             Retourne true si tout ce passe bien
 */
function update( &$json_array , array $fields = null ) {

    if ( count( $fields ) > 0 ) {
        foreach ( $fields as $field => $value ){
            if ( count ( find( $json_array , $field ) ) > 0 ){
                $json_array = array_merge ( $json_array , array ( $field => $value ) );
            }
        }
    }
    return $json_array;
}


/**
 * DELETE ( on supprime un champ et sa valeur associé )
 *
 * @param  array     $json_array    nom de la base: peut etre de type $table['nom de la collection']
 * @param  string    $field         Champs à supprimer
 * @return bool      Retourne true si tout ce passe bien
 */
function delete( &$json_array , $field ) {

    // On redefinit les variables
    $field = (string) $field;

    return $json_array = array_diff_key( $json_array , array ($field => null) );

}

$test =  json_connect ('testing' );
json_close($test);
