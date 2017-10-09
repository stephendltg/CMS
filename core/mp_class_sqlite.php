<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Class SQLITE
 *
 *
 * @package CMS mini POPS
 * @subpackage SQLITE
 * @version 1
 */

class sqlite
{
    

    private $sqlite;

    /*
    * Constructeur
    */
    function __construct( $path = ':memory:' ) {

        if(!class_exists('SQLite3'))        
            return false;   

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