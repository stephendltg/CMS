<?php defined('ABSPATH') or die('No direct script access.');

/**
 *
 *
 * @package CMS mini POPS
 * @subpackage SQLITE
 * @version 1
 */


/*
* https://sqlite.org/inmemorydb.html
*/
class sqlite
{
    

    private $sqlite;

    /*
    * Constructeur
    */
    function __construct( $path = ':memory:' ) {

        $this->sqlite = new SQLite3( $path, SQLITE3_OPEN_READWRITE  | SQLITE3_OPEN_CREATE | SQLITE3_OPEN_SHAREDCACHE, MP_SQLITE_ENCRYPT );
    }


    /*
    * destructeur
    */
    function __destruct() {

        $this->sqlite->close();
    }

    /*
    * Escape data
    */
    public function esc_sql( $data ) {
        if ( is_array( $data ) ) {
            foreach ( $data as $k => $v ) {
                if ( is_array( $v ) )
                    $data[$k] = $this->esc_sql( $v );
                else
                    $data[$k] = $this->sqlite->escapeString( $v );
            }
        } else {
            $data = $this->sqlite->escapeString( $data );
        }

        return $data;
    }

    /*
    * Error sqlite  output code or message
    */
    public function error( $mode = 'code' ){

        switch ($mode) {
            case 'code':
                return $this->sqlite->lastErrorCode();
                break;
            case 'msg':
                return $this->sqlite->lastErrorMsg();
                break;
            default:
                return array($this->sqlite->lastErrorCode() => $this->sqlite->lastErrorMsg() );
                break;
        }
    }


    /*
    * query
    *
    *
    * ex:
    *
    * DROP a table: 'DROP TABLE mytable'
    *
    * CREATE a table: "CREATE TABLE mytable( 
    *                       ID INTEGER PRIMARY KEY, 
    *                       post_author INTEGER NOT NULL,            
    *                       post_date TEXT,
    *                       post_content TEXT,
    *                       post_title TEXT,
    *                       guid TEXT            
    *                   )"
    *
    * READ: "SELECT ID, post_title, post_content, post_author, post_date, guid FROM mytable"
    *
    * INSERT: "INSERT INTO mytable(ID, post_title, post_content, post_author, post_date, guid) VALUES ('$number', '$title', '$content', '$author', '$date', '$url')"
    *
    * UPDATE: "UPDATE mytable SET post_content = '$changed' WHERE (id=1)"
    *
    * DELETE: "DELETE from mytable where ID = 10"
    *
    * STATISTIC: "SELECT * FROM sqlite_master"
    *
    */
    public function query( $query, $output = 'ARRAY' ){

        $query = (string) $query;

        /*
        * MODE
        * ====
        *
        * CREATE TABLE, SELECT, INSERT INTO, UPDATE, DELETE, DROP TABLE
        *
        */


        /*
        * CREATE TABLE
        *
        *
        * TYPE
        * ====
        *
        * TEXT: CHARACTER(20) VARCHAR(255) VARYING CHARACTER(255) NCHAR(55) NATIVE CHARACTER(70) NVARCHAR(100) TEXT CLOB
        * NUMERIC: NUMERIC DECIMAL(10,5) BOOLEAN DATE DATETIME
        * INTEGER: INT INTEGER TINYINT SMALLINT MEDIUMINT BIGINT UNSIGNED BIG INT INT2 INT8
        * REAL: REAL DOUBLE DOUBLE PRECISION FLOAT
        * BLOB: BLOB
        *
        *
        *
        * CONSTRAINTS
        * ===========
        * 
        * PRIMARY KEY, CHECK, NOT NULL, UNIQUE, FOREIGN KEY
        *
        */

        // verify if query is good
        if( !$this->sqlite->prepare($query) )   
            return;

        // If not SELECT query
        if( !preg_match('|\bSELECT\b|', $query ) )
            return $this->sqlite->exec($query);


        $results = $this->sqlite->query($query);

        // Mode output SELECT
        if( strtoupper($output) === 'OBJECT' )
            return $results->fetchArray(1);

        // Create array to keep all results
        $data= array();

        //F etch Associated Array (1 for SQLITE3_ASSOC)
        while ($res= $results->fetchArray(1))
            array_push($data, $res);

        return $data;
    }

    /*
    * Query single
    * 
    * ex: "SELECT post_author, id FROM mytable"
    */
    public function query_single( $query, $entire_row = true ){

        $query        = (string) $query;
        $entire_row   = (bool) $entire_row;

        // If not SELECT query
        if( !preg_match('|\bSELECT\b|', $query ) )
            return false;

        return $this->sqlite->querySingle($query, $entire_row );
    }
}


