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
function sanitize_option( $option, $value ){

    switch ($option) {

        case 'blog_title':
        case 'blog_subtitle':
        case 'blog_description':
        case 'blog_copyright':
            $value = esc_html($value);
            break;
        case 'plugins_active_plugins':
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
        case 'blog_keywords':
        case 'blog_robots':
            $value = remove_accent($value);
            $value = sanitize_words($value);
            $value = str_replace(' ', ',', $value);
            break;
        case 'blog_author':
            $value = sanitize_user($value);
            break;
        case 'blog_author_email':
            $value = sanitize_email($value);
            break;
        case 'blog_lang':
            $value = preg_replace('/[^a-zA-Z]/', '', $value);
            if( strlen($value)>3) $value = '';
            break;
        case 'blog_theme':
            $value = sanitize_file_name($value);
            break;
        case 'setting_timezone':
        case 'setting_date_format':
        case 'setting_time_format':
            $value = sanitize_allspecialschars($value);
            break;
        case 'setting_api_key':
        case 'setting_api_keysalt':
            $value = sanitize_allspecialschars($value);
            if( strlen($value) < 32) return '';
            break;
        case 'setting_urlrewrite':
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
    private static $_yaml_config = array(), $_autoload = array();
    private static $_flag = false;
    const CONFIG = MP_CONFIG_DIR. '/config.yml';

    function __construct(){

        /* fichier cache php */
        $cache = MP_CACHE_DIR . '/'.md5(self::CONFIG).'.php';

        if( file_exists(self::CONFIG) ){

            if( !file_exists( $cache ) || filemtime(self::CONFIG) > filemtime( $cache ) ){
                
                $yaml_config = yaml_parse_file( self::CONFIG, 0, null );
                self::$_yaml_config = !$yaml_config ? array() : $yaml_config;

            } else {

                self::$_yaml_config = mp_cache_php( self::CONFIG );
            }
        }

        // On ajoute un hook pour la sauvegarde du fichier
        add_action('shutdown', function (){ mp_cache_data('mp_options')->save(); });
    }

    /**
    * Sauvegarde de la table option
    * @access private
    */
    public function save(){

        if( self::$_flag ){

            if( ! yaml_emit_file( self::CONFIG, self::$_yaml_config ) )
                _doing_it_wrong( __CLASS__, 'Error saving file configuration: config.yaml!');

            @chmod( self::CONFIG, 0644 );

            /* Creation du cache */
            mp_cache_php( self::CONFIG, self::$_yaml_config );

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

        // On récupère le noeud passer en option
        if( !$node = $this->_node($option, $domain) ) return $default;

        // On reconstruit l'option
        $_option = implode('_', $node);

        // On court-circuit le résultat
        $pre = apply_filters( 'pre_option_' . $_option, false, $_option, $domain );
        if ( false !== $pre ) return $pre;

        /* On lit la valeur */
        if( $domain !== null ){

            $value = mp_cache_sqlite( $domain.'_'.$_option );

        } else {

            $value = $this->_GetValueByNodeFromArray( $node, self::$_yaml_config );
            
            // On nettoie la valeur
            $value = sanitize_option($_option, $value);
        }

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

        // On lit la valeur de l'option
        if( $domain !== null ){

            $get_value = mp_cache_sqlite( $_option );

        } else {

            $get_value = $this->get($option, null, $domain);
        }
        

        // Si la valeur de l'option est null on peut créer l'option
        if( $value != null && $get_value == null ){

            // On ajoute des actions
            do_action( 'add_option', $_option, $value );

            if( $domain !== null ){

                mp_cache_sqlite( $domain.'_'.$_option, $value, $autoload );

            } else {

                // On nettoie la valeur
                $value = sanitize_option($_option, $value);

                $this->_SetValueByNodeToArray($node, self::$_yaml_config, $value);
                self::$_flag = true;
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

        // On reconstruit l'option
        $_option = implode('_', $node);

        // On récupère l'ancien valeur
        if( $domain !== null )
            $old_value = mp_cache_sqlite( $domain.'_'.$_option );
        else
            $old_value = $this->get( $option, null, $domain );


        // On ajoute des filtres
        $value = apply_filters( 'pre_update_option_' . $_option, $value, $old_value, $_option, $domain );
        $value = apply_filters( 'pre_update_option', $value, $_option, $old_value, $domain );

        // On nettoie la valeur
        if( $domain == null )
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
        if ( $old_value !== $pre_value && $old_value !== null ){

            // On ajoute des actions
            do_action( 'update_option', $old_value, $value, $_option, $domain );

            if( $domain !== null ){

                mp_cache_sqlite( $domain.'_'.$_option, $value );
                
            } else {

                // On met la valeur à null ( pour éviter que si $node est un tableau , on se retrouve avec l'ancienne valeur plus la nouvelle )
                $this->_SetValueByNodeToArray($node, self::$_yaml_config, null);

                // On met à jour la nouvelle valeur
                $this->_SetValueByNodeToArray($node, self::$_yaml_config, $value);
                self::$_flag = true;
            }
        
            // On ajoute des actions
            do_action( 'updated_option_'.$_option, $old_value, $value, $_option, $domain );
            do_action( 'updated_option', $_option, $old_value, $value, $domain );

            return true;

        } else return false;
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

        // on efface les donées
        if( $domain !== null )
            $delete = ( null === mp_cache_sqlite( $domain.'_'.$_option, null ) ) ? true : false;
        else
            $delete = $this->update($option, null, $domain);

        // On ajoute des actions
        do_action( 'deleted_option_' . $_option, $_option, $domain, $delete, $domain );
        do_action( 'deleted_option', $_option, $domain, $delete, $domain );

        return $delete;
    }

}