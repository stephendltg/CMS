<?php


// Set default timezone
date_default_timezone_set('Europe/Paris');
//Set sqlite database path
define( 'DATABASE_PATH', __DIR__.DIRECTORY_SEPARATOR.'base.db' );

//Get all GET/POST var
$_ = array_map('htmlspecialchars',array_merge($_POST,$_GET));


function _echo( $array ){
	echo '<ul>';
	if(!is_array($array) )    return;
	foreach ($array as $k=>$v){
		if( is_array($v) ) _echo($v);
		else echo '<li>'. $k . ' : ' . $v .'</li>';
	}
	echo '</ul>';
}



// test
if(isset($_['action'])){

	switch($_['action']){

		case 'stats':

			$test = new sqlite( __DIR__.DIRECTORY_SEPARATOR.'test.db' );

			//if(fileSize($test->sqlite_path)==0)
			$test->query( "CREATE TABLE mangers( 
								ID INTEGER PRIMARY KEY, 
								post_author INTEGER NOT NULL,
								post_date TEXT,
								post_content TEXT,
								post_title TEXT,
								guid TEXT
							)"
			);

			//$test->query("DROP TABLE mangers");

			$test->query("INSERT INTO manger(ID, post_title, post_content, post_author, post_date, guid) VALUES (230, 'title', 'content', 'author', 'date', 'url')");

			$test->query("UPDATE manger SET post_content = 'mon message', guid ='test' WHERE (id=23)");

			_echo( $test->query("SELECT * FROM manger") );

			_echo( $test->query("SELECT * FROM sqlite_master") );

			var_dump($test->query_single("SELECT post_author, id FROM manger") );

			var_dump($test->error( 'message' ) );

			break;
	}

	exit();
}






/**
* SQLITE
*/
class sqlite
{
	

	private $sqlite;
	//private $sqlite_path = __DIR__.DIRECTORY_SEPARATOR.'test.db';
	private $chmod = 0644;
	private $nb_request = 0;


	/*
	* Constructeur
	*/
	function __construct( $path = '', $chmod = 0644 ) {

		if(!class_exists('SQLite3'))		
			return false;	

  		$this->sqlite = new SQLite3($path /*, $chmod*/);
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
	*						ID INTEGER PRIMARY KEY, 
	*						post_author INTEGER NOT NULL,            
	*						post_date TEXT,
	*						post_content TEXT,
	*						post_title TEXT,
	*						guid TEXT            
	*			   		)"
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
		if( !@$this->sqlite->prepare($query) )   
			return false;

		// If not SELECT query
		if( !preg_match('|\bSELECT\b|', $query ) )
			return @$this->sqlite->exec($query);


		$results = @$this->sqlite->query($query);

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
	*/
	public function query_single( $query, $entire_row = true ){

		$query 		  = (string) $query;
		$entire_row   = (bool) $entire_row;

		// If not SELECT query
		if( !preg_match('|\bSELECT\b|', $query ) )
			return false;

		return @$this->sqlite->querySingle($query, $entire_row );
	}

}
