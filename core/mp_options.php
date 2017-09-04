<?php defined('ABSPATH') or die('No direct script access.');

/**
 * class OPTIONS
 *
 *
 * @package cms mini POPS
 * @subpackage options
 * @version 1
 */




// On charge la classe
mp_cache_data('mp_options', new OPTIONS() );


/**
 * Récuperer un champs de configuration du site
 * @return string valeur du champ
 */
function get_the_blog( $field, $default = false ){

    $field = (string) $field;

    $field = strtolower(trim($field));

    switch ($field) {

        case 'copyright':
            $value = get_option('blog.'.$field);
            if( null === $value ) return $default;
            $value = parse_text($value);
            break;
        case 'home':
            $value = esc_url_raw( get_permalink() );
            break;
        case 'rss':
            $value = esc_url_raw( get_permalink('rss', 'feed') );
            break;
        case 'template_url':
            $value = esc_url_raw( MP_TEMPLATE_URL );
            break;
        case 'charset':
            $value = CHARSET;
            break;
        case 'version':
            $value = MP_VERSION;
            break;
        case 'language':
            $value = get_the_lang();
            break;
        case 'logo':
            $value = get_the_image('name=logo&orderby=type&max=5&order=desc', 'uri');
            break;   
        default:
            $value = get_option('blog.'.$field, $default);
            break;
    }
    
    return apply_filters( 'get_the_blog_'. $field, $value, $field );
}


// Les fonctions d'utilisations de la classe
function get_option( $option, $default = null, $domain = null ){

    return mp_cache_data('mp_options')->get($option, $default, $domain);
}

function add_option( $option, $value = null, $domain = null, $autoload = 'no' ){

    return mp_cache_data('mp_options')->add($option, $value, $domain, $autoload);
}

function update_option( $option, $value = null, $domain = null ){

    return mp_cache_data('mp_options')->update($option, $value, $domain);
}

function delete_option( $option, $domain = null ){

    return mp_cache_data('mp_options')->delete($option, $domain);
}

function form_option( $option, $domain = null ){
    
    $value = get_option($option, null, $domain);
    if( is_array($value) ) return;
    echo sanitize_allspecialschars($value);
}



// Fonction de nettoyage des options
function sanitize_option($option, $value){

    switch ($option) {

        case 'site_blog_title':
        case 'site_blog_subtitle':
        case 'site_blog_description':
        case 'site_blog_copyright':
            $value = esc_html($value);
            break;
        case 'site_plugins_active_plugins':
            $plugins = array();
            if( is_array($value) ){
                $value = array_flip(array_flip($value)); // On élimine les doublons
                foreach ($value as $plugin) {
                    if( !is_validate_file($plugin) // $plugin must validate as file
                        && file_exists(MP_PLUGIN_DIR .'/'. $plugin .'/'. $plugin .'.php') // $plugin must exist
                    ) $plugins[] = $plugin;
                }
            }
            $value = $plugins;
            break;
        case 'site_blog_keywords':
        case 'site_blog_robots':
            $value = remove_accent($value);
            $value = sanitize_words($value);
            $value = str_replace(' ', ',', $value);
            break;
        case 'site_blog_author':
            $value = sanitize_user($value);
            break;
        case 'site_blog_author_email':
            $value = sanitize_email($value);
            break;
        case 'site_blog_lang':
            $value = preg_replace('/[^a-zA-Z]/', '', $value);
            if( strlen($value)>3) $value = '';
            break;
        case 'site_blog_theme':
            $value = sanitize_file_name($value);
            break;
        case 'site_setting_timezone':
        case 'site_setting_date_format':
        case 'site_setting_time_format':
            $value = sanitize_allspecialschars($value);
            break;
        case 'site_setting_api_key':
        case 'site_setting_api_keysalt':
            $value = sanitize_allspecialschars($value);
            if( strlen($value) < 32) return '';
            break;
        case 'site_setting_urlrewrite':
            if( is_notin($value, array(true, false, 'disable', 'enable') ) )
                $value = true;
            break;  
        default:
            break;
    }
    return $value;
}



