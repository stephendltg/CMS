<?php defined('ABSPATH') or die('No direct script access.');

/**
 * class YAML
 *
 * http://www.yaml.org/spec/1.2/spec.html
 * https://github.com/symfony/yaml/blob/master/Parser.php
 *
 * @package cms mini POPS
 * @subpackage yaml
 * @version 1
 */


/***********************************************/
/*       Fonction d'appel yaml                 */
/***********************************************/

/**
* Parser un flux texte yaml
* @access private
* @param $string  contenu du flux
*/
if (!function_exists('yaml_parse')) {
    function yaml_parse( $string, $pos = 0, $ndocs = null ){
        $yaml_parse = new YAML();
        return $yaml_parse->parse($string, $pos, $ndocs);
    }
}


/**
* Parser un fichier yaml
* @access private
* @param $path  chemin du fichier
*/
if (!function_exists('yaml_parse_file')) {
    function yaml_parse_file( $path, $pos = 0, $ndocs = null, $cached = false ){
        $yaml_parse = new YAML();
        return $yaml_parse->parse_file( $path, $pos, $ndocs, $cached );
    }
}


/**
* Parser une table vers yaml
* @access private
* @param $array  table de donnée
*/
if (!function_exists('yaml_emit')) {
    function yaml_emit( $data ){
        $yaml_parse = new YAML();
        return $yaml_parse->emit( $data );
    }
}


/**
* Parser une table vers fichier yaml
* @access private
* @param $array  table de donnée
*/
if (!function_exists('yaml_emit_file')) {
    function yaml_emit_file( $path, $data ){
        $yaml_parse = new YAML();
        return $yaml_parse->emit_file($path, $data);
    }
}



/***********************************************/
/*                  Class yaml                 */
/***********************************************/


class yaml {

    # ~

    const VERSION = '1.0.0';

    # ~



    /**#@+
    * @access private
    * @var array
    */
    private $TAB = '    ';
    private $encode_iterator;
    private $yaml_file_header = "# YAML - minipopS CMS.\n\n";


    # ~

    /***********************************************/
    /*              Functions private              */
    /***********************************************/

    # ~

    /**
    * delete yaml commment
    * @access private
    * @param $string
    */
    private function _delete_comment( $string ){

        $comment = strstr($string, '#', true);
        return (false !== $comment) ? $comment : $string;
    }



    /**
    * encode les valeurs d'une au format yaml
    * @access public
    * @param $data          string      Table des donnée à encoder
    * @param $intends       int         nbre d'intendation selon contexte
    * @param $lenght        array       Table des donnée à encoder
    */
    private function _yaml_encode_value( $value, $i, $lenght ){

        $indents = "\n". str_repeat($this->TAB , $i) . str_repeat(" " , $lenght);

        if(     $value === true  )                  return "true";
        elseif( $value === false )                  return "false";
        elseif( $value === null  )                  return "~";
        elseif( strpos($value, "\n") !== false )
            return '|'. $indents . str_replace("\n", "$indents", $value);
        elseif( strlen($value) > 70 )
            return '>'. $indents . str_replace( "\n", "$indents", wordwrap($value, 70, "\n") );

        return $value;
    }