/**
 * Enregistrer, récupérer ou supprimer une donnée sqlite.
 * Get:   Mettre juste la clé recherche en parametre
 * Set:   Mettre un second parametres avec la valeur de la clé
 * Delete: Mettre la valeur : null en second paramètres pour supprimer la clé
 *
 * @param (string) $key clé d'identification. 
 *
 * @return (mixed) La valeur enrégistrer ou null.
 */
function mp_cache_sqlite( $key ) {

    static $sqlite = null;
    static $autoload = array();

    $sqlite_name = substr( md5( __FILE__ ), 0, 8 ) .'.sqlite3';
    $table = 'mp_cache';

    // On créer un instance sqlite
    if( $sqlite === null && true === $sqlite = class_exists('SQLite3') ){

        try{

            $sqlite = new sqlite( MP_SQLITE_DIR. '/mp_' . $sqlite_name );

            if( fileSize( MP_SQLITE_DIR. '/mp_' . $sqlite_name ) == 0 )
                $sqlite->query("CREATE TABLE '$table'( name TEXT PRIMARY KEY, value TEXT, autoload TEXT )");

            // On charge les tables autoload
            $_autoload = $sqlite->query("SELECT name,value FROM '$table' WHERE autoload='yes'");

            foreach ($_autoload as $v)
                $autoload[$v['name']] = $v['value'];

            unset($_autoload);       

        } catch (Exception $e){ 

            $sqlite = false; 
        }   
    }

    /* On extrait les arguments */
    $func_get_args = func_get_args();

    // On utilise le cache php si sqlite desactivé
    if( false === $sqlite )
        return call_user_func_array( 'mp_cache_php', $func_get_args );

    /* Condition pour purger le cache */
    if( is_null($key) )
        return $sqlite->query("SELECT * FROM '$table'");


    /* Valide $key */
    if( strlen($key) == 0 )  return;

    /* Condition pour purger le cache ou afficher la table */
    if( is_null($key) ){
        return $sqlite->query("DROP TABLE '$table'");

        return $sqlite->query("SELECT * FROM '$table'");
    }


    /* update ou insert */
    if ( array_key_exists( 1, $func_get_args ) ) {

        if ( null === $func_get_args[1] ){

            /* On supprime le cache */
            $sqlite->query("DELETE from '$table' where name = '$key'");

            /* On met à jour l'autoload */
            if( isset($autoload[$key]) )    unset($autoload[$key]);

            return;

        } elseif( is_serialized($func_get_args[1]) ){

            return null;

        } else {

            /* time */
            $time = false;

            /* Arguments pour clé autochargé ou expiration*/
            if( array_key_exists( 2, $func_get_args )  ){

                if( is_integer($func_get_args[2]) && $func_get_args[2] ){

                    $time = time() + $func_get_args[2];
                    $func_get_args[2] = 'yes';

                } else {
                    
                    $func_get_args[2] = $sqlite->esc_sql( strtolower($func_get_args[2]) );
                }

            } else {

                $func_get_args[2] = 'no';
            }

            /* on prépare la valeur */
            $value = $sqlite->esc_sql( serialize( array( 'time'=>$time, 'value'=>$func_get_args[1] ) ) );

            /* On stock la valeur */
            if( null === $sqlite->query_single("SELECT value FROM '$table' WHERE name='$key'", false) ){

                /* Insert */
                $sqlite->query("INSERT OR REPLACE INTO '$table'(name,value,autoload) VALUES ('$key','$value','$func_get_args[2]')");

                /* On met à jour l'autoload */
                if( $func_get_args[2] === 'yes' )      $autoload[$key] = $value;

            } else {

                /* Update */
                $sqlite->query("UPDATE '$table' SET value = '$value' WHERE name='$key'");

                /* On met à jour l'autoload */
                if( isset($autoload[$key]) )    $autoload[$key] = $value;

            }

            
        }
    }


    /* On retourne la valeur */
    if( isset($autoload[$key]) ){

        $cache = unserialize($autoload[$key]);

        if( false !== $cache['time'] && microtime(true) > $cache['time'] ){

            $sqlite->query("DELETE from '$table' where name = '$key'");
            unset($autoload[$key]);

        } else {
            return $cache['value'];
        }

    } elseif( $value = $sqlite->query_single("SELECT value FROM '$table' WHERE name='$key'", false) ){
        
        return unserialize($value)['value'];
    }

    return;
}