# ~
# Classe options
# ~
class options {

    # ~
    const VERSION = '1.0.0';
    # ~


    /**#@+
    * @access private
    * @var
    */
    private static $_options, $_db, $_autoload = array();
    private static $_flag = false, $_is_sqlite = true;


    function __construct(){

        /*
        * On charge la base de donnée
        * self::$_db = new sqlite( MP_PAGES_DIR. '/mp_'.substr( md5( __FILE__ ), 0, 8 ).'.db' );
        *
        * https://sqlite.org/inmemorydb.html
        * self::$_db = new sqlite(':memory:');
        */
        self::$_db = new sqlite(MP_SQLITE_DIR. '/mp_'.substr( md5( __FILE__ ), 0, 8 ).'.sqlite3');

        // On valide l'utilisation de sqlite        
        if( false === self::$_db)
            self::$_is_sqlite = false;

        // On créer la table si la base est vide et on charge les données autoload
        if( self::$_is_sqlite ){

            if( fileSize(MP_SQLITE_DIR. '/mp_'.substr( md5( __FILE__ ), 0, 8 ).'.sqlite3') == 0 )
                self::$_db->query("CREATE TABLE options( name TEXT PRIMARY KEY, value TEXT,domain TEXT, autoload TEXT )");

            // On charge les table autoload
            $_autoload = self::$_db->query("SELECT name,value,domain FROM options WHERE autoload='yes'");
            foreach ($_autoload as $v)
                self::$_autoload[$v['domain']][$v['name']] = $v['value'];
            unset($_autoload); 

        }
        
        // On charge la table option dans la variable static
        $options = yaml_parse_file(MP_CONFIG_DIR. '/config.yml', 0, null, apply_filters('mp_options_cache',CACHE) );
        self::$_options = !$options ? array():$options;

        // On ajoute un hook pour la sauvegarde du fichier
        add_action('shutdown', function (){ mp_cache_data('mp_options')->save(); });
    }

    /**
    * Sauvegarde de la table option
    * @access private
    */
    public function save(){

        //_echo( self::$_db->query("SELECT * FROM options") );

        if( self::$_flag ){
            if( ! yaml_emit_file(MP_CONFIG_DIR. '/config.yml', self::$_options) )
                _doing_it_wrong( __CLASS__, 'Error saving file configuration: config.yaml!');
            @chmod(MP_CONFIG_DIR. '/config.yml', 0644);
        }
        self::$_flag = null;
    }

    /**
    * Construire un noeud selon une chaine et un domaine
    * @access private
    * @param $node string  chaine de consrtuction ( 'domaine->noeuds->noeuds_fils')
    * @param $ins array  tableau à insérer
    */
    private function _node( $node, $domain ){

        // On récupère le chemin de la table du domain
        if( $domain === null )
            $node = 'site.'.$node;
        else
            $node = $domain.'.'.$node;

        // On créer le noeuds
        $nodes = array_map( 'trim', explode('.', $node));
        // On vérifie les noeuds
        foreach ($nodes as $node)
            if(!is_match($node, '/^[a-z0-9_]+$/i') ) return false;

        return $nodes;
    }


    /**
    * Insert dans un array
    * @access private
    * @param $arr array  tableau d'origine
    * @param $ins array  tableau à insérer
    */
    private function _array_insert( $arr ,$ins ) {

        if( is_array($arr) && is_array($ins) ){
            foreach( $ins as $k => $v ) {
                if ( isset($arr[$k]) && is_array($v) && is_array($arr[$k]) )
                    $arr[$k] = $this->_array_insert($arr[$k],$v);
                else $arr[$k] = $v;
            }
        }
        return($arr);
    }


    /**
    * Insérer une valeur selon un noeud dans une table
    * @access private
    * @param $array_keys array  Table des noeuds
    * @param $yaml_data  array  Table de retour
    * @param $value  string/array  Données à insérer dans la table
    */
    private function _SetValueByNodeToArray ( $array_keys, &$yaml_data, $value = '' ){

        if( !is_bool($value) && !is_null($value) && empty($value) ) return;
        $dump_node = array();
        $ref = &$dump_node;
        foreach ($array_keys as $k)  $ref = &$ref[$k];
        $ref = $value;
        $yaml_data = $this->_array_insert($yaml_data, $dump_node);
    }