    /**
    * encode une table au format yaml
    * @access private
    * @param $data   array      Table des donnée à encoder
    */
    function _yaml_encode( $data ){

        static $i = 0;

        foreach( $data as $k => $v ) {

            // on nettoie le clé uniquement si c'est une chaine de caractère
            $k = is_string($k) ? sanitize_key($k) : $k;

            // Si v est un tableau et qu'il est vide alors c'est une valeur null
            if( is_array($v) && empty($v) )   $v = null;

            if (  is_array($v) ){
                if( is_integer($k) )
                    $this->encode_iterator .= str_repeat($this->TAB , $i) .'- '. json_encode($v) ."\n";
                else {
                    $this->encode_iterator .= str_repeat($this->TAB , $i) . $k .': '."\n";
                    $i++;
                    $this->_yaml_encode($v);
                    $i--;
                }
            } else {
                if( is_integer($k) )
                    $this->encode_iterator .= str_repeat($this->TAB , $i) .'- '. $this->_yaml_encode_value($v, $i, 2) ."\n";
                else
                    $this->encode_iterator .= str_repeat($this->TAB , $i) . $k .': '. $this->_yaml_encode_value($v, $i, strlen($k)+3) ."\n";
            }
        }

        return $this->encode_iterator;
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
    * Insérer une valeur selon un noeud yaml dans une table
    * @access private
    * @param $array_keys array  Table des noeuds
    * @param $yaml_data  array  Table de retour
    * @param $value  string/array  Données à insérer dans la table
    */
    private function SetValueByNodeToArray ( $array_keys, &$yaml_data, $value = '' ){

        if( !is_bool($value) && !is_null($value) && empty($value) ) return;
        $dump_node = array();
        $ref = &$dump_node;
        foreach ($array_keys as $k)  $ref = &$ref[$k];
        $ref = $value;
        $yaml_data = $this->_array_insert($yaml_data, $dump_node);
    }

    /**
    * Recherche le nombre de document yaml
    * @access private
    * @param $pos position du premier document recherché
    * @param $ndocs nombre de document retourné
    */
    private function _parse_ndocs( $text, $offset, $lenght  ){

        if( preg_match_all('/^[-]{3}(.*?)[\n]+[.]{3}/mis', $text , $match ) )
            return array_slice( $match[1] , $offset, $lenght, true );
        else
            return array();
    }


    /**
    * Parse un document yaml sous forme de tableau
    * @access private
    * @param $yaml_data  string à parser
    */
    private function _parse_yaml_data( $yaml_data){

        // On extrait la première ligne du document
        $line_1 = trim( strstr($yaml_data, "\n", true) );

        // Les données du document yaml
        $data = strstr($yaml_data, "\n");

        // On init $yaml_data pour la table de retour
        $yaml_data = array();

        /**************************************************************/
        /*      GESTION DE BLOCK ITERATOR EN RACINE DE DOCUMENT       */
        /**************************************************************/

        // On supprime le commentaire si présent
        $line_1 = $this->_delete_comment($line_1);

        // Block iterator multi-ligne
        if( false !== strpos($line_1, '|') )
            return $data;

        // Block iterator multi-ligne type code source
        if( false !== strpos($line_1, '>') )
            return trim( preg_replace( '/[\r\n\t ]+/', ' ', $data ) );

        /**********************************/
        /*      ON PREPARE THE LOOP       */
        /**********************************/

        // Extraire data ligne par ligne ( on ajoute un END au document pour garantir une bonne fouille )
        $data = explode( "\n", $data."\n#END");

        // nbr lignes dans les données
        $count_data = count($data);

        // Var pour block iterator
        $init_block_scalar = array( 'enable' => false, 'type' => '', 'position' => 0, 'value' => '' );
        $block_scalar = $init_block_scalar;

        // init pour les noeuds
        $yaml_node = array();

        /***********************/
        /*      THE LOOP       */
        /***********************/

        // var de comptage de lignes
        $i = 0;

        // On lance la fouille dans les données
        for ($i=0; $i < $count_data ; $i++) {

            // THE LINE
            $line = $data[$i];

            /***********************/
            /* BLOCK ITERATOR ONLY */
            /***********************/

            // On stock les lignes pour les blocks iterator
            if( $block_scalar['enable'] === true ){

                // On remplace les tablulations par des espaces
                $line = str_replace("\t", $this->TAB , $line);

                // On conserve les lignes vides
                if( strlen( trim($line) ) === 0 )
                    $block_scalar['value'] .= "\n";

                // On récupère uniquement les lignes intendées
                elseif( preg_match('/^[ ]{'.$block_scalar['position'].'}(.*)/', $line, $match ) )
                    $block_scalar['value'] .= $match[1]."\n";

                else{

                    // On revient d'une ligne pour checker son contenu
                    $i--;

                    // On prépare les données
                    $block_scalar['value'] = $block_scalar['type'] === '>' ? trim( preg_replace('/[\n ]+/', ' ', $block_scalar['value']) ) : $block_scalar['value'];

                    // On stock les données dans la table
                    $this->SetValueByNodeToArray( $yaml_node, $yaml_data, $block_scalar['value'] );

                    // init du block
                    $block_scalar = $init_block_scalar;

                }

            }

            /***********************/
            /*   INLINE ITERATOR   */
            /***********************/

            // On recherche les iterators ( tableau ou liste)
            elseif( preg_match('/^([ \t]*)([\/\-\w]+)[ \t]*:[ \t]*(.*)/', $line, $match ) || preg_match('/^([ \t]*)[\-]+[ \t]*(.*)/', $line, $match ) ){

                // L'intends du tableau de retour
                $indents = (int) round ( strlen( str_replace("\t", $this->TAB , $match[1]) )/4 );

                // On prépare les valeurs
                if( isset($match[3]) ) {

                    $key = $match[2];
                    // On supprime le commentaire si présent
                    $value = $this->_delete_comment($match[3]);
                }
                else {

                    // On récupère la clé en cours de l'iterator type liste
                    $key = isset($yaml_node[$indents]) ? $yaml_node[$indents]+1 : 0;
                    // On supprime le commentaire si présent
                    $value = $this->_delete_comment($match[2]);
                    // On recherche si table tel que "- label : texte"
                    if( preg_match('/^([\/\-\w]+)[ \t]*:[ \t]*(.*)/', $value, $match) )
                        $value = '{"'. $match[1] .'":"'. $match[2].'"}';
                }

                // On construire le noeud
                $yaml_node[$indents] = $key;
                $yaml_node = array_slice( $yaml_node, 0, $indents+1, true);

                // On cherche si une variable yaml existe
                /*
                if( preg_match('/^&(\w+)[ \t]*(.*)/', $value, $match) ){
                    $yaml_var = array($match[1] => $yaml_node);
                    $value    = isset($match[2]) ? $match[2] : '';
                    var_dump($yaml_var);
                }
                */

                // On checked si block iterator
                if( $value && '>' === $value[0] )
                    $block_scalar = array( 'enable' => true, 'type' => '>', 'position' => strpos($line, '>'), 'value' => '' );

                elseif( $value && '|' === $value[0] )
                    $block_scalar = array( 'enable' => true, 'type' => '|', 'position' => strpos($line, '|'), 'value' => '' );

                else{

                    // On decode la valeur
                    $value = trim($value);
                    $value = json_decode($value, true) ?: $value;
                    if( $value === 'false' )  $value = false;
                    elseif( $value === '~' )      $value = null;
                    elseif( $value === 'null' )   $value = null;

                    // On sauvegarde le valeur selon le noeuds trouvé
                    $this->SetValueByNodeToArray( $yaml_node, $yaml_data, $value );
                }
            }
        }

        return $yaml_data;
    }



    # ~

    /***********************************************/
    /*              Public functions               */
    /***********************************************/

    # ~


    /**
    * parse flux yaml
    * @access public
    * @param $text  string      flux texte
    * @param $pos   int         numero de document ou commencer la recherche
    * @param $ndocs int         Nombre de documents recherche
    */
    public function parse( $text, $pos = 0, $ndocs = null ){

        if( !is_string($text) )                      return false;
        if( !is_integer($pos) )                      return false;
        if( !is_integer($ndocs) && $ndocs !== null ) return false;

        // On encode en utf-8
        $text = encode_utf8( $text );

        // On supprime les caractères invisibles
        $text = esc_attr($text);

        // On supprime les retour chariots
        $text = str_replace(array("\r\n", "\r"), "\n", $text);

        if ( strlen($text) == 0 ) return array();

        // On parse chaque documents retenus
        $_ndocs = $this->_parse_yaml_data( implode($this->_parse_ndocs( $text, $pos, $ndocs ) ) );

        return $_ndocs;
    }


    /**
    * parse fichier yaml
    * @access public
    * @param $path   string      Chemin du fichier
    * @param $pos    int         numero de document ou commencer la recherche
    * @param $ndocs  int         Nombre de documents recherche
    * @param $cached bool        Activation du cache
    */
    public function parse_file( $path, $pos = 0, $ndocs = null, $cached = false ){

        if( is_readable($path) ){

            $cache = dirname($path).'/.~'.basename($path);

            if( $cached
                && is_readable($cache)
                && ( filemtime($path) === filemtime($cache) )
                && $text = file_get_contents($cache)
                ){

                return @unserialize($text);
            }
            elseif( $text = file_get_contents($path) ){

                $text = yaml_parse( $text, $pos, $ndocs );

                if( $cached ){
                    @file_put_contents($cache, serialize($text), LOCK_EX);
                    @touch($cache, filemtime($path) );
                    @chmod($cache, 0644);
                }
                return $text;

            } else return false;

        } else return false;
    }


    /**
    * créer une représentation yaml
    * @access public
    * @param $data   array/string      Table des donnée ou texte à encoder
    */
    public function emit( $data ){

        if( is_array($data) ){
            $this->encode_iterator = '';
            $yaml_emit = $this->_yaml_encode( $data );
            if ( strlen($yaml_emit) == 0 ) return false;
            return "---\n$yaml_emit...\n";

        } elseif( is_string($data) ){

            if ( strlen($data) == 0 ) return false;
            if( strpos($data, "\n") !== false )
                return "--- |\n$data\n...\n";
            else
                return "---  >\n" . wordwrap($data, 70, "\n") . "\n...\n";
        }
        else return false;
    }


    /**
    * créer une représentation yaml dans un fichier
    * @access public
    * @param $data   array/string      Table des donnée ou texte à encoder
    */
    public function emit_file( $path, $data ){

        if( $yaml_emit = $this->emit($data) )
            return @file_put_contents($path, $this->yaml_file_header.$yaml_emit, LOCK_EX);
        else return false;
    }
}


