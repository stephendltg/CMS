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

    if ( ! file_exists( $db . '/' . $table_name . '.table.json' ) &&
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

    if ( $json_data['json_object'] = json_loadfile ( $db . '/' . $table_name . '.table.json' ) ) {
        $json_data['json_filename'] = $db . '/' . $table_name . '.table.json';
        return $json_data;
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

    // On sauvegarde uniquement s'il y a une des modifications sur la table
    if ( isset ( $json_data['update'] ) && $json_data['update'] === true ) {
        unset ( $json_data['update'] );
        if ( file_put_contents( $json_data['json_filename'] , json_encode($json_data['json_object']) , LOCK_EX) === false )
            return false;
    }

    unset ( $json_data['json_filename'] );
    unset ( $json_data['json_object'] );
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

    return array(
        'table_name'        => basename(  $json_data['json_filename'] ),
        'table_size'        => filesize(  $json_data['json_filename'] ),
        'table_last_change' => filemtime( $json_data['json_filename'] ),
        'table_last_access' => fileatime( $json_data['json_filename'] ),
        'table_fields'      => $json_data['json_object']['fields'],
        'records_count'     => count( $json_data['json_object'] )-2
    );
}


/***********************************************/
/*                     FIELDS                  */
/***********************************************/

/**
* Ajouté un champ à une table
*
*
* @param  string    $name Nom du champs
* @param  string    $json_data Donnée d'une table json
* @return boolean
*/
function add_field( $name , &$json_data) {

    // On redefinit les variables
    $name = (string) $name;

    if ( in_array( $name , $json_data['json_object']['fields'] ) === true || $name == '' )
        return false;

    $json_data['json_object']['fields'][]= esc_json( $name );
    $json_data['update']= true;
    return true;
}


/**
* Supprimer un champ à une table
*
*
* @param  string    $name Nom du champs
* @param  string    $json_data Donnée d'une table json
* @return boolean
*/
function delete_field( $name , &$json_data ) {

    // On redefinit les variables
    $name = (string) $name;

    if ( in_array( $name , $json_data['json_object']['fields'] ) === true ) {

        $key = array_search( $name , $json_data['json_object']['fields'] );
        unset ( $json_data['json_object']['fields'][ $key ] ); // On supprimer le champs
        $json_data['json_object']['fields'] = array_values ( $json_data['json_object']['fields'] ); // On reaffecte les index du tableau
        $json_data['update']= true;
        return true;
    }
    return false;
}

/**
* Modifier un champ à une table
*
*
* @param  string    $oldname Nom de l'ancien champs
* @param  string    $name Nom du champs
* @param  string    $json_data Donnée d'une table json
* @return boolean
*/
function update_field( $oldname , $name , &$json_data ) {

    // On redefinit les variables
    $oldname = (string) $oldname;
    $name    = (string) $name;

    if ( in_array( $oldname , $json_data['json_object']['fields'] ) === true ) {

        $key = array_search( $oldname , $json_data['json_object']['fields'] );
        $json_data['json_object']['fields'][$key] = $name;
        $json_data['update']= true;
        return true;
    }
    return false;
}


/**
* Vérifier l'existence d'un champ
*
*
* @param  string    $fields tableau de champs à ajouter
* @return boolean
*/
function exists_field( $name , &$json_data ) {

    // On redefinit les variables
    $name    = (string) $name;

    if ( in_array( $name , $json_data['json_object']['fields'] ) === true )
        return true;

    return false;
}


/***********************************************/
/*                     RECORD                  */
/***********************************************/

/**
* Inserer un enregistrement
*
*
* @param  string    $name Nom du champs
* @param  string    $json_data Donnée d'une table json
* @return boolean
*/
function insert( array $fields = null , &$json_data ) {

    if ( count($fields) > 0 ) {
        foreach ($fields as $field => $value) {
            if ( ! exists_field( $field , $json_data ) )
                return false;
            $fields[$field] = esc_json ( $value );
        }
        $fields['id'] = $json_data['json_object']['autoincremente']++;
        $json_data['json_object'][ $fields['id'] ] = $fields;
        $json_data['update']= true;
        return true;
    }
    return false;
}


/**
* Selectionner un enregistrement
*
*
* @param  string    $name Nom du champs
* @param  string    $json_data Donnée d'une table json
* @return boolean
*/
function select( &$json_data, $query = null,  $row_count = 'all', $offset = null, array $fields = null, $order_by = 'id', $order = 'ASC' ) {

    // On redefinit les variables
    $query    = ($query === null)  ? null : (string) $query;
    $offset   = ($offset === null) ? null : (int) $offset;
    $order_by = (string) $order_by;
    $order    = (string) $order;

    // Création des variables
    $records    = array ();
    $one_record = false;

    // Filtre des données json de la requête
    if ($query !== null) {
        $query      = parse_ini_string ( $query );
        $n_records  = count( $json_data['json_object'] ) - 2;
        for( $i = 0 ; $i < $n_records ; $i++ ) {
            $tmp = array_intersect_assoc ( $query , $json_data['json_object'][$i] );
            if ( $tmp != null  ) $records[] = $tmp;
        }
        unset($tmp);
    } else {
        $n_records = count( $json_data['json_object'] ) - 2;
        for( $i = 0 ; $i < $n_records ; $i++ ) {
            $records[$i] = $json_data['json_object'][$i];
        }
    }


    // Filtre pour une réponse unique
    if ( $row_count == null ) {
        if ( isset( $records[0] ) ) {
            $records    = $records[0];
            $one_record = true;
        }
    }


    // If array of fields is exits then get records with this fields only
    if ( count($fields) > 0 ) {

        if ( count($_records) > 0 ) {

            $count = 0;
            foreach ($_records as $key => $record) {

                foreach ($fields as $field) {
                    $record_array[$count][$field] = (string) $record->$field;
                }

                $record_array[$count]['id'] = (int) $record->id;

                if ($order_by == 'id') {
                    $record_array[$count]['sort'] = (int) $record->$order_by;
                } else {
                    $record_array[$count]['sort'] = (string) $record->$order_by;
                }

                $count++;

            }

            // Sort records
            $records = Table::subvalSort($record_array, 'sort', $order);

            // Slice records array
            if ($offset === null && is_int($row_count)) {
                $records = array_slice($records, -$row_count, $row_count);
            } elseif ($offset !== null && is_int($row_count)) {
                $records = array_slice($records, $offset, $row_count);
            }

        }

    } else {

        // Convert from XML object to array

        if (! $one_record) {
            $count = 0;
            foreach ($_records as $xml_objects) {

                $vars = get_object_vars($xml_objects);

                foreach ($vars as $key => $value) {
                    $records[$count][$key] = (string) $value;

                    if ($order_by == 'id') {
                        $records[$count]['sort'] = (int) $vars['id'];
                    } else {
                        $records[$count]['sort'] = (string) $vars[$order_by];
                    }
                }

                $count++;
            }

            // Sort records
            $records = Table::subvalSort($records, 'sort', $order);

            // Slice records array
            if ($offset === null && is_int($row_count)) {
                $records = array_slice($records, -$row_count, $row_count);
            } elseif ($offset !== null && is_int($row_count)) {
                $records = array_slice($records, $offset, $row_count);
            }

        } else {

            // Single record
            $vars = get_object_vars($_records[0]);
            foreach ($vars as $key => $value) {
                $records[$key] = (string) $value;
            }
        }
    }

    // Return records
    return $records;
}

db_create ( 'test');

//table1
//T_Create( 'testing', array ( 'looser', 'encore', 'manger', 'hache', 'vert'), ABSPATH.'test' ) ;
// Table 2
//T_Create( 'esteban', array ( 'fabienne', 'manger'), ABSPATH.'test' ) ;

//T_Drop( 'testing' , ABSPATH.'test' );

$mabase = json_connect( 'testing' , ABSPATH.'test' );

//update_field('gagos','merde',$mabase);
//add_field('gagos' , $mabase );
//add_field('manger' , $mabase );
//add_field('jouer' , $mabase );
//delete_field('jouer' , $mabase);

//var_dump (json_info($mabase) );
//insert ( array ('looser'=>'mangeoirà conchon', 'encore'=>'stephen'), $mabase );
//insert ( array ('merde'=>'login', 'manger'=>'esteban'), $mabase );

select ( $mabase , 'looser=re' , 'all' );

json_close( $mabase );