    /**
    * Récupère une valeur selon un noeud dans une table
    * @access private
    * @param $array_keys array  Table des noeuds
    * @param $yaml_data  array  Table de retour
    */
    private function _GetValueByNodeFromArray ( $array_keys, $yaml_data ){
        $ref = &$yaml_data;
        foreach ($array_keys as $k)
            if(!isset($ref[$k]) ) return; else $ref = &$ref[$k];
        return $ref;
    }

    /**
    * Récupère une option
    * @access public
    * @param $option    champ à récupérer
    * @param $default   valeur retourné si pas trouvé
    * @param $domain    Domaine de recherche ( null: site, name: plugins actif et valid )
    * @param $type      fonction de validation - option
    */
    public function get( $option, $default = null, $domain = null, $type = null ){

        $option = (string) $option;

        // On récupère le noeuds passer en option
        if( !$node = $this->_node($option, $domain) ) return $default;

        // On reconstruit l'option
        $_option = implode('_', $node);

        // On court-circuit le résultat
        $pre = apply_filters( 'pre_option_' . $_option, false, $_option, $domain );
        if ( false !== $pre ) return $pre;


        // On récupère la variable selon le noeud
        if( self::$_is_sqlite && $domain != null ){

            $option = self::$_db->esc_sql($option);
            $domain = self::$_db->esc_sql($domain);

            if( !empty(self::$_autoload[$domain]) && array_key_exists( $option, self::$_autoload[$domain]) )
                $value = self::$_autoload[$domain][$option];
            else
                $value = self::$_db->query_single("SELECT value FROM options WHERE name='$option' AND domain='$domain'", false);

            if( false === $value )  
                $value = null;

        } else{

            $value = $this->_GetValueByNodeFromArray($node, self::$_options);
                        
        }

        if( is_serialized($value) )
            $value = unserialize($value);

        // On nettoie la valeur
        $value = sanitize_option($_option, $value);

        // Si null on retourne la valeur par défaut
        if(is_null($value) ) return $default;

        // On filtre le résultat si la fonction de validation existe
        $type = 'is_'.$type;
        if( function_exists($type) ){
            if( $type($value) )
                return apply_filters( 'option_' . $_option, $value, $_option, $domain );
            else return $default;
        }

        return apply_filters( 'option_' . $_option, $value, $_option, $domain );
    }

    /**
    * Ajoute une option
    * @access public
    * @param $option    champ à insérer
    * @param $value     valeur à insérer
    * @param $domain    Domaine de recherche ( null: site, name: plugins actif et valid )
    */
    public function add( $option, $value = null, $domain = null, $autoload = 'no' ){

        $option = (string) $option;

        // Si table enregistrer on ne peut plus rien inclure
        if( self::$_flag === null ) return false;

        // On récupère le noeuds passer en option
        if( !$node = $this->_node($option, $domain) ) return false;

        // On reconstruit l'option
        $_option = implode('_', $node);

        // On ajoute des filtres
        $value = apply_filters( 'pre_add_option_' . $_option, $value, $_option, $domain );
        $value = apply_filters( 'pre_add_option', $value, $_option, $domain );

        // On nettoie la valeur
        $value = sanitize_option($_option, $value);

        if ( $value !== null && $this->get($option, null, $domain) === null ){

            // On ajoute des actions
            do_action( 'add_option', $_option, $value );

            // On serialize si domain existe
            if( $domain != null && (is_array($value) || is_object($value) ) ){     
                
                $pre_value = serialize($value);

            } else {

                $pre_value = $value;
            }

            // On insère la nouvelle valeur
            if( self::$_is_sqlite && $domain != null ){

                $option    = self::$_db->esc_sql($option);
                $domain    = self::$_db->esc_sql($domain);
                $pre_value = self::$_db->esc_sql($pre_value);
                $autoload  = self::$_db->esc_sql($autoload);

                self::$_db->query("INSERT INTO options(name,value,domain,autoload) VALUES ('$option','$pre_value','$domain','$autoload')");

            } else {

                $this->_SetValueByNodeToArray($node, self::$_options, $pre_value);
                self::$_flag = true;
            }

            // On ajoute des actions
            do_action( 'added_option_'.$_option, $_option, $value, $domain );
            do_action( 'added_option', $_option, $value, $domain );

            return true;
        }
        else return false;
    }


