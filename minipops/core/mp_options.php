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


// Les fonctions d'utilisations de la classe
function get_option( $option, $default = null, $domain = null ){

    return mp_cache_data('mp_options')->get($option, $default, $domain);
}

function add_option( $option, $value = null, $domain = null ){

    return mp_cache_data('mp_options')->add($option, $value, $domain);
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
                $value = null;
            break;
        case 'site_crons':
            if( ! is_array($value) )
                $value = false;
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
    private static $_options;
    private static $_flag = false;


    function __construct(){

        // On charge la table option dans la variable static
        $options = yaml_parse_file(MP_PAGES_DIR. '/site.yml', 0, null, apply_filters('mp_options_cache',CACHE) );
        self::$_options = !$options ? array():$options;

        // On ajoute un hook pour la sauvegarde du fichier
        add_action('after_setup_theme', function (){ mp_cache_data('mp_options')->save(); });
    }

    /**
    * Sauvegarde de la table option
    * @access private
    */
    public function save(){
        if( self::$_flag ){
            if( ! yaml_emit_file(MP_PAGES_DIR. '/site.yml', self::$_options) )
                _doing_it_wrong( __CLASS__, 'Error saving file configuration: site.yaml!');
            @chmod(MP_PAGES_DIR. '/site.yml', 0644);
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
            $node = 'site->'.$node;
        elseif( is_in( $domain, get_option('plugins->active_plugins') ) )
            $node = $domain.'->'.$node;
        else return false;

        // On créer le noeuds
        $nodes = array_map( 'trim', explode('->', $node));
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
        $pre = apply_filters( 'pre_option_' . $_option, false, $_option );
        if ( false !== $pre ) return $pre;

        // On récupère la variable selon le noeud
        $value = $this->_GetValueByNodeFromArray($node, self::$_options);

        // On nettoie la valeur
        $value = sanitize_option($_option, $value);

        // Si null on retourne la valeur par défaut
        if(is_null($value) ) return $default;

        // On filtre le résultat si la fonction de validation existe
        $type = 'is_'.$type;
        if( function_exists($type) ){
            if( $type($value) )
                return apply_filters( 'option_' . $_option, $value, $_option );
            else return $default;
        }

        return apply_filters( 'option_' . $_option, $value, $_option );
    }

    /**
    * Ajoute une option
    * @access public
    * @param $option    champ à insérer
    * @param $value     valeur à insérer
    * @param $domain    Domaine de recherche ( null: site, name: plugins actif et valid )
    */
    public function add( $option, $value = null, $domain = null ){

        $option = (string) $option;

        // Si table enregistrer on ne peut plus rien inclure
        if( self::$_flag === null ) return false;

        // On récupère le noeuds passer en option
        if( !$node = $this->_node($option, $domain) ) return false;

        // On reconstruit l'option
        $_option = implode('_', $node);

        // On ajoute des filtres
        $value = apply_filters( 'pre_add_option_' . $_option, $value, $_option );
        $value = apply_filters( 'pre_add_option', $value, $_option );

        // On nettoie la valeur
        $value = sanitize_option($_option, $value);

        if ( $value !== null && $this->get($option, null, $domain) === null ){

            // On ajoute des actions
            do_action( 'add_option', $_option, $value );

            // On insère la nouvelle valeur
            $this->_SetValueByNodeToArray($node, self::$_options, $value);
            self::$_flag = true;

            // On ajoute des actions
            do_action( 'added_option_'.$_option, $_option, $value );
            do_action( 'added_option', $_option, $value );

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
        $value = apply_filters( 'pre_update_option_' . $_option, $value, $old_value, $_option );
        $value = apply_filters( 'pre_update_option', $value, $_option, $old_value );

        // On nettoie la valeur
        $value = sanitize_option($_option, $value);

        if ( $old_value !== $value && $old_value !== null){

            // On ajoute des actions
            do_action( 'update_option', $old_value, $value, $_option );

            // On met la valeur à null ( pour éviter que si $node est un tableau , on se retrouve avec l'ancienne valeur plus la nouvelle )
            $this->_SetValueByNodeToArray($node, self::$_options, null);
            // On met à jour la nouvelle valeur
            $this->_SetValueByNodeToArray($node, self::$_options, $value);
            self::$_flag = true;

            // On ajoute des actions
            do_action( 'updated_option_'.$_option, $old_value, $value, $_option );
            do_action( 'updated_option', $_option, $old_value, $value );

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
        $pre = apply_filters( 'pre_delete_option_' . $_option, null, $_option, $domain );
        if ( null !== $pre ) return $pre;

        $delete = update_option( $option , null, $domain );

        // On ajoute des actions
        do_action( 'deleted_option_' . $_option, $_option, $domain, $delete );
        do_action( 'deleted_option', $_option, $domain, $delete );

        return $delete;
    }
}
