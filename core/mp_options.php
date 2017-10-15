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
    if(is_array($value) || is_object($value) )
        $value = serialize($value);
    echo sanitize_allspecialschars($value);
}

function all_option(){
    
    return mp_cache_data('mp_options')->all();
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
    private static $_yaml_config, $_autoload = array();
    private static $_sqlite = false;
    private static $_flag = false;

    function __construct(){

        /*
        * On charge la base de donnée
        * self::$_sqlite = new sqlite( MP_PAGES_DIR. '/mp_'.substr( md5( __FILE__ ), 0, 8 ).'.db' );
        *
        * https://sqlite.org/inmemorydb.html
        * self::$_sqlite = new sqlite(':memory:');
        */

        if( true === self::$_sqlite = class_exists('SQLite3') ){

            try{

                self::$_sqlite = new sqlite( MP_SQLITE_DIR. '/mp_options.sqlite3' );

                 // On valide l'utilisation de sqlite 
                mp_cache_data('is_sqlite_enable', true);

                if( fileSize(MP_SQLITE_DIR. '/mp_options.sqlite3') == 0 )
                    self::$_sqlite->query("CREATE TABLE options( name TEXT PRIMARY KEY, value TEXT,domain TEXT, autoload TEXT )");

                // On charge les tables autoload
                $_autoload = self::$_sqlite->query("SELECT name,value,domain FROM options WHERE autoload='yes'");

                foreach ($_autoload as $v)
                    self::$_autoload[$v['domain']][$v['name']] = $v['value'];
                unset($_autoload); 


                /* On surcharge avec le fichier config si présent */
/***************
                if( isset( self::$_autoload['site'] ) && file_exists( MP_CONFIG_DIR. '/config.yml' ) ) {
                    
                    $yaml_config = yaml_parse_file( MP_CONFIG_DIR. '/config.yml', 0, null );
                    $yaml_config = !$yaml_config ? array() : $yaml_config;

                    if( isset( $yaml_config['site'] ) ){

                        foreach ($yaml_config['site'] as $k => $v)    $yaml_config['site'][$k] = serialize($v);
                        self::$_autoload['site'] = array_merge(self::$_autoload['site'], $yaml_config['site']);
                    }

                    unset($yaml_config);
                }
 ****************/               

            } catch (Exception $e){  self::$_sqlite = false;  }   

        }

        /* On utilise le driver yaml */
        if( false === self::$_sqlite ){

            // On charge la table option dans la variable static
            $yaml_config = yaml_parse_file( MP_CONFIG_DIR. '/config.yml', 0, null );
            self::$_yaml_config = !$yaml_config ? array() : $yaml_config;

            // On ajoute un hook pour la sauvegarde du fichier
            add_action('shutdown', function (){ mp_cache_data('mp_options')->save(); });

        }

    }

    /**
    * Sauvegarde de la table option
    * @access private
    */
    public function save(){

        if( self::$_flag ){

            if( ! yaml_emit_file(MP_CONFIG_DIR. '/config.yml', self::$_yaml_config) )
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
        if( false !== self::$_sqlite ){

            // On supprime le domaine devant le noeud
            $domain = array_shift($node);

            // Nom de l'option racine
            $node_name = $node[0];

            $node_name = self::$_sqlite->esc_sql($node_name);
            $domain    = self::$_sqlite->esc_sql($domain);

            // On lit la valeur de l'option soit la table autolaod, soit sqlite
            if( !empty(self::$_autoload[$domain]) && array_key_exists( $node_name, self::$_autoload[$domain]) )
                $value = self::$_autoload[$domain][$node_name];
            else
                $value = self::$_sqlite->query_single("SELECT value FROM options WHERE name='$node_name' AND domain='$domain'", false);

            // On unserialize la valeur si besoin
            if( is_serialized($value) )
                $value = unserialize($value);

            // On récupère la valeur du noeud si celui ci est complexe
            if( count($node) > 1 )
                $value = is_array($value) ? $this->_GetValueByNodeFromArray($node, array( $node_name => $value ) ) : null;

        } else {

            /* On utilise le driver yaml */
            if( $domain !== null )
                $value = mp_cache_php( $_option );
            else
                $value = $this->_GetValueByNodeFromArray($node, self::$_yaml_config);
        }

        // On nettoie la valeur
        $value = sanitize_option($_option, $value);

        // Si null on retourne la valeur par défaut
        if( is_null($value) ) return $default;

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

        // Si table enregistrer on ne peut plus rien inclure (fichier yaml uniquement)
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

        if( is_serialized($value) )     return false;

        // On récupère la valeur de l'option
        if( false !== self::$_sqlite ){

            // On met de côté le domain et on le supprime du noeud
            $domain = array_shift($node);

            // Nom de l'option racine
            $node_name = $node[0];

            // On lit les données du premier noeud
            $get_value = $node_value = $this->get($node_name, null, $domain);

            // Si plusieurs noeud on récupère la valeur du noeud
            if( count($node) > 1 )
                $get_value = $this->_GetValueByNodeFromArray($node, array($node_name=>$node_value) );

        } else {

            /* On utilise le driver yaml */

            if( $domain !== null )
                $get_value = mp_cache_php( $_option );
            else
                $get_value = $this->get($option, null, $domain);
        }


        // Si la valeur de l'option est null on peut créer l'option
        if( $value != null && $get_value == null ){

            // On ajoute des actions
            do_action( 'add_option', $_option, $value );


            if( false !== self::$_sqlite ){

                // On construit la variable qui va recevoir les données
                $node_value = array( $node_name => $node_value );

                // On affecte les données
                $this->_SetValueByNodeToArray($node, $node_value , $value);

                // On prepare les données pour sqlite
                $node_name  = self::$_sqlite->esc_sql($node_name);
                $domain     = self::$_sqlite->esc_sql($domain);
                $autoload   = self::$_sqlite->esc_sql($autoload);
                $node_value = $node_value[$node_name];

                if( is_array($node_value) || is_object($node_value) )
                    $node_value = serialize($node_value);

                $node_value = self::$_sqlite->esc_sql($node_value);

                // On met à jour le cache autoload
                if( !empty(self::$_autoload[$domain]) && array_key_exists( $node_name,self::$_autoload[$domain]) ){
                    
                    self::$_autoload[$domain][$node_name] = $node_value;
                    $autoload = 'yes';
                }
                
                self::$_sqlite->query("INSERT OR REPLACE INTO options(name,value,domain,autoload) VALUES ('$node_name','$node_value','$domain','$autoload')");

            } else {

                /* On utilise le driver yaml */
                if( $domain !== null ){

                    mp_cache_php( $_option, $value );

                } else {

                    $this->_SetValueByNodeToArray($node, self::$_yaml_config, $value);
                    self::$_flag = true;
                }

            }

            // On ajoute des actions
            do_action( 'added_option_'.$_option, $_option, $value, $domain );
            do_action( 'added_option', $_option, $value, $domain );

            return true;

        } else return false;
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

        // Si table enregistrer on ne peut plus rien inclure (fichier yaml uniquement)
        if( self::$_flag === null ) return false;

        // On récupère le noeuds passer en option
        if( !$node = $this->_node($option, $domain) ) return false;

        if( is_serialized($value) )     return false;

        // On récupère la valeur de l'option
        if( false !== self::$_sqlite ) {

            // On met de côté le domain et on le supprime du noeud
            $domain = array_shift($node);

            // Nom de l'option racine
            $node_name = $node[0];

            // On lit les données du premier noeud
            $old_value = $node_value = $this->get($node_name, null, $domain);

            // Si plusieurs noeud on récupère la valeur du noeud
            if( count($node) > 1 )
                $old_value = $this->_GetValueByNodeFromArray($node, array($node_name=>$node_value) );

        } else {

            /* On utilise le driver yaml */
            if( $domain !== null )
                $old_value = mp_cache_php( implode('_', $node) );
            else
                $old_value = $this->get($option, null, $domain);
        }

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


        // On met à jour si les conditions sont correct
        if ( $old_value !== $pre_value && $old_value != null ){

            // On ajoute des actions
            do_action( 'update_option', $old_value, $value, $_option, $domain );

            if( false !== self::$_sqlite ){

                // On construit la variable qui va recevoir les données
                $node_value = array( $node_name => $node_value );

                // On affecte les données
                $this->_SetValueByNodeToArray($node, $node_value , $value);

                $node_name  = self::$_sqlite->esc_sql($node_name);
                $domain     = self::$_sqlite->esc_sql($domain);
                $node_value = $node_value[$node_name];

                if( is_array($node_value) || is_object($node_value) )
                    $node_value = serialize($node_value);

                $node_value = self::$_sqlite->esc_sql($node_value);

                // On met à jour le cache autoload
                if( !empty(self::$_autoload[$domain]) && array_key_exists( $node_name,self::$_autoload[$domain]) )
                    self::$_autoload[$domain][$node_name] = $node_value;

                self::$_sqlite->query("UPDATE options SET value = '$node_value' WHERE name='$node_name' AND domain='$domain'");

            } else {

                /* On utilise le driver yaml */
                if( $domain !== null ){

                    mp_cache_php( $_option, $value );
                
                } else {

                    // On met la valeur à null ( pour éviter que si $node est un tableau , on se retrouve avec l'ancienne valeur plus la nouvelle )
                    $this->_SetValueByNodeToArray($node, self::$_yaml_config, null);

                    // On met à jour la nouvelle valeur
                    $this->_SetValueByNodeToArray($node, self::$_yaml_config, $value);
                    self::$_flag = true;
                }
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

        // Si table enregistrer on ne peut plus rien exclure (fichier yaml uniquement)
        if( self::$_flag === null ) return false;

        // On récupère le noeuds passer en option
        if( !$node = $this->_node($option, $domain) ) return false;
        // On reconstruit l'option
        $_option = implode('_', $node);

        // On court-circuit le résultat
        $pre = apply_filters( 'pre_delete_option_' . $_option, null, $_option, $domain, $domain );
        if ( null !== $pre ) return $pre;

        if( false !== self::$_sqlite ){

            // On met de côté le domain et on le supprime du noeud
            $domain = array_shift($node);

            if( count($node) > 1 ){

                $delete = $this->update($option, null, $domain);

            } else {

                $node_name = $node[0];
                $node_name = self::$_sqlite->esc_sql($node_name);
                $domain    = self::$_sqlite->esc_sql($domain);

                // On met à jour le cache autoload
                if( !empty(self::$_autoload[$domain]) && array_key_exists( $node_name,self::$_autoload[$domain]) )
                    unset(self::$_autoload[$domain][$node_name]);

                $delete = self::$_sqlite->query("DELETE from options where name = '$node_name' AND domain='$domain'");
            }
        
        } else {

            /* On utilise le driver yaml */

            if( $domain !== null )
                $delete = ( null === mp_cache_php( $_option, null ) ) ? true : false;
            else
                $delete = $this->update($option, null, $domain);
        }

        // On ajoute des actions
        do_action( 'deleted_option_' . $_option, $_option, $domain, $delete, $domain );
        do_action( 'deleted_option', $_option, $domain, $delete, $domain );

        return $delete;
    }


    /**
    * retourne toutes les options
    * @access public
    * @return array    tableau de toutes les options
    */
    public function all(){

        if( false !== self::$_sqlite )
            return self::$_sqlite->query("SELECT * FROM options");
        else
            return self::$_yaml_config;

    }


}





/**
* Supprime un transient
*/
function delete_transient( $transient ){
    
    $option_timeout = '_transient_timeout_' . $transient;
    $option = '_transient_' . $transient;

    // Cache php optimiser pour transient si sqlite est désactivé
    if( null === mp_cache_data('is_sqlite_enable') )
        return null === mp_cache_php($option, null) ? false : true;

    $result = delete_option( $option, 'mp_transient' );

    if ( $result )
        delete_option( $option_timeout, 'mp_transient' );

    return $result;
}




/**
* Récupère un transient
*/
function get_transient( $transient ) {

    $transient  = (string) $transient;

    $transient_option = '_transient_' . $transient;

    // Cache php optimiser pour transient si sqlite est désactivé
    if( null === mp_cache_data('is_sqlite_enable') )
        return null === mp_cache_php($transient_option) ? false : mp_cache_php($transient_option);
                                 
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

    $transient  = (string) $transient;
    $expiration = (int) $expiration;

    $transient_timeout = '_transient_timeout_' . $transient;
    $transient_option = '_transient_' . $transient;

    // Cache php optimiser pour transient si sqlite est désactivé
    if( null === mp_cache_data('is_sqlite_enable') ){

        if( null === $transient_cache = mp_cache_php($transient_option) )
            $transient_cache = mp_cache_php($transient_option, $value, $expiration );

        return ( null === $transient_cache ) ? false : true;
    }

    
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