    /**
    * Met à jour la valeur d'une option
    * @access public
    * @param $option    champ à modifier
    * @param $value     valeur à mettre à jour
    * @param $domain    Domaine de recherche ( null: site, name: plugins actif et valid )
    */
    public function update( $option, $value = null, $domain = null ){

        $option = (string) $option;

        // Si table enregistrer on ne peut plus rien inclure
        if( self::$_flag === null ) return false;

        // On récupère la valeur existente
        $old_value = $this->get($option, null, $domain);

        // On récupère le noeuds passer en option
        if( !$node = $this->_node($option, $domain) ) return false;

        // On reconstruit l'option
        $_option = implode('_', $node);

        // On ajoute des filtres
        $value = apply_filters( 'pre_update_option_' . $_option, $value, $old_value, $_option, $domain );
        $value = apply_filters( 'pre_update_option', $value, $_option, $old_value, $domain );

        // On nettoie la valeur
        $value = sanitize_option($_option, $value);

        // On serialize si value is a array
        if( is_array($value) || is_object($value) )   
            $pre_value = serialize($value);
        else
            $pre_value = $value;

        // On serialize si old_value is a array
        if ( is_array($old_value) || is_object($old_value) )
            $old_value = serialize($old_value);


        if ( $old_value !== $pre_value && $old_value !== null){

            // Si domaine pas nul on force l'utilisation de la value initiale
            if( $domain == null )
                $pre_value = $value;

            // On ajoute des actions
            do_action( 'update_option', $old_value, $value, $_option, $domain );

            if( self::$_is_sqlite && $domain != null ){

                $option = self::$_db->esc_sql($option);
                $domain = self::$_db->esc_sql($domain);
                $pre_value  = self::$_db->esc_sql($pre_value);

                // On met à jour le cache autoload
                if( !empty(self::$_autoload[$domain]) && array_key_exists( $option,self::$_autoload[$domain]) )
                    self::$_autoload[$domain][$option] = $pre_value;

                self::$_db->query("UPDATE options SET value = '$pre_value' WHERE name='$option' AND domain='$domain'");

            } else{
                // On met la valeur à null ( pour éviter que si $node est un tableau , on se retrouve avec l'ancienne valeur plus la nouvelle )
                $this->_SetValueByNodeToArray($node, self::$_options, null);
                // On met à jour la nouvelle valeur
                $this->_SetValueByNodeToArray($node, self::$_options, $pre_value);
                self::$_flag = true;
            }

            // On ajoute des actions
            do_action( 'updated_option_'.$_option, $old_value, $value, $_option, $domain );
            do_action( 'updated_option', $_option, $old_value, $value, $domain );

            return true;
        }
        else return false;
    }


    /**
    * Supprime une option
    * @access public
    * @param $option    champ à récupérer
    * @param $value     valeur à insérer
    * @param $domain    Domaine de recherche ( null: site, name: plugins actif et valid )
    */
    public function delete( $option, $domain = null ){

        $option = (string) $option;

        // Si table enregistrer on ne peut plus rien exclure
        if( self::$_flag === null ) return false;

        // On récupère le noeuds passer en option
        if( !$node = $this->_node($option, $domain) ) return false;
        // On reconstruit l'option
        $_option = implode('_', $node);

        // On court-circuit le résultat
        $pre = apply_filters( 'pre_delete_option_' . $_option, null, $_option, $domain, $domain );
        if ( null !== $pre ) return $pre;

        if( self::$_is_sqlite && $domain != null ){

            $option = self::$_db->esc_sql($option);
            $domain = self::$_db->esc_sql($domain);

            // On met à jour le cache autoload
            if( !empty(self::$_autoload[$domain]) && array_key_exists( $option,self::$_autoload[$domain]) )
                unset(self::$_autoload[$domain][$option]);

            $delete = self::$_db->query("DELETE from options where name = '$option' AND domain='$domain'");
        }
        else{

            $delete = update_option( $option , null, $domain );
        }

        // On ajoute des actions
        do_action( 'deleted_option_' . $_option, $_option, $domain, $delete, $domain );
        do_action( 'deleted_option', $_option, $domain, $delete, $domain );

        return $delete;
    }
}





/**
* Supprime un transient
*/
function delete_transient( $transient ){
    
    $option_timeout = '_transient_timeout_' . $transient;
    $option = '_transient_' . $transient;
    $result = delete_option( $option, 'mp_transient' );

    if ( $result )
        delete_option( $option_timeout, 'mp_transient' );

    return $result;
}




/**
* Récupère un transient
*/
function get_transient( $transient ) {

    $transient_option = '_transient_' . $transient;
    
                                 
    $transient_timeout = '_transient_timeout_' . $transient;
    $timeout = get_option( $transient_timeout, false, 'mp_transient' );
            
    if ( false !== $timeout && $timeout < time() ) {
        delete_option( $transient_option, 'mp_transient' );
        delete_option( $transient_timeout, 'mp_transient' );
        $value = false;
    }
        
    if ( ! isset( $value ) )
        $value = get_option( $transient_option, false, 'mp_transient' );

    return $value;
}



/**
* ajoute un transient
*/
function set_transient( $transient, $value, $expiration = 0 ) {

    $expiration = (int) $expiration;

    $transient_timeout = '_transient_timeout_' . $transient;
    $transient_option = '_transient_' . $transient;
    
    if ( null === get_option( $transient_option, null, 'mp_transient' ) ) {

        $autoload = 'yes';
        
        if ( $expiration ) {
             $autoload = 'no';
             add_option( $transient_timeout, time() + $expiration, 'mp_transient', 'no' );
        }
    
        $result = add_option( $transient_option, $value, 'mp_transient', $autoload );

    } else {

        $update = true;

        if ( $expiration ) {
            
            if ( null === get_option( $transient_timeout, null, 'mp_transient' ) ) {

                delete_option( $transient_option, 'mp_transient' );
                add_option( $transient_timeout, time() + $expiration, 'mp_transient', 'no' );
                $result = add_option( $transient_option, $value, 'mp_transient', 'no' );
                $update = false;

            } else {

                update_option( $transient_timeout, time() + $expiration, 'mp_transient' );
            }
        }
        
        if ( $update )
            $result = update_option( $transient_option, $value, 'mp_transient' );
    }
         
    return $result;
}


/**
* Systeme de cache transient
*/

function mp_transient_data( $transient , $function , $expiration = 60 , $params = array() ){

    $transient  = (string) $transient;
    $expiration = (int) $expiration;

    if( null === $function || !is_callable($function) ){
        delete_transient( $transient );
        return;
    }

    if ( false === ( $value = get_transient( $transient ) ) ) {
        set_transient( $transient, call_user_func_array( $function, $params ) , $expiration );
        $value = get_transient( $transient );
    }

    return $value; 
}



/**
* SQLITE
*/
class sqlite
{
    

    private $sqlite;

    /*
    * Constructeur
    */
    function __construct( $path = '' ) {

        if(!class_exists('SQLite3'))        
            return false;   

        $this->sqlite = new SQLite3( $path, SQLITE3_OPEN_READWRITE  | SQLITE3_OPEN_CREATE | SQLITE3_OPEN_SHAREDCACHE );
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
    * 
    * ex: "SELECT post_author, id FROM mytable"
    */
    public function query_single( $query, $entire_row = true ){

        $query        = (string) $query;
        $entire_row   = (bool) $entire_row;

        // If not SELECT query
        if( !preg_match('|\bSELECT\b|', $query ) )
            return false;

        return @$this->sqlite->querySingle($query, $entire_row );
    }

}
